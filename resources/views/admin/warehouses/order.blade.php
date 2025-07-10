@extends('admin.layouts.app')

@section('title')
    <title>Заказ из главного склада</title>
@endsection

@section('content')
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

        @if(session()->has('success'))
            <div class="alert alert-solid-success alert-dismissible d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="container">
        <h4 class="mb-4">Оформить заказ в главный склад</h4>

        <form action="{{ route('supply-requests.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Склад</label>
                <select name="warehouse_id" class="form-control" required>
                    @foreach($warehouses  as $warehouse)
                        <option value="{{ $warehouse->id }}"> {{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="items-container">
                {{-- JS добавит блоки --}}
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm my-2" id="add-item-btn">
                ➕ Добавить товар
            </button>

            <button type="submit" class="btn btn-success w-100 mt-3">
                📦 Заказать
            </button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        let itemIndex = 0;

        const addItem = () => {
            const block = `
        <div class="item-group border rounded p-3 mb-3 bg-light">
            <div class="row mb-2">
                <div class="col-md-3">
                    <label>Тип</label>
                    <select name="items[${itemIndex}][type]" class="form-control type-select" required>
                        <option value="">-- Выбрать --</option>
                        <option value="medication">Медикамент</option>
                        <option value="medical_supply">Мед. расходник</option>
                        <option value="household_material">Хоз. товар</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label>Товар</label>
                    <select name="items[${itemIndex}][id]" class="form-control supply-select" required>
                        <option value="">-- Сначала выберите тип --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Кол-во</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" min="1" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-item">✖</button>
                </div>
            </div>
        </div>`;
            $('#items-container').append(block);
            itemIndex++;
        };

        $(document).on('change', '.type-select', function () {
            const selectedType = $(this).val();
            const itemGroup = $(this).closest('.item-group');
            const supplySelect = itemGroup.find('.supply-select');
            supplySelect.empty().append(`<option value="">-- Выбрать товар --</option>`);

            let options = [];

            switch (selectedType) {
                case 'medication':
                    @foreach($medications as $med)
                    options.push({id: {{ $med->id }}, text: "{{ $med->name }}"});
                    @endforeach
                        break;
                case 'medical_supply':
                    @foreach($supplies as $supply)
                    options.push({id: {{ $supply->id }}, text: "{{ $supply->name }}"});
                    @endforeach
                        break;
                case 'household_material':
                    @foreach($householdMaterial as $house)
                    options.push({id: {{ $house->id }}, text: "{{ $house->name }}"});
                    @endforeach
                        break;
            }

            options.forEach(opt => {
                supplySelect.append(`<option value="${opt.id}">${opt.text}</option>`);
            });
        });

        $(document).on('click', '.remove-item', function () {
            $(this).closest('.item-group').remove();
        });

        $('#add-item-btn').on('click', function () {
            addItem();
        });

        // Добавить первый блок по умолчанию
        $(document).ready(function () {
            addItem();
        });
    </script>
@endsection
