import test from 'node:test';
import assert from 'node:assert/strict';

let moduleVersion = 0;

async function setup(t, { reducedMotion = false, storageBlocked = false, pathname = '/', referrer = 'http://localhost/cari-mobil', navigationType = 'navigate' } = {}) {
    t.mock.timers.enable({ apis: ['setTimeout'] });
    const storage = new Map();
    const cookies = new Map();
    const classes = () => {
        const values = new Set();
        return {
            add: (...items) => items.forEach(item => values.add(item)),
            remove: (...items) => items.forEach(item => values.delete(item)),
            contains: item => values.has(item),
        };
    };
    const button = new EventTarget();
    button.focus = () => {};
    const panels = [1, 2, 3].map(stage => ({
        dataset: { welcomeStage: String(stage) }, hidden: stage !== 1,
        setAttribute() {},
    }));
    const dialog = Object.assign(new EventTarget(), {
        classList: classes(), dataset: {}, open: false, removed: false,
        showModal() { this.open = true; },
        close() { this.open = false; },
        remove() { this.removed = true; },
        querySelector: () => button,
        querySelectorAll: () => panels,
    });
    const root = { classList: classes(), clientWidth: 1000, style: { setProperty() {} } };
    const doc = Object.assign(new EventTarget(), {
        documentElement: root,
        referrer,
        querySelector: () => dialog,
    });
    Object.defineProperty(doc, 'cookie', {
        get: () => [...cookies].map(([key, value]) => `${key}=${value}`).join('; '),
        set: value => {
            const [key, content] = value.split(';')[0].split('=');
            cookies.set(key, content);
        },
    });
    globalThis.document = doc;
    globalThis.window = Object.assign(new EventTarget(), {
        matchMedia: () => ({ matches: reducedMotion, addEventListener() {} }),
        innerWidth: 1000,
        location: { pathname, origin: 'http://localhost' },
        performance: { getEntriesByType: () => [{ type: navigationType }] },
    });
    globalThis.sessionStorage = {
        getItem(key) {
            if (storageBlocked) throw new Error('Storage unavailable');
            return storage.get(key);
        },
        setItem: (key, value) => storage.set(key, value),
    };
    t.after(() => {
        delete globalThis.document;
        delete globalThis.window;
        delete globalThis.sessionStorage;
    });
    const module = await import(`../../resources/js/motion.js?test=${moduleVersion++}`);
    return { ...module, dialog, root, button, panels };
}

test('first visit exits within five seconds and the next page skips the welcome', async t => {
    const { showWelcome, dialog, root } = await setup(t);
    const done = showWelcome();
    assert.equal(dialog.open, true);
    t.mock.timers.tick(4599);
    assert.equal(dialog.classList.contains('is-leaving'), false);
    t.mock.timers.tick(1);
    assert.equal(dialog.classList.contains('is-leaving'), true);
    t.mock.timers.tick(400);
    await done;
    assert.equal(dialog.open, false);
    assert.equal(root.classList.contains('welcome-open'), false);
    await showWelcome();
    assert.equal(dialog.open, false);
});

test('welcome shows one stage at a time for 1.8s, 1.8s and 1s before exit', async t => {
    const { showWelcome, dialog, panels } = await setup(t);
    const done = showWelcome();
    const visibleStages = () => panels.filter(panel => !panel.hidden).map(panel => panel.dataset.welcomeStage);
    assert.deepEqual(visibleStages(), ['1']);
    t.mock.timers.tick(1799);
    assert.deepEqual(visibleStages(), ['1']);
    t.mock.timers.tick(1);
    assert.deepEqual(visibleStages(), ['2']);
    t.mock.timers.tick(1799);
    assert.deepEqual(visibleStages(), ['2']);
    t.mock.timers.tick(1);
    assert.deepEqual(visibleStages(), ['3']);
    t.mock.timers.tick(999);
    assert.equal(dialog.classList.contains('is-leaving'), false);
    t.mock.timers.tick(1);
    assert.equal(dialog.classList.contains('is-leaving'), true);
    t.mock.timers.tick(400);
    await done;
    assert.equal(dialog.open, false);
});

for (const action of ['click', 'cancel']) {
    test(`${action} allows entry before five seconds`, async t => {
        const { showWelcome, dialog, button } = await setup(t);
        const done = showWelcome();
        t.mock.timers.tick(action === 'click' ? 3600 : 1200);
        (action === 'click' ? button : dialog).dispatchEvent(new Event(action, { cancelable: true }));
        t.mock.timers.tick(400);
        await done;
        assert.equal(dialog.open, false);
        assert.equal(dialog.removed, true);
    });
}

test('reduced motion retains the five-second limit with a short exit', async t => {
    const { showWelcome, dialog } = await setup(t, { reducedMotion: true });
    const done = showWelcome();
    t.mock.timers.tick(4600);
    assert.equal(dialog.classList.contains('is-leaving'), true);
    t.mock.timers.tick(160);
    await done;
    assert.equal(dialog.open, false);
});

test('session cookie prevents replay when sessionStorage is unavailable', async t => {
    const { showWelcome, dialog } = await setup(t, { storageBlocked: true });
    const done = showWelcome();
    t.mock.timers.tick(5000);
    await done;
    await showWelcome();
    assert.equal(dialog.open, false);
});

for (const entry of [
    { name: 'opening the homepage directly', referrer: '' },
    { name: 'arriving from another site', referrer: 'https://example.test/' },
    { name: 'refreshing the homepage', navigationType: 'reload' },
    { name: 'refreshing with cookie fallback', navigationType: 'reload', storageBlocked: true },
]) {
    test(`${entry.name} plays the welcome even after an earlier visit`, async t => {
        const { showWelcome, dialog } = await setup(t, entry);
        const firstVisit = showWelcome();
        t.mock.timers.tick(5000);
        await firstVisit;
        const nextVisit = showWelcome();
        assert.equal(dialog.open, true);
        t.mock.timers.tick(5000);
        await nextVisit;
        assert.equal(dialog.open, false);
    });
}

for (const entry of [
    { name: 'internal homepage navigation' },
    { name: 'refreshing another page', pathname: '/cari-mobil', navigationType: 'reload' },
    { name: 'history navigation', referrer: '', navigationType: 'back_forward' },
]) {
    test(`${entry.name} skips a previously seen welcome`, async t => {
        const { showWelcome, dialog } = await setup(t, entry);
        const firstVisit = showWelcome();
        t.mock.timers.tick(5000);
        await firstVisit;
        await showWelcome();
        assert.equal(dialog.open, false);
    });
}

test('leaving the page releases the scroll lock immediately', async t => {
    const { showWelcome, dialog, root } = await setup(t);
    const done = showWelcome();
    window.dispatchEvent(new Event('pagehide'));
    await done;
    assert.equal(dialog.open, false);
    assert.equal(root.classList.contains('welcome-open'), false);
});

for (const reducedMotion of [false, true]) {
    test(`landing components wait for the welcome exit and are only observed once (reduced motion: ${reducedMotion})`, async t => {
        const { showWelcome, initializeScrollMotion, root } = await setup(t, { reducedMotion });
        const observed = [];
        const makeComponent = () => ({
            classList: (() => {
                const classes = new Set();
                return { add: value => classes.add(value), contains: value => classes.has(value) };
            })(),
            style: { setProperty() {} },
            getBoundingClientRect: () => ({ bottom: 400 }),
            contains: () => false,
        });
        const brand = makeComponent();
        const car = makeComponent();
        const originalQuery = document.querySelector;
        document.querySelector = selector => selector === '#welcome-screen' ? originalQuery(selector) : selector === '.hero-car' ? car : null;
        document.querySelectorAll = selector => selector === '.hero-copy > *' ? [brand] : [];
        document.body = {};
        root.scrollHeight = 1000;
        window.innerHeight = 700;
        globalThis.IntersectionObserver = class {
            observe(target) { observed.push(target); }
            unobserve() {}
        };
        window.IntersectionObserver = globalThis.IntersectionObserver;
        globalThis.requestAnimationFrame = () => 1;
        t.after(() => {
            delete globalThis.IntersectionObserver;
            delete globalThis.requestAnimationFrame;
        });

        const done = showWelcome();
        initializeScrollMotion(done);
        assert.equal(brand.classList.contains('scroll-reveal'), true);
        assert.deepEqual(observed, []);
        t.mock.timers.tick(4599);
        assert.deepEqual(observed, []);
        t.mock.timers.tick(1);
        assert.deepEqual(observed, [brand, car]);
        t.mock.timers.tick(400);
        await done;
        assert.deepEqual(observed, [brand, car]);
    });
}
