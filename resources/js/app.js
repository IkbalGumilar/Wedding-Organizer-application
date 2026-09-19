(() => {
    const themeToggle = document.querySelector('[data-theme-toggle]');

    const applyTheme = (theme) => {
        const isDark = theme === 'dark';
        document.documentElement.classList.toggle('dark', isDark);

        if (!themeToggle) {
            return;
        }

        themeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        themeToggle.setAttribute('aria-label', isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
        themeToggle.title = isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap';
        themeToggle.querySelector('[data-theme-sun]')?.classList.toggle('hidden', !isDark);
        themeToggle.querySelector('[data-theme-moon]')?.classList.toggle('hidden', isDark);
    };

    if (themeToggle) {
        const storedTheme = window.localStorage.getItem('atha-theme');
        applyTheme(storedTheme ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));

        themeToggle.addEventListener('click', () => {
            const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            window.localStorage.setItem('atha-theme', nextTheme);
            applyTheme(nextTheme);
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
            if (!window.localStorage.getItem('atha-theme')) {
                applyTheme(event.matches ? 'dark' : 'light');
            }
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        const targetId = toggle.dataset.passwordToggleTarget;
        const input = targetId ? document.getElementById(targetId) : null;

        if (!input) {
            return;
        }

        const showIcon = toggle.querySelector('[data-password-show-icon]');
        const hideIcon = toggle.querySelector('[data-password-hide-icon]');

        const updateState = (isVisible) => {
            input.type = isVisible ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
            toggle.setAttribute('aria-label', isVisible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            toggle.title = isVisible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';
            showIcon?.classList.toggle('hidden', isVisible);
            hideIcon?.classList.toggle('hidden', !isVisible);
        };

        toggle.addEventListener('click', () => {
            updateState(input.type === 'password');
        });

        updateState(false);
    });

    const calendar = document.querySelector('[data-availability-calendar]');

    if (!calendar) {
        return;
    }

    const endpoint = calendar.dataset.endpoint;
    const input = calendar.querySelector('#event_date');
    const grid = calendar.querySelector('[data-calendar-grid]');
    const monthLabel = calendar.querySelector('[data-calendar-month]');
    const message = calendar.querySelector('[data-calendar-message]');
    const previousButton = calendar.querySelector('[data-calendar-prev]');
    const nextButton = calendar.querySelector('[data-calendar-next]');
    const minimumDate = calendar.dataset.minDate;
    const initialDate = calendar.dataset.initialDate || minimumDate;
    const initialParts = initialDate.split('-').map(Number);
    let month = new Date(initialParts[0], initialParts[1] - 1, 1);
    let days = new Map();

    const pad = (number) => String(number).padStart(2, '0');
    const dateValue = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    const monthValue = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}`;
    const monthTitle = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });

    const statusText = (day) => {
        if (day.status === 'past') {
            return 'Tanggal sudah lewat';
        }

        if (day.status === 'full') {
            return 'Penuh';
        }

        if (day.status === 'last_slot') {
            return 'Tersisa 1 slot';
        }

        return `Tersedia — ${day.remaining} slot tersisa`;
    };

    const setMessage = (text, isError = false) => {
        message.textContent = text;
        message.classList.toggle('text-rose-700', isError);
        message.classList.toggle('dark:text-rose-300', isError);
    };

    const render = () => {
        monthLabel.textContent = monthTitle.format(month);
        grid.replaceChildren();

        const firstDay = new Date(month.getFullYear(), month.getMonth(), 1).getDay();

        for (let index = 0; index < firstDay; index += 1) {
            const spacer = document.createElement('span');
            spacer.className = 'h-10';
            spacer.setAttribute('aria-hidden', 'true');
            grid.append(spacer);
        }

        days.forEach((day) => {
            const button = document.createElement('button');
            const isSelected = input.value === day.date;
            const disabled = day.status === 'full' || day.status === 'past' || day.date < minimumDate;
            button.type = 'button';
            button.textContent = day.date.slice(-2);
            button.disabled = disabled;
            button.dataset.status = day.status;
            button.className = 'h-10 rounded-xl border text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-rose-600';
            button.classList.add(disabled ? 'cursor-not-allowed' : 'hover:border-rose-500');
            button.classList.add(day.status === 'full' ? 'border-rose-200 bg-rose-50 text-rose-400 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-300' : 'border-stone-200 bg-white text-stone-700 dark:border-stone-700 dark:bg-stone-800 dark:text-stone-200');

            if (day.status === 'last_slot') {
                button.classList.add('border-amber-300', 'text-amber-700', 'dark:border-amber-700', 'dark:text-amber-300');
            }

            if (isSelected) {
                button.classList.add('ring-2', 'ring-rose-600', 'ring-offset-1', 'dark:ring-offset-stone-900');
            }

            button.setAttribute('aria-label', `${day.date}: ${statusText(day)}`);
            button.title = statusText(day);
            button.addEventListener('click', () => {
                input.value = day.date;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
            grid.append(button);
        });
    };

    const loadMonth = async () => {
        setMessage('Memuat ketersediaan tanggal...');

        try {
            const response = await fetch(`${endpoint}?month=${monthValue(month)}`, { headers: { Accept: 'application/json' } });

            if (!response.ok) {
                throw new Error('Availability request failed');
            }

            const data = await response.json();
            days = new Map(data.days.map((day) => [day.date, day]));
            render();

            if (input.value && days.has(input.value)) {
                setMessage(statusText(days.get(input.value)), days.get(input.value).status === 'full');
            } else {
                setMessage('Pilih tanggal untuk melihat ketersediaan.');
            }
        } catch {
            setMessage('Ketersediaan belum dapat dimuat. Tanggal tetap akan diperiksa saat dikonfirmasi.', true);
            grid.replaceChildren();
        }
    };

    input.addEventListener('change', () => {
        const selected = days.get(input.value);

        if (!selected) {
            return;
        }

        setMessage(statusText(selected), selected.status === 'full' || selected.status === 'past');
        render();
    });

    previousButton.addEventListener('click', () => {
        month = new Date(month.getFullYear(), month.getMonth() - 1, 1);
        loadMonth();
    });

    nextButton.addEventListener('click', () => {
        month = new Date(month.getFullYear(), month.getMonth() + 1, 1);
        loadMonth();
    });

    loadMonth();
})();
