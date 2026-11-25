<?php

namespace App\View\Components;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Foundation\Vite;
use Illuminate\Foundation\ViteException;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

/**
 * <x-optional-vite :entrypoints="['resources/scss/app.scss']" />: the tags of @vite, or nothing when
 * the build is missing or stale.
 *
 * Only the error pages use it. A 500 may be rendered precisely because public/build/manifest.json is
 * missing (a deploy without `npm run build`), and a 503 is shown while a deploy replaces the build:
 * the plain directive would throw again there, and Laravel would fall back to its bare English page.
 * Without the stylesheet the error page still reads well: its layout carries a few lines of inline CSS.
 */
final class OptionalVite extends Component
{
    /**
     * @param  list<string>  $entrypoints
     */
    public function __construct(public array $entrypoints) {}

    public function render(): Htmlable
    {
        try {
            return app(Vite::class)($this->entrypoints);
        } catch (ViteException) {
            return new HtmlString('');
        }
    }
}
