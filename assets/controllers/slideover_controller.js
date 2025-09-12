// assets/controllers/slideover_controller.js
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["panel", "overlay"];
    static values = { dialogId: String, duration: { type: Number, default: 500 } };

    connect() {
        this.dialog = document.getElementById(this.dialogIdValue);
        if (!this.dialog) return;

        // état initial
        this.overlayTarget?.classList.add("opacity-0");
        this.panelTarget?.classList.add("translate-x-full");

        // Observer 'open'
        this.mo = new MutationObserver((muts) => {
            for (const m of muts) {
                if (m.type === "attributes" && m.attributeName === "open") {
                    if (this.dialog.open) this.animateIn();
                }
            }
        });
        this.mo.observe(this.dialog, { attributes: true });

        this.triggerTarget?.setAttribute("aria-expanded", this.dialog.open ? "true" : "false");

        // Esc => anime avant close
        this.onCancel = (e) => { e.preventDefault(); this.closeAnimated(); };
        this.dialog.addEventListener("cancel", this.onCancel);

        // Si déjà ouvert (rare), force visible sans flash
        if (this.dialog.open) this.animateIn(true);
    }

    disconnect() {
        if (!this.dialog) return;
        this.mo && this.mo.disconnect();
        this.dialog.removeEventListener("cancel", this.onCancel);
    }

    // Actions ===================================================================
    open(e) {
        e?.preventDefault();
        if (!this.dialog.open) this.dialog.showModal(); // animateIn via observer
        if (!this.dialog.open) this.dialog.showModal();
        this.triggerTarget?.setAttribute("aria-expanded", "true");
    }

    async close(e) {
        e?.preventDefault();
        await this.closeAnimated();
        if (this.dialog.open) this.dialog.close();
        await this.closeAnimated();
        if (this.dialog.open) this.dialog.close();
        this.triggerTarget?.setAttribute("aria-expanded", "false");
    }

    // Animations ================================================================
    animateIn(skip = false) {
        if (!this.panelTarget || !this.overlayTarget) return;
        if (!skip) this.panelTarget.offsetHeight; // reflow
        this.overlayTarget.classList.remove("opacity-0");
        this.overlayTarget.classList.add("opacity-100");
        this.panelTarget.classList.remove("translate-x-full");
        this.panelTarget.classList.add("translate-x-0");
        this.overlayTarget.classList.remove("pointer-events-none");
    }

    async closeAnimated() {
        if (!this.panelTarget || !this.overlayTarget) return;
        this.overlayTarget.classList.remove("opacity-100");
        this.overlayTarget.classList.add("opacity-0");
        this.overlayTarget.classList.add("pointer-events-none");
        this.panelTarget.classList.remove("translate-x-0");
        this.panelTarget.classList.add("translate-x-full");
        this.triggerTarget?.setAttribute("aria-expanded", "false");

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
