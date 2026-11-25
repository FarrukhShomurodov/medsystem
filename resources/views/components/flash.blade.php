{{--
    Flash messages and validation errors for every dashboard page, rendered once by the layout.
    Controllers set session('success' | 'info' | 'warning' | 'error'). A success or info message
    hides itself after a few seconds (app.js, [data-autohide]); warnings and errors stay until closed.
--}}
@php
    $flashes = [
        'success' => ['variant' => 'success', 'icon' => 'bx-check-circle', 'autohide' => true],
        'info' => ['variant' => 'info', 'icon' => 'bx-info-circle', 'autohide' => true],
        'warning' => ['variant' => 'warning', 'icon' => 'bx-error', 'autohide' => false],
        'error' => ['variant' => 'danger', 'icon' => 'bx-error-circle', 'autohide' => false],
    ];
@endphp

<div id="flash-messages" class="flash-messages" aria-live="polite">
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bx bx-error-circle alert-icon" aria-hidden="true"></i>
            @if (count($errors->all()) === 1)
                <div>{{ $errors->first() }}</div>
            @else
                <h4 class="alert-heading">Проверьте данные формы</h4>
                <ul class="alert-list">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
        </div>
    @endif

    @foreach ($flashes as $key => $flash)
        @if (session()->has($key))
            <div class="alert alert-{{ $flash['variant'] }} alert-dismissible fade show"
                 role="{{ $flash['autohide'] ? 'status' : 'alert' }}" @if ($flash['autohide']) data-autohide @endif>
                <i class="bx {{ $flash['icon'] }} alert-icon" aria-hidden="true"></i>
                <div>{{ session($key) }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
            </div>
        @endif
    @endforeach
</div>
