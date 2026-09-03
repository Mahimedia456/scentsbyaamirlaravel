<div id="house-page-loader" class="house-page-loader" aria-hidden="true">
    <div class="house-page-loader__inner">
        <img
            src="{{ asset('logo-02.png') }}"
            alt=""
            class="house-page-loader__logo"
        >
        <div class="house-page-loader__line"><span></span></div>
        <p>Preparing the house</p>
    </div>
</div>

<script>
    (() => {
        const getLoader = () => document.getElementById('house-page-loader');

        const hide = () => {
            const loader = getLoader();
            if (!loader) return;
            loader.classList.add('is-hidden');
        };

        const show = () => {
            const loader = getLoader();
            if (!loader) return;
            loader.classList.remove('is-hidden');
        };

        window.__hideHouseLoader = hide;
        window.__showHouseLoader = show;

        // Initial document: remain covered until the browser reports the page loaded.
        if (document.readyState === 'complete') {
            window.setTimeout(hide, 80);
        } else {
            window.addEventListener('load', () => window.setTimeout(hide, 80), { once: true });
        }

        // Back/forward cache restoration must reveal the restored page.
        window.addEventListener('pageshow', () => window.setTimeout(hide, 30));

        // Emergency only: never leave the storefront permanently trapped if a
        // browser/third-party resource prevents a normal load event.
        window.setTimeout(hide, 12000);
    })();
</script>
