<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WardRoomRequest extends FormRequest
{
    /**
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'room_number' => 'required|string',
            'bed_numbers' => 'required|array',
        ];
    }
}
