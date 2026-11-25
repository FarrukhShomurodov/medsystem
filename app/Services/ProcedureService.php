<?php

namespace App\Services;

use App\Models\Procedure;

class ProcedureService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): Procedure
    {
        return Procedure::query()->create($validated);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function update(Procedure $procedure, array $validated): Procedure
    {
        $procedure->update($validated);

        return $procedure;
    }

    public function destroy(Procedure $procedure): void
    {
        $procedure->delete();
    }
}
