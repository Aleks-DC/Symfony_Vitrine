import { Controller } from "@hotwired/stimulus";

/**
 * Contrôleur de la barre de navigation sticky.
 *
 * Rôles :
 * - Auto-hide / auto-reveal selon le sens du scroll (avec petites zones tampons).
 * - "Lock visible" pendant un scroll piloté par un clic (desktop et mobile) :
 *   -> empêche la barre de se cacher le temps d’atteindre la cible.
 * - Met à jour la variable CSS --header-h (utile pour scroll-margin-top / hero overlay).
 * - ScrollSpy : active le(s) lien(s) du header correspondant à la section courante.
 * - Sur mobile, ferme le <dialog> (géré par slideover) PUIS scrolle/navigue.
 */
export default class extends Controller {
    static values = {
        // --- Options d’affichage / ergonomie
        dialogId: String,                             // id du <dialog> mobile
        lgBreakpoint: { type: Number, default: 1024 },// Tailwind 'lg'
        hideAfter:   { type: Number, default: 64 },   // px à la descente avant de cacher
        revealAfter: { type: Number, default: 48 },   // px à la montée avant de réafficher
        minDelta:    { type: Number, default: 2 },    // ignore micro-variations de scroll
        blur:        { type: Boolean, default: true },// petit blur hors tout en haut

        // --- ScrollSpy (classes actives/inactives)
        activeClasses:   { type: String, default: "border-indigo-600 dark:border-indigo-500 text-gray-900 dark:text-white" },
        inactiveClasses: { type: String, default: "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-300 dark:hover:border-white/20 dark:hover:text-white" },

        // Optionnel: hash à activer quand on est au-dessus de la 1re section
        homeHash: { type: String, default: "" },
    };

    /* ====================== lifecycle ====================== */
    connect() {
        // Élément <nav> réel (sert à mesurer la hauteur)
        this.navEl  = this.element.querySelector('nav[aria-label="Global"]')
            || this.element.querySelector('nav')
            || this.element;

        // <dialog> mobile (fourni par data-navbar-dialog-id-value)
        this.dialog = this.hasDialogIdValue ? document.getElementById(this.dialogIdValue) : null;

        // Media query desktop vs mobile
        this.mqDesktop = window.matchMedia(`(min-width: ${this.lgBreakpointValue}px)`);

        // États pour la logique d’auto-hide
        this.lastY     = window.scrollY;
        this.downTravel = 0;
        this.hiddenAtY  = null;
        this.ticking    = false;

        // États de "lock visible" (garder la barre affichée pendant un scroll piloté)
        this.lockVisible   = false; // true = force show() dans onScroll
        this.lockTarget    = null;  // élément cible (section) quand on scrolle vers un hash
        this.lockTargetSMT = 0;     // scroll-margin-top de la cible (pour l’alignement précis)
        this.nearPx        = 2;     // tolérance d’alignement en px

        // Bindings
        this.onScroll       = this.onScroll.bind(this);
        this.onResizeOrLoad = this.onResizeOrLoad.bind(this);
        this.onDocClick     = this.onDocClick.bind(this);
        this.onDialogClick  = this.onDialogClick.bind(this);

        // Écouteurs globaux
        addEventListener("scroll",  this.onScroll,       { passive: true });
        addEventListener("resize",  this.onResizeOrLoad, { passive: true });
        addEventListener("load",    this.onResizeOrLoad, { passive: true });
        document.addEventListener("click", this.onDocClick, { passive: true });

        // Écoute les clics DANS le dialog (pour fermer + scroller en mobile)
        if (this.dialog) this.dialog.addEventListener("click", this.onDialogClick, { passive: false });

        // Init affichage
        this.updateHeaderVar();           // fixe --header-h
        if (window.scrollY < 8) this.show();
        this.setRaised();

        // Init ScrollSpy
        this.indexScrollSpy();
        this.updateActiveLink();
    }

    disconnect() {
        removeEventListener("scroll", this.onScroll);
        removeEventListener("resize", this.onResizeOrLoad);
        removeEventListener("load",  this.onResizeOrLoad);
        document.removeEventListener("click", this.onDocClick);
        if (this.dialog) this.dialog.removeEventListener("click", this.onDialogClick);
    }

    /* ====================== utils ====================== */

    // Mesure la hauteur du header et la pousse en CSS var (--header-h)
    updateHeaderVar() {
        const h = this.navEl ? Math.round(this.navEl.getBoundingClientRect().height) : 0;
        document.documentElement.style.setProperty("--header-h", `${h}px`);
    }

    // Sur resize/load: on recalcule tailles + réactualise ScrollSpy
    onResizeOrLoad() {
        this.updateHeaderVar();
        this.indexScrollSpy();
        this.updateActiveLink();
    }

    // Affiche/Cache la barre (en jouant sur translateY)
    show() { this.element.classList.remove("-translate-y-full"); this.element.classList.add("translate-y-0"); }
    hide() { this.element.classList.remove("translate-y-0");     this.element.classList.add("-translate-y-full"); }

    // Ajoute un léger blur / bordure / ombre quand on scrolle
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

    // Hauteur du header (depuis la CSS var si dispo)
    getHeaderHeight() {
        const v = getComputedStyle(document.documentElement).getPropertyValue('--header-h').trim();
        const n = parseFloat(v);
        return Number.isFinite(n) ? n : (this.navEl?.getBoundingClientRect().height || 0);
    }

    /* ====================== ScrollSpy ====================== */

    // Prépare les liens # et les sections correspondantes
    indexScrollSpy() {
        // 1) Tous les liens ancres du header (desktop + mobile)
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

        // 3) sections effectivement présentes dans la page
        this.sections = Array.from(this.linksByHash.keys())
            .map(hash => document.querySelector(hash))
            .filter(Boolean);

        // 4) ordre vertical + tops absolus (coordonnées page)
        this.sectionOrder = this.sections.slice().sort((a, b) => this.pageTopOf(a) - this.pageTopOf(b));
        this.sectionTops  = this.sectionOrder.map(el => this.pageTopOf(el));
    }

    pageTopOf(el) { return Math.round(el.getBoundingClientRect().top + window.scrollY); }

    // Réinitialise l’état "actif" sur tous les liens
    clearActive() {
        const inactive = this.inactiveClassesValue.split(/\s+/).filter(Boolean);
        const active   = this.activeClassesValue.split(/\s+/).filter(Boolean);
        this.links.forEach(a => {
            a.removeAttribute('aria-current');
            inactive.forEach(c => a.classList.add(c));
            active.forEach(c => a.classList.remove(c));
        });
    }

    // Applique l’état "actif" aux liens qui pointent vers `hash`
    applyActiveForHash(hash) {
        const inactive = this.inactiveClassesValue.split(/\s+/).filter(Boolean);
        const active   = this.activeClassesValue.split(/\s+/).filter(Boolean);

        // Tout inactif d’abord
        this.links.forEach(a => {
            a.removeAttribute('aria-current');
            inactive.forEach(c => a.classList.add(c));
            active.forEach(c => a.classList.remove(c));
        });

        // Puis activation ciblée
        (this.linksByHash.get(hash) || []).forEach(a => {
            a.setAttribute('aria-current','page');
            inactive.forEach(c => a.classList.remove(c));
            active.forEach(c => a.classList.add(c));
        });
    }

    // Détermine quel lien doit être actif en fonction de la position de scroll
    updateActiveLink() {
        if (!this.sectionOrder?.length) return;

        // Ligne de référence juste sous la navbar
        const yLine    = window.scrollY + this.getHeaderHeight() + 1;
        const firstTop = this.sectionTops[0];

        // Avant la 1re section
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

        // Dernière section dont le top est passé sous la ligne
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

    // Gestion du scroll (auto-hide + lock visible + ScrollSpy live)
    onScroll() {
        if (this.ticking) return;
        this.ticking = true;

        requestAnimationFrame(() => {
            const y  = window.scrollY;
            const dy = y - this.lastY;
            this.lastY = y;

            this.setRaised();

            // Pendant un lock visible : on force l’affichage
            if (this.lockVisible) {
                this.show();

                // Si on a une cible (ancre), on libère le lock quand on est arrivé
                if (this.lockTarget) {
                    const top = this.lockTarget.getBoundingClientRect().top;
                    if (Math.abs(top - this.lockTargetSMT) <= this.nearPx) {
                        this.lockVisible = false;
                        this.lockTarget  = null;
                        this.hiddenAtY   = null;
                        this.downTravel  = 0;
                    }
                }
                // Maj visuelle durant le lock
                this.updateActiveLink();
                this.ticking = false;
                return;
            }

            // ScrollSpy temps réel hors lock
            this.updateActiveLink();

            if (Math.abs(dy) < this.minDeltaValue) { this.ticking = false; return; }

            // Auto-hide vers le bas
            if (dy > 0) {
                this.downTravel += dy;
                if (this.downTravel > this.hideAfterValue && y > 64) {
                    this.hide();
                    this.hiddenAtY = y;
                    this.downTravel = 0;
                }
            } else {
                // Révélation vers le haut
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

    // Desktop : clic sur lien # -> lock visible + feedback visuel immédiat
    onDocClick(e) {
        const a = e.target.closest('a[href^="#"]');
        if (!a || !this.mqDesktop.matches) return;

        const hash = a.getAttribute("href");
        const target = document.querySelector(hash);
        if (!target) return;

        this.lockVisible   = true; // garde la barre visible pendant le scroll
        this.lockTarget    = target;
        this.lockTargetSMT = parseFloat(getComputedStyle(target).scrollMarginTop) || 0;

        this.show();
        this.applyActiveForHash(hash); // activer tout de suite le lien
        // Le scroll smooth est laissé au navigateur/CSS (pas géré ici)
    }

    // Mobile : clic sur n’importe quel <a> dans le dialog
    // -> lock visible immédiatement, on ferme le dialog (slideover), puis:
    //    - hash: scroll immédiat
    //    - même page sans hash: remonter en haut + libérer le lock
    //    - autre page: navigation
    onDialogClick(e) {
        const a = e.target.closest('a');
        if (!a) return;

        // Respecter middle-click / Cmd/Ctrl/Shift/Alt / target=_blank
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button === 1) return;
        if (a.getAttribute('target') === '_blank') return;

        const href = a.getAttribute('href') || '';
        if (!href) return;

        e.preventDefault(); // on orchestre la suite

        // 1) Lock visible immédiat (affiche la barre logo+burger)
        this.lockVisible   = true;
        this.lockTarget    = null;
        this.lockTargetSMT = 0;
        this.show();

        const go = () => {
            // Cas 1: ancre -> scroll (instantané, tu peux passer "smooth" si tu préfères)
            if (href.startsWith('#')) {
                const target = document.querySelector(href);
                if (history.pushState) {
                    history.pushState(null, "", href);
                    target?.scrollIntoView({ behavior: "auto", block: "start" });
                    this.applyActiveForHash(href);
                } else {
                    location.hash = href;
                }
                // lockVisible sera libéré naturellement au prochain scroll (si nécessaire)
                return;
            }

            // Cas 2: URL/chemin
            try {
                const url  = new URL(href, window.location.href);
                const here = new URL(window.location.href);
                const sameOrigin = url.origin === here.origin;
                const samePath   = url.pathname === here.pathname;

                // Même page + pas de hash -> remonter en haut et LIBÉRER le lock
                if (sameOrigin && samePath && !url.hash) {
                    window.scrollTo({ top: 0, behavior: "smooth" });
                    // >>> IMPORTANT : relâcher le lock ici, sinon il reste actif
                    this.lockVisible = false;
                    this.lockTarget  = null;
                    this.hiddenAtY   = null;
                    this.downTravel  = 0;
                    this.updateActiveLink();
                    return;
                }

                // Autre page -> navigation
                window.location.assign(url.href);
            } catch {
                // href relatif simple
                if (href === "/") {
                    window.scrollTo({ top: 0, behavior: "auto" });
                    // >>> relâcher le lock également dans ce fallback
                    this.lockVisible = false;
                    this.lockTarget  = null;
                    this.hiddenAtY   = null;
                    this.downTravel  = 0;
                    this.updateActiveLink();
                    return;
                }
                window.location.assign(href);
            }
        };

        // 2) Fermer le <dialog> (slideover animera), puis exécuter go()
        if (typeof this.dialog?.close === "function") {
            this.dialog.addEventListener("close", go, { once: true });
            try { this.dialog.close(); } catch { go(); }
        } else {
            this.dialog?.removeAttribute("open");
            requestAnimationFrame(go);
        }
    }
}
