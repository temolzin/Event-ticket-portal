import { Html5Qrcode } from 'html5-qrcode';

const SECURE_CONTEXT_MESSAGE = 'La cámara solo funciona en localhost o en un sitio con HTTPS.';
const CAMERA_ERRORS = {
    NotAllowedError: 'No diste permiso para usar la cámara. Habilítalo desde el candado de la barra de direcciones.',
    NotFoundError: 'No se encontró ninguna cámara en este dispositivo.',
    NotReadableError: 'La cámara está siendo usada por otra aplicación.',
    OverconstrainedError: 'No se encontró una cámara trasera disponible.',
};
const TONES = {
    info: 'mt-3 text-sm text-gray-500 dark:text-gray-400',
    ok: 'mt-3 text-sm text-success-600 dark:text-success-400',
    error: 'mt-3 text-sm text-error-600 dark:text-error-400',
};

export function initCheckInScanner() {
    const root = document.querySelector('[data-check-in]');

    if (!root) {
        return;
    }

    const reader = root.querySelector('#check-in-reader');
    const message = root.querySelector('#check-in-camera-message');
    const toggle = root.querySelector('#check-in-camera-toggle');
    const form = root.querySelector('#check-in-form');
    const result = root.querySelector('#check-in-result');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let scanner = null;
    let busy = false;

    const tell = (text, tone = 'info') => {
        message.textContent = text;
        message.className = TONES[tone] ?? TONES.info;
    };

    const showErrorResult = (title, description) => {
        const panel = document.createElement('div');
        panel.className = 'overflow-hidden rounded-2xl border border-error-200 bg-error-50 dark:border-error-500/30 dark:bg-error-500/10';
        panel.setAttribute('role', 'alert');

        const content = document.createElement('div');
        content.className = 'px-6 py-8 text-center';

        const icon = document.createElement('span');
        icon.className = 'mx-auto flex size-20 items-center justify-center rounded-full bg-error-500 text-white';
        icon.setAttribute('aria-hidden', 'true');

        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'size-10');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '2.5');
        svg.setAttribute('viewBox', '0 0 24 24');

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('stroke-linecap', 'round');
        path.setAttribute('stroke-linejoin', 'round');
        path.setAttribute('d', 'M6 18L18 6M6 6l12 12');
        svg.append(path);
        icon.append(svg);

        const heading = document.createElement('h2');
        heading.className = 'mt-5 text-2xl font-bold text-error-700 dark:text-error-400';
        heading.textContent = title;

        const detail = document.createElement('p');
        detail.className = 'mt-2 text-sm text-error-700 dark:text-error-300';
        detail.textContent = description;

        content.append(icon, heading, detail);

        const footer = document.createElement('div');
        footer.className = 'border-t border-gray-900/5 px-6 py-4 dark:border-white/10';

        const retry = document.createElement('button');
        retry.type = 'button';
        retry.dataset.checkInReset = '';
        retry.className = 'inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600';
        retry.textContent = root.dataset.tryAgainLabel;
        footer.append(retry);

        panel.append(content, footer);
        result.replaceChildren(panel);
        result.scrollIntoView({ behavior: 'smooth', block: 'start' });
    };

    const stopScanner = async () => {
        if (!scanner) {
            return;
        }

        const running = scanner;

        scanner = null;

        try {
            await running.stop();
        } catch (error) {
            console.warn(error);
        }

        try {
            running.clear();
        } catch (error) {
            console.warn(error);
        }
    };

    const launch = async (camera, onFound) => {
        const instance = new Html5Qrcode(reader.id);

        await instance.start(
            camera,
            { fps: 10, qrbox: { width: 240, height: 240 }, aspectRatio: 1 },
            onFound,
            () => {}
        );

        return instance;
    };

    const startScanner = async () => {
        if (scanner) {
            return;
        }

        if (!window.isSecureContext) {
            tell(SECURE_CONTEXT_MESSAGE, 'error');
            toggle.textContent = 'Reintentar cámara';
            return;
        }

        toggle.disabled = true;

        const onFound = (decodedText) => submit(decodedText);

        try {
            try {
                scanner = await launch({ facingMode: 'environment' }, onFound);
            } catch (error) {
                const cameras = await Html5Qrcode.getCameras();

                if (cameras.length === 0) {
                    throw error;
                }

                scanner = await launch(cameras[0].id, onFound);
            }

            tell('Cámara lista. Apunta al código QR del boleto.', 'ok');
            toggle.textContent = 'Apagar cámara';
        } catch (error) {
            scanner = null;
            tell(CAMERA_ERRORS[error?.name] ?? 'No se pudo encender la cámara.', 'error');
            toggle.textContent = 'Reintentar cámara';
        } finally {
            toggle.disabled = false;
        }
    };

    const submit = async (code) => {
        if (busy) {
            return;
        }

        busy = true;

        await stopScanner();

        const body = new FormData();

        body.append('code', code);

        try {
            const response = await fetch(root.dataset.storeUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body,
            });

            if (response.redirected) {
                showErrorResult(root.dataset.validationErrorTitle, root.dataset.validationErrorDescription);
                tell('No se pudo validar el código capturado.', 'error');
                return;
            }

            if (!response.ok) {
                const invalidCode = response.status === 422;
                showErrorResult(
                    invalidCode ? root.dataset.invalidQrTitle : root.dataset.validationErrorTitle,
                    invalidCode ? root.dataset.invalidQrDescription : root.dataset.validationErrorDescription
                );
                return;
            }

            result.innerHTML = await response.text();
            result.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) {
            showErrorResult(root.dataset.validationErrorTitle, root.dataset.validationErrorDescription);
            tell('No se pudo enviar el código al servidor.', 'error');
        } finally {
            busy = false;
        }
    };

    toggle.addEventListener('click', async () => {
        if (scanner) {
            await stopScanner();
            tell('Cámara apagada. Puedes escribir el código a mano.', 'info');
            toggle.textContent = 'Encender cámara';
            return;
        }

        await startScanner();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const input = form.querySelector('[name="code"]');
        const code = input.value.trim();

        if (code === '') {
            tell('Escribe el código del boleto.', 'error');
            input.focus();
            return;
        }

        await submit(code);
    });

    root.addEventListener('click', (event) => {
        const reset = event.target.closest('[data-check-in-reset]');

        if (!reset) {
            return;
        }

        event.preventDefault();
        result.innerHTML = '';
        startScanner();
    });

    startScanner();
}
