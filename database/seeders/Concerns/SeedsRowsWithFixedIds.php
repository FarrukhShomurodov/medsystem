<?php

namespace Database\Seeders\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Some reference rows are still looked up by id in application code (main warehouse 1,
 * document 1, staff position 1, warehouse types 1-3, service type 1), so they must get the
 * same id on every database, including test databases whose sequences have already moved on.
 */
trait SeedsRowsWithFixedIds
{
    /**
     * Create the row with the given id unless it already exists; an existing row is left as it is.
     *
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $model
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    protected function firstOrCreateWithId(string $model, int $id, array $attributes): Model
    {
        return $model::query()->find($id) ?? $model::query()->forceCreate(['id' => $id, ...$attributes]);
    }

    /**
     * Move the table's id sequence past explicitly inserted ids so later inserts do not collide.
     */
    protected function syncIdSequence(string $table): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::select(
            "select setval(pg_get_serial_sequence(?, 'id'), greatest(coalesce(max(id), 0), 1)) from {$table}",
            [$table],
        );
    }
}
