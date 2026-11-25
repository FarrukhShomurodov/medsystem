<?php

use App\Enums\Contracts\HasLabel;
use App\Http\Controllers\Controller;
use App\Services\Stock\StockLedger;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Session as SessionContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Session;

/*
| Architecture rules for the namespaced code (App, Database\Factories, Database\Seeders), guarding
| the layout of docs/architecture/folder-layout.md. Each rule holds for the whole code base today;
| ignoring() only leaves out the namespace a rule is about, or the one class the rule's name allows,
| never a class that quietly breaks it.
*/

arch('PHP preset: no debugging or unsafe language functions')
    ->preset()->php();

arch('security preset: no weak hashing, predictable randomness, eval or shell calls')
    ->preset()->security();

arch('no dd(), dump(), ray() or var_dump() left behind')
    ->expect(['dd', 'ddd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('env() is read in config files only')
    ->expect('env')
    ->not->toBeUsed();

it('reads env() in config files only, in the files without a namespace too', function (): void {
    // arch() sees namespaced classes only; the routes, bootstrap/app.php and the migrations are not.
    $root = dirname(__DIR__, 2);
    $files = [
        $root.'/bootstrap/app.php',
        ...glob($root.'/routes/*.php'),
        ...glob($root.'/database/migrations/*.php'),
    ];

    $callers = array_values(array_filter($files, fn (string $file): bool => callsEnv((string) file_get_contents($file))));

    expect($files)->not->toBeEmpty()
        ->and($callers)->toBe([]);
});

/**
 * Whether the code calls the global env() function (not a method or a function named env).
 */
function callsEnv(string $code): bool
{
    $tokens = array_values(array_filter(
        token_get_all($code),
        fn (array|string $token): bool => ! is_array($token) || ! in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true),
    ));

    foreach ($tokens as $index => $token) {
        if (! is_array($token) || strtolower(ltrim($token[1], '\\')) !== 'env' || ($tokens[$index + 1] ?? null) !== '(') {
            continue;
        }

        $previous = $tokens[$index - 1] ?? null;

        if (! is_array($previous) || ! in_array($previous[0], [T_OBJECT_OPERATOR, T_NULLSAFE_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW], true)) {
            return true;
        }
    }

    return false;
}

arch('controllers have the Controller suffix')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toHaveSuffix('Controller');

arch('controllers extend the base Controller')
    ->expect('App\Http\Controllers')
    ->classes()
    ->toExtend(Controller::class)
    ->ignoring(Controller::class);

arch('controllers live in App\Http\Controllers')
    ->expect('App')
    ->not->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers');

arch('models extend Eloquent Model')
    ->expect('App\Models')
    ->classes()
    ->toExtend(Model::class);

arch('models live in App\Models')
    ->expect('App')
    ->not->toExtend(Model::class)
    ->ignoring('App\Models');

arch('App\Enums holds string-backed enums, besides their contracts and concerns')
    ->expect('App\Enums')
    ->toBeStringBackedEnums()
    ->ignoring(['App\Enums\Contracts', 'App\Enums\Concerns']);

arch('every enum of App\Enums has a label()')
    ->expect('App\Enums')
    ->toImplement(HasLabel::class)
    ->ignoring(['App\Enums\Contracts', 'App\Enums\Concerns']);

arch('the enum contracts are interfaces')
    ->expect('App\Enums\Contracts')
    ->toBeInterfaces();

arch('the enum concerns are traits')
    ->expect('App\Enums\Concerns')
    ->toBeTraits();

arch('form requests extend FormRequest and have the Request suffix')
    ->expect('App\Http\Requests')
    ->classes()
    ->toExtend(FormRequest::class)
    ->toHaveSuffix('Request');

arch('form requests live in App\Http\Requests')
    ->expect('App')
    ->not->toExtend(FormRequest::class)
    ->ignoring('App\Http\Requests');

arch('domain exceptions extend DomainException, directly or through their module base')
    ->expect('App\Exceptions')
    ->classes()
    ->toExtend(DomainException::class)
    ->toHaveSuffix('Exception');

arch('exceptions live in App\Exceptions')
    ->expect('App')
    ->not->toImplement(Throwable::class)
    ->ignoring('App\Exceptions');

arch('services do not depend on the HTTP layer or the session')
    ->expect('App\Services')
    ->not->toUse([
        'App\Http',
        Request::class,
        Response::class,
        JsonResponse::class,
        RedirectResponse::class,
        RequestFacade::class,
        ResponseFacade::class,
        Redirect::class,
        Session::class,
        SessionContract::class,
        Cookie::class,
        'Symfony\Component\HttpFoundation',
        'request',
        'response',
        'redirect',
        'back',
        'to_route',
        'abort',
        'session',
        'cookie',
    ]);

arch('services take the acting admin as a parameter; only the stock ledger reads the signed-in one')
    // StockLedger records the signed-in admin as the author of every stock movement, null outside a
    // request (docs/architecture/domain.md); any other service gets its actor from the controller.
    ->expect('App\Services')
    ->not->toUse([Auth::class, AuthFactory::class, Guard::class, 'auth'])
    ->ignoring(StockLedger::class);

arch('*Query classes live only in App\Queries')
    ->expect('App')
    ->not->toHaveSuffix('Query')
    ->ignoring('App\Queries');

arch('read models do not depend on the HTTP layer or the session')
    ->expect('App\Queries')
    ->not->toUse([
        'App\Http',
        'Illuminate\Http',
        'Symfony\Component\HttpFoundation',
        RequestFacade::class,
        Session::class,
        Auth::class,
        'request',
        'response',
        'session',
        'auth',
    ]);

arch('Blade components and the menu do not query models')
    ->expect(['App\View', 'App\Support\Navigation'])
    ->not->toUse([
        'App\Models',
        'Illuminate\Database',
        DB::class,
    ]);

it('cites no ticket of a tracker the repository does not hold', function (): void {
    // A comment states the rule or the behaviour it guards; the history of a fix is told by
    // CHANGELOG.md and the ADRs, not by the ids of a review readers cannot open.
    $root = dirname(__DIR__, 2);
    $files = glob($root.'/*.md');

    foreach (['app', 'bootstrap', 'config', 'database', 'docs', 'resources', 'routes', 'tests'] as $directory) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/{$directory}", FilesystemIterator::SKIP_DOTS)) as $file) {
            if (in_array($file->getExtension(), ['php', 'js', 'md', 'css'], true)) {
                $files[] = $file->getPathname();
            }
        }
    }

    $citing = array_values(array_filter(
        $files,
        fn (string $file): bool => preg_match('/\b(BUG|SEC|ARCH|DB|FE|UPG)-[A-Z]?\d+\b/', (string) file_get_contents($file)) === 1,
    ));

    expect($files)->not->toBeEmpty()
        ->and($citing)->toBe([]);
});
