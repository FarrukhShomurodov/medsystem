@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Редактировать палату</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('departments.room.index') }}">Палаты</a> /</span>Редактировать
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
            <h5 class="mb-0">Редактировать палату</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('departments.room.update', $room->id) }}" method="POST">
                @csrf
                @method('put')

                {{-- Номер палаты --}}
                <div class="mb-3">
                    <label class="form-label" for="room_number">Номер палаты <span class="text-danger">*</span></label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text @error('room_number') text-danger icon-border @enderror">
                            <i class="bx bx-rename"></i>
                        </span>
                        <input type="text" id="room_number"
                               class="form-control @error('room_number') is-invalid @enderror"
                               placeholder="Номер палаты"
                               name="room_number"
                               value="{{ old('room_number', $room->room_number) }}" required/>
                    </div>
                    @error('room_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Койки --}}
                <div class="mb-3">
                    <label class="form-label">Койки <span class="text-danger">*</span></label>
                    <div id="beds-container">
                        @foreach($room->beds as $index => $bed)
                            <div class="input-group mb-2">
                                <input type="hidden" name="bed_numbers[{{ $index }}][id]" value="{{ $bed->id }}">
                                <input type="text" name="bed_numbers[{{ $index }}][bed_number]" class="form-control"
                                       placeholder="Номер койки"
                                       value="{{ $bed->bed_number }}"
                                       required>
                                <button type="button" class="btn btn-outline-danger remove-bed"><i class="bx bx-x"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="add-bed"><i class="bx bx-plus"></i>
                        Добавить койку
                    </button>
                </div>


                <div class="mt-4 mb-3">
                    <h6 class="mb-2">Схема палаты</h6>
                    <div id="room-map" class="room-map">
                        @foreach($room->beds as $bed)
                            <div class="bed-item" data-id="{{ $bed->id }}">
                                🛏 Койка: {{ $bed->bed_number }}
                            </div>
                        @endforeach
                    </div>
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

            $('#add-bed').on('click', function () {
                const bedInput = `
                    <div class="input-group mb-2">
                        <input type="hidden" name="bed_numbers[${bedIndex}][id]" value="">
                        <input type="text" name="bed_numbers[${bedIndex}][bed_number]" class="form-control" placeholder="Номер койки" required>
                        <button type="button" class="btn btn-outline-danger remove-bed"><i class="bx bx-x"></i></button>
                    </div>
                `;
                $('#beds-container').append(bedInput);

                bedIndex++;
            });

            $(document).on('click', '.remove-bed', function () {
                $(this).closest('.input-group').remove();
            });
        });
    </script>
@endsection
