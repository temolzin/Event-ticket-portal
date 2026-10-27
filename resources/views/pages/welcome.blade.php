@vite(['resources/css/welcome.css'])
<!doctype html>
<html class="no-js" lang="es">
    <head>
        <meta charset="utf-8" />
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>
        Nexus Tech 2027 — IA, Ciberseguridad y Automatización | Root Heim
        </title>
        <meta
        content="Cumbre ejecutiva de Root Heim para dueños, directores y fundadores de PyMEs. IA aplicada, ciberseguridad, automatización y networking de alto nivel. 23 de enero de 2027, Estado de México."
        name="description"
        />
        <meta content="#00043E" name="theme-color" />
        <meta content="Nexus Tech 2027 — Root Heim" property="og:title" />
        <meta
        content="El futuro de tu empresa se decide en un día. Cumbre ejecutiva de IA, ciberseguridad y automatización. 23 de enero de 2027."
        property="og:description"
        />
        <meta content="website" property="og:type" />
        <link href={{ asset('images/brand/rootheim-logo-light.png') }} rel="icon" type="image/png" />
        <link href="https://fonts.googleapis.com" rel="preconnect" />
        <link crossorigin href="https://fonts.gstatic.com" rel="preconnect" />
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;family=Sora:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
        <script>
        document.documentElement.classList.replace("no-js", "js");
        </script>
    </head>
    <body>
        <svg aria-hidden="true" class="sprite" xmlns="http://www.w3.org/2000/svg">
        <symbol id="i-arrow" viewBox="0 0 24 24">
            <path d="M4 12h15M13 6l6 6-6 6"></path>
        </symbol>
        <symbol id="i-check" viewBox="0 0 24 24">
            <path d="m5 12 4 4L19 6"></path>
        </symbol>
        <symbol id="i-spark" viewBox="0 0 24 24">
            <path d="M13 2 4 14h7l-1 8 10-13h-7l1-7Z"></path>
        </symbol>
        <symbol id="i-shield" viewBox="0 0 24 24">
            <path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"></path>
            <path d="m9 12 2 2 4-4"></path>
        </symbol>
        <symbol id="i-network" viewBox="0 0 24 24">
            <circle cx="12" cy="5" r="2.5"></circle>
            <circle cx="5" cy="18" r="2.5"></circle>
            <circle cx="19" cy="18" r="2.5"></circle>
            <path d="M12 7.5v4M6.8 16l3.7-3M17.2 16l-3.7-3"></path>
        </symbol>
        <symbol id="i-badge" viewBox="0 0 24 24">
            <circle cx="12" cy="9" r="5"></circle>
            <path d="m8.5 13.5-1 7 4.5-2 4.5 2-1-7"></path>
            <path d="m10.2 9 1.3 1.3 2.5-2.6"></path>
        </symbol>
        <symbol id="i-calendar" viewBox="0 0 24 24">
            <rect height="16" rx="2" width="18" x="3" y="5"></rect>
            <path d="M8 3v4M16 3v4M3 10h18"></path>
        </symbol>
        <symbol id="i-clock" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="9"></circle>
            <path d="M12 7v5l3 2"></path>
        </symbol>
        <symbol id="i-pin" viewBox="0 0 24 24">
            <path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"></path>
            <circle cx="12" cy="10" r="2.5"></circle>
        </symbol>
        <symbol id="i-mail" viewBox="0 0 24 24">
            <rect height="14" rx="2" width="18" x="3" y="5"></rect>
            <path d="m3 7 9 6 9-6"></path>
        </symbol>
        <symbol id="i-phone" viewBox="0 0 24 24">
            <path
            d="M6.5 3h3l1.5 4-2 1.5a14 14 0 0 0 6.5 6.5L17 13l4 1.5v3a2 2 0 0 1-2.2 2A17 17 0 0 1 4 5.2 2 2 0 0 1 6.5 3Z"
            ></path>
        </symbol>
        <symbol id="i-whatsapp" viewBox="0 0 24 24">
            <path
            d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.26-.46-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.07.15.2 2.1 3.2 5.08 4.49.7.3 1.26.49 1.69.62.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2-1.41.25-.7.25-1.29.18-1.42-.08-.12-.28-.2-.58-.34m-5.42 7.4h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.89-9.88 2.64 0 5.12 1.03 6.99 2.9a9.83 9.83 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.88 9.88m8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.69 1.45h.01c6.55 0 11.89-5.34 11.89-11.9 0-3.17-1.24-6.15-3.48-8.41Z"
            ></path>
        </symbol>
        </svg>

        <a class="skip" href="#content">Skip to content</a>

        <main id="content" tabindex="-1">
        <!-- HERO · LOGO, TÍTULO, SUBTÍTULO, IMAGEN, FORMULARIO Y CTA -->
        <section class="hero" id="inicio">
            <div aria-hidden="true" class="hero__bg"></div>
            <div class="container hero__inner">
            <div class="hero__copy">
                <div class="hero__lockup reveal">
                <img
                    alt="Root Heim"
                    height="575"
                    src={{ asset('images/brand/rootheim-logo-light.png') }}
                    width="1000"
                />
                <span class="hero__lockup-text">presenta</span>
                <span class="hero__lockup-rule" aria-hidden="true"></span>
                <span class="hero__lockup-date">23 · 01 · 2027</span>
                </div>

                <p class="eyebrow reveal" style="--d: 50ms">
                <span class="eyebrow__dot"></span>
                Cumbre ejecutiva · Estado de México
                </p>

                <h1 class="hero__title reveal" style="--d: 110ms">
                <span class="hero__name">Nexus Tech<br /><em>2027</em></span>
                </h1>

                <p class="hero__claim reveal" style="--d: 140ms">
                El futuro de tu empresa se decide en un día.
                </p>

                <p class="hero__lead reveal" style="--d: 170ms">
                Inteligencia artificial, ciberseguridad y automatización
                aplicadas a la operación real de tu negocio. Un día para salir con
                decisiones, contactos y un plan que sí se puede ejecutar.
                </p>

                <ul class="hero__meta reveal" style="--d: 290ms">
                <li>
                    <svg aria-hidden="true" class="icon">
                    <use href="#i-check"></use>
                    </svg>
                    4 módulos aplicados
                </li>
                <li>
                    <svg aria-hidden="true" class="icon">
                    <use href="#i-check"></use>
                    </svg>
                    Buffet &amp; Night Experience
                </li>
                <li>
                    <svg aria-hidden="true" class="icon">
                    <use href="#i-check"></use>
                    </svg>
                    Certificación oficial
                </li>
                </ul>
            </div>

            <aside
                aria-labelledby="formTitle"
                class="hero__panel reveal"
                style="--d: 150ms"
            >

                <form class="reg-form" id="register" data-form="registerForm">
                @csrf
                <div class="reg-form__head">
                    <div>
                    <p class="reg-form__kicker">Registro</p>
                    <h2 id="formTitle">Reserva tu lugar</h2>
                    </div>
                </div>

                <div class="reg-form__grid">
                    <div class="field">
                    <label for="name">Nombre completo</label>
                    <input
                        autocomplete="name"
                        id="name"
                        name="name"
                        placeholder="Nombre y apellido"
                        required
                        type="text"
                    />
                    </div>

                    <div class="field">
                    <label for="company">Empresa</label>
                    <input
                        autocomplete="organization"
                        id="company"
                        name="company"
                        placeholder="Razón social o marca"
                        required
                        type="text"
                    />
                    </div>

                    <div class="form-row">
                    <div class="field">
                        <label for="email">Correo corporativo</label>
                        <input
                        autocomplete="email"
                        id="email"
                        name="email"
                        placeholder="nombre@empresa.com"
                        required
                        type="email"
                        />
                    </div>
                    <div class="field">
                        <label for="phone">Teléfono</label>
                        <input
                        autocomplete="tel"
                        id="phone"
                        inputmode="numeric"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        name="phone"
                        placeholder="5512345678"
                        required
                        type="tel"
                        />
                    </div>
                    </div>

                    <div class="field">
                    <label for="ticket_type">Pase de interés</label>
                    <div class="select-wrap">
                        <select id="ticket_type" name="ticket_type" required>
                        <option disabled selected value="">
                            Selecciona una opción
                        </option>
                        <option value="individual_launch">Individual · $990 MXN</option>
                        <option value="duo_launch">Dupla · $1,800 MXN</option>
                        <option value="individual_standard">Individual estándar · $1,500 MXN</option>
                        <option value="duo_executive_standard">Dupla ejecutiva estándar · $2,600 MXN</option>
                        </select>
                        <svg aria-hidden="true" class="icon">
                        <use href="#i-arrow"></use>
                        </svg>
                    </div>
                    </div>
                </div>

                <button class="btn btn--primary btn--wide" type="submit">
                    Enviar registro
                    <svg aria-hidden="true" class="icon">
                    <use href="#i-mail"></use>
                    </svg>
                </button>
                <p class="form-note">
                    Tus datos se usan únicamente para coordinar tu registro. No se comparten con terceros.
                </p>
                <p
                    aria-live="polite"
                    class="form-status"
                    id="formStatus"
                    role="status"
                ></p>
                </form>
            </aside>
            </div>
        </section>

        <!-- 07 · CAMPAIGN -->
        <section class="campaign" id="campaign">
            <div aria-hidden="true" class="marquee">
            <div class="marquee__track">
                <div class="marquee__group">
                <span>IA aplicada</span><i class="marquee__sep"></i>
                <span>Ciberseguridad</span><i class="marquee__sep"></i>
                <span>Automatización</span><i class="marquee__sep"></i>
                <span>Networking C-Level</span><i class="marquee__sep"></i>
                <span>Certificación oficial</span><i class="marquee__sep"></i>
                <span>Buffet &amp; Night Experience</span><i class="marquee__sep"></i>
                </div>
                <div class="marquee__group">
                <span>IA aplicada</span><i class="marquee__sep"></i>
                <span>Ciberseguridad</span><i class="marquee__sep"></i>
                <span>Automatización</span><i class="marquee__sep"></i>
                <span>Networking C-Level</span><i class="marquee__sep"></i>
                <span>Certificación oficial</span><i class="marquee__sep"></i>
                <span>Buffet &amp; Night Experience</span><i class="marquee__sep"></i>
                </div>
            </div>
            </div>

            <div class="container campaign__inner">
            <div class="campaign__copy reveal">
                <p class="eyebrow">
                <span class="eyebrow__dot"></span>
                Campaña fundadores
                </p>
                <h2>30 accesos.<br /><em>Precio de lanzamiento.</em></h2>
                <p class="campaign__lead">
                Diseñada para empresas que toman la decisión antes que el resto.
                Al agotarse el cupo fundador, aplica la tarifa estándar. El acceso
                incluye la experiencia completa: formación, alimentos, networking
                y certificación.
                </p>

                <div class="campaign__prices">
                <div class="campaign__price">
                    <strong>$990</strong>
                    <span>Individual · MXN</span>
                </div>
                <div class="campaign__price">
                    <strong>$1,800</strong>
                    <span>Dupla · MXN</span>
                </div>
                <div class="campaign__price">
                    <strong>30</strong>
                    <span>Accesos preventa</span>
                </div>
                </div>
            </div>

            <div class="countdown reveal" style="--d: 100ms">
                <div class="countdown__head">
                <span>Cuenta regresiva</span>
                <span>23 ENE 2027</span>
                </div>
                <div class="countdown__grid">
                <div>
                    <span data-count="days">00</span>
                    <small>Días</small>
                </div>
                <div>
                    <span data-count="hours">00</span>
                    <small>Horas</small>
                </div>
                <div>
                    <span data-count="minutes">00</span>
                    <small>Min</small>
                </div>
                <div>
                    <span data-count="seconds">00</span>
                    <small>Seg</small>
                </div>
                </div>
                <div class="countdown__foot">
                <span>10:00 AM</span>
                <span>Estado de México</span>
                </div>
            </div>
            </div>
        </section>

        <!-- 08 · BENEFICIOS Y PRECIOS -->
        <section class="benefits" id="benefits">
            <div class="container">
            <div class="section-head reveal">
                <p class="eyebrow eyebrow--dark">
                <span class="eyebrow__dot"></span>
                Beneficios
                </p>
                <h2>Cuatro ventajas que se quedan en tu empresa.</h2>
                <p>
                No es un evento de inspiración. Es una jornada de trabajo para
                salir con criterio, contactos y herramientas listas para usar el
                lunes.
                </p>
            </div>

            <div class="benefits__grid">
                <article class="benefit reveal">
                <div class="benefit__top">
                    <span class="benefit__num">01</span>
                    <span class="benefit__icon">
                    <svg aria-hidden="true" class="icon">
                        <use href="#i-spark"></use>
                    </svg>
                    </span>
                </div>
                <h3>IA que reduce costos</h3>
                <p>
                    Prompts, flujos y automatizaciones documentales aplicables desde
                    el día siguiente. Casos reales para vender más y operar con
                    menos fricción.
                </p>
                </article>

                <article class="benefit reveal" style="--d: 60ms">
                <div class="benefit__top">
                    <span class="benefit__num">02</span>
                    <span class="benefit__icon">
                    <svg aria-hidden="true" class="icon">
                        <use href="#i-shield"></use>
                    </svg>
                    </span>
                </div>
                <h3>Ciberseguridad para decidir</h3>
                <p>
                    Protocolos anti-fraude, respuesta ante ransomware y criterios
                    claros para proteger el patrimonio de tu empresa sin depender de
                    falsos gurús.
                </p>
                </article>

                <article class="benefit reveal" style="--d: 120ms">
                <div class="benefit__top">
                    <span class="benefit__num">03</span>
                    <span class="benefit__icon">
                    <svg aria-hidden="true" class="icon">
                        <use href="#i-network"></use>
                    </svg>
                    </span>
                </div>
                <h3>Networking de alto nivel</h3>
                <p>
                    Dueños, directores y fundadores de PyMEs del EdoMéx en un
                    formato sin protocolo: buffet ejecutivo, coctelería y Night
                    Experience para cerrar alianzas.
                </p>
                </article>

                <article class="benefit reveal" style="--d: 180ms">
                <div class="benefit__top">
                    <span class="benefit__num">04</span>
                    <span class="benefit__icon">
                    <svg aria-hidden="true" class="icon">
                        <use href="#i-badge"></use>
                    </svg>
                    </span>
                </div>
                <h3>Certificación verificable</h3>
                <p>
                    Acreditación oficial de Root Heim con código criptográfico para
                    respaldar tu formación directiva en gobernanza de IA y
                    resiliencia operativa.
                </p>
                </article>
            </div>
            </div>
        </section>
        </main>

        <footer class="site-footer">
        <div class="container">
            <div class="footer__top">
            <a aria-label="Volver al inicio" class="footer__brand" href="#inicio">
                <img
                alt="Root Heim"
                height="575"
                loading="lazy"
                src={{ asset('images/brand/rootheim-logo-light.png') }}
                width="1000"
                />
                <span>Nexus Tech 2027</span>
            </a>
            <nav aria-label="Navegación del pie de página" class="footer__nav">
                <a href="#register">Inscripción</a>
                <a href="#campaign">Campaña</a>
                <a href="#benefits">Beneficios</a>
            </nav>
            </div>
            <div class="footer__bottom">
            <p>
                © 2027 Root Heim Company S.A. de C.V. Todos los derechos
                reservados.
            </p>
            <p>Nexus Tech 2027 · Estado de México · Producción Root Heim</p>
            </div>
        </div>
        </footer>

        <script>
        (() => {
            const $ = (selector, context = document) =>
            context.querySelector(selector);
            const $$ = (selector, context = document) => [
            ...context.querySelectorAll(selector),
            ];
            const reducedMotion = window.matchMedia(
            "(prefers-reduced-motion: reduce)",
            ).matches;

            $$('a[href^="#"]').forEach((link) => {
            link.addEventListener("click", (event) => {
                const href = link.getAttribute("href");
                if (!href || href === "#") return;
                const target = document.querySelector(href);
                if (!target) return;

                if (link.classList.contains("skip")) {
                event.preventDefault();
                target.focus({ preventScroll: true });
                target.scrollIntoView({ behavior: "auto", block: "start" });
                return;
                }

                event.preventDefault();
                const top =
                target.getBoundingClientRect().top + window.scrollY - 12;

                window.scrollTo({
                top: Math.max(0, top),
                behavior: reducedMotion ? "auto" : "smooth",
                });

                if (history.replaceState) history.replaceState(null, "", href);
            });
            });

            const reveals = $$(".reveal");
            if (!reducedMotion && "IntersectionObserver" in window) {
            const revealObserver = new IntersectionObserver(
                (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add("is-in");
                    revealObserver.unobserve(entry.target);
                });
                },
                { threshold: 0.12, rootMargin: "0px 0px -8% 0px" },
            );
            reveals.forEach((element) => revealObserver.observe(element));

            window.addEventListener("load", () => {
                window.setTimeout(() => {
                reveals.forEach((element) => element.classList.add("is-in"));
                }, 2600);
            });
            } else {
            reveals.forEach((element) => element.classList.add("is-in"));
            }

            const targetTime = new Date("2027-01-23T10:00:00-06:00").getTime();
            const countdown = {
            days: $('[data-count="days"]'),
            hours: $('[data-count="hours"]'),
            minutes: $('[data-count="minutes"]'),
            seconds: $('[data-count="seconds"]'),
            };

            const pad = (value) => String(value).padStart(2, "0");
            const tick = () => {
            if (!countdown.days) return;
            const distance = Math.max(0, targetTime - Date.now());
            const days = Math.floor(distance / 86400000);
            const hours = Math.floor((distance % 86400000) / 3600000);
            const minutes = Math.floor((distance % 3600000) / 60000);
            const seconds = Math.floor((distance % 60000) / 1000);

            countdown.days.textContent = pad(days);
            countdown.hours.textContent = pad(hours);
            countdown.minutes.textContent = pad(minutes);
            countdown.seconds.textContent = pad(seconds);
            };
            tick();
            window.setInterval(tick, 1000);

            const form = $('[data-form="registerForm"]');
            const status = $("#formStatus");
            const phoneInput = $("#phone");

            const onlyNumbers = (value) => value.replace(/[^0-9]/g, "").slice(0, 10);

            if (phoneInput) {
                phoneInput.setAttribute("maxlength", "10");
                phoneInput.addEventListener("input", () => {
                    phoneInput.value = onlyNumbers(phoneInput.value);
                });
            }

            if (form) {
                form.addEventListener("submit", async (event) => {
                    event.preventDefault();

                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonText = submitButton ? submitButton.innerHTML : null;

                    submitButton.disabled = true;
                    submitButton.innerHTML = "Enviando...";

                    status.textContent = "";

                    const data = new FormData(form);
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;

                    let response;
                    try {
                        response = await fetch("{{ route('event-leads.store') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": token,
                                Accept: "application/json",
                            },
                            body: data,
                        });
                    } catch (error) {
                        status.textContent = "Hubo un error al enviar tu registro. Intenta nuevamente.";
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                        return;
                    }

                    if (response.ok) {
                        status.textContent = "Registro enviado correctamente. Nos pondremos en contacto contigo.";
                        form.reset();
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                        return;
                    }

                    let errorMessage = "Hubo un error al enviar tu registro. Intenta nuevamente.";

                    try {
                        const errorData = await response.json();
                        const hasMessage = errorData && errorData.message;
                        const hasErrors = errorData && errorData.errors;
                        const errorsValues = hasErrors ? Object.values(errorData.errors) : [];
                        const firstError = errorsValues.length ? errorsValues[0] : null;
                        const firstErrorMessage = Array.isArray(firstError) && firstError[0] ? firstError[0] : null;

                        errorMessage = hasMessage ? errorData.message : firstErrorMessage ? firstErrorMessage : errorMessage;
                    } catch (e) {
                    }

                    status.textContent = errorMessage;
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                });
            }
        })();
        </script>
    </body>
</html>
