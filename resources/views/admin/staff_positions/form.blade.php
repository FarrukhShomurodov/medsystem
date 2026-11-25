{{--
    The fields of the position form (create and edit): the name and the permissions of the
    position's role, grouped by action. resources/js/pages/staff-position-form.js adds the search,
    "select all" and the state of the group checkboxes.

    @include('admin.staff_positions.form', ['position' => $position ?? null, 'selected' => [...]])
--}}
<div class="card-body">
    <div class="mb-4">
        <label class="form-label required" for="name">Название</label>
        <div class="input-group input-group-flat">
            <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
            <input type="text" id="name" name="name" value="{{ old('name', $position?->name) }}"
                   class="form-control @error('name') is-invalid @enderror" placeholder="Например, Врач" required
                   @error('name') aria-describedby="name-error" aria-invalid="true" @enderror>
        </div>
        @error('name')
            <div class="invalid-feedback" id="name-error">{{ $message }}</div>
        @enderror
    </div>

    <fieldset class="permission-selector" data-permission-selector
              @error('permissions') aria-describedby="permissions-error" @enderror>
        <legend class="form-label required">Разрешения</legend>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <div class="input-icon flex-grow-1 permission-search">
                <span class="input-icon-addon"><i class="bx bx-search" aria-hidden="true"></i></span>
                <input type="search" class="form-control" id="permission-search" placeholder="Поиск разрешений…"
                       aria-label="Поиск разрешений" autocomplete="off" data-permission-search>
            </div>
            <div class="btn-list">
                <button type="button" class="btn btn-outline-primary" data-permission-select="all">Выбрать все</button>
                <button type="button" class="btn" data-permission-select="none">Очистить</button>
            </div>
        </div>

        @error('permissions')
            <div class="invalid-feedback mb-3" id="permissions-error">{{ $message }}</div>
        @enderror

        <div class="row g-3">
            @foreach($groupedPermissions as $category => $permissions)
                @php($group = Str::slug($category))
                <div class="col-md-6" data-permission-group="{{ $group }}">
                    <div class="card h-100">
                        <div class="card-header">
                            <label class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" data-permission-category="{{ $group }}">
                                <span class="form-check-label fw-medium">{{ $category }}</span>
                            </label>
                        </div>
                        <div class="card-body permission-list">
                            @foreach($permissions as $permission)
                                <label class="form-check" data-permission-item>
                                    <input class="form-check-input" type="checkbox" name="permissions[]"
                                           value="{{ $permission['name'] }}" data-category="{{ $group }}"
                                           @checked(in_array($permission['name'], $selected, true))>
                                    <span class="form-check-label">{{ $permission['russian_name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="text-secondary mt-3 mb-0" data-permission-empty hidden>Ни одно разрешение не подходит под запрос.</p>
    </fieldset>
</div>
