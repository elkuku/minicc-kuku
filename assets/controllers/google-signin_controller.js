import { Controller } from '@hotwired/stimulus';

/*
 * Renders the "Sign in with Google" button and re-renders it when the Bootstrap
 * color mode (data-bs-theme on <html>) changes, so it matches light and dark mode.
 * The client is configured by the g_id_onload element in base.html.twig.
 */
export default class extends Controller {
    static values = {
        size: { type: String, default: 'large' },
    };

    connect() {
        this.observer = new MutationObserver(() => this.render());
        this.observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-bs-theme'],
        });

        // The GIS script is loaded async and reads g_id_onload when it runs.
        // The window load event fires only after it ran, so rendering earlier
        // fails with "Failed to render button before calling initialize()".
        if ('complete' === document.readyState) {
            this.render();
        } else {
            this.onLoad = () => this.render();
            window.addEventListener('load', this.onLoad, { once: true });
        }
    }

    disconnect() {
        this.observer?.disconnect();
        if (this.onLoad) {
            window.removeEventListener('load', this.onLoad);
        }
    }

    render() {
        if (!window.google?.accounts?.id) {
            return;
        }

        const dark = 'dark' === document.documentElement.getAttribute('data-bs-theme');

        this.element.replaceChildren();
        window.google.accounts.id.renderButton(this.element, {
            type: 'standard',
            size: this.sizeValue,
            // filled_black blends into the dark cards, the blue button stands out.
            theme: dark ? 'filled_blue' : 'outline',
            text: 'signin_with',
            shape: 'pill',
            logo_alignment: 'left',
            locale: 'es',
        });
    }
}
