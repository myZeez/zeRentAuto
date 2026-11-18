const welcomeSessionKey = 'zeautorent-welcome-seen';
const welcomeDuration = 5000;
const welcomeStages = [1800, 1800, 1000];
const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');

function isFreshHomepageEntry() {
    if (window.location.pathname !== '/') return false;
    const navigation = window.performance?.getEntriesByType('navigation')[0];
    if (navigation?.type === 'back_forward') return false;
    if (navigation?.type === 'reload') return true;
    try {
        return !document.referrer || new URL(document.referrer).origin !== window.location.origin;
    } catch {
        return false;
    }
}

function claimWelcomeSession() {
    try {
        if (sessionStorage.getItem(welcomeSessionKey) && !isFreshHomepageEntry()) return false;
        sessionStorage.setItem(welcomeSessionKey, '1');
        return true;
    } catch {
        // A session cookie prevents repeated intros when sessionStorage is unavailable.
        if (document.cookie.split('; ').includes(`${welcomeSessionKey}=1`) && !isFreshHomepageEntry()) return false;
        document.cookie = `${welcomeSessionKey}=1; Path=/; SameSite=Lax`;
        return document.cookie.split('; ').includes(`${welcomeSessionKey}=1`);
    }
}

export function showWelcome() {
    const dialog = document.querySelector('#welcome-screen');
    if (!dialog || typeof dialog.showModal !== 'function' || !claimWelcomeSession()) {
        dialog?.remove();
        return Promise.resolve();
    }

    return new Promise(resolve => {
        const root = document.documentElement;
        const startedAt = performance.now();
        let leaving = false;
        let finished = false;
        let exitTimer;
        let autoTimer;
        let deadlineTimer;
        const stageTimers = [];

        const setStage = stage => {
            if (finished || leaving) return;
            dialog.dataset.phase = String(stage);
            dialog.querySelectorAll('[data-welcome-stage]').forEach(panel => {
                panel.hidden = panel.dataset.welcomeStage !== String(stage);
                panel.setAttribute('aria-hidden', String(panel.hidden));
            });
            if (stage === 3 && document.activeElement === dialog) {
                dialog.querySelector('#welcome-start').focus({ preventScroll: true });
            }
        };

        const finish = () => {
            if (finished) return;
            finished = true;
            clearTimeout(autoTimer);
            clearTimeout(exitTimer);
            clearTimeout(deadlineTimer);
            stageTimers.forEach(clearTimeout);
            document.removeEventListener('visibilitychange', checkDeadline);
            window.removeEventListener('pagehide', finish);
            root.classList.remove('welcome-open', 'welcome-leaving');
            dialog.close();
            dialog.remove();
            resolve();
        };

        const leave = () => {
            if (leaving || finished) return;
            leaving = true;
            clearTimeout(autoTimer);
            stageTimers.forEach(clearTimeout);
            const duration = motionPreference.matches ? 160 : 400;
            root.style.setProperty('--welcome-exit-duration', `${duration}ms`);
            root.classList.add('welcome-leaving');
            dialog.classList.add('is-leaving');
            document.dispatchEvent(new Event('welcome:leaving'));
            exitTimer = setTimeout(finish, duration);
        };

        const checkDeadline = () => {
            if (performance.now() - startedAt >= welcomeDuration) finish();
        };

        dialog.querySelector('#welcome-start').addEventListener('click', leave);
        dialog.addEventListener('cancel', event => {
            event.preventDefault();
            leave();
        });
        document.addEventListener('visibilitychange', checkDeadline);
        window.addEventListener('pagehide', finish, { once: true });
        root.style.setProperty('--welcome-scrollbar', `${Math.max(0, window.innerWidth - root.clientWidth)}px`);
        root.classList.add('welcome-open');
        setStage(1);
        dialog.showModal();
        stageTimers.push(setTimeout(() => setStage(2), welcomeStages[0]));
        stageTimers.push(setTimeout(() => setStage(3), welcomeStages[0] + welcomeStages[1]));
        autoTimer = setTimeout(leave, welcomeStages.reduce((total, duration) => total + duration, 0));
        deadlineTimer = setTimeout(finish, welcomeDuration);
    });
}

export function initializeScrollMotion(welcomeFinished = Promise.resolve()) {
    if (!('IntersectionObserver' in window)) return;

    const root = document.documentElement;
    root.classList.add('motion-enabled');
    const targets = new Map();
    const add = (target, delay = 0) => {
        if (target) targets.set(target, delay);
    };
    document.querySelectorAll('.hero-copy > *').forEach((target, index) => {
        if (target.classList.contains('hero-metrics') || target.classList.contains('hero-actions')) {
            [...target.children].forEach((child, offset) => add(child, 440 + offset * 140));
        } else add(target, index * 130);
    });
    add(document.querySelector('.hero-car'), 180);
    document.querySelectorAll('.hero-spec').forEach((target, index) => add(target, 640 + index * 160));
    document.querySelectorAll('.hero-search-form > div').forEach((target, index) => add(target, 280 + index * 100));
    document.querySelectorAll('main section').forEach(section => {
        if (section.classList.contains('home-hero')) return;
        section.querySelectorAll('h1, h2, h2 + p').forEach(target => add(target));
        section.querySelectorAll('[data-car-card], .grid > div, #faq-container > div').forEach((target, index) => add(target, (index % 4) * 140));
    });
    // Keep each animated component independent of an animated ancestor.
    [...targets.keys()].forEach(target => {
        if ([...targets.keys()].some(other => other !== target && target.contains(other))) targets.delete(target);
    });

    const observer = new IntersectionObserver(entries => {
        entries.filter(entry => entry.isIntersecting).forEach(entry => {
            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0, rootMargin: '0px 0px -32px 0px' });

    targets.forEach((delay, target) => {
        // Do not hide content above a restored scroll position or hash destination.
        if (target.getBoundingClientRect().bottom < 0) return;
        target.classList.add('scroll-reveal');
        target.style.setProperty('--reveal-delay', `${delay}ms`);
    });

    let started = false;
    const start = () => {
        if (started) return;
        started = true;
        document.removeEventListener('welcome:leaving', start);
        targets.forEach((_, target) => {
            if (target.classList.contains('scroll-reveal')) observer.observe(target);
        });
    };
    document.addEventListener('welcome:leaving', start, { once: true });
    welcomeFinished.then(start);

    const ribbon = document.querySelector('.brand-ribbon');
    const ribbonObserver = ribbon ? new IntersectionObserver(entries => {
        ribbon.classList.toggle('is-in-view', entries[0].isIntersecting);
    }) : null;
    if (ribbon) ribbonObserver.observe(ribbon);

    const revealFocusedContent = event => {
        const target = event.target.closest('.scroll-reveal');
        if (target) {
            target.style.setProperty('--reveal-delay', '0ms');
            target.classList.add('is-revealed');
            observer.unobserve(target);
        }
    };
    document.addEventListener('focusin', revealFocusedContent);

    const progress = document.querySelector('#scroll-progress');
    const car = document.querySelector('.hero-car');
    let frame = 0;
    let maxScroll = 1;

    const update = () => {
        frame = 0;
        const scroll = window.scrollY;
        progress?.style.setProperty('--scroll-progress', Math.min(1, Math.max(0, scroll / maxScroll)));
        if (!motionPreference.matches && car && car.getBoundingClientRect().bottom > 0) {
            car.style.setProperty('--car-scroll', `${-Math.min(scroll * 0.06, 28)}px`);
        }
    };
    const schedule = () => {
        if (!frame) frame = requestAnimationFrame(update);
    };
    const measure = () => {
        maxScroll = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        schedule();
    };

    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', measure, { passive: true });
    const resizeObserver = 'ResizeObserver' in window ? new ResizeObserver(measure) : null;
    resizeObserver?.observe(document.body);
    measure();

    motionPreference.addEventListener('change', event => {
        if (!event.matches) return;
        observer.disconnect();
        ribbonObserver?.disconnect();
        resizeObserver?.disconnect();
        cancelAnimationFrame(frame);
        window.removeEventListener('scroll', schedule);
        window.removeEventListener('resize', measure);
        document.removeEventListener('focusin', revealFocusedContent);
        document.removeEventListener('welcome:leaving', start);
        root.classList.remove('motion-enabled');
        car?.style.removeProperty('--car-scroll');
    }, { once: true });
}
