import { initializeDropdowns } from './dropdowns';

initializeDropdowns();

const bookingStatus = document.querySelector('[data-booking-status]');
const statusGuidance = document.querySelector('#booking-status-guidance');
bookingStatus?.addEventListener('change', () => {
    statusGuidance.textContent = bookingStatus.selectedOptions[0].dataset.guidance;
});

document.querySelector('[data-handover-now]')?.addEventListener('click', () => {
    const form = bookingStatus.form;
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
    }).formatToParts(new Date());
    const time = Object.fromEntries(parts.map(part => [part.type, part.value]));
    form.elements.start_date.value = `${time.year}-${time.month}-${time.day}`;
    form.elements.start_time.value = `${time.hour}:${time.minute}`;
    form.elements.returned_at.value = '';
    bookingStatus.value = 'active';
    bookingStatus.dispatchEvent(new Event('change', { bubbles: true }));
    form.querySelector('[data-handover-guidance]').textContent = `Waktu serah terima diisi ${time.day}/${time.month}/${time.year}, ${time.hour}:${time.minute} WIB. Periksa unit dan batas pengembalian, lalu klik Simpan perubahan.`;
});

document.querySelectorAll('[data-password-toggle]').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        button.querySelector('.material-symbols-outlined').textContent = visible ? 'visibility_off' : 'visibility';
    });
});

const upload = document.querySelector('#image-upload');
const preview = document.querySelector('#car-image-preview');
let previewUrl;
upload?.addEventListener('change', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    const file = upload.files[0];
    if (!file || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return;
    previewUrl = URL.createObjectURL(file);
    preview.src = previewUrl;
});
document.querySelector('#image-url')?.addEventListener('change', event => {
    if (upload.files.length) return;
    const value = event.target.value.trim();
    if (/^https?:\/\/|^\/(?!\/)/i.test(value)) preview.src = value;
});
window.addEventListener('pagehide', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
});
