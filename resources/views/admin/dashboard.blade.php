{{--
    The dashboard home: the figures of the day the account may see. App\Queries\Dashboard\DashboardSummary
    computes them; each card links to its page and is shown only with that page's permission
    (App\Queries\Dashboard\DashboardCard).
--}}
@extends('admin.layouts.app')

@use('App\Enums\ReceptionStatus')
@use('App\Queries\Dashboard\DashboardCard')
@use('App\Services\Billing\Amount')
@use('App\Services\Billing\PaymentState')

@section('title', 'Панель управления')

@section('header')
    <x-page-header title="Панель управления"
                   :pretitle="Str::ucfirst($dashboard->today->locale(app()->getLocale())->isoFormat('dddd, D MMMM YYYY'))">
        @canany(['create_patients', 'view_appointment_receptions'])
            <x-slot:actions>
                @can('create_patients')
                    <a href="{{ route('patients.create') }}" class="btn">
                        <i class="bx bx-user-plus" aria-hidden="true"></i>Новый пациент
                    </a>
                @endcan
                @can('view_appointment_receptions')
                    <a href="{{ route('receptions.appointment') }}" class="btn btn-primary">
                        <i class="bx bx-calendar-plus" aria-hidden="true"></i>Записать на приём
                    </a>
                @endcan
            </x-slot:actions>
        @endcanany
    </x-page-header>
@endsection

@section('content')
    @if ($dashboard->isEmpty())
        <div class="card" data-dashboard-card="welcome">
            <x-empty-state title="Добро пожаловать в {{ config('app.name') }}" icon="bx-home"
                           text="Ваша учётная запись пока не открывает ни одного раздела. Права выдаёт администратор панели." />
        </div>
    @else
        @php
            $day = $dashboard->today->toDateString();
            $hasMain = $dashboard->shows(DashboardCard::Appointments, DashboardCard::Admissions);
            $hasSide = $dashboard->shows(DashboardCard::RegistrarQueue, ...DashboardCard::stock());
        @endphp

        <div class="row row-cards dashboard">
            @if ($dashboard->shows(DashboardCard::Inpatients, DashboardCard::ReceptionsToday, DashboardCard::AwaitingPayment, DashboardCard::PaidToday))
                <div class="col-12">
                    <div class="row row-cards">
                        @if ($dashboard->inpatients !== null)
                            @include('admin.dashboard.tile', [
                                'card' => DashboardCard::Inpatients,
                                'url' => route('sister.window.index'),
                                'icon' => 'bx-bed',
                                'tone' => 'primary',
                                'label' => 'На лечении',
                                'values' => [$dashboard->inpatients],
                                'meta' => trans_choice('пациент|пациента|пациентов', $dashboard->inpatients)
                                    .($dashboard->inpatientsOfAllDepartments ? ' во всех отделениях' : ' в вашем отделении'),
                            ])
                        @endif

                        @if ($dashboard->shows(DashboardCard::ReceptionsToday) && $dashboard->receptions !== null)
                            @php
                                $cancelled = $dashboard->receptions->count(ReceptionStatus::Cancelled);
                            @endphp
                            @include('admin.dashboard.tile', [
                                'card' => DashboardCard::ReceptionsToday,
                                'url' => route('receptions.index', ['date' => $day]),
                                'icon' => 'bx-calendar-check',
                                'tone' => 'green',
                                'label' => $dashboard->receptions->ownOnly ? 'Мои приёмы сегодня' : 'Приёмы сегодня',
                                'values' => [$dashboard->receptions->booked()],
                                'meta' => 'выполнено '.$dashboard->receptions->count(ReceptionStatus::Done)
                                    .($cancelled > 0 ? ' · отменено '.$cancelled : ''),
                            ])
                        @endif

                        @if ($dashboard->awaitingPayment !== null)
                            @include('admin.dashboard.tile', [
                                'card' => DashboardCard::AwaitingPayment,
                                'url' => route('cashier.index', ['service_date' => $day]),
                                'icon' => 'bx-wallet',
                                'tone' => 'orange',
                                'label' => 'Ждут оплаты сегодня',
                                'values' => [$dashboard->awaitingPayment->orders],
                                'meta' => trans_choice('заказ|заказа|заказов', $dashboard->awaitingPayment->orders)
                                    .' на '.$dashboard->awaitingPayment->sums->format(),
                            ])
                        @endif

                        @if ($dashboard->paid !== null)
                            @include('admin.dashboard.tile', [
                                'card' => DashboardCard::PaidToday,
                                'url' => route('cashier.index', ['status' => PaymentState::Paid->value, 'service_date' => $day]),
                                'icon' => 'bx-receipt',
                                'tone' => 'teal',
                                'label' => 'Оплачено сегодня',
                                'values' => collect($dashboard->paid->sums->all())
                                    ->map(fn (Amount $sum, string $currency): string => "{$sum->format()} {$currency}")
                                    ->values()
                                    ->all(),
                                'meta' => $dashboard->paid->orders.' '.trans_choice('заказ|заказа|заказов', $dashboard->paid->orders),
                            ])
                        @endif
                    </div>
                </div>
            @endif

            @if ($hasMain)
                <div @class(['col-12', 'col-xl-8' => $hasSide])>
                    <div class="row row-cards">
                        @if ($dashboard->appointments !== null && $dashboard->receptions !== null)
                            <div class="col-12">
                                @include('admin.dashboard.appointments', ['receptions' => $dashboard->receptions, 'appointments' => $dashboard->appointments, 'day' => $day])
                            </div>
                        @endif

                        @if ($dashboard->admissions !== null)
                            <div class="col-12">
                                @include('admin.dashboard.admissions', ['admissions' => $dashboard->admissions])
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($hasSide)
                <div @class(['col-12', 'col-xl-4' => $hasMain, 'col-xl-6' => ! $hasMain])>
                    <div class="row row-cards">
                        @if ($dashboard->registrarQueue !== null)
                            <div class="col-12">
                                @include('admin.dashboard.registrar-queue', ['queue' => $dashboard->registrarQueue])
                            </div>
                        @endif

                        @if ($dashboard->shows(...DashboardCard::stock()))
                            <div class="col-12">
                                @include('admin.dashboard.stock')
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif
@endsection
