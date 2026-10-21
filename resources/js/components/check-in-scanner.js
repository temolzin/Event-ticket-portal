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
                    'X-CSRF-TOKEN': csrf,
                },
                body,
            });

            if (response.redirected) {
                result.innerHTML = '';
                tell('No se pudo validar el código capturado. Escríbelo manualmente.', 'error');
                return;
            }

            result.innerHTML = await response.text();
            result.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (error) {
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
