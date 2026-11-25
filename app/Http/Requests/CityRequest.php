<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CityRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'название',
            'region_id' => 'область',
        ];
    }

    /**
     * @return array{name: string, region_id: int}
     */
    public function city(): array
    {
        return [
            'name' => $this->string('name')->value(),
            'region_id' => $this->integer('region_id'),
        ];
    }
}
