// assets/controllers/navbar_controller.js
import { Controller } from "@hotwired/stimulus";

/**
 * Navbar sticky :
 * - auto-hide à la descente (avec hystérésis), réaffiche à la montée
 * - "lock visible" sur desktop pendant le smooth scroll vers une section (clic menu)
 * - ferme le menu mobile puis scrolle
 * - met à jour --header-h (pour scroll-margin-top et overlay du Hero)
 * - ScrollSpy déterministe (active le lien de la dernière section passée sous la navbar)
 */
export default class extends Controller {
    static values = {
        // Options
        dialogId: String,                                // id du <dialog> mobile
        lgBreakpoint: { type: Number, default: 1024 },   // Tailwind lg
        hideAfter:   { type: Number, default: 64 },      // px cumulés vers le bas avant de cacher
        revealAfter: { type: Number, default: 48 },      // px cumulés vers le haut avant d’afficher
        minDelta:    { type: Number, default: 2 },       // ignore micro-variations
        blur:        { type: Boolean, default: true },   // blur léger hors top
        // ScrollSpy
        activeClasses:   { type: String, default: "border-indigo-600 dark:border-indigo-500 text-gray-900 dark:text-white" },
        inactiveClasses: { type: String, default: "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-300 dark:hover:border-white/20 dark:hover:text-white" },
        homeHash:        { type: String, default: "" },  // ex: "#accueil" si tu ajoutes un lien Accueil
    };

    /* ====================== lifecycle ====================== */
    connect() {
        this.navEl     = this.element.querySelector('nav[aria-label="Global"]') || this.element.querySelector('nav') || this.element;
        this.dialog    = this.hasDialogIdValue ? document.getElementById(this.dialogIdValue) : null;
        this.mqDesktop = window.matchMedia(`(min-width: ${this.lgBreakpointValue}px)`);

        // états auto-hide
        this.lastY = window.scrollY;
        this.downTravel = 0;
        this.hiddenAtY  = null;
        this.ticking = false;

        // lock visible (desktop) jusqu’à arrivée sur la cible
        this.lockVisible   = false;
        this.lockTarget    = null;
        this.lockTargetSMT = 0;     // scroll-margin-top lu sur la cible
        this.nearPx        = 2;     // tolérance d’alignement (px)

        // bindings
        this.onScroll       = this.onScroll.bind(this);
        this.onResizeOrLoad = this.onResizeOrLoad.bind(this);
        this.onDocClick     = this.onDocClick.bind(this);
        this.onDialogClick  = this.onDialogClick.bind(this);

        // listeners
        addEventListener("scroll", this.onScroll, { passive: true });
        addEventListener("resize", this.onResizeOrLoad, { passive: true });
        addEventListener("load",  this.onResizeOrLoad, { passive: true });
        document.addEventListener("click", this.onDocClick, { passive: true });
        if (this.dialog) this.dialog.addEventListener("click", this.onDialogClick, { passive: false });

        // init
        this.updateHeaderVar();
        if (window.scrollY < 8) this.show();
        this.setRaised();

        // ScrollSpy : indexe liens/sections + tops absolus
        this.indexScrollSpy();
        this.updateActiveLink(); // état initial
    }

    disconnect() {
        removeEventListener("scroll", this.onScroll);
        removeEventListener("resize", this.onResizeOrLoad);
        removeEventListener("load",  this.onResizeOrLoad);
        document.removeEventListener("click", this.onDocClick);
        if (this.dialog) this.dialog.removeEventListener("click", this.onDialogClick);
    }

    /* ====================== utils ====================== */
    updateHeaderVar() {
        const h = this.navEl ? Math.round(this.navEl.getBoundingClientRect().height) : 0;
        document.documentElement.style.setProperty("--header-h", `${h}px`);
    }
    onResizeOrLoad() {
        this.updateHeaderVar();
        this.indexScrollSpy();   // re-calcule les positions si layout change
        this.updateActiveLink();
    }

    show() { this.element.classList.remove("-translate-y-full"); this.element.classList.add("translate-y-0"); }
    hide() { this.element.classList.remove("translate-y-0");     this.element.classList.add("-translate-y-full"); }

    setRaised() {
        const s = window.scrollY > 4; // pas d’effet tout en haut
        if (this.blurValue) {
            this.element.classList.toggle("supports-[backdrop-filter]:backdrop-blur-[2px]", s);
            this.element.classList.toggle("supports-[backdrop-filter]:bg-gray-900/25", s);
            this.element.classList.toggle("bg-gray-900/30", s); // fallback si pas de backdrop-filter
        }
        this.element.classList.toggle("border-b", s);
        this.element.classList.toggle("border-white/10", s);
        this.element.classList.toggle("shadow-sm", s);

        if (!s) {
            this.element.classList.remove(
                "backdrop-blur",
                "supports-[backdrop-filter]:backdrop-blur-[2px]",
                "supports-[backdrop-filter]:bg-gray-900/25",
                "bg-gray-900/30",
                "border-b",
                "border-white/10",
                "shadow-sm"
            );
        }
    }

    getHeaderHeight() {
        const v = getComputedStyle(document.documentElement).getPropertyValue('--header-h').trim();
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : (this.navEl?.getBoundingClientRect().height || 0);
    }

    /* ====================== ScrollSpy ====================== */
    indexScrollSpy() {
        // 1) tous les liens # du header (desktop + mobile)
        this.links = Array.from(new Set([
            ...this.element.querySelectorAll('a[href^="#"]'),
            ...document.querySelectorAll('#site-header a[href^="#"]'),
        ]));

        // 2) map hash -> liens
        this.linksByHash = new Map();
        this.links.forEach(a => {
            const hash = a.getAttribute('href');
            if (!hash || hash === '#') return;
            if (!this.linksByHash.has(hash)) this.linksByHash.set(hash, []);
            this.linksByHash.get(hash).push(a);
        });

        // 3) sections présentes
        this.sections = Array.from(this.linksByHash.keys())
            .map(hash => document.querySelector(hash))
            .filter(Boolean);

        // 4) ordre + tops absolus (pageY)
        this.sectionOrder = this.sections.slice().sort((a, b) => this.pageTopOf(a) - this.pageTopOf(b));
        this.sectionTops  = this.sectionOrder.map(el => this.pageTopOf(el));
    }
    pageTopOf(el) { return Math.round(el.getBoundingClientRect().top + window.scrollY); }

    clearActive() {
        const inactive = this.inactiveClassesValue.split(/\s+/).filter(Boolean);
        const active   = this.activeClassesValue.split(/\s+/).filter(Boolean);
        this.links.forEach(a => {
            a.removeAttribute('aria-current');
            inactive.forEach(c => a.classList.add(c));
            active.forEach(c => a.classList.remove(c));
        });
    }
    applyActiveForHash(hash) {
        const inactive = this.inactiveClassesValue.split(/\s+/).filter(Boolean);
        const active   = this.activeClassesValue.split(/\s+/).filter(Boolean);
        // tout inactif
        this.links.forEach(a => {
            a.removeAttribute('aria-current');
            inactive.forEach(c => a.classList.add(c));
            active.forEach(c => a.classList.remove(c));
        });
        // activer
        (this.linksByHash.get(hash) || []).forEach(a => {
            a.setAttribute('aria-current','page');
            inactive.forEach(c => a.classList.remove(c));
            active.forEach(c => a.classList.add(c));
        });
    }

    updateActiveLink() {
        if (!this.sectionOrder?.length) return;

        const yLine = window.scrollY + this.getHeaderHeight() + 1;  // ligne sous la navbar
        const firstTop = this.sectionTops[0];

        // Avant la 1re section : rien d’actif (ou homeHash si fourni)
        if (yLine < firstTop) {
            if (this.homeHashValue && this.linksByHash.has(this.homeHashValue)) {
                if (this.currentHash !== this.homeHashValue) {
                    this.currentHash = this.homeHashValue;
                    this.applyActiveForHash(this.homeHashValue);
                }
            } else {
                if (this.currentHash !== null) {
                    this.currentHash = null;
                    this.clearActive();
                }
            }
            return;
        }

        // Sinon : dernière section dont le top est passé sous la ligne
        let idx = 0;
        while (idx < this.sectionTops.length && this.sectionTops[idx] <= yLine) idx++;
        const activeEl = this.sectionOrder[Math.max(0, idx - 1)];
        const hash = activeEl ? `#${activeEl.id}` : null;

        if (hash !== this.currentHash) {
            this.currentHash = hash;
            if (hash) this.applyActiveForHash(hash);
            else this.clearActive();
        }
    }

    /* ====================== scroll logic ====================== */
    onScroll() {
        if (this.ticking) return;
        this.ticking = true;

        requestAnimationFrame(() => {
            const y  = window.scrollY;
            const dy = y - this.lastY;
            this.lastY = y;

            this.setRaised();

            // Pendant un clic desktop (lock) : garder visible jusqu’à la cible
            if (this.lockVisible) {
                this.show();
                if (this.lockTarget) {
                    const top = this.lockTarget.getBoundingClientRect().top;
                    if (Math.abs(top - this.lockTargetSMT) <= this.nearPx) {
                        this.lockVisible = false;
                        this.lockTarget  = null;
                        this.hiddenAtY   = null;
                        this.downTravel  = 0;
                    }
                }
                this.updateActiveLink(); // maj visuelle pendant le lock
                this.ticking = false;
                return;
            }

            // ScrollSpy en temps réel (hors lock)
            this.updateActiveLink();

            if (Math.abs(dy) < this.minDeltaValue) { this.ticking = false; return; }

            // Auto-hide
            if (dy > 0) {
                this.downTravel += dy; // descente
                if (this.downTravel > this.hideAfterValue && y > 64) {
                    this.hide();
                    this.hiddenAtY = y;
                    this.downTravel = 0;
                }
            } else {
                // montée
                if (this.hiddenAtY !== null && (this.hiddenAtY - y) > this.revealAfterValue) {
                    this.show();
                    this.hiddenAtY = null;
                }
                if (Math.abs(dy) > this.minDeltaValue) this.downTravel = 0;
            }

            this.ticking = false;
        });
    }

    /* ====================== click handlers ====================== */
    // Desktop : clic sur lien -> lock visible + activer tout de suite
    onDocClick(e) {
        const a = e.target.closest('a[href^="#"]');
        if (!a || !this.mqDesktop.matches) return;

        const hash = a.getAttribute("href");
        const target = document.querySelector(hash);
        if (!target) return;

        this.lockVisible   = true;
        this.lockTarget    = target;
        this.lockTargetSMT = parseFloat(getComputedStyle(target).scrollMarginTop) || 0;

        this.show();
        this.applyActiveForHash(hash); // feedback instantané
        // (on laisse l’ancre native gérer le smooth via CSS)
    }

    // Mobile : fermer le dialog PUIS scroller (sans lock)
    onDialogClick(e) {
        const a = e.target.closest('a');
        if (!a) return;

        // Respecter middle-click / Cmd/Ctrl/Shift/Alt / target=_blank
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button === 1) return;
        if (a.getAttribute('target') === '_blank') return;

        const href = a.getAttribute('href') || '';
        if (!href) return;

        // Empêche la nav immédiate, on orchestre: lock + close + nav/scroll
        e.preventDefault();

        // 1) Lock visible immédiatement (garde la barre logo+burger affichée)
        this.lockVisible   = true;
        this.lockTarget    = null;
        this.lockTargetSMT = 0;
        this.show();

        const go = () => {
            // Cas 1: ancre => scroll (instantané ici)
            if (href.startsWith('#')) {
                const target = document.querySelector(href);
                if (history.pushState) {
                    history.pushState(null, "", href);
                    target?.scrollIntoView({ behavior: "auto", block: "start" });
                    this.applyActiveForHash(href);
                } else {
                    location.hash = href;
                }
                return;
            }

            // Cas 2: URL/chemin
            try {
                const url  = new URL(href, window.location.href);
                const here = new URL(window.location.href);
                const sameOrigin = url.origin === here.origin;
                const samePath   = url.pathname === here.pathname;

                // / (racine) sur la même page => juste remonter en haut
                if (sameOrigin && samePath && !url.hash) {
                    window.scrollTo({ top: 0, behavior: "auto" });
                    // pas de change d'URL (ou fais location.assign('/') si tu veux reload)
                    return;
                }

                // Sinon: vraie navigation
                window.location.assign(url.href);
            } catch {
                // href relatif simple
                if (href === "/") {
                    window.scrollTo({ top: 0, behavior: "auto" });
                    return;
                }
                window.location.assign(href);
            }
        };

        // 2) Fermer le dialog (anim) puis exécuter go()
        if (typeof this.dialog?.close === "function") {
            this.dialog.addEventListener("close", go, { once: true });
            try { this.dialog.close(); } catch { go(); }
        } else {
            this.dialog?.removeAttribute("open");
            requestAnimationFrame(go);
        }
    }
}
