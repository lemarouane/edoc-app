// assets/js/stages.js

import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt';
import 'datatables.net-responsive';
import 'datatables.net-responsive-dt';
import 'bootstrap'; // Bootstrap JS (includes Popper.js)

// Make jQuery available globally
window.jQuery = window.$ = $;

$(document).ready(function () {
    var langue = 'fr-FR';
    try {
        if ($('#langue').length) {
            langue = $('#langue').val();
            if (langue == 'ar-AR') { langue = 'ar'; }
        }
    } catch (e) {
        console.log('Using default language');
    }

    var langue_file = "https://cdn.datatables.net/plug-ins/1.13.1/i18n/" + langue + ".json";

    $('#formationTable').DataTable({
        language: { url: langue_file },
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        order: [[4, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100]
    });

    $('#form_moduleId').on('change', function() {
        $('#form_cadreStage').val($(this).find('option:selected').text());
    });
});

function deleteStage(url, token) {
    $.ajax({
        url: url,
        type: 'POST',
        data: { '_token': token },
        success: function() { window.location.reload(); },
        error: function(xhr) { alert('Erreur lors de la suppression: ' + xhr.responseText); }
    });
}

// Expose deleteStage to the global scope
window.deleteStage = deleteStage;