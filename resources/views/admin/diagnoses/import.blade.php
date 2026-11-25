@extends('admin.layouts.app')

@section('title', 'Импорт диагнозов')

@section('header')
    <x-page-header title="Импорт диагнозов" :breadcrumbs="['Диагнозы' => route('diagnoses.index')]" />
@endsection

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('diagnoses.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                <div class="col-lg-6">
                    <label class="form-label required" for="file">Файл</label>
                    <input type="file" id="file" name="file" class="form-control @error('file') is-invalid @enderror"
                           accept=".xlsx,.xls,.csv" aria-describedby="file-hint" required>
                    @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-hint" id="file-hint">Таблица Excel (xlsx, xls) или CSV, до 10 МБ.</div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit"><i class="bx bx-import" aria-hidden="true"></i>Импортировать</button>
            </div>
        </form>
    </div>
@endsection
