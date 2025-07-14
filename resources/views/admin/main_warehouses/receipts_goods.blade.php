<style>
    :root {
        --primary: #3a86ff;
        --secondary: #8338ec;
        --success: #06d6a0;
        --danger: #ef476f;
        --warning: #ffd166;
        --light: #f8f9fa;
        --dark: #212529;
    }

    body {
        background-color: #f5f7fb;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .section-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .section-card.active {
        box-shadow: 0 8px 25px rgba(58, 134, 255, 0.2);
        border-left: 4px solid var(--primary);
    }

    .section-header {
        background-color: #f8fafc;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-header h5 {
        margin: 0;
        font-weight: 600;
        color: #495057;
    }

    .section-content {
        padding: 20px;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 8px;
    }

    .input-group-text {
        background-color: #e9ecef;
        color: #6c757d;
    }

    .product-details {
        background-color: #f8fafc;
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
        border-left: 3px solid var(--primary);
    }

    .product-title {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #dee2e6;
    }

    .sub-section-title {
        color: var(--secondary);
        font-weight: 600;
        margin: 15px 0 10px;
        font-size: 1.05rem;
    }

    .action-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .badge-count {
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        margin-left: 8px;
    }

    .empty-state {
        text-align: center;
        padding: 30px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }

    .toggle-icon {
        transition: transform 0.3s ease;
    }

    .collapsed .toggle-icon {
        transform: rotate(180deg);
    }
</style>
@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Медикаменты склада</title>
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

        <div class="accordion mb-3">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        Заканчивающиейся позиции
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse"
                     aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <table class="datatables-users table border-top">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Тип</th>
                                <th>Название</th>
                                <th>Количество</th>
                                <th>Срок годности</th>
                                <th>Дата поступления</th>
                                <th>Партия</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php $index = 1; @endphp

                            {{-- Медикаменты --}}
                            @foreach ($remainMedications as $item)
                                <tr class="bg-warning text-dark">
                                    <td>{{ $index++ }}</td>
                                    <td><span class="badge bg-info">Медикамент</span></td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->pivot->quantity }}</td>
                                    <td>
                                        {{ $item->pivot->expiration_date?->format('d.m.Y') ?? '—' }}
                                    </td>
                                    <td>{{ $item->pivot->date_of_receipt ?? '—' }}</td>
                                    <td>{{ $item->pivot->batch_number ?? '—' }}</td>
                                </tr>
                            @endforeach

                            {{-- Расходники --}}
                            @foreach ($remainSupplies as $item)
                                <tr class="bg-warning text-dark">
                                    <td>{{ $index++ }}</td>
                                    <td><span class="badge bg-success">Расходник</span></td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->pivot->quantity }}</td>
                                    <td>—</td>
                                    <td>—</td>
                                    <td>—</td>
                                </tr>
                            @endforeach

                            {{-- Хоз. материалы --}}
                            @foreach($remainHousehold as $item)
                                <tr class="bg-warning text-dark">
                                    <td>{{ $index++ }}</td>
                                    <td><span class="badge bg-warning">Хоз. материал</span></td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->pivot->quantity }}</td>
                                    <td>—</td>
                                    <td>—</td>
                                    <td>—</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('main.warehouse.receipts.goods.store') }}" method="post">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <button class="btn btn-primary"><i class="fas fa-save me-2"></i>Сохранить</button>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Выберите тип товаров для добавления на склад и заполните детали прихода
            </div>

            <!-- Медикаменты -->
            <div class="section-card" id="medications-section">
                <div class="section-header">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="medications-toggle" checked>
                        <label class="form-check-label" for="medications-toggle">
                            <h5><i class="fas fa-pills me-2 text-primary"></i>Медикаменты</h5>
                        </label>
                    </div>
                    <span class="badge bg-primary rounded-pill" id="medications-count">0</span>
                </div>
                <div class="section-content">
                    <div class="row g-3">
                        <div class="col-sm-12">
                            <label class="form-label" for="medication">Выберите медикаменты</label>
                            <select id="medication" class="form-control select2" multiple="multiple">
                                @foreach($medications as $medication)
                                    <option value="{{ $medication->id }}">{{ $medication->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="medication-details-container">
                            <div class="empty-state">
                                <i class="fas fa-pills"></i>
                                <h5>Добавьте медикаменты</h5>
                                <p class="mb-0">Выберите медикаменты из списка выше, чтобы добавить их на склад</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Расходные материалы -->
            <div class="section-card" id="supplies-section">
                <div class="section-header">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="supplies-toggle" checked>
                        <label class="form-check-label" for="supplies-toggle">
                            <h5><i class="fas fa-syringe me-2 text-success"></i>Расходные материалы</h5>
                        </label>
                    </div>
                    <span class="badge bg-success rounded-pill" id="supplies-count">0</span>
                </div>
                <div class="section-content">
                    <div class="row g-3">
                        <div class="col-sm-12">
                            <label class="form-label" for="supply">Выберите расходные материалы</label>
                            <select id="supply" class="form-control select2" multiple="multiple">
                                @foreach($supplies as $supply)
                                    <option value="{{ $supply->id }}">{{ $supply->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="supply-details-container">
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h5>Добавьте расходные материалы</h5>
                                <p class="mb-0">Выберите материалы из списка выше, чтобы добавить их на склад</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Хозяйственные материалы -->
            <div class="section-card" id="household-section">
                <div class="section-header">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="household-toggle" checked>
                        <label class="form-check-label" for="household-toggle">
                            <h5><i class="fas fa-broom me-2 text-warning"></i>Хозяйственные материалы</h5>
                        </label>
                    </div>
                    <span class="badge bg-warning rounded-pill" id="household-count">0</span>
                </div>
                <div class="section-content">
                    <div class="row g-3">
                        <div class="col-sm-12">
                            <label class="form-label" for="household">Выберите хозяйственные материалы</label>
                            <select id="household" class="form-control select2" multiple="multiple">
                                @foreach($householdMaterial as $material)
                                    <option value="{{ $material->id }}">{{ $material->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="household-details-container">
                            <div class="empty-state">
                                <i class="fas fa-boxes"></i>
                                <h5>Добавьте хозяйственные материалы</h5>
                                <p class="mb-0">Выберите материалы из списка выше, чтобы добавить их на склад</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary"><i class="fas fa-save me-2"></i>Сохранить приход</button>
            </div>
        </form>

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Инициализация Select2
            $('.select2').select2({
                placeholder: "Выберите товары",
                allowClear: true
            });

            // Инициализация Flatpickr
            $('.form-control[type="date"]').flatpickr({
                dateFormat: "Y-m-d",
                locale: "ru"
            });

            // Функция для обновления счетчика
            function updateCounter(containerId, counterId) {
                const count = $(containerId).children('.product-details').length;
                $(counterId).text(count);
            }

            // Переключение разделов
            $('#medications-toggle').change(function () {
                if (this.checked) {
                    $('#medications-section .section-content').slideDown();
                } else {
                    $('#medications-section .section-content').slideUp();
                    // Сброс выбора
                    $('#medication').val(null).trigger('change');
                    $('#medication-details-container').html('<div class="empty-state"><i class="fas fa-pills"></i><h5>Добавьте медикаменты</h5><p class="mb-0">Выберите медикаменты из списка выше, чтобы добавить их на склад</p></div>');
                    $('#medications-count').text('0');
                }
            });

            $('#supplies-toggle').change(function () {
                if (this.checked) {
                    $('#supplies-section .section-content').slideDown();
                } else {
                    $('#supplies-section .section-content').slideUp();
                    $('#supply').val(null).trigger('change');
                    $('#supply-details-container').html('<div class="empty-state"><i class="fas fa-box-open"></i><h5>Добавьте расходные материалы</h5><p class="mb-0">Выберите материалы из списка выше, чтобы добавить их на склад</p></div>');
                    $('#supplies-count').text('0');
                }
            });

            $('#household-toggle').change(function () {
                if (this.checked) {
                    $('#household-section .section-content').slideDown();
                } else {
                    $('#household-section .section-content').slideUp();
                    $('#household').val(null).trigger('change');
                    $('#household-details-container').html('<div class="empty-state"><i class="fas fa-boxes"></i><h5>Добавьте хозяйственные материалы</h5><p class="mb-0">Выберите материалы из списка выше, чтобы добавить их на склад</p></div>');
                    $('#household-count').text('0');
                }
            });

            // Шаблон для медикаментов
            function createMedicationTemplate(id, name) {
                return `
                    <div class="product-details" data-id="${id}">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="product-title">${name}</h5>
                            <button class="btn btn-sm btn-outline-danger remove-item"><i class="fas fa-times"></i></button>
                        </div>
                        <input type="hidden" name="medications[${id}][id]" value="${id}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="quantity_${id}">Количество <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                                        <input type="number" id="quantity_${id}" name="medications[${id}][quantity]" class="form-control" placeholder="Введите количество">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="expiration_date_${id}">Срок годности <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="date" id="expiration_date_${id}" name="medications[${id}][expiration_date]" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="date_of_receipt_${id}">Дата получения <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-truck"></i></span>
                                        <input type="date" id="date_of_receipt_${id}" name="medications[${id}][date_of_receipt]" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="batch_number_${id}">Номер партии <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                        <input type="text" id="batch_number_${id}" name="medications[${id}][batch_number]" class="form-control" placeholder="Введите номер партии">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="sub-section-title">Упаковка</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="packaging_type_${id}">Тип упаковки</label>
                                    <select id="packaging_type_${id}" name="medications[${id}][packaging_type]" class="form-select">
                                        <option>блистер</option>
                                        <option>флакон</option>
                                        <option>тюбик</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="quantity_per_package_${id}">Кол-во в упаковке</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-box"></i></span>
                                        <input type="number" id="quantity_per_package_${id}" name="medications[${id}][quantity_per_package]" class="form-control" placeholder="Введите количество">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="package_size_${id}">Размер упаковки</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-ruler"></i></span>
                                        <input type="number" id="package_size_${id}" name="medications[${id}][package_size]" class="form-control" placeholder="Введите размер">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="sub-section-title">Стоимость и поставки</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="retail_price_${id}">Розничная цена</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                        <input type="number" id="retail_price_${id}" name="medications[${id}][retail_price]" class="form-control" placeholder="Введите цену">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="wholesale_price_${id}">Закупочная цена</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-file-invoice-dollar"></i></span>
                                        <input type="number" id="wholesale_price_${id}" name="medications[${id}][wholesale_price]" class="form-control" placeholder="Введите цену">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="supplier_${id}">Поставщик</label>
                                    <select id="supplier_${id}" name="medications[${id}][supplier_id]" class="form-select">
                                        @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
`;
            }

            // Шаблон для расходных материалов
            function createSupplyTemplate(id, name) {
                return `
                    <div class="product-details" data-id="${id}" style="border-left-color: #06d6a0;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="product-title">${name}</h5>
                            <button class="btn btn-sm btn-outline-danger remove-item"><i class="fas fa-times"></i></button>
                        </div>
                        <input type="hidden" name="supplies[${id}][id]" value="${id}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="quantity_${id}">Количество <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                                        <input type="number" id="quantity_${id}" name="supplies[${id}][quantity]" class="form-control" placeholder="Введите количество">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Шаблон для хозяйственных материалов
            function createHouseholdTemplate(id, name) {
                return `
                    <div class="product-details" data-id="${id}" style="border-left-color: #ffd166;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="product-title">${name}</h5>
                            <button class="btn btn-sm btn-outline-danger remove-item"><i class="fas fa-times"></i></button>
                        </div>
                        <input type="hidden" name="household[${id}][id]" value="${id}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="quantity_${id}">Количество <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calculator"></i></span>
                                        <input type="number" id="quantity_${id}" name="household[${id}][quantity]" class="form-control" placeholder="Введите количество">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Добавление медикамента
            $('#medication').on('change', function () {
                const container = $('#medication-details-container');
                const selected = $(this).val();

                if (selected && selected.length > 0) {
                    // Удаляем пустое состояние если есть
                    if (container.find('.empty-state').length) {
                        container.empty();
                    }

                    // Добавляем новые элементы
                    selected.forEach(id => {
                        if (!container.find(`[data-id="${id}"]`).length) {
                            const name = $(this).find('option[value="' + id + '"]').text();
                            const template = createMedicationTemplate(id, name);
                            container.append(template);
                        }
                    });

                    // Удаляем элементы, которые не выбраны
                    container.find('.product-details').each(function () {
                        const id = $(this).data('id');
                        if (!selected.includes(id.toString())) {
                            $(this).remove();
                        }
                    });

                    // Обновляем счетчик
                    updateCounter('#medication-details-container', '#medications-count');
                } else {
                    container.html('<div class="empty-state"><i class="fas fa-pills"></i><h5>Добавьте медикаменты</h5><p class="mb-0">Выберите медикаменты из списка выше, чтобы добавить их на склад</p></div>');
                    $('#medications-count').text('0');
                }
            });

            // Добавление расходных материалов
            $('#supply').on('change', function () {
                const container = $('#supply-details-container');
                const selected = $(this).val();

                if (selected && selected.length > 0) {
                    if (container.find('.empty-state').length) {
                        container.empty();
                    }

                    selected.forEach(id => {
                        if (!container.find(`[data-id="${id}"]`).length) {
                            const name = $(this).find('option[value="' + id + '"]').text();
                            const template = createSupplyTemplate(id, name);
                            container.append(template);
                        }
                    });

                    container.find('.product-details').each(function () {
                        const id = $(this).data('id');
                        if (!selected.includes(id.toString())) {
                            $(this).remove();
                        }
                    });

                    updateCounter('#supply-details-container', '#supplies-count');
                } else {
                    container.html('<div class="empty-state"><i class="fas fa-box-open"></i><h5>Добавьте расходные материалы</h5><p class="mb-0">Выберите материалы из списка выше, чтобы добавить их на склад</p></div>');
                    $('#supplies-count').text('0');
                }
            });

            // Добавление хозяйственных материалов
            $('#household').on('change', function () {
                const container = $('#household-details-container');
                const selected = $(this).val();

                if (selected && selected.length > 0) {
                    if (container.find('.empty-state').length) {
                        container.empty();
                    }

                    selected.forEach(id => {
                        if (!container.find(`[data-id="${id}"]`).length) {
                            const name = $(this).find('option[value="' + id + '"]').text();
                            const template = createHouseholdTemplate(id, name);
                            container.append(template);
                        }
                    });

                    container.find('.product-details').each(function () {
                        const id = $(this).data('id');
                        if (!selected.includes(id.toString())) {
                            $(this).remove();
                        }
                    });

                    updateCounter('#household-details-container', '#household-count');
                } else {
                    container.html('<div class="empty-state"><i class="fas fa-boxes"></i><h5>Добавьте хозяйственные материалы</h5><p class="mb-0">Выберите материалы из списка выше, чтобы добавить их на склад</p></div>');
                    $('#household-count').text('0');
                }
            });

            // Удаление элемента
            $(document).on('click', '.remove-item', function () {
                const container = $(this).closest('.product-details');
                const id = container.data('id');
                const section = container.closest('.section-content');

                // Удаляем элемент из выбора Select2
                if (section.attr('id') === 'medication-details-container') {
                    $('#medication option[value="' + id + '"]').prop('selected', false);
                    $('#medication').trigger('change');
                } else if (section.attr('id') === 'supply-details-container') {
                    $('#supply option[value="' + id + '"]').prop('selected', false);
                    $('#supply').trigger('change');
                } else if (section.attr('id') === 'household-details-container') {
                    $('#household option[value="' + id + '"]').prop('selected', false);
                    $('#household').trigger('change');
                }

                container.remove();

                // Обновляем счетчик
                if (section.attr('id') === 'medication-details-container') {
                    updateCounter('#medication-details-container', '#medications-count');
                } else if (section.attr('id') === 'supply-details-container') {
                    updateCounter('#supply-details-container', '#supplies-count');
                } else if (section.attr('id') === 'household-details-container') {
                    updateCounter('#household-details-container', '#household-count');
                }

                // Если контейнер пуст, показываем пустое состояние
                if (section.children().length === 0) {
                    if (section.attr('id') === 'medication-details-container') {
                        section.html('<div class="empty-state"><i class="fas fa-pills"></i><h5>Добавьте медикаменты</h5><p class="mb-0">Выберите медикаменты из списка выше, чтобы добавить их на склад</p></div>');
                    } else if (section.attr('id') === 'supply-details-container') {
                        section.html('<div class="empty-state"><i class="fas fa-box-open"></i><h5>Добавьте расходные материалы</h5><p class="mb-0">Выберите материалы из списка выше, чтобы добавить их на склад</p></div>');
                    } else if (section.attr('id') === 'household-details-container') {
                        section.html('<div class="empty-state"><i class="fas fa-boxes"></i><h5>Добавьте хозяйственные материалы</h5><p class="mb-0">Выберите материалы из списка выше, чтобы добавить их на склад</p></div>');
                    }
                }
            });
        });
    </script>
@endsection
