@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Препараты</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('pharmacies.index') }}">Аптеки</a> /</span>Препараты
    </h6>

    <div class="card shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-header">Препараты </h5>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatables-users table border-top">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Препарат</th>
                    <th>Кол-во</th>
                </tr>
                </thead>
                <tbody>
                @foreach($medications as $medication)
                    <tr>
                        <td>{{ $medication->id }}</td>
                        <td>{{ $medication->name }}</td>
                        <td>{{ $medication->pivot->quantity ?? 0 }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
