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
        const hide = () => {
            const loader = document.getElementById('house-page-loader');
            if (!loader) return;
            loader.classList.add('is-hidden');
        };

        window.__hideHouseLoader = hide;

        if (document.readyState === 'complete') {
            window.setTimeout(hide, 80);
        } else {
            window.addEventListener('load', () => window.setTimeout(hide, 80), { once: true });
        }

        // Independent fail-safe: loader can never trap the storefront even if
        // a Vite module or third-party asset throws before app.js initializes.
        window.setTimeout(hide, 1800);
        window.addEventListener('pageshow', hide);
        window.addEventListener('error', () => window.setTimeout(hide, 0));
        window.addEventListener('unhandledrejection', () => window.setTimeout(hide, 0));
    })();
</script>
