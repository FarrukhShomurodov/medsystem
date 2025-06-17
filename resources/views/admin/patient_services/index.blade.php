@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Назначеные услуги</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Назначеные услуги</span>
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

    <div id="errors_alert"></div>

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Назначеные услуги </h5>
            <a href="{{ route('patient.services.create') }}" class="btn btn-primary "
               style="margin-right: 22px;">Назначить услугу</a>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Услуга</th>
                    <th>Пациент</th>
                    <th>Дата оказания услуги</th>
                    <th>Статус</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($services as $service)
                    <tr>
                        <td>{{ $service->id }}</td>
                        <td>{{ $service->service->name }}</td>
                        <td>{{ $service->patient->first_name }}</td>
                        <td>{{ $service->service_date }}</td>
                        <td> {!! $service->getStatusBadge() !!} </td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-warning"
                                        onclick="location.href='{{ route('patient.services.edit', $service->id) }}'">
                                    Редактировать
                                </button>
                                <form action="{{ route('patient.services.destroy', $service->id) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-4 p-1">
                {{ $services->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
