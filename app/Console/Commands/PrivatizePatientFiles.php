<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Moves the patient documents 1.x stored on the public disk to the private one. The public disk is
 * published by the storage:link symlink, so the web server served them to anyone holding the URL,
 * and active content (an HTML or SVG upload) ran in the dashboard's origin.
 *
 * Each file keeps its path, so files.url stays valid. The command walks the folder rather than the
 * rows: 1.x stored an upload before its row inside a transaction, so a rolled-back save or a deleted
 * patient left files no row points to, and those are patient data as well.
 */
#[Signature('patient-files:privatize {--force : Move the files instead of only listing them}')]
#[Description('List the patient files 1.x left on the public disk; --force moves them to the private disk')]
class PrivatizePatientFiles extends Command
{
    /** The disk and folder of the 1.x uploads. Staff photos and other reference documents on it stay public. */
    private const string PUBLIC_DISK = 'public';

    private const string FOLDER = 'patient_files';

    private const string MOVE = 'move';

    private const string ALREADY_COPIED = 'delete the public copy: the private disk holds the same file';

    private const string CONFLICT = 'keep: the private disk holds a different file at this path';

    public function handle(): int
    {
        $public = Storage::disk(self::PUBLIC_DISK);
        $private = Storage::disk(File::DISK);

        $plan = $this->plan($public, $private);

        if ($plan === []) {
            $this->components->info('No patient file is left on the public disk.');

            $this->reportLostRows($public, $private);

            return self::SUCCESS;
        }

        $rows = File::query()->whereIn('url', array_keys($plan))->pluck('id', 'url');

        $this->table(['File', 'Size', 'Row', 'Action'], array_map(
            fn (string $path, string $action): array => [$path, (string) $public->size($path), (string) ($rows[$path] ?? '—'), $action],
            array_keys($plan),
            $plan,
        ));

        $movable = array_keys(array_filter($plan, fn (string $action): bool => $action !== self::CONFLICT));

        if (! $this->option('force')) {
            $this->components->warn('Dry run: '.$this->files(count($movable)).' would be moved to the private disk. Run it with --force to move them.');

            $this->reportLostRows($public, $private);

            return self::SUCCESS;
        }

        $moved = array_filter($movable, fn (string $path): bool => $this->move($path, $public, $private));
        $left = count($plan) - count($moved);

        $this->components->info('Moved '.$this->files(count($moved)).' to the private disk.');

        if ($left > 0) {
            $this->components->error($this->files($left).' left on the public disk: resolve them by hand, then run the command again.');
        }

        $this->reportLostRows($public, $private);

        return $left === 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<string, string> path => action
     */
    private function plan(FilesystemAdapter $public, FilesystemAdapter $private): array
    {
        $plan = [];

        foreach ($public->allFiles(self::FOLDER) as $path) {
            $plan[$path] = match (true) {
                ! $private->exists($path) => self::MOVE,
                $this->sameFile($path, $public, $private) => self::ALREADY_COPIED,
                default => self::CONFLICT,
            };
        }

        return $plan;
    }

    /**
     * Copies the file (as a stream: scans can be large), checks the copy, then deletes the public
     * one. A copy that differs is deleted again and the public file stays.
     */
    private function move(string $path, FilesystemAdapter $public, FilesystemAdapter $private): bool
    {
        if (! $private->exists($path)) {
            $stream = $public->readStream($path);

            if ($stream === null) {
                return false;
            }

            try {
                $written = $private->writeStream($path, $stream);
            } finally {
                fclose($stream);
            }

            if (! $written || ! $this->sameFile($path, $public, $private)) {
                $private->delete($path);

                return false;
            }
        }

        return $public->delete($path);
    }

    private function sameFile(string $path, FilesystemAdapter $public, FilesystemAdapter $private): bool
    {
        return $public->size($path) === $private->size($path)
            && $public->checksum($path) === $private->checksum($path);
    }

    /**
     * Rows whose file is on neither disk: lost before the move, the command cannot bring them back.
     */
    private function reportLostRows(FilesystemAdapter $public, FilesystemAdapter $private): void
    {
        $lost = File::query()->select(['id', 'name', 'url'])->lazyById()
            ->reject(fn (File $file): bool => $private->exists($file->url) || $public->exists($file->url))
            ->map(fn (File $file): array => [(string) $file->id, $file->name, $file->url])
            ->values()
            ->all();

        if ($lost !== []) {
            $this->components->warn(count($lost).' '.Str::plural('row', count($lost)).' of the files table point to no file on either disk:');
            $this->table(['Row', 'Name', 'Path'], $lost);
        }
    }

    private function files(int $count): string
    {
        return $count.' '.Str::plural('file', $count);
    }
}
