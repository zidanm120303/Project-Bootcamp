import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const rupiahDigits = value => String(value ?? '').replace(/\D/g, '');
const rupiahFormat = value => {
    const digits = rupiahDigits(value).replace(/^0+(?=\d)/, '');

    return digits ? `Rp ${Number(digits).toLocaleString('id-ID')}` : '';
};

document.querySelectorAll('[data-rupiah]').forEach(input => {
    input.value = rupiahFormat(input.value);

    input.addEventListener('input', () => {
        input.value = rupiahFormat(input.value);
    });
});

const shiftDate = (date, days) => {
    const [year, month, day] = date.split('-').map(Number);
    const value = new Date(Date.UTC(year, month - 1, day));
    value.setUTCDate(value.getUTCDate() + days);

    return value.toISOString().slice(0, 10);
};

const dateDistance = (start, end) => {
    const startAt = new Date(`${start}T00:00:00Z`);
    const endAt = new Date(`${end}T00:00:00Z`);

    return Math.max(1, Math.round((endAt - startAt) / 86400000));
};

window.bookingAvailability = config => ({
    start: config.start,
    end: config.end,
    quantity: 1,
    checking: false,
    result: null,
    calendar: config.calendar,
    calendarPage: 0,
    favorite: false,

    init() {
        this.favorite = localStorage.getItem(`rentalpro-favorite-${config.productId}`) === '1';
        this.check();
    },

    get rentalDays() {
        return dateDistance(this.start, this.end);
    },

    get rentalSubtotal() {
        return config.price * this.rentalDays * this.quantity;
    },

    get depositTotal() {
        return config.deposit * this.quantity;
    },

    get bookingTotal() {
        return this.rentalSubtotal + this.depositTotal;
    },

    get visibleCalendar() {
        const offset = this.calendarPage * 7;
        return this.calendar.slice(offset, offset + 7);
    },

    get calendarTitle() {
        const date = this.visibleCalendar[0]?.date;
        if (!date) return '';

        return new Intl.DateTimeFormat('id-ID', {
            month: 'long',
            year: 'numeric',
            timeZone: 'UTC',
        }).format(new Date(`${date}T00:00:00Z`));
    },

    money(value) {
        return `Rp${Number(value || 0).toLocaleString('id-ID')}`;
    },

    resetResult() {
        this.result = null;
    },

    setDuration(days) {
        this.end = shiftDate(this.start, days);
        this.check();
    },

    changeQuantity(amount) {
        this.quantity = Math.max(1, Math.min(config.maxQuantity, this.quantity + amount));
        this.check();
    },

    selectDay(day) {
        if (['past', 'unavailable'].includes(day.status)) {
            return;
        }

        const duration = dateDistance(this.start, this.end);
        this.start = day.date;
        this.end = shiftDate(day.date, duration);
        this.check();
    },

    selectSuggestion(suggestion) {
        this.start = suggestion.start_at;
        this.end = suggestion.end_at;
        this.check();
    },

    durationLabel(days) {
        const end = shiftDate(this.start, days);
        return `${this.start.slice(8, 10)}/${this.start.slice(5, 7)} – ${end.slice(8, 10)}/${end.slice(5, 7)}`;
    },

    changeDate() {
        this.resetResult();
        if (this.start && this.end && this.end > this.start) this.check();
    },

    previousCalendar() {
        this.calendarPage = Math.max(0, this.calendarPage - 1);
    },

    toggleFavorite() {
        this.favorite = !this.favorite;
        localStorage.setItem(`rentalpro-favorite-${config.productId}`, this.favorite ? '1' : '0');
        window.dispatchEvent(new CustomEvent('app-notify', {
            detail: {
                type: 'success',
                message: this.favorite ? `${config.productName} disimpan ke favorit.` : `${config.productName} dihapus dari favorit.`,
            },
        }));
    },

    isSelected(day) {
        return day.date >= this.start && day.date < this.end;
    },

    proceed(form) {
        if (!this.result) {
            window.dispatchEvent(new CustomEvent('app-notify', {
                detail: { type: 'warning', message: 'Silakan cek ketersediaan tanggal terlebih dahulu.' },
            }));
            return;
        }

        if (!this.result.available) {
            window.dispatchEvent(new CustomEvent('app-notify', {
                detail: { type: 'error', message: 'Pilih salah satu tanggal yang tersedia sebelum melanjutkan.' },
            }));
            return;
        }

        form.submit();
    },

    async check() {
        this.checking = true;
        this.result = null;

        try {
            const response = await fetch(config.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    start_at: this.start,
                    end_at: this.end,
                    quantity: this.quantity,
                }),
            });
            const payload = await response.json();

            if (!response.ok) {
                const message = Object.values(payload.errors ?? {}).flat()[0] ?? 'Tanggal yang dipilih belum valid.';
                throw new Error(message);
            }

            this.result = payload;
            this.calendar = payload.calendar ?? this.calendar;
        } catch (error) {
            this.result = {
                available: false,
                message: error.message ?? 'Ketersediaan belum dapat diperiksa. Silakan coba lagi.',
                suggestions: [],
            };
        } finally {
            this.checking = false;
        }
    },
});

const fieldLabel = field => {
    const linkedLabel = field.id ? document.querySelector(`label[for="${CSS.escape(field.id)}"]`) : null;
    const nearbyLabel = field.closest('div')?.querySelector('label');

    return (linkedLabel?.textContent || nearbyLabel?.textContent || field.name || 'Field').trim();
};

const validationMessage = field => {
    const label = fieldLabel(field);
    const validity = field.validity;

    if (validity.valueMissing) return `${label} wajib diisi.`;
    if (validity.typeMismatch) return `Format ${label.toLowerCase()} tidak valid.`;
    if (validity.patternMismatch) return `Format ${label.toLowerCase()} belum sesuai.`;
    if (validity.tooShort) return `${label} minimal ${field.minLength} karakter.`;
    if (validity.tooLong) return `${label} maksimal ${field.maxLength} karakter.`;
    if (validity.rangeUnderflow) return `${label} minimal ${field.min}.`;
    if (validity.rangeOverflow) return `${label} maksimal ${field.max}.`;
    if (validity.stepMismatch || validity.badInput) return `Nilai ${label.toLowerCase()} tidak valid.`;

    return field.validationMessage || `${label} belum valid.`;
};

const clearFieldError = field => {
    field.classList.remove('input-error');
    field.removeAttribute('aria-invalid');
    field.parentElement?.querySelector(`[data-error-for="${CSS.escape(field.name)}"]`)?.remove();
};

const showFieldError = (field, message) => {
    if (!field.name || ['hidden', 'submit', 'button'].includes(field.type)) return;

    clearFieldError(field);
    field.classList.add('input-error');
    field.setAttribute('aria-invalid', 'true');

    const error = document.createElement('p');
    error.className = 'field-error';
    error.dataset.errorFor = field.name;
    error.textContent = message;
    field.insertAdjacentElement('afterend', error);
};

const notify = (message, type = 'error') => {
    window.dispatchEvent(new CustomEvent('app-notify', {
        detail: { message, type },
    }));
};

const fileLimits = {
    avatar: 2,
    identity_file: 2,
    proof_file: 2,
    image: 3,
    file: 5,
};

const validateFile = field => {
    const file = field.files?.[0];
    if (!file) {
        field.setCustomValidity('');
        return true;
    }

    const maxSize = fileLimits[field.name] ?? 5;
    if (file.size > maxSize * 1024 * 1024) {
        field.setCustomValidity(`Ukuran file maksimal ${maxSize} MB.`);
        return false;
    }

    const accepted = (field.accept || '').split(',').map(type => type.trim().toLowerCase()).filter(Boolean);
    const extension = `.${file.name.split('.').pop()?.toLowerCase()}`;
    const validType = accepted.length === 0 || accepted.some(type =>
        type === extension
        || type === file.type.toLowerCase()
        || (type.endsWith('/*') && file.type.toLowerCase().startsWith(type.slice(0, -1)))
    );
    if (!validType) {
        field.setCustomValidity('Format file tidak didukung.');
        return false;
    }

    field.setCustomValidity('');
    return true;
};

document.querySelectorAll('input[name="phone"], input[name="customer_phone"], input[name="emergency_contact_phone"]').forEach(field => {
    if (!field.pattern) field.pattern = '[0-9+\\-\\s()]{8,20}';
    if (!field.maxLength || field.maxLength < 0) field.maxLength = 20;
    field.inputMode = 'tel';
});

document.querySelectorAll('input[name="bank_account_number"], input[name="sender_account"]').forEach(field => {
    if (!field.pattern) field.pattern = '[0-9]{6,30}';
    field.inputMode = 'numeric';
});

document.querySelectorAll('form').forEach(form => {
    form.noValidate = true;

    form.querySelectorAll('input[type="file"]').forEach(field => {
        field.addEventListener('change', () => {
            validateFile(field);
            if (field.checkValidity()) clearFieldError(field);
        });
    });

    form.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('input', () => {
            field.setCustomValidity('');
            clearFieldError(field);
        });
        field.addEventListener('change', () => {
            if (field.type !== 'file') clearFieldError(field);
        });
    });

    form.addEventListener('submit', event => {
        form.querySelectorAll('input[type="file"]').forEach(validateFile);

        const password = form.querySelector('[name="password"]');
        const confirmation = form.querySelector('[name="password_confirmation"]');
        if (password && confirmation) {
            confirmation.setCustomValidity(
                confirmation.value && password.value !== confirmation.value
                    ? 'Konfirmasi password tidak sama.'
                    : ''
            );
        }

        if (form.checkValidity()) return;

        event.preventDefault();
        event.stopPropagation();

        const invalidFields = [...form.querySelectorAll('input, select, textarea')]
            .filter(field => !field.checkValidity());
        invalidFields.forEach(field => showFieldError(field, validationMessage(field)));

        const firstInvalid = invalidFields[0];
        if (firstInvalid) {
            firstInvalid.focus({ preventScroll: true });
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            notify(`${validationMessage(firstInvalid)} Periksa ${invalidFields.length} field yang ditandai.`);
        }
    });
});

Object.entries(window.serverValidationErrors ?? {}).forEach(([name, messages]) => {
    const field = document.querySelector(`[name="${CSS.escape(name)}"]`);
    if (field) showFieldError(field, messages[0]);
});

Alpine.start();
