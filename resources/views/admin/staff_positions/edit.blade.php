@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Редактировать должность</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('staffs.position.index') }}">Должности</a> /</span>Редактировать
    </h6>

    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center" role="alert">
                    <i class="bx bx-error-circle fs-4 me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Редактировать: {{ $position->name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('staffs.position.update', $position) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="basic-icon-default-company">Название <span
                            class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('name') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                        <input type="text" id="basic-icon-default-company"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Название" aria-label="Название"
                               name="name"
                               value="{{ old('name', $position->name) }}"
                               aria-describedby="basic-icon-default-company2" required/>
                    </div>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @php
                    $roleName = $position->name;
                    $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
                    $selectedPermissions = $role ? $role->permissions->pluck('name')->toArray() : [];
                    $selectedPermissions = old('permissions', $selectedPermissions);
                @endphp

                <div class="col-12 mb-3">
                    <label class="form-label" for="permissions">Разрешения <span class="text-danger">*</span></label>

                    <div class="card permission-selector">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <input type="text" class="form-control" placeholder="Поиск разрешений..." id="permission-search">
                                </div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary me-2" id="select-all">
                                        Выбрать все
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all">
                                        Очистить
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($groupedPermissions as $category => $permissions)
                                    <div class="col-md-6 mb-4">
                                        <div class="permission-category card">
                                            <div class="card-header py-2">
                                                <div class="form-check">
                                                    <input class="form-check-input category-checkbox"
                                                           type="checkbox"
                                                           id="category-{{ Str::slug($category) }}"
                                                           data-category="{{ Str::slug($category) }}">
                                                    <label class="form-check-label fw-medium" for="category-{{ Str::slug($category) }}">
                                                        {{ $category }}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                @foreach($permissions as $permission)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input permission-checkbox"
                                                               type="checkbox"
                                                               name="permissions[]"
                                                               value="{{ $permission['name'] }}"
                                                               id="permission-{{ $permission['id'] }}"
                                                               data-category="{{ Str::slug($category) }}"
                                                            {{ in_array($permission['name'], $selectedPermissions) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission-{{ $permission['id'] }}">
                                                            {{ $permission['russian_name'] }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @error('permissions')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="{{ route('staffs.position.index') }}" class="btn btn-secondary">Отмена</a>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Выбрать все разрешения
            document.getElementById('select-all').addEventListener('click', function() {
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                });
                document.querySelectorAll('.category-checkbox').forEach(checkbox => {
                    checkbox.checked = true;
                    checkbox.indeterminate = false;
                });
            });

            // Очистить все разрешения
            document.getElementById('deselect-all').addEventListener('click', function() {
                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.querySelectorAll('.category-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                    checkbox.indeterminate = false;
                });
            });

            // Обработка выбора категории
            document.querySelectorAll('.category-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const category = this.dataset.category;
                    document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`).forEach(permission => {
                        permission.checked = this.checked;
                    });
                    this.indeterminate = false;
                });
            });

            // Обновление состояния чекбокса категории при изменении разрешений
            document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const category = this.dataset.category;
                    const categoryCheckbox = document.querySelector(`.category-checkbox[data-category="${category}"]`);
                    const categoryPermissions = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);
                    const allChecked = Array.from(categoryPermissions).every(permission => permission.checked);
                    const someChecked = Array.from(categoryPermissions).some(permission => permission.checked);

                    categoryCheckbox.checked = allChecked;
                    categoryCheckbox.indeterminate = someChecked && !allChecked;
                });
            });

            // Инициализация состояния категорий при загрузке
            document.querySelectorAll('.category-checkbox').forEach(checkbox => {
                const category = checkbox.dataset.category;
                const categoryPermissions = document.querySelectorAll(`.permission-checkbox[data-category="${category}"]`);
                const allChecked = Array.from(categoryPermissions).every(permission => permission.checked);
                const someChecked = Array.from(categoryPermissions).some(permission => permission.checked);

                checkbox.checked = allChecked;
                checkbox.indeterminate = someChecked && !allChecked;
            });

            // Поиск разрешений
            const searchInput = document.getElementById('permission-search');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                    const label = checkbox.nextElementSibling;
                    const text = label.textContent.toLowerCase();
                    const category = checkbox.closest('.permission-category');

                    if (text.includes(searchTerm)) {
                        label.closest('.form-check').style.display = 'block';
                        category.style.display = 'block';
                        category.closest('.col-md-6').style.display = 'block';
                    } else {
                        label.closest('.form-check').style.display = 'none';

                        // Скрываем категорию, если в ней не осталось видимых разрешений
                        const visiblePermissions = category.querySelectorAll('.form-check:not([style*="display: none"])').length;

                        if (visiblePermissions === 0) {
                            category.style.display = 'none';
                            category.closest('.col-md-6').style.display = 'none';
                        } else {
                            category.style.display = 'block';
                            category.closest('.col-md-6').style.display = 'block';
                        }
                    }
                });
            });
        });
    </script>
@endsection

@section('styles')
    <style>
        .permission-category {
            height: 100%;
        }
        .permission-category .card-body {
            max-height: 300px;
            overflow-y: auto;
        }
        .category-checkbox:indeterminate {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .category-checkbox:indeterminate:after {
            content: "";
            display: block;
            width: 60%;
            height: 2px;
            background-color: white;
            position: absolute;
            top: 50%;
            left: 20%;
            transform: translateY(-50%);
        }
        #permission-search {
            max-width: 250px;
        }
    </style>
@endsection
