<?php

use App\Models\Manufacturer;
use App\Models\Medication;
use Illuminate\Support\Facades\DB;

it('refuses to delete reference data that is still in use, with a message instead of a 500', function (): void {
    $medication = Medication::factory()->create();
    $manufacturer = $medication->manufacturer;

    // PostgreSQL aborts the surrounding test transaction after the failed DELETE.
    // A savepoint around the request keeps the connection usable for the checks below.
    DB::beginTransaction();

    $this->actingAs(adminWithPermissions('list_manufacturers', 'delete_manufacturers'), 'admin')
        ->from(route('manufacturers.index'))
        ->delete(route('manufacturers.destroy', $manufacturer))
        ->assertRedirect(route('manufacturers.index'))
        ->assertSessionHas('error', 'Запись нельзя удалить: она используется в других разделах.');

    DB::rollBack();

    expect(Manufacturer::query()->whereKey($manufacturer->id)->exists())->toBeTrue();
});

it('answers a restricted delete on an AJAX route with a JSON 409', function (): void {
    $medication = Medication::factory()->create();

    DB::beginTransaction();

    $this->actingAs(adminWithPermissions('list_manufacturers', 'delete_manufacturers'), 'admin')
        ->deleteJson(route('manufacturers.destroy', $medication->manufacturer))
        ->assertStatus(409)
        ->assertJson(['message' => 'Запись нельзя удалить: она используется в других разделах.']);

    DB::rollBack();
});

it('still deletes reference data that nothing points to', function (): void {
    $manufacturer = Manufacturer::factory()->create();

    $this->actingAs(adminWithPermissions('list_manufacturers', 'delete_manufacturers'), 'admin')
        ->delete(route('manufacturers.destroy', $manufacturer))
        ->assertRedirect(route('manufacturers.index'))
        ->assertSessionHas('success');

    expect(Manufacturer::query()->whereKey($manufacturer->id)->exists())->toBeFalse();
});

it('shows flash messages from the layout on every dashboard page', function (): void {
    $this->actingAs(adminWithPermissions('list_manufacturers'), 'admin')
        ->withSession(['error' => 'Проверка сообщения'])
        ->get(route('manufacturers.index'))
        ->assertOk()
        ->assertSeeText('Проверка сообщения');
});
