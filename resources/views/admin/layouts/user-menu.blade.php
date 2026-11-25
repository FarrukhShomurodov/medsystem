{{-- The signed-in account and its sign-out form (shown in the top bar, and in the sidebar on small screens). --}}
@php($account = auth()->user())
<div class="nav-item dropdown">
    <button class="nav-link d-flex align-items-center lh-1 p-0 px-2 text-reset border-0 bg-transparent" type="button"
            data-bs-toggle="dropdown" aria-expanded="false" aria-label="Учётная запись {{ $account->email }}">
        <span class="avatar avatar-sm rounded-circle bg-primary-lt" aria-hidden="true">{{ mb_strtoupper(mb_substr($account->email, 0, 1)) }}</span>
        <span class="d-none d-xl-block ps-2 text-start">
            <span class="d-block">{{ $account->email }}</span>
        </span>
    </button>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        <span class="dropdown-header text-truncate">{{ $account->email }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="dropdown-item" type="submit">
                <i class="bx bx-log-out me-2" aria-hidden="true"></i>Выйти
            </button>
        </form>
    </div>
</div>
