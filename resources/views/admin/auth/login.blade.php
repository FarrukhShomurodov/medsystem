<!DOCTYPE html>
<html lang="en" class="light-style  customizer-hide" dir="ltr" data-theme="theme-default"
      data-assets-path="/" data-template="horizontal-menu-template">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>

    <title>Медицинская система | Вход</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('vendor/css/rtl/core.css') }}" class="template-customizer-core-css"/>
    <link rel="stylesheet" href="{{ asset('vendor/css/rtl/theme-default.css') }}"
          class="template-customizer-theme-css"/>
    <link rel="stylesheet" href="{{ asset('css/demo.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/css/pages/page-auth.css') }}">

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/fonts/boxicons.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/fonts/fontawesome.css') }}"/>
    <link rel="stylesheet" href="{{ asset('vendor/fonts/flag-icons.css') }}"/>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet">

    <script src="{{ asset('vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('js/config.js') }}"></script>

</head>
<body>
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <div class="card">
                <div class="card-body">
{{--                    <div class="app-brand justify-content-center">--}}
{{--                        <a href="{{ route('dashboard') }}" class="app-brand-link gap-2">--}}
{{--                            <span class="app-brand-text demo h3 mb-0 fw-bold">Медицинская система</span>--}}
{{--                        </a>--}}
{{--                    </div>--}}
                    <!-- /Logo -->
                    <h4 class="mb-2">Добро пожаловать в Медицинскую систему! 👋</h4>
                    <p class="mb-4">Пожалуйста, войдите в свою учетную запись и начните приключение
                    @if ($errors->any())
                        <div class="alert alert-solid-danger" role="alert">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </div>
                    @endif
                    <form id="formAuthentication" class="mb-3" action="{{ route('dashboard.login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="login" class="form-label">Логин</label>
                            <input type="text" class="form-control @error('email') is-invalid @enderror" id="login"
                                   name="email"
                                   placeholder="Введите ваш логи" autofocus>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="password">Пароль</label>
                            </div>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror" name="password"
                                       placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                       aria-describedby="password"/>
                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-me">
                                <label class="form-check-label" for="remember-me">
                                    Запомнить меня
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary d-grid w-100" type="submit">Войти</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Core JS -->
<script src="{{ asset('vendor/libs/jquery/jquery.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="{{ asset('vendor/libs/popper/popper.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="{{ asset('vendor/js/bootstrap.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script
    src="{{ asset('vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script src="{{ asset('vendor/libs/hammer/hammer.js') }}?v={{ now()->addHours(2)->timestamp }}" defer></script>

<script src="{{ asset('vendor/libs/i18n/i18n.js') }}"></script>
<script src="{{ asset('vendor/libs/typeahead-js/typeahead.js') }}"></script>

<script src="{{ asset('vendor/js/menu.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>

<!-- Vendors JS -->
<script
    src="{{ asset('vendor/libs/formvalidation/dist/js/plugins/Bootstrap5.min.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>
<script
    src="{{ asset('vendor/libs/formvalidation/dist/js/plugins/AutoFocus.min.js') }}?v={{ now()->addHours(2)->timestamp }}"></script>

<!-- Main JS -->
<script src="{{ asset('js/main.js') }}"></script>

<!-- Page JS -->
<script src="{{asset('js/pages-auth.js')}}"></script>
</body>

