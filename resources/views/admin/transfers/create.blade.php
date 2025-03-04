@extends('admin.layouts.app')

@section('title')
    <title>Создать перевод</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Переводы /</span> Создать
    </h6>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('transfers.store') }}" method="POST">
                @csrf

                <label>Со склада</label>
                <select name="from_warehouse_id" class="form-control" required>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>

                <label>В аптеку</label>
                <select name="to_pharmacy_id" class="form-control" required>
                    @foreach($pharmacies as $pharmacy)
                        <option value="{{ $pharmacy->id }}">{{ $pharmacy->name }}</option>
                    @endforeach
                </select>

                <div id="medications-container">
                    <div class="medication-group">
                        <label>Препарат</label>
                        <select name="medications[0][medication_id]" class="form-control">
                            @foreach($medications as $medication)
                                <option value="{{ $medication->id }}">{{ $medication->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="medications[0][quantity]" class="form-control" required>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary" id="add-medication">Добавить препарат</button>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('add-medication').addEventListener('click', function () {
            let container = document.getElementById('medications-container');
            let index = container.getElementsByClassName('medication-group').length;
            let newGroup = document.createElement('div');
            newGroup.classList.add('medication-group');
            newGroup.innerHTML = `
                <label>Препарат</label>
                <select name="medications[${index}][medication_id]" class="form-control">
                    @foreach($medications as $medication)
            <option value="{{ $medication->id }}">{{ $medication->name }}</option>
                    @endforeach
            </select>
            <input type="number" name="medications[${index}][quantity]" class="form-control" required>
            `;
            container.appendChild(newGroup);
        });
    </script>
@endsection
