<?php

namespace App\Http\Requests;

use App\Models\Admin;
use App\Models\StaffPosition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffPositionRequest extends FormRequest
{
    /**
     * The position name becomes the name of its role, so it may not be the name of a system role
     * or of a role another position (or nobody's position) already has.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $position = $this->route('position');
        $position = $position instanceof StaffPosition ? $position : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::notIn(StaffPosition::RESERVED_ROLE_NAMES),
                Rule::unique('staff_positions', 'name')->ignore($position),
                Rule::unique(config('permission.table_names.roles'), 'name')
                    ->where('guard_name', Admin::GUARD)
                    ->ignore($this->currentRoleId($position)),
            ],
            'permissions' => ['required', 'array'],
            'permissions.*' => [
                'string',
                'distinct',
                Rule::exists(config('permission.table_names.permissions'), 'name')->where('guard_name', Admin::GUARD),
            ],
        ];
    }

    /**
     * @return array{name: string, permissions: list<string>}
     */
    public function position(): array
    {
        return [
            'name' => $this->string('name')->value(),
            'permissions' => array_values(array_map(strval(...), $this->array('permissions'))),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'название',
            'permissions' => 'разрешения',
            'permissions.*' => 'разрешение',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.not_in' => 'Это название зарезервировано для системной роли.',
            'name.unique' => 'Должность или роль с таким названием уже существует.',
        ];
    }

    /**
     * The role the position has, or will be linked to by name on first use (StaffPositionService::roleOf()).
     */
    private function currentRoleId(?StaffPosition $position): ?int
    {
        if ($position === null) {
            return null;
        }

        return $position->role_id ?? Role::query()
            ->where('guard_name', Admin::GUARD)
            ->where('name', $position->name)
            ->whereNotIn('name', StaffPosition::RESERVED_ROLE_NAMES)
            ->value('id');
    }
}
