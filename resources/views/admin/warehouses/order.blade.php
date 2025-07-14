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
                <select class="form-control warehouse-select" required>
                    <option value="">Выберите склад</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" data-type="{{ $warehouse->warehouse_type_id }}">
                            {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>

                <input type="hidden" name="warehouse_id" id="warehouse_id" value="">
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
        let warehouseType = null;

        // Собираем все возможные товары заранее
        const products = {
            1: [ // medications
                @foreach($medications as $med)
                { id: {{ $med->id }}, name: "{{ $med->name }}" },
                @endforeach
            ],
            2: [ // household
                @foreach($householdMaterial as $house)
                { id: {{ $house->id }}, name: "{{ $house->name }}" },
                @endforeach
            ],
            3: [ // medical supplies
                    @foreach($supplies as $supply)
                { id: {{ $supply->id }}, name: "{{ $supply->name }}" },
                @endforeach
            ]
        };

        // При выборе склада
        $(document).on('change', '.warehouse-select', function () {
            const type = $(this).find('option:selected').data('type');
            warehouseType = type;

            $('#items-container').empty();
            itemIndex = 0;
            addItem();

            $('#warehouse_id').val($(this).val());

            // Блокируем смену склада
            $(this).prop('disabled', true);
        });

        // Добавление товара
        const addItem = () => {
            if (!warehouseType) {
                alert('Сначала выберите склад!');
                return;
            }

            let options = `<option value="">-- Выбрать товар --</option>`;
            if (products[warehouseType]) {
                products[warehouseType].forEach(item => {
                    options += `<option value="${item.id}">${item.name}</option>`;
                });
            }

            const block = `
        <div class="item-group border rounded p-3 mb-3 bg-light">
            <div class="row mb-2">
                <div class="col-md-5">
                    <label>Товар</label>
                    <select name="items[${itemIndex}][id]" class="form-control" required>
                        ${options}
                    </select>
                </div>
                <div class="col-md-5">
                    <label>Кол-во</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" min="1" required>
                </div>
                <div class="col-md-2 d-flex align-items-end mb-2">
                    <button type="button" class="btn btn-danger btn-sm remove-item">✖</button>
                </div>
            </div>
        </div>`;

            $('#items-container').append(block);
            itemIndex++;
        };

        // Удаление товара
        $(document).on('click', '.remove-item', function () {
            $(this).closest('.item-group').remove();
        });

        // Кнопка добавления товара
        $('#add-item-btn').on('click', function () {
            addItem();
        });

        // При загрузке
        $(document).ready(function () {
            // Ждём выбора склада
        });
    </script>
@endsection
