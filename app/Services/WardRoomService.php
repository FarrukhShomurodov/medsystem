<?php

namespace App\Services;

use App\Models\WardRoom;
use Illuminate\Support\Facades\DB;

class WardRoomService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function store(array $validated): WardRoom
    {
        return DB::transaction(function () use ($validated) {
            $wardRoom = WardRoom::query()->create([
                'room_number' => $validated['room_number'],
            ]);

            foreach ($validated['bed_numbers'] as $bedNumber) {
                $wardRoom->beds()->create([
                    'bed_number' => $bedNumber,
                ]);
            }

            return $wardRoom;
        });
    }

    /**
     * Saves the beds as the form lists them: a bed with an id is renamed (WardRoomRequest has
     * checked it is one of this room's), one without is added, and the beds the form no longer
     * lists are deleted. Only the ids of rows saved here are kept, never a posted one.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(WardRoom $room, array $validated): WardRoom
    {
        return DB::transaction(function () use ($room, $validated) {
            $room->update([
                'room_number' => $validated['room_number'],
            ]);

            $savedIds = [];

            foreach ($validated['bed_numbers'] as $bed) {
                $row = isset($bed['id']) ? $room->beds()->whereKey($bed['id'])->firstOrFail() : $room->beds()->make();
                $row->fill(['bed_number' => $bed['bed_number']])->save();
                $savedIds[] = $row->id;
            }

            $room->beds()->whereNotIn('id', $savedIds)->delete();

            return $room->refresh();
        });
    }

    public function destroy(WardRoom $room): void
    {
        DB::transaction(function () use ($room): void {
            $room->beds()->delete();
            $room->delete();
        });
    }
}
