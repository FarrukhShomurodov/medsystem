{{--
    The standalone page of an HTTP error (errors/403 ... errors/503). It touches neither the database
    nor the session, so it renders whatever broke. The stylesheet is optional (<x-optional-vite>):
    when the build is missing, as in a 500 caused by it or a 503 during a deploy, the few inline
    rules below keep the page readable.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('code') · @yield('title') · {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <style>
        body { margin: 0; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; color: #1f2937; background: #f6f8fb; }
        .error-page { display: flex; min-height: 100vh; align-items: center; justify-content: center; padding: 1.5rem; text-align: center; }
        .error-code { font-size: 4rem; font-weight: 700; line-height: 1; color: #5a8dee; }
        .error-page h1 { margin: 1rem 0 0.5rem; font-size: 1.5rem; }
        .error-page p { max-width: 32rem; margin: 0 auto 1.5rem; color: #6b7280; line-height: 1.5; }
    </style>
    <x-optional-vite :entrypoints="['resources/scss/app.scss']" />
</head>
<body>
<main class="error-page page page-center">
    <div class="container container-tight py-4">
        <div class="empty">
            <div class="error-code empty-header" aria-hidden="true">@yield('code')</div>
            <h1 class="empty-title">@yield('title')</h1>
            <p class="empty-subtitle text-secondary">@yield('message')</p>
            <div class="empty-action">
                <a href="{{ url('dashboard') }}" class="btn btn-primary">На главную страницу</a>
            </div>
        </div>
    </div>
</main>
</body>
</html>
