@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Карты обследования</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Карты обследования</span>
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
            <h5 class="card-header">Карты обследования </h5>
            <a href="{{ route('maps.create') }}" class="btn btn-primary "
               style="margin-right: 22px;">Создать</a>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Имя</th>
                    <th>Лечащие врач</th>
                    <th>Отделение, к которому прикреплен</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($maps as $map)
                    <tr>
                        <td>{{ $map->id }}</td>
                        <td>{{ $map->patient->first_name }}</td>
                        <td>{{ $map->doctor->first_name }}</td>
                        <td>{{ $map->institution->name }}</td>

                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-icon"
                                        onclick="location.href='{{ route('maps.show', $map->id) }}'"><i
                                        class="bx bx-show"></i></button>
                                <button class="btn btn-sm btn-icon"
                                        onclick="location.href='{{ route('maps.edit', $map->id) }}'"><i
                                        class="bx bx-edit"></i></button>
                                <form action="{{ route('maps.destroy', $map->id) }}" method="POST"
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
                {{ $maps->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
