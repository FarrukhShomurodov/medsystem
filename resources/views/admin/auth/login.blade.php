{{-- The dashboard sign-in form (AdminAuthController). Its fields: email, password, remember. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Вход · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <script src="{{ Vite::asset('node_modules/jquery/dist/jquery.min.js') }}"></script>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column">
<main class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <span class="navbar-brand navbar-brand-autodark gap-2 fs-2">
                <span class="brand-mark" aria-hidden="true"><i class="bx bx-plus-medical"></i></span>
                {{ config('app.name') }}
            </span>
        </div>

        <div class="card card-md">
            <div class="card-body">
                <h1 class="h2 text-center mb-2">Вход в систему</h1>
                <p class="text-secondary text-center mb-4">Войдите под учётной записью, выданной администратором.</p>

                @error('login')
                    <div class="alert alert-danger" role="alert">
                        <i class="bx bx-error-circle alert-icon" aria-hidden="true"></i>
                        <div>{{ $message }}</div>
                    </div>
                @enderror

                <form method="POST" action="{{ route('dashboard.login') }}" autocomplete="on">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="email">Логин (эл. почта)</label>
                        <input type="email" id="email" name="email" value="{{ is_string(old('email')) ? old('email') : '' }}"
                               @class(['form-control', 'is-invalid' => $errors->has('email')])
                               autocomplete="username" placeholder="name@example.com" required autofocus
                               @error('email') aria-describedby="email-error" aria-invalid="true" @enderror>
                        @error('email')
                            <div class="invalid-feedback" id="email-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Пароль</label>
                        <div class="input-group input-group-flat">
                            <input type="password" id="password" name="password"
                                   @class(['form-control', 'is-invalid' => $errors->has('password')])
                                   autocomplete="current-password" required
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

                    <label class="form-check mb-0">
                        <input type="checkbox" class="form-check-input" name="remember" value="1" @checked(old('remember'))>
                        <span class="form-check-label">Запомнить меня на этом компьютере</span>
                    </label>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary w-100">Войти</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>
