@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Редактировать группу</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('service.groups.index') }}">Группы</a> /</span>Редактировать
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
            <h5 class="mb-0">Редактировать группу</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('service.groups.update', $group->id) }}" method="POST">
                @csrf
                @method('put')

                {{-- Номер палаты --}}
                <div class="mb-3">
                    <label class="form-label" for="name">Названия <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text @error('name') text-danger icon-border @enderror">
                            <i class="bx bx-rename"></i>
                        </span>
                        <input type="text" id="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Названия"
                               name="name"
                               value="{{ old('name', $group->name) }}" required/>
                    </div>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Койки --}}
                <div class="mb-3">
                    <label class="form-label">Подгруппы <span class="text-danger">*</span></label>
                    <div id="beds-container">
                        @foreach($group->subGroups as $index => $subGroup)
                            <div class="input-group mb-2">
                                <input type="hidden" name="sub_groups[{{ $index }}][id]" value="{{ $subGroup->id }}">
                                <input type="text" name="sub_groups[{{ $index }}][name]" class="form-control"
                                       placeholder="Названия подгруппы"
                                       value="{{ $subGroup->name }}"
                                       required>
                                <button type="button" class="btn btn-outline-danger remove-bed-sub-group"><i class="bx bx-x"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="add-sub-group"><i class="bx bx-plus"></i>
                        Добавить подгруппу
                    </button>
                </div>
                <button type="submit" class="btn btn-warning ">Редактировать</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let bedIndex = $('#beds-container .input-group').length;

            $('#add-sub-group').on('click', function () {
                const bedInput = `
                    <div class="input-group mb-2">
                        <input type="hidden" name="sub_groups[${bedIndex}][id]" value="">
                        <input type="text" name="sub_groups[${bedIndex}][name]" class="form-control" placeholder="Названия подгруппы" required>
                        <button type="button" class="btn btn-outline-danger remove-bed-sub-group"><i class="bx bx-x"></i></button>
                    </div>
                `;
                $('#beds-container').append(bedInput);

                bedIndex++;
            });

            $(document).on('click', '.remove-bed-sub-group', function () {
                $(this).closest('.input-group').remove();
            });
        });
    </script>
@endsection
