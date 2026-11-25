{{--
    The heading of a dashboard page, placed in the layout's header section:

    @section('header')
        <x-page-header title="Создать" :breadcrumbs="['Персонал' => route('staffs.index')]">
            <x-slot:actions>
                <a href="..." class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
            </x-slot:actions>
        </x-page-header>
    @endsection

    breadcrumbs: the pages above this one, label => URL (null for a crumb without a page).
    pretitle:    an optional small line above the title (the section of the dashboard).
--}}
@props(['title', 'breadcrumbs' => [], 'pretitle' => null])

<div {{ $attributes->class(['row g-2 align-items-center']) }}>
    <div class="col">
        @if ($breadcrumbs !== [])
            <nav aria-label="Навигационная цепочка">
                <ol class="breadcrumb mb-1">
                    @foreach ($breadcrumbs as $label => $url)
                        <li class="breadcrumb-item">
                            @if ($url)
                                <a href="{{ $url }}">{{ $label }}</a>
                            @else
                                {{ $label }}
                            @endif
                        </li>
                    @endforeach
                    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                </ol>
            </nav>
        @elseif ($pretitle)
            <div class="page-pretitle">{{ $pretitle }}</div>
        @endif
        <h1 class="page-title">{{ $title }}</h1>
    </div>

    @isset($actions)
        <div class="col-auto ms-auto d-print-none">
            <div class="btn-list">{{ $actions }}</div>
        </div>
    @endisset
</div>
