@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Анализы</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Анализы</span>
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
            <h5 class="card-header">Анализы </h5>
            <a href="{{ route('analyzes.create') }}" class="btn btn-primary "
               style="margin-right: 22px;">Назначить анализ</a>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Имя Пациента</th>
                    <th>Тип анализа</th>
                    <th>Статус</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($analyzes as $analyze)
                    <tr>
                        <td>{{ $analyze->id }}</td>
                        <td>{{ $analyze->patient->first_name }}</td>
                        <td>
                            {{ implode(', ', $analyze->types->pluck('name')->toArray()) }}
                        </td>
                        <td>
                            @if ($analyze->status == 'received')
                                <span class="badge bg-label-success me-1">Результаты получены</span>
                            @elseif($analyze->status == 'completed')
                                <span class="badge bg-label-info me-1">Выполнен</span>
                            @else
                                <span class="badge bg-label-warning me-1">Назначен</span>
                            @endif

                        </td>

                        <td>
                            @if(!$analyze->results->isEmpty())
                                <button
                                    class="btn btn-primary btn-sm flex items-center gap-2"
                                    onclick="window.location='{{ route('analyze.result.show', $analyze->id) }}'">
                                    <i class="bx bx-show text-lg"></i>
                                    Посмотреть результаты
                                </button>
                            @else
                                <button
                                    class="btn btn-primary btn-sm flex items-center gap-2"
                                    onclick="window.location='{{ route('analyze.result', $analyze->id) }}'">
                                    <i class="bx bx-edit text-lg"></i>
                                    Добавить результаты
                                </button>
                            @endif
                        </td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-icon"
                                        onclick="location.href='{{ route('analyzes.edit', $analyze->id) }}'"><i
                                        class="bx bx-edit"></i></button>
                                <form action="{{ route('analyzes.destroy', $analyze->id) }}" method="POST"
                                      style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-icon delete-record"><i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="mt-4 p-1">
                {{ $analyzes->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
