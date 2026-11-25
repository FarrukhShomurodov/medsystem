{{--
    The dashboard layout (Tabler vertical layout). A page sets:
      @section('title', 'Области')        the page name, shown in the browser tab as "Области · MedSystem"
      @section('header') <x-page-header ...> @endsection   optional: title, breadcrumbs and actions above the content
      @section('content') ... @endsection
      @section('scripts') ... @endsection  short inline glue, or @vite('resources/js/pages/x.js')
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- @section('title', ...) has escaped the page's title already: it is printed as it is. --}}
    <title>@hasSection('title')@yield('title') · @endif{{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    {{-- jQuery is a classic script so the pages' inline scripts can call $ while the page is parsed. --}}
    <script src="{{ Vite::asset('node_modules/jquery/dist/jquery.min.js') }}"></script>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body>
<a href="#main-content" class="visually-hidden-focusable skip-link">Перейти к содержимому</a>

<div class="page">
    <x-sidebar>
        @include('admin.layouts.user-menu')
    </x-sidebar>

    <div class="page-wrapper">
        {{-- The top bar sits inside the wrapper: Tabler hides the sidebar of a .page that has a
             horizontal navbar of its own as a direct child. --}}
        <header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
            <div class="container-xl">
                <div class="navbar-nav flex-row ms-auto">
                    @include('admin.layouts.user-menu')
                </div>
            </div>
        </header>

        <main id="main-content" tabindex="-1">
            @hasSection('header')
                <div class="page-header d-print-none">
                    <div class="container-xl">
                        @yield('header')
                    </div>
                </div>
            @endif

            <div class="page-body">
                <div class="container-xl">
                    @yield('content')
                </div>
            </div>
        </main>

        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="text-secondary small">© {{ now()->year }} {{ config('app.name') }}</div>
            </div>
        </footer>
    </div>
</div>

<x-flash />

@yield('scripts')
</body>
</html>
