<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class InstallSeoNavigationPhase extends Command
{
    protected $signature = 'site:install-seo-navigation {--force-loader}';
    protected $description = 'Install SEO sitemap routes and the navigation loader timing fix without replacing the current routes/web.php.';

    public function handle(): int
    {
        try {
            $this->installSeoRoutes();
            $this->installLoaderFix();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info('SEO routes and navigation loader source patch installed.');
        $this->line('Next: php artisan optimize:clear');
        $this->line('Next: npm run build');

        return self::SUCCESS;
    }

    private function installSeoRoutes(): void
    {
        $path = base_path('routes/web.php');

        if (!File::exists($path)) {
            throw new RuntimeException('routes/web.php was not found.');
        }

        $contents = File::get($path);

        // Remove the old single sitemap route if it exists. This prevents the
        // original /sitemap.xml route from conflicting with routes/seo.php.
        $contents = preg_replace(
            '/^\s*Route::get\(\s*[\'"]\/sitemap\.xml[\'"].*?SitemapController.*?;\s*$/m',
            '',
            $contents
        ) ?? $contents;

        $require = "require __DIR__.'/seo.php';";

        if (!str_contains($contents, $require)) {
            $contents = rtrim($contents).PHP_EOL.PHP_EOL.$require.PHP_EOL;
        }

        File::put($path, $contents);
        $this->line('✓ routes/web.php now loads routes/seo.php');
    }

    private function installLoaderFix(): void
    {
        $path = resource_path('js/app.js');

        if (!File::exists($path)) {
            throw new RuntimeException('resources/js/app.js was not found.');
        }

        $contents = File::get($path);

        if (str_contains($contents, '// Global page loader v2 — persist until real navigation completes.')) {
            $this->line('✓ navigation loader v2 already installed');
            return;
        }

        $pattern = '/\/\/ Global page loader: initial page load \+ real navigations\/forms\.\s*\(\(\) => \{.*?window\.addEventListener\([\'"]pageshow[\'"], reveal\);\s*\}\)\(\);/s';

        $replacement = <<<'JS'
// Global page loader v2 — persist until real navigation completes.
(() => {
  const getLoader = () => document.getElementById('house-page-loader');

  const reveal = () => {
    const loader = getLoader();
    if (!loader) return;
    loader.classList.add('is-hidden');
  };

  const show = () => {
    const loader = getLoader();
    if (!loader) return;
    loader.classList.remove('is-hidden');
  };

  // Expose the same API used by the Blade loader component.
  window.__hideHouseLoader = reveal;
  window.__showHouseLoader = show;

  if (document.readyState === 'complete') {
    window.setTimeout(reveal, 80);
  } else {
    window.addEventListener('load', () => window.setTimeout(reveal, 80), { once: true });
  }

  window.addEventListener('pageshow', () => window.setTimeout(reveal, 30));

  document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0) return;

    const link = event.target.closest('a[href]');
    if (!link || link.hasAttribute('data-no-page-loader') || link.hasAttribute('download')) return;

    const href = link.getAttribute('href') || '';

    if (
      !href ||
      href.startsWith('#') ||
      href.startsWith('javascript:') ||
      href.startsWith('mailto:') ||
      href.startsWith('tel:') ||
      link.target === '_blank' ||
      event.ctrlKey ||
      event.metaKey ||
      event.shiftKey ||
      event.altKey
    ) return;

    try {
      const url = new URL(link.href, window.location.href);

      if (url.origin !== window.location.origin) return;

      // In-page anchor movement is not a page navigation.
      if (
        url.pathname === window.location.pathname &&
        url.search === window.location.search &&
        url.hash &&
        url.hash !== window.location.hash
      ) return;

      // There is deliberately no 2–3 second hide timer here. Once a real
      // navigation starts, the loader stays visible until the next document's
      // load/pageshow lifecycle reveals it.
      show();
    } catch (_) {}
  });

  document.addEventListener('submit', (event) => {
    if (event.defaultPrevented) return;

    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (form.dataset.noLoader === 'true') return;
    if (!form.checkValidity()) return;

    show();
  });

  // Covers location changes triggered by normal browser navigation.
  window.addEventListener('beforeunload', show);
})();
JS;

        $updated = preg_replace($pattern, $replacement, $contents, 1, $count);

        if (!$updated || $count !== 1) {
            if (!$this->option('force-loader')) {
                throw new RuntimeException(
                    'The existing loader block was not recognized. No JS was changed. '.
                    'Re-run with --force-loader only after reviewing resources/js/app.js.'
                );
            }

            $updated = rtrim($contents).PHP_EOL.PHP_EOL.$replacement.PHP_EOL;
        }

        File::put($path, $updated);
        $this->line('✓ resources/js/app.js navigation loader timing fixed');
    }
}
