@extends('admin.layouts.app')

@section('title', 'Касса')

@section('header')
    <x-page-header title="Касса" pretitle="Статистика кассира">
        <x-slot:actions>
            <a href="{{ route('cashier.export', request()->query()) }}" class="btn"><i class="bx bx-download" aria-hidden="true"></i>Экспорт в Excel</a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    @php
        $filtered = request()->anyFilled(['first_name', 'last_name', 'status', 'service_date']);
    @endphp

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Услуги пациентов</h2>
            <div class="card-actions">
                <button class="btn btn-sm @unless ($filtered) collapsed @endunless" type="button" data-bs-toggle="collapse" data-bs-target="#cashier-filters"
                        aria-expanded="{{ $filtered ? 'true' : 'false' }}" aria-controls="cashier-filters">
                    <i class="bx bx-filter-alt" aria-hidden="true"></i>Фильтры
                </button>
            </div>
        </div>

        <div id="cashier-filters" class="collapse @if ($filtered) show @endif">
            <form method="GET" action="{{ route('cashier.index') }}" class="card-body border-bottom">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-lg">
                        <label class="form-label" for="filter-first-name">Имя пациента</label>
                        <input type="text" id="filter-first-name" name="first_name" value="{{ $filter->firstName }}" class="form-control">
                    </div>
                    <div class="col-sm-6 col-lg">
                        <label class="form-label" for="filter-last-name">Фамилия пациента</label>
                        <input type="text" id="filter-last-name" name="last_name" value="{{ $filter->lastName }}" class="form-control">
                    </div>
                    <div class="col-sm-6 col-lg">
                        <label class="form-label" for="filter-date">Дата</label>
                        <input type="text" id="filter-date" name="service_date" value="{{ $filter->date?->format('Y-m-d') }}"
                               class="form-control flatpickr-date" placeholder="ГГГГ-ММ-ДД">
                    </div>
                    <div class="col-sm-6 col-lg">
                        <label class="form-label" for="filter-status">Статус оплаты</label>
                        <select id="filter-status" name="status" class="form-select">
                            @foreach ($paymentStates as $value => $label)
                                <option value="{{ $value }}" @selected($filter->state->value === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-auto">
                        <div class="btn-list">
                            <button class="btn btn-primary" type="submit">Применить</button>
                            @if ($filtered)
                                <a href="{{ route('cashier.index') }}" class="btn">Сбросить</a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <form action="{{ route('cashier.updateAll') }}" method="POST" id="paymentForm">
            @csrf
            @method('PUT')

            <div class="table-responsive">
                <table class="table card-table table-vcenter">
                    <thead>
                    <tr>
                        @if ($canPay)
                            <th class="w-1"><input type="checkbox" class="form-check-input m-0 align-middle" id="select-all" aria-label="Выбрать все"></th>
                        @endif
                        <th class="w-1">#</th>
                        <th>Тип</th>
                        <th>Услуга</th>
                        <th>Пациент</th>
                        <th>Ответственный</th>
                        <th class="text-end">Цена</th>
                        <th>Дата и время</th>
                        <th>Статус</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($records as $item)
                        <tr>
                            @if ($canPay)
                                <td>
                                    @if ($item->isPayable())
                                        <input type="checkbox" class="form-check-input m-0 align-middle" name="records[]" value="{{ $item->key() }}"
                                               data-patient="{{ $item->patientId }}" aria-label="Выбрать: {{ $item->name }}, {{ $item->patientName }}">
                                    @endif
                                </td>
                            @endif
                            <td class="text-secondary">{{ $item->id }}</td>
                            <td><x-status-badge :status="$item->type" /></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->patientName }}</td>
                            <td>{{ $item->staffName ?? '—' }}</td>
                            <td class="text-end text-nowrap">{{ $item->price->format() }} {{ $item->currency }}</td>
                            <td class="text-nowrap">{{ $item->occurredAt }}</td>
                            <td><x-status-badge :status="$item->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canPay ? 9 : 8 }}"><x-empty-state title="Нет данных" text="Нет заказов с выбранными условиями." icon="bx-receipt" /></td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages() || $canPay)
                <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>{{ $records->links() }}</div>

                    @if ($canPay)
                        <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">
                            <span id="severalPatientsHint" class="text-danger d-none" role="status">
                                Оплата проводится по одному пациенту: выберите записи одного пациента.
                            </span>
                            <button class="btn btn-success" type="submit" id="payButton">
                                <i class="bx bx-wallet" aria-hidden="true"></i>Оплатить выбранные
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </form>
    </div>
@endsection

@section('scripts')
    @if ($canPay)
        <script>
            (function () {
                const checkboxes = document.querySelectorAll('input[name="records[]"]');
                const selectAll = document.getElementById('select-all');
                const payButton = document.getElementById('payButton');
                const hint = document.getElementById('severalPatientsHint');

                // The server refuses a payment that mixes patients; say so before the cashier submits.
                function checkSelection() {
                    const patients = new Set();
                    let checked = 0;

                    checkboxes.forEach((checkbox) => {
                        if (checkbox.checked) {
                            checked++;
                            patients.add(checkbox.dataset.patient);
                        }
                    });

                    payButton.disabled = patients.size !== 1;
                    hint.classList.toggle('d-none', patients.size <= 1);
                    selectAll.checked = checked > 0 && checked === checkboxes.length;
                    selectAll.indeterminate = checked > 0 && checked < checkboxes.length;
                }

                selectAll.addEventListener('change', () => {
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = selectAll.checked;
                    });
                    checkSelection();
                });
                checkboxes.forEach((checkbox) => checkbox.addEventListener('change', checkSelection));
                checkSelection();
            })();
        </script>
    @endif
@endsection
