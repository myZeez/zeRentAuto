import { showWelcome, initializeScrollMotion } from './motion';
import { initializeDropdowns } from './dropdowns';

const welcomeFinished = showWelcome();
initializeScrollMotion(welcomeFinished);
initializeDropdowns();

document.querySelector('.brand-pause')?.addEventListener('click', event => {
    const button = event.currentTarget;
    const paused = button.closest('.brand-ribbon').classList.toggle('is-paused');
    button.setAttribute('aria-pressed', String(paused));
    button.setAttribute('aria-label', paused ? 'Lanjutkan gerakan merek' : 'Jeda gerakan merek');
    button.querySelector('.material-symbols-outlined').textContent = paused ? 'play_arrow' : 'pause';
});

const $ = (selector) => document.querySelector(selector);
const money = (value) => 'Rp ' + value.toLocaleString('id-ID');
const toast = (message) => {
    const el = $('#toast');
    el.textContent = message;
    el.classList.remove('hidden');
    clearTimeout(toast.timer);
    toast.timer = setTimeout(() => el.classList.add('hidden'), 3500);
};
$('#menu-toggle')?.addEventListener('click', () => {
    const expanded = $('#menu-toggle').getAttribute('aria-expanded') === 'true';
    $('#menu-toggle').setAttribute('aria-expanded', String(!expanded));
    $('#mobile-menu').classList.toggle('hidden', expanded);
});
document.querySelectorAll('header a[data-path]').forEach(link => {
    link.classList.remove('bg-subtle-gray');
    if (new URL(link.href).pathname === location.pathname) {
        link.setAttribute('aria-current', 'page');
        link.classList.add('bg-subtle-gray');
    }
});
let favorites;
try { const saved = JSON.parse(localStorage.getItem('autora-favorites') || '[]'); favorites = new Set(Array.isArray(saved) ? saved : []); }
catch { favorites = new Set(); }
function renderFavorites() {
    document.querySelectorAll('[data-favorite]').forEach(button => {
        const selected = favorites.has(button.dataset.favorite);
        button.setAttribute('aria-pressed', String(selected));
        button.classList.toggle('text-error', selected);
        button.querySelector('.material-symbols-outlined').style.fontVariationSettings = "'FILL' " + Number(selected);
    });
    document.querySelectorAll('[data-favorite-count]').forEach(el => el.textContent = favorites.size);
    if ($('#favorites-page')) {
        let visible = 0;
        document.querySelectorAll('[data-car-card]').forEach(card => {
            card.hidden = !favorites.has(card.dataset.carCard);
            if (!card.hidden) visible++;
        });
        $('#favorites-empty').classList.toggle('hidden', visible > 0);
    }
}
document.querySelectorAll('[data-favorite]').forEach(button => button.addEventListener('click', () => {
    const id = button.dataset.favorite;
    favorites.has(id) ? favorites.delete(id) : favorites.add(id);
    try { localStorage.setItem('autora-favorites', JSON.stringify([...favorites])); }
    catch { toast('Penyimpanan browser tidak tersedia; favorit disimpan sementara.'); }
    renderFavorites();
}));
renderFavorites();
document.querySelectorAll('.faq-toggle').forEach(button => {
    button.setAttribute('aria-expanded', String(!button.nextElementSibling.classList.contains('hidden')));
    button.addEventListener('click', () => {
    const content = button.nextElementSibling;
    const open = content.classList.contains('hidden');
    content.classList.toggle('hidden', !open);
    button.setAttribute('aria-expanded', String(open));
    const icon = button.querySelector('.faq-icon');
    if (icon) icon.textContent = open ? 'remove' : 'add';
    });
});
document.querySelectorAll('[data-service]').forEach(button => button.addEventListener('click', () => {
    $('#quick-service').value = button.dataset.service;
    document.querySelectorAll('[data-service]').forEach(tab => {
        const selected = tab === button;
        tab.setAttribute('aria-pressed', String(selected));
        tab.classList.toggle('bg-primary', selected);
        tab.classList.toggle('text-on-primary', selected);
        tab.classList.toggle('bg-subtle-gray', !selected);
        tab.classList.toggle('text-on-surface', !selected);
    });
}));
$('#sortSelector')?.addEventListener('change', () => $('#catalog-filter').requestSubmit());
$('#max-price')?.addEventListener('input', event => $('#price-output').textContent = money(Number(event.target.value)));
for (const [id, list] of [['viewGridBtn',false],['viewListBtn',true]]) {
    $('#' + id)?.addEventListener('click', () => {
        $('#carCatalogGrid').classList.toggle('list-view', list);
        $('#viewGridBtn').setAttribute('aria-pressed', String(!list));
        $('#viewListBtn').setAttribute('aria-pressed', String(list));
        $('#viewGridBtn').classList.toggle('bg-subtle-gray', !list);
        $('#viewListBtn').classList.toggle('bg-subtle-gray', list);
    });
}
const reservation = $('#reservation-form');
if (reservation) {
    const update = () => {
        const start = $('#date-start');
        const end = $('#date-end');
        const days = Math.round((Date.parse(end.value) - Date.parse(start.value)) / 86400000);
        const valid = Number.isFinite(days) && days > 0;
        end.setCustomValidity(valid ? '' : 'Tanggal selesai harus setelah tanggal mulai.');
        $('#duration-label').textContent = valid ? days + ' Hari Penuh' : 'Pilih rentang tanggal yang valid.';
        if (!valid) {
            for (const id of ['row-base-price','row-addons-price','grand-total-display']) $('#'+id).textContent = '—';
            return;
        }
        const data = new FormData(reservation);
        const base = days * Number(reservation.dataset.rate);
        const addons = (data.has('insurance') ? days * 250000 : 0) + (data.get('service') === 'driver' ? days * 350000 : 0) + (data.has('valet') ? 150000 : 0) + (data.has('fuel') ? 500000 : 0);
        $('#row-base-price').textContent = money(base);
        $('#row-addons-price').textContent = money(addons);
        $('#grand-total-display').textContent = money(base + addons - 500000 + 2500000);
    };
    reservation.addEventListener('change', update);
    update();

    const submit = reservation.querySelector('[data-reserve-submit]');
    const notice = reservation.querySelector('[data-unit-notice]');
    let availabilityRequest;
    let unitImages = new Map();
    const updateUnitImage = () => {
        const selected = reservation.querySelector('[name="unit_id"]:checked');
        const image = unitImages.get(Number(selected?.value)) || reservation.dataset.carImage;
        $('#main-hero-car-img').src = image;
        $('#image-dialog img').src = image;
    };
    const refreshUnits = async () => {
        availabilityRequest?.abort();
        availabilityRequest = new AbortController();
        submit.disabled = true;
        const data = new FormData(reservation);
        if (!['start_date', 'end_date', 'start_time', 'end_time'].every(name => data.get(name)) || $('#date-end').validationMessage) return;
        notice.hidden = false;
        notice.textContent = 'Memeriksa ketersediaan unit…';
        try {
            const query = new URLSearchParams();
            for (const name of ['start_date', 'end_date', 'start_time', 'end_time']) query.set(name, data.get(name));
            const response = await fetch(`${reservation.dataset.availabilityUrl}?${query}`, { signal: availabilityRequest.signal, headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('availability');
            const stock = await response.json();
            const selectedId = data.get('unit_id');
            unitImages = new Map(stock.units.map(unit => [unit.id, unit.image]));
            const options = reservation.querySelector('[data-unit-options]');
            options.replaceChildren();
            for (const unit of stock.units) {
                const option = $('#unit-option-template').content.firstElementChild.cloneNode(true);
                option.dataset.unitOption = unit.id;
                option.classList.toggle('is-unavailable', !unit.available);
                const radio = option.querySelector('input');
                radio.value = unit.id;
                radio.disabled = !unit.available;
                radio.checked = unit.available && String(unit.id) === selectedId;
                option.querySelector('.unit-swatch').style.background = unit.color_hex;
                option.querySelector('[data-unit-color]').textContent = unit.color;
                option.querySelector('[data-unit-code]').textContent = unit.code;
                option.querySelector('[data-unit-state]').textContent = unit.label;
                option.querySelector('[data-unit-until]').textContent = unit.overdue ? 'Batas waktu terlewati; menunggu mobil kembali.' : unit.until ? `${unit.available && unit.rented ? 'Saat ini disewa. Rencana kembali: ' : 'Rencana kembali / selesai pemesanan: '}${unit.until}` : '';
                options.append(option);
            }
            reservation.querySelector('[data-unit-available]').textContent = stock.available;
            reservation.querySelector('[data-unit-total]').textContent = stock.total;
            submit.disabled = stock.available === 0;
            notice.hidden = stock.available > 0;
            notice.textContent = 'Semua unit tidak tersedia untuk jadwal ini. Periksa waktu kembali tiap unit atau pilih jadwal lain.';
            updateUnitImage();
        } catch (error) {
            if (error.name === 'AbortError') return;
            notice.hidden = false;
            notice.textContent = 'Ketersediaan belum dapat diperiksa. Periksa jadwal, lalu coba lagi.';
        }
    };
    reservation.addEventListener('change', event => {
        if (['start_date', 'end_date', 'start_time', 'end_time'].includes(event.target.name)) refreshUnits();
        if (event.target.name === 'unit_id') updateUnitImage();
    });
    refreshUnits();
}
$('#zoom-car')?.addEventListener('click', () => $('#image-dialog').showModal());
$('#close-image')?.addEventListener('click', () => $('#image-dialog').close());
$('#image-dialog')?.addEventListener('click', event => { if (event.target === $('#image-dialog')) $('#image-dialog').close(); });
async function copy(text) {
    try { await navigator.clipboard.writeText(text); toast('Berhasil disalin.'); }
    catch { toast('Tidak dapat menyalin otomatis. Silakan salin teks secara manual.'); }
}
$('#share-car')?.addEventListener('click', async () => {
    if (navigator.share) {
        try { await navigator.share({ title: document.title, url: location.href }); }
        catch (error) { if (error.name !== 'AbortError') await copy(location.href); }
    } else await copy(location.href);
});
$('#copyCodeBtn')?.addEventListener('click', () => copy($('#bookingCodeText').textContent.trim()));
$('#print-booking')?.addEventListener('click', () => window.print());
$('#checkout-form')?.addEventListener('submit', () => {
    const button = document.querySelector('button[form="checkout-form"]');
    button.disabled = true;
    button.textContent = 'Menyimpan reservasi...';
});
window.addEventListener('pageshow', () => {
    const button = document.querySelector('button[form="checkout-form"]');
    if (button) { button.disabled = false; button.textContent = 'Konfirmasi Reservasi Demo →'; }
});

function updateRentalDeadlines() {
    document.querySelectorAll('[data-rental-deadline]').forEach(element => {
        const remaining = Number(element.dataset.rentalDeadline) - Date.now();
        const late = remaining < 0;
        const minutes = Math.ceil(Math.abs(remaining) / 60000);
        const days = Math.floor(minutes / 1440);
        const hours = Math.floor((minutes % 1440) / 60);
        const duration = [days && `${days} hari`, hours && `${hours} jam`, `${minutes % 60} menit`].filter(Boolean).join(' ');
        element.classList.toggle('is-late', late);
        element.querySelector('[data-deadline-label]').textContent = late ? 'Batas pengembalian terlewati' : 'Sisa waktu sewa';
        element.querySelector('[data-deadline-text]').textContent = late ? `Terlambat ${duration}. Segera koordinasikan pengembalian kendaraan.` : `${duration} menuju batas pengembalian.`;
        element.querySelector('.material-symbols-outlined').textContent = late ? 'warning' : 'timer';
        const badge = document.querySelector(`[data-rental-badge="${element.dataset.bookingId}"]`);
        if (badge) {
            badge.classList.toggle('status-badge-late', late);
            badge.textContent = late ? 'Terlambat dikembalikan' : 'Sedang disewa';
        }
    });
}
if (document.querySelector('[data-rental-deadline]')) {
    updateRentalDeadlines();
    setInterval(() => { if (!document.hidden) updateRentalDeadlines(); }, 60000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) updateRentalDeadlines(); });
}

