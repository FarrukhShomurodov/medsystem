# Front end

The dashboard is a Russian Blade + jQuery UI on [Tabler](https://tabler.io) (MIT, Bootstrap 5.3),
built by Vite. Every library comes from npm, pinned by `package-lock.json`; nothing is loaded from a
CDN and no template files are committed (`public/build` is generated).

## Build

| Command | What it does |
|---|---|
| `npm ci && npm run build` | builds `public/build` (CI job `assets`, the `assets` stage of `docker/php/Dockerfile`) |
| `npm run dev` | Vite dev server with hot reload; Laravel reads `public/hot` and loads assets from it |
| `make assets` | the build in a Node container (also run by `make install`) |
| `make assets-watch` | the dev server in a container (compose profile `assets`, port `VITE_PORT`, 5173) |
| `php artisan telescope:publish` | Telescope's own assets for local debugging (`public/vendor/telescope`, gitignored) |

Tests never need a build: `tests/TestCase.php` calls `withoutVite()`.

## Files

```
resources/scss/app.scss        Tabler configured with our colour/font, then our partials
  _fonts, _icons               Inter (self-hosted), Boxicons
  vendor/_select2|_flatpickr|_stepper   the widgets restyled for Tabler
  _layout, _components, _pages layout frame, shared components and form rules, single-page blocks
  pages/_patients|_scheduling|_clinical|_dashboard   blocks of one part of the dashboard
resources/js/app.js            loaded on every page: Bootstrap, CSRF, widget initialisers, helpers
resources/js/lib/*.js          the helpers, importable by page modules
resources/js/pages/*.js        one module per page that needs more than a few lines of script
app/Support/Navigation         the sidebar's single definition (links, icons, permissions)
app/View/Components            Sidebar, StatusBadge, StatusIcon, OptionalVite
resources/views/components     sidebar, flash, page-header, delete-button, empty-state, status-*
resources/views/errors         403 404 419 429 500 503 on a standalone layout
```

jQuery is the one library loaded as a classic `<script>` (`Vite::asset()`), before `app.js`: the views'
inline scripts use `$` while the page is parsed. Modules that `import $ from 'jquery'` get the same
instance (alias in `vite.config.js`).

## Widgets

`app.js` sets up every widget of the page once (and `MedSystem.initWidgets(element)` does it for HTML
a script inserts). A page marks its fields; it never initialises these itself:

| Markup | Widget |
|---|---|
| `<select class="form-select select2">` | select2 in Russian; `.select2-tags` lets a typed value become a new option; `data-placeholder`, `data-allow-clear="true"`. A select with its own configuration (AJAX search) has no `.select2` class and is set up by its page module. |
| `<input class="form-control flatpickr-date">` | date picker, submits `Y-m-d`; `.flatpickr-date-time` submits `Y-m-d H:i`, `.flatpickr-clock` `H:i`; `data-mode="range"`, `data-min-date`, `data-max-date`. (Not `.flatpickr-time`: that is the time row inside flatpickr's own calendar.) |
| `<div class="bs-stepper wizard-vertical">` | bs-stepper wizard; `.btn-next` / `.btn-prev` move between steps, a step with an invalid field opens. |
| `[data-row-list]` with `[data-row-list-items]`, `<template data-row-list-template>`, `[data-row-add]`, rows `[data-row]` with `[data-row-remove]` | rows the user adds and removes; `__INDEX__` in `name`/`id`/`for` becomes the row's index; `data-row-list-next` (next index), `data-row-list-min` (rows that must stay). |
| `data-confirm="Вопрос?"` on a form, its submit button or a link | the confirmation modal before the action; `data-confirm-button="Отменить"` names the confirming button. |
| `data-password-toggle="#password"` on a button | shows or hides the password. |

The layout renders `<x-flash />`; a script shows a message with `MedSystem.flash(text, variant)` and asks
with `await MedSystem.confirm(question, { accept })`, never `alert()` / `confirm()`. A 401 or 419 answer to
an AJAX call (`$.ajax` or `fetchJson`) opens a modal that can only reload the page.

Statuses are drawn by `<x-status-badge :status="$status" />` and `<x-status-icon :status="$status" />` from their enums. Next to
the printed label, write `<x-status-icon :status="$status" decorative />`: screen readers then skip the icon.

## Writing a page

```blade
@extends('admin.layouts.app')

@section('title', 'Области')

@section('header')
    <x-page-header title="Области" pretitle="Организация">
        @can('create_regions')
            <x-slot:actions>
                <a href="{{ route('regions.create') }}" class="btn btn-primary"><i class="bx bx-plus" aria-hidden="true"></i>Создать</a>
            </x-slot:actions>
        @endcan
    </x-page-header>
@endsection

@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead><tr><th>Название</th><th class="w-1"></th></tr></thead>
                <tbody>
                @forelse ($regions as $region)
                    <tr>
                        <td>{{ $region->name }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('regions.edit', $region) }}" class="btn btn-sm">Изменить</a>
                                <x-delete-button :action="route('regions.destroy', $region)" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2"><x-empty-state /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $regions->links() }}</div>
    </div>
@endsection
```

A page that needs more than a few lines of script gets a module of its own in `resources/js/pages`
and loads it in its scripts section, as the staff form does:
`@section('scripts') @vite('resources/js/pages/staff-form.js') @endsection`.
`Frontend/ViteEntriesTest.php` fails on an `@vite()` entry whose file does not exist.

Page names follow the buttons that lead to them. A list is named by the plural noun ("Области").
Its "Создать" button opens "Создать область", and a row's "Изменить" button opens "Изменить
область". The `<title>` repeats the heading. On an edit page whose record has a name (an employee,
an account, a patient, "Перевод №3"), the title adds it after a colon, and the breadcrumbs show it
as the last crumb before the heading, linked to the record's own page when there is one. Forms
submit with "Сохранить". A page whose action has a word of its own in the domain keeps it
("Назначить услугу"). `Frontend/AllPagesTest.php` checks the verb and the title of every create
and edit route.

Values are printed in their Russian form through `App\Support\Display` (`@use('App\Support\Display')`):
`Display::date()` "18.11.2026", `dateTime()` "18.11.2026 19:06", `age()` "62 года", `money()`
"150 000,00" (`Amount::format()`), `unitPrice()` for the four-decimal price per unit and
`percent()`. A missing value prints as "—". Form fields keep the ISO dates their inputs expect.
`AllPagesTest` fails on an ISO date or a decimal point left in the text of any page
(`RenderedPage::rawValues()`).

Headings do not skip a level: the page header is the only `<h1>`, wizard steps and card titles
are `<h2>` (styled smaller with `h3` or `card-title`), and a section inside a step is an
`<h3 class="subheader">`. `RenderedPage::markupProblems()` reports a skip on every page.

Server data reaches a page module through `data-*` attributes or a
`<script type="application/json" id="x">@json($data)</script>` block read with `pageData('x')`
(`resources/js/lib/data.js`), never `{!! !!}`; HTML built in JavaScript escapes every value
(`escapeHtml()` or `textContent`). URLs come from `route()` in the view, not from string literals
in the script.

Pagination: `{{ $records->links() }}`. AppServiceProvider makes Bootstrap 5 the default, rendered by
the Russian Tabler-styled view in `resources/views/vendor/pagination`. Its chevron links are named
"Предыдущая страница" and "Следующая страница" (`resources/lang/ru/pagination.php`).

## Related decisions

- [ADR 0010](../adr/0010-tabler-and-vite-instead-of-a-commercial-template.md): Tabler and Vite instead of the commercial template
