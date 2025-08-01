import $ from "jquery";
import "datatables.net";
import "datatables.net-dt";
import "datatables.net-responsive";
import "datatables.net-responsive-dt";
import "bootstrap";

// Expose jQuery globally for DataTables and other dependencies
window.jQuery = window.$ = $;

// Form field visibility toggles for checkboxes
function initializeCheckboxToggles() {
    const toggles = [
        {
            checkboxId: "bourse_merite",
            fieldId: "bourse_merite_depuis_field"
        },
        {
            checkboxId: "bourse_troisieme_cycle",
            fieldId: "bourse_troisieme_cycle_depuis_field"
        },
        {
            checkboxId: "bourse_cotutelle",
            fieldId: "bourse_cotutelle_dates_field"
        },
        {
            checkboxId: "cotutelle",
            fieldId: "cotutelle_fields"
        }
    ];

    toggles.forEach(({ checkboxId, fieldId }) => {
        const checkbox = document.getElementById(checkboxId);
        const field = document.getElementById(fieldId);
        if (checkbox && field) {
            checkbox.addEventListener("change", () => {
                field.style.display = checkbox.checked ? "block" : "none";
            });
        } else {
            console.warn(`Checkbox (${checkboxId}) or field (${fieldId}) not found`);
        }
    });
}

// Manage dynamic formation rows
let formationIndex = 1;

function addFormationRow() {
    const formationsContainer = document.getElementById("formations_complementaires");
    if (!formationsContainer) {
        console.error("Formations container not found");
        return;
    }

    const row = document.createElement("div");
    row.className = "formation-row";
    row.innerHTML = `
        <input type="date" name="formations[${formationIndex}][date]" placeholder="Date">
        <input type="text" name="formations[${formationIndex}][duree]" placeholder="Durée en heures">
        <input type="text" name="formations[${formationIndex}][intitule]" placeholder="Intitulé">
        <input type="text" name="formations[${formationIndex}][organisateur]" placeholder="Organisateur">
        <input type="text" name="formations[${formationIndex}][equivalence_heures]" placeholder="Équivalence en heures">
        <button type="button" class="btn-remove" onclick="removeFormationRow(this)">Supprimer</button>
    `;
    formationsContainer.appendChild(row);
    formationIndex++;
}

function removeFormationRow(button) {
    if (button && button.parentElement) {
        button.parentElement.remove();
    } else {
        console.error("Invalid button or parent element for removal");
    }
}

// Handle reinscription deletion
function deleteReinscription(element) {
    const url = element.getAttribute("href");
    const csrfToken = element.getAttribute("data-csrf");

    if (!url || !csrfToken) {
        console.error("Missing URL or CSRF token for deletion");
        alert("Erreur: Données manquantes pour la suppression.");
        return;
    }

    $.ajax({
        url: url,
        type: "POST",
        data: { _token: csrfToken },
        success: () => {
            console.log("Reinscription deleted successfully");
            window.location.reload();
        },
        error: (xhr, status, error) => {
            console.error(`Deletion failed: ${status} - ${error}`);
            alert("Erreur lors de la suppression.");
        }
    });
}

// Initialize DataTable
function initializeDataTable() {
    let language = "fr-FR";
    try {
        const langueElement = $("#langue");
        if (langueElement.length) {
            language = langueElement.val();
            if (language === "ar-AR") {
                language = "ar";
            }
        }
    } catch (e) {
        console.warn("Error determining language, using default: fr-FR", e);
    }

    const languageUrl = `https://cdn.datatables.net/plug-ins/1.13.1/i18n/${language}.json`;

    $("#reinscriptionTable").DataTable({
        language: {
            url: languageUrl
        },
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        order: [[3, "desc"]],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100]
    });
}

// Initialize all functionality on document ready
$(document).ready(() => {
    initializeCheckboxToggles();
    initializeDataTable();
});

// Expose functions globally
window.addFormationRow = addFormationRow;
window.removeFormationRow = removeFormationRow;
window.deleteReinscription = deleteReinscription;