<?php

namespace App\Services;

use App\Models\WardRoom;
use Illuminate\Support\Facades\DB;

class WardRoomService
{
    /**
     * @param array<string, mixed> $validated
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
     * @param WardRoom $room
     * @param array<string, mixed> $validated
     * @return WardRoom
     */
    public function update(WardRoom $room, array $validated): WardRoom
    {
        return DB::transaction(function () use ($room, $validated) {
            $room->update([
                'room_number' => $validated['room_number'],
            ]);

            $submittedIds = [];

            foreach ($validated['bed_numbers'] as $bed) {
                if (isset($bed['id'])) {
                    $room->beds()->updateOrCreate(
                        ['id' => $bed['id']],
                        ['bed_number' => $bed['bed_number']]
                    );
                    $submittedIds[] = $bed['id'];
                } else {
                    $newBed = $room->beds()->create([
                        'bed_number' => $bed['bed_number'],
                    ]);
                    $submittedIds[] = $newBed->id;
                }
            }

            $room->beds()->whereNotIn('id', $submittedIds)->delete();

            return $room->refresh();
        });
    }

    /**
     * @param WardRoom $room
     * @return void
     */
    public function destroy(WardRoom $room): void
    {
        DB::transaction(function () use ($room) {
            $room->beds()->delete();
            $room->delete();
        });
    }
}
