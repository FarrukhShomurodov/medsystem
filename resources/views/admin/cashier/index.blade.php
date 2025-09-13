@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Статистика кассира</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Статистика</span>
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

        @if(session()->has('success'))
            <div class="alert alert-solid-success alert-dismissible d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                {{ session()->get('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Услуги пациентов</h5>
            <button class="btn btn-link collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                <i class="bx bx-filter-alt"></i> Фильтры
            </button>
        </div>

        {{-- Фильтры --}}
        <div id="filtersCollapse" class="collapse @if(request()->anyFilled(['first_name','last_name','status','service_id','service_date'])) show @endif">
            <div class="card-body">
                <form method="GET" action="{{ route('cashier.index') }}">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <input type="text" name="first_name" value="{{ request('first_name') }}" class="form-control" placeholder="Имя пациента">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="last_name" value="{{ request('last_name') }}" class="form-control" placeholder="Фамилия пациента">
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="service_date" value="{{ request('service_date') }}"
                                   class="form-control flatpickr-date"
                                   placeholder="Дата услуги (YYYY-MM-DD)">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Применить</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Таблица --}}
        <form action="{{ route('cashier.updateAll') }}" method="POST" id="bulkForm">
            @csrf
            @method('PUT')

            <div class="card-datatable table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>#</th>
                        <th>Тип</th>
                        <th>Услуга</th>
                        <th>Пациент</th>
                        <th>Ответственный</th>
                        <th>Цена</th>
                        <th>Дата и время</th>
                        <th>Статус</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($records as $row)
                        <tr>
                            <td>
                                @if($row['type'] !== 'procedure' && strip_tags($row['status']) !== 'Оплачено')
                                    <input type="checkbox" name="records[]" value="{{ $row['type'] }}:{{ $row['id'] }}">
                                @endif
                            </td>
                            <td>{{ $row['id'] }}</td>
                            <td>
                                @if($row['type'] === 'service')
                                    <span class="badge bg-primary">Услуга</span>
                                @elseif($row['type'] === 'reception')
                                    <span class="badge bg-success">Приём</span>
                                @elseif($row['type'] === 'medication')
                                    <span class="badge bg-success">Лист назначение</span>
                                @elseif($row['type'] === 'procedure')
                                    <span class="badge bg-warning text-dark">Процедура</span>
                                @endif
                            </td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['patient'] }}</td>
                            <td>{{ $row['doctor'] ?? '-' }}</td>
                            <td>{{ $row['price'] }} {{ $row['currency'] }}</td>
                            <td>{{ $row['date_time'] }}</td>
                            <td>{!! $row['status'] !!}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Нет данных</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="mt-4 p-1 d-flex justify-content-between align-items-center">
                    {{ $records->links('pagination::bootstrap-5') }}

                    <button type="submit" class="btn btn-success">
                        Оплатить выбранные
                    </button>
                </div>
            </div>
        </form>

        {{-- JS для выделения всех чекбоксов --}}
        <script>
            document.getElementById('select-all').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="records[]"]');
                checkboxes.forEach(cb => cb.checked = this.checked);
            });
        </script>
    </div>
@endsection
