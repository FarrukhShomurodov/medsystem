@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Присвоение опросников </title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('staffs.index') }}">Персоналы</a> /</span>Создать
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
            <h5 class="mb-0">Создать</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('staffs.attach.surveys') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="staff">Персоналы <span
                            class="text-danger">*</span></label>

                    <select name="staff_id"
                            class="select2 form-control @error('staff_id') is-invalid @enderror"
                            id="staff"
                            required>
                        <option value="" disabled selected>Выберите персонал</option>

                        @foreach($staffs as $staff)
                            <option
                                value="{{ $staff->id }}" @selected($staff->id == old('staff_id'))> {{ $staff->name }} </option>
                        @endforeach
                    </select>

                    @error('staff_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="surveys">Опросники<span
                            class="text-danger">*</span></label>
                    <select name="surveys[]"
                            class="select2 form-control @error('surveys') is-invalid @enderror"
                            id="surveys"
                            multiple>
                        @foreach($surveys as $survey)
                            <option
                                value="{{ $survey->id }}" @selected(in_array($survey->id, old('surveys', [])))> {{ $survey->title }} </option>
                        @endforeach
                    </select>

                    @error('surveys')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('#staff').on('select2:select', function (e) {
            let staff_id = e.params.data.id;
            const surveysSelect = $('#surveys');

            $.ajax({
                url: `/api/surveys/${staff_id}`,
                type: 'GET',
                success: function (data) {
                    surveysSelect.html('');

                    const selectedSurveys = surveysSelect.val() || [];

                    data.forEach(survey => {
                        const isSelected = selectedSurveys.includes(survey.id.toString());
                        surveysSelect.append(
                            `<option value="${survey.id}" ${isSelected ? 'selected' : ''}>${survey.title}</option>`
                        );
                    });

                    surveysSelect.trigger('change');
                },
                error: function () {
                    surveysSelect.html('<option value="" disabled selected>Ошибка загрузки</option>');
                }
            });
        });
    </script>
@endsection()
