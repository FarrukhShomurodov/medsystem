{{--
    The dashboard sidebar, rendered by App\View\Components\Sidebar from App\Support\Navigation\Navigation.
    The slot holds what the collapsed (mobile) bar shows next to the brand: the account menu.
--}}
<aside id="sidebar" class="navbar navbar-vertical navbar-expand-lg d-print-none" data-bs-theme="dark">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
                aria-controls="sidebar-menu" aria-expanded="false" aria-label="Показать меню">
            <span class="navbar-toggler-icon"></span>
        </button>

        <a href="{{ route('dashboard') }}" class="navbar-brand navbar-brand-autodark">
            <span class="brand-mark" aria-hidden="true"><i class="bx bx-plus-medical"></i></span>
            <span class="brand-name">{{ config('app.name') }}</span>
        </a>

        <div class="navbar-nav flex-row d-lg-none">
            {{ $slot }}
        </div>

        <nav class="collapse navbar-collapse" id="sidebar-menu" aria-label="Главное меню">
            <ul class="navbar-nav pt-lg-3" id="sidebar-nav">
                @foreach ($sections as $section)
                    <li class="nav-section-title">{{ $section->title }}</li>

                    @foreach ($section->items as $item)
                        @if ($item->isGroup())
                            @php($open = $isActive($item))
                            <li @class(['nav-item', 'dropdown', 'active' => $open])>
                                <button type="button" @class(['nav-link', 'dropdown-toggle', 'show' => $open])
                                        data-bs-toggle="dropdown" data-bs-auto-close="false"
                                        aria-expanded="{{ $open ? 'true' : 'false' }}">
                                    <span class="nav-link-icon" aria-hidden="true"><i class="bx {{ $item->icon }}"></i></span>
                                    <span class="nav-link-title">{{ $item->label }}</span>
                                </button>
                                <div @class(['dropdown-menu', 'show' => $open])>
                                    @foreach ($item->children as $link)
                                        @php($current = $isActive($link))
                                        <a href="{{ route($link->route) }}" @class(['dropdown-item', 'active' => $current])
                                           @if ($current) aria-current="page" @endif>{{ $link->label }}</a>
                                    @endforeach
                                </div>
                            </li>
                        @else
                            @php($current = $isActive($item))
                            <li @class(['nav-item', 'active' => $current])>
                                <a href="{{ route($item->route) }}" class="nav-link" @if ($current) aria-current="page" @endif>
                                    <span class="nav-link-icon" aria-hidden="true"><i class="bx {{ $item->icon }}"></i></span>
                                    <span class="nav-link-title">{{ $item->label }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
