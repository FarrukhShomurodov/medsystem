<?php

namespace App\Http\Requests;

use App\Models\ServiceGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The service group form. The create form posts the subgroup names (sub_groups[]); the edit form
 * posts each subgroup with its id (sub_groups[i][id], sub_groups[i][name]), an empty id for a
 * subgroup added on the form. An id must be one of this group's subgroups: one deleted or moved
 * since the form was opened is refused instead of being silently dropped.
 */
class ServiceGroupRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'sub_groups' => ['required', 'array', 'min:1'],
        ];
        $group = $this->route('group');

        if (! $group instanceof ServiceGroup) {
            return [...$rules, 'sub_groups.*' => ['required', 'string', 'max:255', 'distinct']];
        }

        return [
            ...$rules,
            'sub_groups.*' => ['required', 'array:id,name'],
            'sub_groups.*.id' => ['nullable', 'integer', 'distinct', Rule::exists('service_sub_groups', 'id')->where('service_group_id', $group->id)],
            'sub_groups.*.name' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'название',
            'sub_groups' => 'подгруппы',
            'sub_groups.*' => 'название подгруппы',
            'sub_groups.*.id' => 'подгруппа',
            'sub_groups.*.name' => 'название подгруппы',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sub_groups.*.id.exists' => 'Подгруппа уже удалена или относится к другой группе. Обновите страницу.',
        ];
    }
}
