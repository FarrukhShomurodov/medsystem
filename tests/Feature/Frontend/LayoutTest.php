<?php

use App\Models\Admin;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

/*
| admin/layouts/app (the Tabler layout every dashboard page extends) and the shared components.
*/

beforeEach(function (): void {
    seed(DatabaseSeeder::class);
    actingAs(Admin::factory()->mainAdmin()->create(), 'admin');

    // A page of the dashboard made of a Blade string, to try the layout's sections.
    Route::middleware(['web', 'auth:admin'])->get('/_frontend-test/page', fn () => Blade::render((string) request()->query('blade')));
});

function renderedPage(string $blade): string
{
    return (string) get('/_frontend-test/page?blade='.urlencode($blade))->assertOk()->getContent();
}

function xpath(string $html): DOMXPath
{
    $document = new DOMDocument;
    // The encoding declaration makes libxml read a fragment (no <meta charset>) as UTF-8 too.
    $document->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);

    return new DOMXPath($document);
}

it('is a Russian page with the CSRF token, the sidebar, the flash area and a POST sign-out form', function (): void {
    $html = (string) get('/dashboard')->assertOk()->getContent();
    $page = xpath($html);

    expect($html)->toContain('<html lang="ru">')
        ->and($page->query('//meta[@name="csrf-token"][@content]')->length)->toBe(1)
        ->and($page->query('//aside[@id="sidebar"]//ul[@id="sidebar-nav"]')->length)->toBe(1)
        ->and($page->query('//*[@id="flash-messages"]')->length)->toBe(1)
        ->and($page->query('//form[@method="POST"][@action="'.route('logout').'"][input[@name="_token"]]/button[@type="submit"][normalize-space()="Выйти"]')->length)->toBeGreaterThan(0)
        ->and($page->query('//main[@id="main-content"]')->length)->toBe(1);

    // No inline event handlers in the layout: behaviour lives in resources/js.
    expect(preg_match('/\son[a-z]+\s*=/i', $html))->toBe(0);
});

it('titles the tab "page · application", from @section(\'title\', ...)', function (): void {
    $html = renderedPage("@extends('admin.layouts.app') @section('title', 'Области & города') @section('content') ok @endsection");

    expect($html)->toContain('<title>Области &amp; города · '.e(config('app.name')).'</title>');
});

it('prints the title section as the page set it, escaped once', function (): void {
    // A record's name as the title (a survey, a report): nothing in it is cut or decoded.
    $html = renderedPage("@extends('admin.layouts.app') @section('title', 'MED ERA | Шкала <3 баллов') @section('content') ok @endsection");

    expect($html)->toContain('<title>MED ERA | Шкала &lt;3 баллов · '.e(config('app.name')).'</title>')
        ->and(substr_count($html, '<title>'))->toBe(1);
});

it('names only the application when a page sets no title', function (): void {
    $html = renderedPage("@extends('admin.layouts.app') @section('content') ok @endsection");

    expect($html)->toContain('<title>'.e(config('app.name')).'</title>');
});

it('draws the page header section with breadcrumbs and actions above the content', function (): void {
    $html = renderedPage(<<<'BLADE'
        @extends('admin.layouts.app')
        @section('title', 'Создать')
        @section('header')
            <x-page-header title="Создать" :breadcrumbs="['Персонал' => '/dashboard/staffs/list', 'Раздел' => null]">
                <x-slot:actions><a href="/dashboard/staffs/list" class="btn">К списку</a></x-slot:actions>
            </x-page-header>
        @endsection
        @section('content') ok @endsection
        BLADE);
    $page = xpath($html);

    expect($page->query('//div[contains(@class, "page-header")]//h1[contains(@class, "page-title")][normalize-space()="Создать"]')->length)->toBe(1)
        ->and($page->query('//nav[@aria-label="Навигационная цепочка"]//a[@href="/dashboard/staffs/list"][normalize-space()="Персонал"]')->length)->toBe(1)
        ->and($page->query('//nav//li[normalize-space()="Раздел"][not(.//a)]')->length)->toBe(1)
        ->and($page->query('//nav//li[@aria-current="page"][normalize-space()="Создать"]')->length)->toBe(1)
        ->and($page->query('//div[contains(@class, "btn-list")]/a[normalize-space()="К списку"]')->length)->toBe(1);
});

it('marks the current page and opens its group in the sidebar', function (): void {
    $page = xpath((string) get(route('cities.index'))->assertOk()->getContent());

    expect($page->query('//ul[@id="sidebar-nav"]//a[@aria-current="page"]/@href')->item(0)?->nodeValue)->toBe(route('cities.index'))
        ->and($page->query('//ul[@id="sidebar-nav"]//li[contains(@class, "active")]/button[@aria-expanded="true"][normalize-space()="Области"]')->length)->toBe(1)
        ->and($page->query('//ul[@id="sidebar-nav"]//button[@aria-expanded="true"]')->length)->toBe(1);
});

it('shows flash messages: success hides itself, errors stay and are listed together', function (): void {
    // <x-flash /> reads the shared $errors and the session, as after a redirect back. (An errors bag
    // put into the test session does not survive to the next request, so the component is rendered.)
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag([
        'name' => ['Укажите название.'],
        'city_id' => ['Выберите город.'],
    ])));
    session()->put(['success' => 'Сохранено.', 'error' => 'Не удалось удалить.']);

    $flash = xpath(Blade::render('<x-flash />'));

    expect($flash->query('//*[@id="flash-messages"]/div[contains(@class, "alert-success")][@data-autohide][@role="status"][normalize-space()="Сохранено."]')->length)->toBe(1)
        ->and($flash->query('//*[@id="flash-messages"]/div[contains(@class, "alert-danger")][not(@data-autohide)][@role="alert"][normalize-space()="Не удалось удалить."]')->length)->toBe(1)
        ->and($flash->query('//*[@id="flash-messages"]//h4[normalize-space()="Проверьте данные формы"]')->length)->toBe(1)
        ->and($flash->query('//*[@id="flash-messages"]//ul[contains(@class, "alert-list")]/li')->length)->toBe(2);

    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(['name' => ['Укажите название.']])));
    session()->forget(['success', 'error']);

    $single = xpath(Blade::render('<x-flash />'));

    expect($single->query('//*[@id="flash-messages"]/div[contains(@class, "alert-danger")][normalize-space()="Укажите название."]')->length)->toBe(1)
        ->and($single->query('//ul')->length)->toBe(0);
});

it('escapes flash messages', function (): void {
    $this->withSession(['success' => '<img src=x onerror=alert(1)>'])
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSeeHtml('<img src=x')
        ->assertSeeHtml('&lt;img src=x onerror=alert(1)&gt;');
});

it('renders the delete button as a confirmed DELETE form', function (): void {
    $html = Blade::render('<x-delete-button :action="$url" />', ['url' => '/dashboard/regions/5']);
    $form = xpath($html);

    expect($form->query('//form[@method="POST"][@action="/dashboard/regions/5"][@data-confirm="Удалить эту запись? Действие нельзя отменить."]')->length)->toBe(1)
        ->and($form->query('//form/input[@name="_method"][@value="DELETE"]')->length)->toBe(1)
        ->and($form->query('//form/input[@name="_token"]')->length)->toBe(1)
        ->and($form->query('//form/button[@type="submit"][contains(@class, "btn-outline-danger")][normalize-space()="Удалить"]')->length)->toBe(1);

    $custom = xpath(Blade::render('<x-delete-button action="/x" confirm="Удалить пациента?" label="Удалить пациента" class="w-100" />'));

    expect($custom->query('//form[@data-confirm="Удалить пациента?"]/button[contains(@class, "w-100")][normalize-space()="Удалить пациента"]')->length)->toBe(1);
});

it('renders the empty state with an optional action', function (): void {
    $html = Blade::render('<x-empty-state text="Добавьте первую область."><a href="/x" class="btn">Создать</a></x-empty-state>');
    $empty = xpath($html);

    expect($empty->query('//div[contains(@class, "empty")]/p[contains(@class, "empty-title")][normalize-space()="Записей пока нет"]')->length)->toBe(1)
        ->and($empty->query('//p[contains(@class, "empty-subtitle")][normalize-space()="Добавьте первую область."]')->length)->toBe(1)
        ->and($empty->query('//div[contains(@class, "empty-action")]/a[@href="/x"]')->length)->toBe(1);

    expect(Blade::render('<x-empty-state />'))->not->toContain('empty-action');
});

it('paginates in Russian', function (): void {
    $paginator = new LengthAwarePaginator(range(1, 10), 45, 10, 2, ['path' => '/dashboard/regions']);
    // The default view (AppServiceProvider), as the lists call it: ->links().
    $html = (string) $paginator->links();

    expect($html)->toContain('Показаны')
        ->and(strip_tags($html))->toMatch('/Показаны\s+11–20\s+из\s+45/u')
        ->and($html)->toContain('aria-current="page"><span class="page-link">2</span>');

    // The chevrons are icons: the links are named by their label, which was a bare "<" and ">".
    $links = xpath($html);
    expect($links->query('//a[@rel="prev"][@aria-label="Предыдущая страница"]')->length)->toBe(1)
        ->and($links->query('//a[@rel="next"][@aria-label="Следующая страница"]')->length)->toBe(1);

    $first = (string) (new LengthAwarePaginator(range(1, 10), 45, 10, 1, ['path' => '/dashboard/regions']))->links();
    expect(xpath($first)->query('//li[contains(@class, "disabled")]//span[contains(@class, "visually-hidden")][normalize-space()="Предыдущая страница"]')->length)->toBe(1)
        ->and($first)->not->toContain('"><</span>');
});
