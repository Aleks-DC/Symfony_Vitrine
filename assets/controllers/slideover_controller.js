import { Controller } from "@hotwired/stimulus";

/**
 * Contrôleur Slide-over (panneau mobile) :
 * - Ouvre/ferme le <dialog> avec animation (overlay + translation).
 * - Ne gère PAS la navigation : c’est la navbar qui s’en charge.
 * - Met à jour aria-expanded sur le bouton burger (accessibilité).
 * - Rend l’overlay cliquable pour fermer (quand visible).
 */
export default class extends Controller {
    // On déclare aussi "trigger" car on utilise this.triggerTarget (le bouton burger)
    static targets = ["panel", "overlay", "trigger"];
    static values  = { dialogId: String, duration: { type: Number, default: 500 } };

    connect() {
        // Récupère le <dialog> par son ID (fourni sur le <header>)
        this.dialog = document.getElementById(this.dialogIdValue);
        if (!this.dialog) return;

        // État initial : panneau hors écran + overlay transparent/inactif
        this.overlayTarget?.classList.add("opacity-0", "pointer-events-none");
        this.panelTarget?.classList.add("translate-x-full");

        // Observer l’attribut 'open' du <dialog> pour déclencher l’animation d’entrée
        this.mo = new MutationObserver((muts) => {
            for (const m of muts) {
                if (m.type === "attributes" && m.attributeName === "open") {
                    if (this.dialog.open) this.animateIn();
                }
            }
        });
        this.mo.observe(this.dialog, { attributes: true });

        // Init ARIA sur le bouton burger
        this.triggerTarget?.setAttribute("aria-expanded", this.dialog.open ? "true" : "false");

        // ESC (event 'cancel' du <dialog>) : on anime la fermeture
        this.onCancel = (e) => { e.preventDefault(); this.closeAnimated(); };
        this.dialog.addEventListener("cancel", this.onCancel);

        // Si déjà ouvert au chargement, forcer l’état visible sans flash
        if (this.dialog.open) this.animateIn(true);
    }

    disconnect() {
        if (!this.dialog) return;
        this.mo && this.mo.disconnect();
        this.dialog.removeEventListener("cancel", this.onCancel);
    }

    /* ====================== Actions (trigger) ====================== */

    // Ouvre le dialog (animation d’entrée via l’observer)
    open(e) {
        e?.preventDefault();
        if (!this.dialog.open) this.dialog.showModal();
        this.triggerTarget?.setAttribute("aria-expanded", "true");
    }

    // Ferme le dialog avec animation
    async close(e) {
        e?.preventDefault();
        await this.closeAnimated();
        if (this.dialog.open) this.dialog.close();
        this.triggerTarget?.setAttribute("aria-expanded", "false");
    }

    /* ====================== Animations ====================== */

    // Animation d’entrée : fade-in overlay + slide-in panel
    animateIn(skip = false) {
        if (!this.panelTarget || !this.overlayTarget) return;
        if (!skip) this.panelTarget.offsetHeight; // reflow pour bien déclencher la transition

        this.overlayTarget.classList.remove("opacity-0", "pointer-events-none");
        this.overlayTarget.classList.add("opacity-100");

        this.panelTarget.classList.remove("translate-x-full");
        this.panelTarget.classList.add("translate-x-0");
    }

    // Animation de sortie : fade-out overlay + slide-out panel
    async closeAnimated() {
        if (!this.panelTarget || !this.overlayTarget) return;

        this.overlayTarget.classList.remove("opacity-100");
        this.overlayTarget.classList.add("opacity-0", "pointer-events-none");

        this.panelTarget.classList.remove("translate-x-0");
        this.panelTarget.classList.add("translate-x-full");

        this.triggerTarget?.setAttribute("aria-expanded", "false");

        // On attend la fin de la transition du panel pour enchaîner
        await new Promise((resolve) => {
            const onEnd = (ev) => {
                if (ev.target !== this.panelTarget) return;
                this.panelTarget.removeEventListener("transitionend", onEnd);
                resolve();
            };
            this.panelTarget.addEventListener("transitionend", onEnd);
        });
    }
}
