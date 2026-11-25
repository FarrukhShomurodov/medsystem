<?php

namespace Tests\Feature\Frontend;

use DOMDocument;
use DOMElement;
use DOMNameSpaceNode;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Checks on the HTML of a rendered dashboard page that a browser would otherwise catch only at run
 * time: leftovers of the removed commercial template, CDN assets, inline scripts that do not parse.
 */
final class RenderedPage
{
    /**
     * Paths and names of the removed template and its plugins. A page that still points at one of
     * them loads a 404 (the files are gone) or depends on the paid template again.
     */
    public const array TEMPLATE_PATTERNS = [
        'vendor/libs/', 'vendor/css/', 'vendor/js/', 'vendor/fonts/', 'assets/vendor/',
        'js/main.js', 'js/config.js', 'js/dashboard.js', 'css/demo.css', 'js/forms-', 'js/form-wizard',
        'template-customizer', 'formvalidation', 'pixinvent', 'frest', 'data-template=',
    ];

    /** Script and font hosts the dashboard must not load from: everything comes from the build. */
    public const array CDN_PATTERNS = ['cdn.jsdelivr.net', 'cdnjs.cloudflare.com', 'unpkg.com', 'fonts.googleapis.com', 'fonts.gstatic.com', 'code.jquery.com'];

    /**
     * Class names of the removed template and of Bootstrap 4 that neither Tabler nor our own
     * stylesheet defines: an element that still carries one is unstyled (docs/architecture/frontend.md
     * has the mapping to the Tabler classes).
     */
    public const array OLD_CLASSES = [
        'input-group-merge', 'icon-border', 'breadcrumb-wrapper', 'card-datatable', 'datatables-users', 'avatar-initial',
        'form-group', 'form-row', 'container-p-y', 'app-brand', 'mb_3', 'switch', 'switch-input', 'switch-toggle-slider',
        'btn-close-white', 'custom-select', 'custom-control', 'input-group-append', 'input-group-prepend', 'btn-block',
        'no-gutters', 'float-right', 'float-left', 'text-right', 'text-left', 'font-bold', 'text-lg', 'list-disc',
        'fa', 'fas', 'far', 'bi',
    ];

    /** Prefixes of the same: the old badge/button/alert colours, layout and menu parts, other icon sets, Bootstrap 4 spacing. */
    public const array OLD_CLASS_PREFIXES = [
        'btn-label-', 'bg-label-', 'alert-solid-', 'layout-', 'menu-', 'fa-', 'bi-', 'ml-', 'mr-', 'pl-', 'pr-',
    ];

    /**
     * @return list<string> the patterns of `$patterns` found in the page
     */
    public static function find(string $html, array $patterns): array
    {
        return array_values(array_filter($patterns, fn (string $pattern): bool => stripos($html, $pattern) !== false));
    }

    /**
     * The inline scripts of the page: JavaScript <script> elements without src (templates and JSON
     * data blocks are not scripts). The HTML spec ends script data at the first "</script", which
     * is what the pattern does.
     *
     * @return list<array{code: string, module: bool}>
     */
    public static function inlineScripts(string $html): array
    {
        preg_match_all('#<script\b([^>]*)>(.*?)</script\s*>#is', $html, $matches, PREG_SET_ORDER);

        $scripts = [];

        foreach ($matches as [, $attributes, $code]) {
            if (preg_match('/\bsrc\s*=/i', $attributes) === 1) {
                continue;
            }

            $type = preg_match('/\btype\s*=\s*["\']?([^"\'\s>]+)/i', $attributes, $typeMatch) === 1 ? strtolower($typeMatch[1]) : '';

            if ($type !== '' && $type !== 'module' && ! in_array($type, ['text/javascript', 'application/javascript'], true)) {
                continue;
            }

            $scripts[] = ['code' => $code, 'module' => $type === 'module'];
        }

        return $scripts;
    }

    /**
     * What a browser or a screen reader would trip over in the markup of the page: old class names,
     * inline event handlers, javascript: links, Bootstrap 4 data attributes, images without alt,
     * form controls, buttons and links without an accessible name, POST forms without the CSRF
     * token, buttons that would submit their form by accident, selects whose "choose" placeholder
     * is never selected, ids used twice, and headings that skip a level.
     *
     * @return list<string>
     */
    public static function markupProblems(string $html): array
    {
        $document = new DOMDocument;
        $errors = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($errors);

        $xpath = new DOMXPath($document);
        $problems = [];

        foreach (self::elements($xpath, '//*[@class]') as $element) {
            foreach (preg_split('/\s+/', trim($element->getAttribute('class'))) ?: [] as $class) {
                $old = in_array($class, self::OLD_CLASSES, true)
                    || array_filter(self::OLD_CLASS_PREFIXES, fn (string $prefix): bool => str_starts_with($class, $prefix)) !== [];

                if ($old) {
                    $problems[] = 'old class "'.$class.'" on '.self::describe($element);
                }
            }
        }

        foreach (self::elements($xpath, '//*[@*]') as $element) {
            foreach ($element->attributes ?? [] as $attribute) {
                $name = strtolower($attribute->nodeName);

                if (str_starts_with($name, 'on')) {
                    $problems[] = "inline handler {$name}= on ".self::describe($element);
                } elseif (in_array($name, ['data-toggle', 'data-dismiss', 'data-target'], true) && ! self::isStepperTarget($element, $name)) {
                    $problems[] = "Bootstrap 4 attribute {$name}= on ".self::describe($element);
                } elseif (in_array($name, ['href', 'src', 'action'], true) && str_starts_with(strtolower(trim($attribute->nodeValue ?? '')), 'javascript:')) {
                    $problems[] = 'javascript: URL on '.self::describe($element);
                }
            }
        }

        foreach (self::elements($xpath, '//img[not(@alt)]') as $image) {
            $problems[] = 'image without alt: '.self::describe($image);
        }

        $labelled = [];

        foreach (self::elements($xpath, '//label[@for]') as $label) {
            $labelled[$label->getAttribute('for')] = true;
        }

        // Rows inside a <template> get their ids, labels and texts from the script that copies them.
        $live = '[not(ancestor::template)]';
        $controls = "//input[not(@type='hidden' or @type='submit' or @type='button' or @type='reset' or @type='image')]{$live} | //select{$live} | //textarea{$live}";

        foreach (self::elements($xpath, $controls) as $control) {
            $named = self::hasNameAttribute($control)
                || isset($labelled[$control->getAttribute('id')])
                || $xpath->query('ancestor::label', $control)?->length > 0;

            if (! $named) {
                $problems[] = 'form control without a label: '.self::describe($control);
            }
        }

        foreach (self::elements($xpath, "//button{$live} | //a[@href]{$live}") as $control) {
            $text = trim(preg_replace('/\s+/u', ' ', $control->textContent) ?? '');
            $named = $text !== '' || self::hasNameAttribute($control) || $xpath->query('.//img[@alt!=""]', $control)?->length > 0;

            if (! $named) {
                $problems[] = 'button or link without a name: '.self::describe($control);
            }
        }

        foreach (self::elements($xpath, '//form') as $form) {
            $method = strtolower($form->getAttribute('method') ?: 'get');

            if ($method === 'post' && $xpath->query('.//input[@name="_token"]', $form)?->length === 0) {
                $problems[] = 'POST form without its CSRF token: '.self::describe($form);
            } elseif (! in_array($method, ['get', 'post', 'dialog'], true)) {
                $problems[] = "form method=\"{$method}\" (use @method): ".self::describe($form);
            }
        }

        // A button without a type submits its form; the wizards' .btn-next / .btn-prev are handled by app.js.
        foreach (self::elements($xpath, "//form//button[not(@type)]{$live}") as $button) {
            if (preg_match('/\bbtn-(next|prev)\b/', $button->getAttribute('class')) !== 1) {
                $problems[] = 'button without a type inside a form: '.self::describe($button);
            }
        }

        // A disabled placeholder that nothing selects: the browser selects the first real option
        // instead, so a field the user never touched is submitted with that value, past "required".
        foreach (self::elements($xpath, "//select[not(@multiple)]{$live}") as $select) {
            $first = $xpath->query('.//option', $select)?->item(0);
            $placeholder = $first instanceof DOMElement && $first->getAttribute('value') === '' && $first->hasAttribute('disabled');

            if ($placeholder && $xpath->query('.//option[@selected]', $select)?->length === 0) {
                $problems[] = 'placeholder never selected: '.self::describe($select);
            }
        }

        foreach (self::elements($xpath, '//*[@form]') as $control) {
            if ($xpath->query('//form[@id="'.$control->getAttribute('form').'"]')?->length === 0) {
                $problems[] = 'form="'.$control->getAttribute('form').'" names no form: '.self::describe($control);
            }
        }

        $ids = [];

        foreach (self::elements($xpath, "//*[@id]{$live}") as $element) {
            $ids[$element->getAttribute('id')][] = $element;
        }

        foreach ($ids as $id => $elements) {
            if (count($elements) > 1) {
                $problems[] = 'id "'.$id.'" used '.count($elements).' times';
            }
        }

        // Screen readers list the headings as an outline: a level may go back up, never skip one
        // on the way down (h1 then h6 hides where the sections belong).
        $previous = null;

        foreach (self::elements($xpath, "//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6]{$live}") as $heading) {
            $level = (int) substr($heading->tagName, 1);

            if ($previous !== null && $level > $previous + 1) {
                $problems[] = "heading skips from h{$previous} to h{$level}: \"".trim((string) preg_replace('/\s+/u', ' ', $heading->textContent)).'"';
            }

            $previous = $level;
        }

        return $problems;
    }

    /**
     * Values printed as the database or PHP hands them over instead of in the Russian form of the
     * rest of the dashboard: ISO dates ("2026-11-18", "2026-11-18 19:06:26") and decimals with a
     * point ("51250.00", "41000.0000"). Only the text a reader sees counts: scripts, templates and
     * form values (date inputs take ISO dates) are left out.
     *
     * @return list<string> the raw values found, each once
     */
    public static function rawValues(string $html): array
    {
        $document = new DOMDocument;
        $errors = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($errors);

        $texts = (new DOMXPath($document))->query('//body//text()[not(ancestor::script or ancestor::style or ancestor::template or ancestor::textarea)]') ?: [];
        $found = [];

        foreach ($texts as $text) {
            preg_match_all('/\b\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?)?\b|(?<![\d.])\d+\.\d{2,4}(?![\d.])/', (string) $text->textContent, $matches);

            foreach ($matches[0] as $value) {
                $found[$value] = true;
            }
        }

        return array_keys($found);
    }

    /**
     * @return list<DOMElement>
     */
    private static function elements(DOMXPath $xpath, string $query): array
    {
        $nodes = $xpath->query($query);

        return $nodes === false ? [] : array_values(array_filter(iterator_to_array($nodes), fn (DOMNode|DOMNameSpaceNode $node): bool => $node instanceof DOMElement));
    }

    /** A name a screen reader can say: "<", ">" or "×" alone is read as a symbol, or not at all. */
    private static function hasNameAttribute(DOMElement $element): bool
    {
        return preg_match('/[\p{L}\p{N}]/u', $element->getAttribute('aria-label')) === 1
            || trim($element->getAttribute('aria-labelledby')) !== ''
            || preg_match('/[\p{L}\p{N}]/u', $element->getAttribute('title')) === 1;
    }

    /** bs-stepper's steps name their pane with data-target, which is its own markup, not Bootstrap 4's. */
    private static function isStepperTarget(DOMElement $element, string $attribute): bool
    {
        return $attribute === 'data-target' && str_contains(' '.$element->getAttribute('class').' ', ' step ');
    }

    private static function describe(DOMElement $element): string
    {
        $parts = [$element->tagName];

        foreach (['id', 'name', 'type', 'class', 'href'] as $attribute) {
            if ($element->hasAttribute($attribute)) {
                $parts[] = $attribute.'="'.mb_strimwidth($element->getAttribute($attribute), 0, 60, '…').'"';
            }
        }

        return '<'.implode(' ', $parts).'>';
    }

    /**
     * The body rows of a table of the page, each as its cells' text under the column headers (a
     * visually hidden header counts by its text). Rows that span the table, such as an empty state,
     * are left out.
     *
     * @param  string  $table  XPath of the <table>, e.g. "//main//table"
     * @return list<array<string, string>>
     */
    public static function tableRows(string $html, string $table): array
    {
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        $xpath = new DOMXPath($document);
        $text = fn (DOMNode $node): string => trim((string) preg_replace('/\s+/u', ' ', $node->textContent));

        $headers = array_map($text, iterator_to_array($xpath->query("({$table})[1]/thead/tr/th") ?: []));
        $rows = [];

        foreach ($xpath->query("({$table})[1]/tbody/tr") ?: [] as $row) {
            $cells = array_map($text, iterator_to_array($xpath->query('td', $row) ?: []));

            if (count($cells) === count($headers)) {
                $rows[] = array_combine($headers, $cells);
            }
        }

        return $rows;
    }

    /**
     * The text of the options of the <select> with the given id, in page order.
     *
     * @return list<string>
     */
    public static function selectOptions(string $html, string $id): array
    {
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        $options = (new DOMXPath($document))->query('//select[@id="'.$id.'"]/option') ?: [];

        return array_values(array_map(
            fn (DOMNode $option): string => trim((string) preg_replace('/\s+/u', ' ', $option->textContent)),
            iterator_to_array($options),
        ));
    }

    public static function nodeAvailable(): bool
    {
        return Process::run(['node', '--version'])->successful();
    }

    /**
     * Parse every inline script of the pages with Node (vm.Script: parsed, never run).
     *
     * @param  array<string, string>  $pages  label => HTML
     * @return list<string> one line per script that does not parse
     */
    public static function unparsableScripts(array $pages): array
    {
        $scripts = [];

        foreach ($pages as $label => $html) {
            foreach (self::inlineScripts($html) as $index => $script) {
                $scripts[] = ['label' => "{$label} #".($index + 1), ...$script];
            }
        }

        $result = Process::input(json_encode($scripts, JSON_THROW_ON_ERROR))
            ->run(['node', '--experimental-vm-modules', __DIR__.'/parse-scripts.mjs']);

        if (! $result->successful()) {
            throw new RuntimeException('The script parser failed: '.$result->errorOutput());
        }

        /** @var list<array{label: string, error: string}> $failures */
        $failures = json_decode($result->output(), true, flags: JSON_THROW_ON_ERROR);

        return array_map(fn (array $failure): string => "{$failure['label']}: {$failure['error']}", $failures);
    }
}
