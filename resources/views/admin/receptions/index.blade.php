@extends('admin.layouts.app')

@section('title', 'Приёмы')

@php($filtered = request()->anyFilled(['is_disposable', 'staff_id', 'status', 'date']))

@section('header')
    <x-page-header title="Приёмы" pretitle="Приём">
        <x-slot:actions>
            <button type="button" @class(['btn', 'collapsed' => ! $filtered]) data-bs-toggle="collapse" data-bs-target="#reception-filters"
                    aria-controls="reception-filters" aria-expanded="{{ $filtered ? 'true' : 'false' }}">
                <i class="bx bx-filter-alt" aria-hidden="true"></i>Фильтры
            </button>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    <div @class(['collapse', 'show' => $filtered]) id="reception-filters">
        <div class="card mb-3">
            <form method="GET" action="{{ route('receptions.index') }}" class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-lg">
                        <label class="form-label" for="filter-date">Дата</label>
                        <input type="text" id="filter-date" name="date" value="{{ request('date') }}"
                               class="form-control flatpickr-date" placeholder="ГГГГ-ММ-ДД">
                    </div>

                    <div class="col-sm-6 col-lg">
                        <label class="form-label" for="filter-status">Статус</label>
                        <select id="filter-status" name="status" class="form-select">
                            <option value="">Все</option>
                            @foreach(\App\Enums\ReceptionStatus::options() as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-6 col-lg">
                        <label class="form-label" for="disposable">Разовый приём</label>
                        <select id="disposable" name="is_disposable" class="form-select select2">
                            <option value="">Все</option>
                            <option value="yes" @selected(request('is_disposable') === 'yes')>Да, вне карты обследования</option>
                            <option value="no" @selected(request('is_disposable') === 'no')>Нет, по карте обследования</option>
                        </select>
                    </div>

                    {{-- A doctor sees only their own appointments: no filter by employee. --}}
                    @unless($doctor)
                        <div class="col-sm-6 col-lg">
                            <label class="form-label" for="staff">Сотрудник</label>
                            <select id="staff" name="staff_id" class="form-select select2">
                                <option value="">Все</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}" @selected($staff->id == request('staff_id'))>{{ $staff->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endunless

                    <div class="col-12 col-lg-auto">
                        <div class="btn-list">
                            <button type="submit" class="btn btn-primary">Применить</button>
                            @if($filtered)
                                <a href="{{ route('receptions.index') }}" class="btn">Сбросить</a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                <tr>
                    <th class="w-1">#</th>
                    <th>Пациент</th>
                    <th>Услуга</th>
                    <th>Сотрудник</th>
                    <th>Начало</th>
                    <th>Конец</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>Комментарий</th>
                </tr>
                </thead>
                <tbody>
                @forelse($receptions as $reception)
                    <tr>
                        <td class="text-secondary">{{ $reception->id }}</td>
                        <td>{{ $reception->patient?->full_name ?? '—' }}</td>
                        <td>{{ $reception->service->name ?? '—' }}</td>
                        <td>{{ $reception->staff?->full_name ?? '—' }}</td>
                        <td>{{ substr($reception->start_time, 0, 5) }}</td>
                        <td>{{ substr($reception->end_time, 0, 5) }}</td>
                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($reception->date)->format('d.m.Y') }}</td>
                        <td><x-status-badge :status="$reception->status" /></td>
                        <td>{{ $reception->comment }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <x-empty-state :title="$filtered ? 'Ничего не найдено' : 'Приёмов пока нет'"
                                           :text="$filtered ? 'Измените фильтры или сбросьте их.' : null" icon="bx-calendar" />
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($receptions->hasPages())
            <div class="card-footer">{{ $receptions->links() }}</div>
        @endif
    </div>
@endsection
