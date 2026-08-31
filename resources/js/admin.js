const shell = document.querySelector('[data-admin-shell]');
const openBtn = document.querySelector('[data-admin-nav-open]');
const closeEls = document.querySelectorAll('[data-admin-nav-close]');

const closeNav = () => shell?.classList.remove('mobile-nav-open');
const openNav = () => shell?.classList.add('mobile-nav-open');

openBtn?.addEventListener('click', openNav);
closeEls.forEach((el) => el.addEventListener('click', closeNav));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeNav();
});

document.querySelectorAll('[data-admin-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
        const message = button.getAttribute('data-admin-confirm') || 'Are you sure?';
        if (!window.confirm(message)) event.preventDefault();
    });
});


// Enterprise command palette / global search.
const command = document.querySelector('[data-admin-command]');
const commandInput = document.querySelector('[data-admin-command-input]');
const commandResults = document.querySelector('[data-admin-command-results]');
const commandOpeners = document.querySelectorAll('[data-admin-command-open]');
const commandClosers = document.querySelectorAll('[data-admin-command-close]');
let commandTimer = null;
let commandController = null;

const commandOpen = () => {
    if (!command) return;
    command.hidden = false;
    document.documentElement.style.overflow = 'hidden';
    window.setTimeout(() => commandInput?.focus(), 20);
};

const commandClose = () => {
    if (!command) return;
    command.hidden = true;
    document.documentElement.style.overflow = '';
    if (commandInput) commandInput.value = '';
    if (commandResults) {
        commandResults.innerHTML = '<div class="admin-command-empty">Type at least 2 characters to search across commerce records.</div>';
    }
    commandController?.abort();
};

commandOpeners.forEach((el) => el.addEventListener('click', commandOpen));
commandClosers.forEach((el) => el.addEventListener('click', commandClose));

document.addEventListener('keydown', (event) => {
    const modifier = event.ctrlKey || event.metaKey;

    if (modifier && event.key.toLowerCase() === 'k') {
        event.preventDefault();
        command?.hidden ? commandOpen() : commandClose();
    }

    if (event.key === 'Escape' && command && !command.hidden) {
        commandClose();
    }
});

const escapeHtml = (value = '') => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const renderCommandResults = (items) => {
    if (!commandResults) return;

    if (!items.length) {
        commandResults.innerHTML = '<div class="admin-command-empty">No matching products, orders or customers.</div>';
        return;
    }

    let currentGroup = '';
    const html = [];

    for (const item of items) {
        if (item.group !== currentGroup) {
            currentGroup = item.group;
            html.push(`<div class="admin-command-group">${escapeHtml(currentGroup)}</div>`);
        }

        html.push(`
            <a href="${escapeHtml(item.url)}" class="admin-command-result">
                <div>
                    <div class="admin-command-title">${escapeHtml(item.title)}</div>
                    <div class="admin-command-meta">${escapeHtml(item.meta || '')}</div>
                </div>
                <span aria-hidden="true">↗</span>
            </a>
        `);
    }

    commandResults.innerHTML = html.join('');
};

commandInput?.addEventListener('input', () => {
    clearTimeout(commandTimer);
    const query = commandInput.value.trim();

    if (query.length < 2) {
        commandController?.abort();
        if (commandResults) {
            commandResults.innerHTML = '<div class="admin-command-empty">Type at least 2 characters to search across commerce records.</div>';
        }
        return;
    }

    commandTimer = window.setTimeout(async () => {
        commandController?.abort();
        commandController = new AbortController();

        if (commandResults) {
            commandResults.innerHTML = '<div class="admin-command-empty">Searching…</div>';
        }

        try {
            const response = await fetch(`/admin/search?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
                signal: commandController.signal,
            });

            if (!response.ok) throw new Error('Search request failed');

            const payload = await response.json();
            renderCommandResults(payload.results || []);
        } catch (error) {
            if (error.name === 'AbortError') return;

            if (commandResults) {
                commandResults.innerHTML = '<div class="admin-command-empty">Search is temporarily unavailable.</div>';
            }
        }
    }, 220);
});
