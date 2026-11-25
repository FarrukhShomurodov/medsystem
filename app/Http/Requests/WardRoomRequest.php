<?php

namespace App\Http\Requests;

use App\Models\WardRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The ward room form. The create form posts the bed numbers (bed_numbers[]); the edit form posts
 * each bed with its id (bed_numbers[i][id], bed_numbers[i][bed_number]), an empty id for a bed
 * added on the form. An id must be one of this room's beds: a bed deleted or moved since the form
 * was opened is refused instead of being silently dropped.
 */
class WardRoomRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'room_number' => ['required', 'string', 'max:255'],
            'bed_numbers' => ['required', 'array', 'min:1'],
        ];
        $room = $this->route('room');

        if (! $room instanceof WardRoom) {
            return [...$rules, 'bed_numbers.*' => ['required', 'string', 'max:255', 'distinct']];
        }

        return [
            ...$rules,
            'bed_numbers.*' => ['required', 'array:id,bed_number'],
            'bed_numbers.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('room_beds', 'id')->where('ward_room_id', $room->id)],
            'bed_numbers.*.bed_number' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'room_number' => 'номер палаты',
            'bed_numbers' => 'койки',
            'bed_numbers.*' => 'номер койки',
            'bed_numbers.*.id' => 'койка',
            'bed_numbers.*.bed_number' => 'номер койки',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bed_numbers.*.id.exists' => 'Койка уже удалена или относится к другой палате. Обновите страницу.',
        ];
    }
}
