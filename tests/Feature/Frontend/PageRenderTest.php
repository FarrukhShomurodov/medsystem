<?php

use Tests\Feature\Frontend\RenderedPage;

use function Pest\Laravel\get;

/*
| The sign-in page, which AllPagesTest cannot reach (it signs in first), and a self-check of the
| inline-script parser that AllPagesTest relies on.
*/

it('serves the sign-in page without the old template, with scripts that parse', function (): void {
    $html = (string) get(route('dashboard.login'))->assertOk()->getContent();

    expect(RenderedPage::find($html, RenderedPage::TEMPLATE_PATTERNS))->toBe([])
        ->and(RenderedPage::find($html, RenderedPage::CDN_PATTERNS))->toBe([]);

    if (RenderedPage::nodeAvailable()) {
        expect(RenderedPage::unparsableScripts(['login' => $html]))->toBe([]);
    }
});

it('catches an inline script that does not parse, and leaves templates and data blocks alone', function (): void {
    if (! RenderedPage::nodeAvailable()) {
        $this->markTestSkipped('Node is not installed.');
    }

    $html = '<script>let total = ;</script>'
        .'<script type="text/template"><div class="row">{{ name }}</div></script>'
        .'<script type="application/json">{"a": 1}</script>'
        .'<script src="/build/assets/app.js"></script>'
        .'<script>const label = "</div>"; window.ok = label.length;</script>';

    expect(RenderedPage::inlineScripts($html))->toHaveCount(2)
        ->and(RenderedPage::unparsableScripts(['sample' => $html]))->toHaveCount(1)
        ->and(RenderedPage::unparsableScripts(['sample' => $html])[0])->toStartWith('sample #1:');
});
