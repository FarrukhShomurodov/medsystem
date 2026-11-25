@extends('admin.layouts.app')

@section('title', 'Создать специальность')

@section('header')
    <x-page-header title="Создать специальность" :breadcrumbs="['Специальности' => route('specialties.index')]" />
@endsection

@section('content')
    <div class="card">
        <form action="{{ route('specialties.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <label class="form-label required" for="name">Название</label>
                <div class="input-group input-group-flat">
                    <span class="input-group-text"><i class="bx bx-rename" aria-hidden="true"></i></span>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" placeholder="Например, Терапевт" required
                           @error('name') aria-describedby="name-error" aria-invalid="true" @enderror>
                </div>
                @error('name')
                    <div class="invalid-feedback" id="name-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="card-footer">
                <div class="btn-list justify-content-end">
                    <a href="{{ route('specialties.index') }}" class="btn">Отмена</a>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </div>
        </form>
    </div>
@endsection
