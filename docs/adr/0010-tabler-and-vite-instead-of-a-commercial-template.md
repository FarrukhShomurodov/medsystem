# 10. Tabler and Vite instead of the commercial template

- Status: Accepted
- Date: 2025-11

## Context

The dashboard was built on the commercial Frest HTML admin template by PIXINVENT, committed whole
into `public/`: about 920 files and 22 MB, of which roughly 95% (demo scripts and images, most of
the vendor libraries, hundreds of flag icons) was never referenced. It bundled FormValidation.io,
a separately licensed commercial library whose plugins were loaded while its core file was not, so
it never ran.
The template's licence allows using it in one end product, not redistributing its source files, so
a public repository that contains them is very likely a licence violation; rebuilding the template
from its Sass sources would need the paid source package.

Pages loaded scripts and fonts from CDNs and Google Fonts with a `now()` cache-buster, there was no
Node build, and the views mixed the template's class names with Bootstrap 4 and Font Awesome
leftovers. The views are server-rendered Blade with jQuery code inline, and the screens are Russian.

## Decision

1. **Tabler (`@tabler/core` 1.7.5, MIT, Bootstrap 5.3) is the UI kit**, compiled from its Sass
   sources in `resources/scss/app.scss` with the dashboard's existing primary colour (`#5a8dee`), a
   wider sidebar for the long Russian page names, and self-hosted fonts: Inter with its Cyrillic
   subsets and Boxicons in woff2 and woff only.
2. **Vite builds the assets** through `laravel-vite-plugin`. The entries are the stylesheet,
   `resources/js/app.js` (loaded on every page) and one module per page that needs more than a few
   lines of script (`resources/js/pages/*.js`). `public/build` is generated and ignored by git; every
   library comes from npm at an exact version pinned in `package.json` and `package-lock.json`;
   nothing is loaded from a CDN.
3. **The pages stay server-rendered Blade with jQuery.** jQuery 4 is the one library loaded as a
   classic script (`Vite::asset()`), before `app.js`, because the views' remaining inline code uses
   `$` while the page is parsed; modules that import `jquery` get the same instance through an
   alias. `app.js` initialises every widget once from markup hooks (select2, flatpickr,
   bs-stepper, the row lists, `data-confirm`), and `moment` is replaced by native `Date` and `Intl`
   in `resources/js/lib/time.js`. The conventions are in
   [docs/architecture/frontend.md](../architecture/frontend.md).
4. **Shared UI is built from Blade components and one PHP definition of the menu**: the sidebar
   from `App\Support\Navigation\Navigation`, `<x-page-header>`, `<x-flash>`, `<x-empty-state>`,
   `<x-delete-button>` and the status components of [ADR 3](0003-backed-enums-and-morph-aliases.md).
   The Russian error pages render through `<x-optional-vite>`, so a 500 or 503 still renders when the
   build or the database is missing.
5. **The template cannot come back unnoticed.** `tests/Feature/Frontend/RenderedPage.php` lists the
   template's file and class patterns, the old class names and the CDN hosts; `AllPagesTest` renders
   every GET page of the dashboard over demo data and fails on any of them, on an inline script that
   does not parse, and on markup errors (fields without labels, duplicate ids, POST forms without the
   CSRF token). `ViteEntriesTest` checks that every referenced entry exists, and the CI `assets` job
   builds the front end and runs `check-manifest.mjs` against the manifest. PHP tests render pages
   without a build (`withoutVite()` in `tests/TestCase.php`).

The result is visible in the screenshots: the role-aware home page
([dashboard.png](../screenshots/dashboard.png)), the patient list with its search and filters
([patients.png](../screenshots/patients.png)), the sign-in page ([login.png](../screenshots/login.png))
and the Russian 404 inside the layout ([error-404.png](../screenshots/error-404.png)).

## Consequences

### Positive

- The repository holds only MIT-licensed front-end code from npm and the project's own files, so it
  can be public.
- Assets are hashed by Vite, and nginx serves `/build/assets/` with an immutable cache header; the
  Docker image builds them in its own `assets` stage.
- One SCSS entry configures Tabler; the old compatibility layer for template class names was
  deleted, and the tests keep those names out.
- A page that breaks its markup or its scripts fails the suite, not a user's browser.

### Negative

- jQuery and select2 remain, with jQuery loaded as a global before the modules. A move to plain
  modules would touch every page script.
- Three views still contain inline scripts, and no Content-Security-Policy header is sent yet.
- The CSS targets 2024 browsers (Chrome and Edge 123, Firefox 120, Safari 17.5): with older targets
  the minifier replaces Tabler's `light-dark()` with a polyfill that paints the dark sidebar white.
- A Node toolchain is now part of development, CI and the Docker build.
- The CI audit checks the runtime dependencies only (`--omit=dev`). An advisory in a build tool,
  such as the high one picomatch carried before its patched release, does not fail the build and
  has to be followed by hand (Dependabot proposes the update).
- ApexCharts, used only by the nurse window, is a large chunk on its own; the chunk size warning is
  raised for it.

## Alternatives considered

- **Keep Frest, trimmed to the files in use.** Still the template's source files in a public
  repository, and its Sass sources are part of the paid package, so the styles could not be
  rebuilt or themed.
- **A single-page application (Vue, React or Inertia).** A rewrite of every screen and of the
  server's role in rendering them, for a dashboard whose pages are forms and tables.
- **Livewire.** Interactive components without a separate front end, but again a rewrite of every
  page; the current pages work with server-rendered Blade and small scripts.
- **Another free Bootstrap admin kit.** Tabler was chosen for its Bootstrap 5.3 base (the template
  was built on Bootstrap 5.2, so most markup mapped directly), its MIT licence, its Sass sources and
  its vertical dark sidebar layout.
- **Laravel Mix**, whose unused configuration was still in the repository. Vite is Laravel's
  default build tool, and its dev server reloads the Blade pages on change.
