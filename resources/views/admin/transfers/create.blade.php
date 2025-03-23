@extends('admin.layouts.app')

@section('title')
    <title>Создать перевод</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('transfers.index') }}">Переводы </a> /</span>Создать
    </h6>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('transfers.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="from">Со склада <span
                            class="text-danger">*</span></label>
                    <select id="from" name="from_warehouse_id" class="form-control select2" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="to">В аптеку <span
                            class="text-danger">*</span></label>
                    <select id="to" name="to_pharmacy_id" class="form-control select2" required>
                        @foreach($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                        @endforeach
                    </select>
                </div>

                <hr>

                <div id="medications-container">
                    <div class="medication-group">
                        <label class="form-label" for="medication">Препарат <span
                                class="text-danger">*</span></label>
                        <select id="medication" name="medications[0][medication_id]" class="form-control mb-3 select2">
                            @foreach($medications as $medication)
                                <option value="{{ $medication->id }}">{{ $medication->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="medications[0][quantity]" class="form-control mt-3 mb-3"
                               placeholder="Кол-во" required>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary" id="add-medication">Добавить препарат</button>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('#add-medication').on('click', function () {
            let container = $('#medications-container');
            let index = container.find('.medication-group').length;

            let newGroup = $(`
        <div class="medication-group">
            <label>Препарат <span class="text-danger">*</span></label>
            <select name="medications[${index}][medication_id]" class="form-control mb-3 select2">
                @foreach($medications as $medication)
            <option value="{{ $medication->id }}">{{ $medication->name }}</option>
                @endforeach
            </select>
            <input type="number" name="medications[${index}][quantity]" class="form-control mt-3 mb-3" placeholder="Кол-во" required>
        </div>
    `);

            container.append(newGroup);
            $('.select2').select2();
        });
    </script>
@endsection
