export function initializeDropdowns() {
    let closeActive = () => {};

    document.querySelectorAll('select').forEach((select, index) => {
        if (select.multiple || select.size > 1) return;

        const labelElement = select.labels?.[0];
        const labelText = labelElement ? [...labelElement.childNodes].filter(node => node !== select).map(node => node.textContent).join('').trim() : '';
        const label = select.getAttribute('aria-label') || labelText || 'Pilihan';
        const button = document.createElement('button');
        const value = document.createElement('span');
        const arrow = document.createElement('span');
        const menu = document.createElement('div');
        const options = [...select.options];
        let activeIndex = select.selectedIndex;
        let search = '';
        let searchTimer;

        button.type = 'button';
        button.className = `${select.className} dropdown-trigger`;
        button.id = select.id ? `${select.id}-trigger` : `dropdown-${index}-trigger`;
        button.setAttribute('role', 'combobox');
        button.setAttribute('aria-label', label);
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');
        value.className = 'dropdown-value';
        arrow.className = 'dropdown-chevron';
        arrow.setAttribute('aria-hidden', 'true');
        button.append(value, arrow);

        menu.id = `dropdown-${index}-options`;
        menu.className = 'dropdown-menu';
        menu.hidden = true;
        menu.setAttribute('role', 'listbox');
        menu.setAttribute('aria-label', label);
        button.setAttribute('aria-controls', menu.id);

        const items = options.map((option, optionIndex) => {
            const item = document.createElement('div');
            item.id = `${menu.id}-${optionIndex}`;
            item.className = 'dropdown-option';
            item.textContent = option.textContent;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-disabled', String(option.disabled));
            item.addEventListener('pointerdown', event => event.preventDefault());
            item.addEventListener('click', () => choose(optionIndex));
            menu.append(item);
            return item;
        });

        const sync = () => {
            value.textContent = options[select.selectedIndex]?.textContent || 'Pilih';
            button.disabled = select.disabled;
            items.forEach((item, itemIndex) => item.setAttribute('aria-selected', String(itemIndex === select.selectedIndex)));
        };
        const highlight = optionIndex => {
            activeIndex = optionIndex;
            items.forEach((item, itemIndex) => item.classList.toggle('is-active', itemIndex === activeIndex));
            if (items[activeIndex]) {
                button.setAttribute('aria-activedescendant', items[activeIndex].id);
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        };
        const close = () => {
            menu.hidden = true;
            button.setAttribute('aria-expanded', 'false');
            button.removeAttribute('aria-activedescendant');
            clearTimeout(searchTimer);
            search = '';
        };
        const open = () => {
            closeActive();
            closeActive = close;
            sync();
            const bounds = button.getBoundingClientRect();
            const width = Math.min(Math.max(bounds.width, 220), window.innerWidth - 24);
            const below = window.innerHeight - bounds.bottom - 16;
            const above = bounds.top - 16;
            const opensAbove = below < Math.min(options.length * 46 + 12, 220) && above > below;
            menu.style.width = `${width}px`;
            menu.style.left = `${Math.max(12, Math.min(bounds.left, window.innerWidth - width - 12))}px`;
            menu.style.maxHeight = `${Math.max(80, Math.min(280, opensAbove ? above : below))}px`;
            menu.style.top = opensAbove ? 'auto' : `${bounds.bottom + 8}px`;
            menu.style.bottom = opensAbove ? `${window.innerHeight - bounds.top + 8}px` : 'auto';
            menu.hidden = false;
            button.setAttribute('aria-expanded', 'true');
            highlight(select.selectedIndex);
        };
        const choose = optionIndex => {
            if (options[optionIndex].disabled) return;
            const changed = select.selectedIndex !== optionIndex;
            select.selectedIndex = optionIndex;
            sync();
            close();
            button.focus({ preventScroll: true });
            if (changed) {
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };
        const move = direction => {
            const available = options.map((option, optionIndex) => option.disabled ? -1 : optionIndex).filter(optionIndex => optionIndex !== -1);
            const current = available.indexOf(activeIndex);
            const next = Math.max(0, Math.min(available.length - 1, current + direction));
            if (available.length) highlight(available[next]);
        };

        button.addEventListener('click', () => menu.hidden ? open() : close());
        button.addEventListener('keydown', event => {
            if (['ArrowDown', 'ArrowUp', 'Home', 'End', 'Enter', ' ', 'Escape'].includes(event.key)) {
                event.preventDefault();
                if (event.key === 'Escape') return close();
                if (event.key === 'Enter' || (event.key === ' ' && !search)) {
                    if (menu.hidden) open();
                    else if (activeIndex >= 0) choose(activeIndex);
                    return;
                }
                if (menu.hidden) open();
                if (event.key === 'ArrowDown') move(1);
                if (event.key === 'ArrowUp') move(-1);
                if (event.key === 'Home') highlight(options.findIndex(option => !option.disabled));
                if (event.key === 'End') highlight(options.findLastIndex(option => !option.disabled));
            }
            if (event.key === 'Tab') close();
            if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
                event.preventDefault();
                if (menu.hidden) open();
                search += event.key.toLocaleLowerCase('id-ID');
                const match = options.findIndex(option => !option.disabled && option.textContent.trim().toLocaleLowerCase('id-ID').startsWith(search));
                if (match >= 0) highlight(match);
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => { search = ''; }, 700);
            }
        });
        button.addEventListener('blur', close);
        document.addEventListener('pointerdown', event => {
            if (!button.contains(event.target) && !menu.contains(event.target)) close();
        });
        window.addEventListener('resize', close, { passive: true });
        document.addEventListener('scroll', event => {
            if (!menu.contains(event.target)) close();
        }, { capture: true, passive: true });
        select.addEventListener('change', sync);
        select.form?.addEventListener('reset', () => requestAnimationFrame(sync));
        select.labels?.forEach(labelElement => {
            labelElement.addEventListener('click', event => {
                event.preventDefault();
                button.focus({ preventScroll: true });
            });
        });

        select.after(button);
        document.body.append(menu);
        select.hidden = true;
        sync();
    });
}
