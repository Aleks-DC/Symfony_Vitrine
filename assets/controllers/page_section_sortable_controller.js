// assets/controllers/page_section_sortable_controller.js
import { Controller } from "@hotwired/stimulus";
import Sortable from "sortablejs";

export default class extends Controller {
    static values = {
        url: String,
    };

    connect() {
        // this.element = <tbody ...>
        this.sortable = Sortable.create(this.element, {
            handle: "[data-drag-handle]",   // clique sur l’icône ☰
            draggable: "tr[data-id]",       // chaque ligne est draggable
            animation: 150,
            onEnd: this.reorder.bind(this),
        });
    }

    reorder() {
        const rows = Array.from(this.element.querySelectorAll("tr[data-id]"));
        const ids  = rows.map(row => row.dataset.id);

        fetch(this.urlValue, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({ ids }),
        })
            .then(response => {
                if (!response.ok) {
                    console.error("Erreur lors de la sauvegarde de l'ordre des sections");
                }
            })
            .catch(error => {
                console.error("Erreur réseau :", error);
            });
    }

    disconnect() {
        if (this.sortable) {
            this.sortable.destroy();
            this.sortable = null;
        }
    }
}
