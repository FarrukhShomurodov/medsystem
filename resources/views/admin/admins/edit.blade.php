@extends('admin.layouts.app')

@section('title', 'Изменить пользователя панели: '.$admin->email)

@section('header')
    <x-page-header title="Изменить пользователя панели" :breadcrumbs="['Пользователи панели' => route('admins.index'), $admin->email => null]" />
@endsection

@section('content')
    <div class="card">
        <form action="{{ route('admins.update', $admin) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required" for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="name@example.com" autocomplete="off" required
                               @error('email') aria-describedby="email-error" aria-invalid="true" @enderror>
                        @error('email')
                            <div class="invalid-feedback" id="email-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="password">Новый пароль</label>
                        <div class="input-group input-group-flat">
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Оставьте пустым, чтобы не менять" autocomplete="new-password"
                                   @error('password') aria-describedby="password-error" aria-invalid="true" @enderror>
                            <span class="input-group-text">
                                <button type="button" class="link-secondary border-0 bg-transparent p-0 lh-1" data-password-toggle="#password"
                                        aria-controls="password" aria-pressed="false" aria-label="Показать пароль">
                                    <i class="bx bx-show" aria-hidden="true"></i>
                                </button>
                            </span>
                        </div>
                        @error('password')
                            <div class="invalid-feedback" id="password-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="btn-list justify-content-end">
                    <a href="{{ route('admins.index') }}" class="btn">Отмена</a>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </div>
            </div>
        </form>
    </div>
@endsection
