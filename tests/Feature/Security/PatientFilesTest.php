<?php

use App\Models\Admin;
use App\Models\File;
use App\Models\Patient;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\seed;

beforeEach(function (): void {
    seed(PermissionSeeder::class);
    Storage::fake(File::DISK);
    // Where 1.x stored patient uploads, published by the storage:link symlink.
    Storage::fake('public');

    $this->patient = Patient::factory()->create();
});

/**
 * The patient edit form, re-submitted with the given uploads.
 *
 * @param  array<int, UploadedFile>  $files
 * @return array<string, mixed>
 */
function patientFormWithFiles(Patient $patient, array $files): array
{
    return $patient->only([
        'first_name', 'last_name', 'birth_date', 'citizenship', 'passport_series', 'phone',
    ]) + ['gender' => $patient->gender->value, 'passport_validity_period' => now()->addYears(5)->toDateString(), 'files' => $files];
}

function uploadedFileWithContent(string $clientName, string $content): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'upload');
    file_put_contents($path, $content);

    return new UploadedFile($path, $clientName, test: true);
}

function attachPatientFile(Patient $patient, string $disk = File::DISK, string $name = 'scan.pdf'): File
{
    $path = 'patient_files/'.$name;
    Storage::disk($disk)->put($path, '%PDF-1.4 scan of '.$patient->last_name);

    return $patient->files()->create(['name' => $name, 'url' => $path]);
}

it('stores uploaded patient documents on the private disk', function (): void {
    $editor = Admin::factory()->create()->givePermissionTo('edit_patients');

    // The editor may open neither the card nor the list, so the save returns to the edit form.
    actingAs($editor, 'admin')
        ->from(route('patients.edit', $this->patient))
        ->put("/dashboard/patients/{$this->patient->id}", patientFormWithFiles($this->patient, [
            UploadedFile::fake()->create('analysis.pdf', 120, 'application/pdf'),
            UploadedFile::fake()->image('xray.png'),
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('patients.edit', $this->patient));

    $files = $this->patient->files()->get();

    expect($files->pluck('name')->all())->toBe(['analysis.pdf', 'xray.png']);
    $files->each(function (File $file): void {
        Storage::disk(File::DISK)->assertExists($file->url);
        Storage::disk('public')->assertMissing($file->url);
    });
});

it('rejects script, oversized and too many uploads', function (array $files, string $errorKey): void {
    actingAs(Admin::factory()->mainAdmin()->create(), 'admin')
        ->put("/dashboard/patients/{$this->patient->id}", patientFormWithFiles($this->patient, $files))
        ->assertSessionHasErrors($errorKey);

    expect($this->patient->files()->count())->toBe(0)
        ->and(Storage::disk(File::DISK)->allFiles())->toBeEmpty();
})->with([
    'a PHP script' => [fn (): array => [UploadedFile::fake()->create('shell.php', 1, 'application/x-php')], 'files.0'],
    // A real temporary file: "mimes" sniffs the content, the fake would report the type of its name.
    'a script renamed to .pdf' => [fn (): array => [uploadedFileWithContent('scan.pdf', '<?php system($_GET["c"]);')], 'files.0'],
    'a file over 10 MB' => [fn (): array => [UploadedFile::fake()->create('scan.pdf', 10241, 'application/pdf')], 'files.0'],
    'more than 10 files' => [fn (): array => array_map(fn (int $i) => UploadedFile::fake()->create("scan-{$i}.pdf", 1, 'application/pdf'), range(1, 11)), 'files'],
]);

it('serves a patient file only to signed-in staff who may see the patient', function (): void {
    $file = attachPatientFile($this->patient);
    $url = route('patients.files.show', [$this->patient, $file]);

    $this->get($url)->assertRedirectToRoute('dashboard.login');

    actingAs(Admin::factory()->role('Кассир')->create(), 'admin')->get($url)->assertForbidden();

    $response = actingAs(Admin::factory()->create()->givePermissionTo('view_patients'), 'admin')->get($url);

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename=scan.pdf');
    expect($response->streamedContent())->toBe('%PDF-1.4 scan of '.$this->patient->last_name);
});

it('reads patient files from the private disk only', function (): void {
    // A 1.x upload left on the public disk is not served until patient-files:privatize moved it.
    $file = attachPatientFile($this->patient, 'public');
    $url = route('patients.files.show', [$this->patient, $file]);
    $admin = Admin::factory()->mainAdmin()->create();

    actingAs($admin, 'admin')->get($url)->assertNotFound();

    artisan('patient-files:privatize', ['--force' => true])->assertSuccessful();

    actingAs($admin, 'admin')->get($url)->assertOk();
});

it('has no framework route that serves or writes the private disk', function (): void {
    // 'serve' => true registered GET and PUT storage/{path} for the private disk, outside the web
    // group and checked only by an APP_KEY-signed URL: whoever held the key could read or replace
    // any patient document without an account.
    $routes = app('router')->getRoutes();
    $file = attachPatientFile($this->patient);

    expect($routes->hasNamedRoute('storage.local'))->toBeFalse()
        ->and($routes->hasNamedRoute('storage.local.upload'))->toBeFalse()
        ->and(collect($routes->getRoutes())->map->uri()->filter(fn (string $uri): bool => str_starts_with($uri, 'storage/'))->all())->toBe([]);

    $this->get('/storage/'.$file->url)->assertNotFound();
    $this->put('/storage/'.$file->url.'?upload=1', ['x' => 'replaced'])->assertNotFound();
    Storage::disk(File::DISK)->assertExists($file->url);
});

it('downloads, rather than renders, a file type the browser could run', function (): void {
    Storage::disk(File::DISK)->put('patient_files/report.html', '<script>alert(1)</script>');
    $file = $this->patient->files()->create(['name' => 'report.html', 'url' => 'patient_files/report.html']);

    actingAs(Admin::factory()->mainAdmin()->create(), 'admin')
        ->get(route('patients.files.show', [$this->patient, $file]))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename=report.html')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('deletes a file through its patient, from the database and the disk', function (): void {
    $file = attachPatientFile($this->patient);
    $editor = Admin::factory()->create()->givePermissionTo('edit_patients');

    actingAs($editor, 'admin')
        ->deleteJson(route('api.patients.files.destroy', [$this->patient, $file]))
        ->assertNoContent();

    expect(File::query()->count())->toBe(0);
    Storage::disk(File::DISK)->assertMissing($file->url);
});

it('cannot delete a file of another patient or by its path', function (): void {
    $file = attachPatientFile($this->patient);
    $otherPatient = Patient::factory()->create();
    $admin = Admin::factory()->mainAdmin()->create();

    actingAs($admin, 'admin')
        ->deleteJson("/api/patients/{$otherPatient->id}/files/{$file->id}")
        ->assertNotFound();
    actingAs($admin, 'admin')
        ->deleteJson('/api/delete/file/patient_files/scan.pdf')
        ->assertNotFound();
    actingAs($admin, 'admin')
        ->deleteJson("/api/patients/{$this->patient->id}/files/..%2Fpatient_files%2Fscan.pdf")
        ->assertNotFound();
    actingAs(Admin::factory()->create()->givePermissionTo('view_patients'), 'admin')
        ->deleteJson(route('api.patients.files.destroy', [$this->patient, $file]))
        ->assertForbidden();

    expect($file->fresh())->not->toBeNull();
    Storage::disk(File::DISK)->assertExists($file->url);
});

/*
| patient-files:privatize moves the uploads 1.x stored on the public disk, which the web server
| served to anyone holding the URL, to the private disk.
*/

it('only lists the patient files left on the public disk unless --force is given', function (): void {
    $file = attachPatientFile($this->patient, 'public', 'old-scan.pdf');

    artisan('patient-files:privatize')
        ->expectsTable(['File', 'Size', 'Row', 'Action'], [
            ['patient_files/old-scan.pdf', (string) Storage::disk('public')->size($file->url), (string) $file->id, 'move'],
        ])
        ->expectsOutputToContain('Dry run: 1 file would be moved to the private disk. Run it with --force to move them.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists($file->url);
    Storage::disk(File::DISK)->assertMissing($file->url);
});

it('moves every file of the folder to the same path on the private disk, also those without a row', function (): void {
    // 1.x stored the upload before its row inside a transaction: a rolled-back save, or a deleted
    // patient, left files no row points to.
    $file = attachPatientFile($this->patient, 'public', 'old-scan.pdf');
    $content = Storage::disk('public')->get($file->url);
    Storage::disk('public')->put('patient_files/orphan.png', 'PNG of a deleted patient');
    // Staff photos are reference documents and stay public.
    Storage::disk('public')->put('staff_photo/doctor.jpg', 'JPEG');

    artisan('patient-files:privatize', ['--force' => true])
        ->expectsOutputToContain('Moved 2 files to the private disk.')
        ->assertSuccessful();

    expect(Storage::disk(File::DISK)->get($file->url))->toBe($content)
        ->and(Storage::disk(File::DISK)->get('patient_files/orphan.png'))->toBe('PNG of a deleted patient')
        ->and(Storage::disk('public')->allFiles('patient_files'))->toBe([])
        ->and($file->fresh()->url)->toBe('patient_files/old-scan.pdf');
    Storage::disk('public')->assertExists('staff_photo/doctor.jpg');

    artisan('patient-files:privatize', ['--force' => true])
        ->expectsOutputToContain('No patient file is left on the public disk.')
        ->assertSuccessful();
});

it('finishes a move that stopped after the copy, and never overwrites a different private file', function (): void {
    Storage::disk('public')->put('patient_files/copied.pdf', 'same content');
    Storage::disk(File::DISK)->put('patient_files/copied.pdf', 'same content');
    Storage::disk('public')->put('patient_files/clash.pdf', 'the public one');
    Storage::disk(File::DISK)->put('patient_files/clash.pdf', 'the private one');

    artisan('patient-files:privatize', ['--force' => true])
        ->expectsOutputToContain('Moved 1 file to the private disk.')
        ->expectsOutputToContain('1 file left on the public disk')
        ->assertFailed();

    Storage::disk('public')->assertMissing('patient_files/copied.pdf');
    expect(Storage::disk(File::DISK)->get('patient_files/copied.pdf'))->toBe('same content')
        ->and(Storage::disk('public')->get('patient_files/clash.pdf'))->toBe('the public one')
        ->and(Storage::disk(File::DISK)->get('patient_files/clash.pdf'))->toBe('the private one');
});

it('reports the rows whose file is on neither disk', function (): void {
    $lost = $this->patient->files()->create(['name' => 'lost.pdf', 'url' => 'patient_files/lost.pdf']);

    artisan('patient-files:privatize')
        ->expectsOutputToContain('No patient file is left on the public disk.')
        ->expectsTable(['Row', 'Name', 'Path'], [[(string) $lost->id, 'lost.pdf', 'patient_files/lost.pdf']])
        ->assertSuccessful();
});

it('ships web server rules that never serve the folder 1.x stored patient files in', function (): void {
    expect(file_get_contents(public_path('.htaccess')))->toContain('RewriteRule ^storage/patient_files/ - [F,L]')
        ->and(file_get_contents(base_path('docker/nginx/default.conf')))->toContain('location ^~ /storage/patient_files/ { deny all; }');
});
