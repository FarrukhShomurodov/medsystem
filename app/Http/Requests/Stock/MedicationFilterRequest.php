<?php

namespace App\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The filter form of the medication list. An id that is not one is refused here: passed on as it
 * came, PostgreSQL rejected it for the bigint column, a 500 instead of an error message.
 * Authorization is the route's: it requires list_medications.
 */
class MedicationFilterRequest extends FormRequest
{
    /**
     * A refused filter goes back to the unfiltered list, not to the previous URL: that is the
     * filtered list itself once the filter was submitted twice, a redirect loop.
     *
     * @var string
     */
    protected $redirectRoute = 'medications.index';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'active_substance_name' => ['nullable', 'string', 'max:255'],
            // exists runs only once integer has passed, so it never sends PostgreSQL a non-number.
            'group_id' => ['nullable', 'integer', 'exists:medication_groups,id'],
            'manufacturer_id' => ['nullable', 'integer', 'exists:manufacturers,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'active_substance_name' => 'активное вещество',
            'group_id' => 'группа',
            'manufacturer_id' => 'производитель',
        ];
    }

    public function activeSubstance(): ?string
    {
        return $this->filled('active_substance_name') ? $this->string('active_substance_name')->value() : null;
    }

    public function groupId(): ?int
    {
        return $this->filled('group_id') ? $this->integer('group_id') : null;
    }

    public function manufacturerId(): ?int
    {
        return $this->filled('manufacturer_id') ? $this->integer('manufacturer_id') : null;
    }
}
