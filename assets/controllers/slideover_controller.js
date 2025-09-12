// assets/controllers/slideover_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["panel", "overlay"];
    static values = {
        duration: { type: Number, default: 500 } // ms (md:700 via Tailwind)
    };

    connect() {
        // État initial
        this.overlayTarget.classList.add("opacity-0");
        this.panelTarget.classList.add("translate-x-full");

        // Observe les changements d'attributs du dialog (open)
        this.mo = new MutationObserver((mutations) => {
            for (const m of mutations) {
                if (m.type === "attributes" && m.attributeName === "open") {
                    if (this.element.open) this.animateIn();
                }
            }
        });
        this.mo.observe(this.element, { attributes: true });

        // Esc => anime avant fermeture
        this.onCancel = (e) => {
            e.preventDefault();
            this.closeAnimated();
        };
        this.element.addEventListener("cancel", this.onCancel);

        // Intercepte tous les "command=close" dans le dialog pour animer la sortie
// Intercepte tous les "command=close" dans le dialog pour animer la sortie
        this.onClick = (e) => {
            const closer = e.target.closest('[command="close"]');
            if (!closer || !this.element.contains(closer)) return;

            // Si c’est un lien, on prépare la navigation
            const link = closer.closest("a");
            const href = link?.getAttribute("href") || "";

            // Respecter middle-click / Cmd/Ctrl/Shift/Alt / target=_blank
            const isModified = e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button === 1;
            const target = link?.getAttribute("target");
            if (isModified || target === "_blank") {
                // Laisser le navigateur faire son job
                return;
            }

            // On gère la fermeture animée puis on navigue nous-mêmes
            e.preventDefault();
            e.stopPropagation();

            this.closeAnimated().then(() => {
                if (!href) return;

                // Cas 1: ancre pure "#section" => scroll smooth + maj hash
                if (href.startsWith("#")) {
                    const id = href.slice(1);
                    const el = document.getElementById(id);
                    if (el) {
                        el.scrollIntoView({ behavior: "smooth", block: "start" });
                        history.pushState(null, "", href);
                    } else {
                        // fallback si l’élément n’existe pas
                        location.hash = href;
                    }
                    return;
                }

                // Cas 2: URL relative/absolue — on décide si même page + hash
                try {
                    const url = new URL(href, window.location.href);
                    const here = new URL(window.location.href);

                    const sameOrigin = url.origin === here.origin;
                    const samePath   = url.pathname === here.pathname;

                    if (sameOrigin && samePath && url.hash) {
                        // même page avec hash => scroll smooth sans reload
                        const id = url.hash.slice(1);
                        const el = document.getElementById(id);
                        if (el) {
                            el.scrollIntoView({ behavior: "smooth", block: "start" });
                            history.pushState(null, "", url.hash);
                            return;
                        }
                    }
                    // Tous les autres cas: vraie navigation
                    window.location.assign(url.href);
                } catch {
                    // href non-parsable => fallback navigation
                    window.location.assign(href);
                }
            });
        };
        this.element.addEventListener("click", this.onClick, true);

        // Si déjà ouvert (rare), force l'état visible sans flash
        if (this.element.open) this.animateIn(true);
    }

    disconnect() {
        this.mo && this.mo.disconnect();
        this.element.removeEventListener("cancel", this.onCancel);
        this.element.removeEventListener("click", this.onClick, true);
    }

    // Pour info: tu peux aussi binder data-action="click->slideover#open" sur n'importe quel enfant du dialog
    open(e) {
        if (e) e.preventDefault();
        if (!this.element.open) this.element.showModal();
        // animateIn sera déclenché par l'observer
    }

    async closeAnimated() {
        // Fade-out overlay
        this.overlayTarget.classList.remove("opacity-100");
        this.overlayTarget.classList.add("opacity-0");
        // Slide-out panel
        this.panelTarget.classList.remove("translate-x-0");
        this.panelTarget.classList.add("translate-x-full");

        // Attend la fin de la transition du panel
        await new Promise((resolve) => {
            const onEnd = (ev) => {
                if (ev.target !== this.panelTarget) return;
                this.panelTarget.removeEventListener("transitionend", onEnd);
                resolve();
            };
            this.panelTarget.addEventListener("transitionend", onEnd);
        });

        if (this.element.open) this.element.close();
    }

    animateIn(skip = false) {
        if (!skip) this.panelTarget.offsetHeight; // force reflow
        this.overlayTarget.classList.remove("opacity-0");
        this.overlayTarget.classList.add("opacity-100");
        this.panelTarget.classList.remove("translate-x-full");
        this.panelTarget.classList.add("translate-x-0");
    }
}
