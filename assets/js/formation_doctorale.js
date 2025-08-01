// assets/js/formation_doctorale.js

import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt'; // Includes DataTables default styling
import 'datatables.net-responsive';
import 'datatables.net-responsive-dt'; // Includes DataTables Responsive styling

// Make jQuery available globally (for DataTables)
window.jQuery = window.$ = $;

$(document).ready(function () {
    var langue = 'fr-FR';
    try {
        if ($('#langue').length) {
            langue = $('#langue').val();
            if (langue == 'ar-AR') {
                langue = 'ar';
            }
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
});

function deleteFormation(link) {
    var url = link.getAttribute('href');
    var token = link.getAttribute('data-csrf');

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            '_token': token
        },
        success: function() {
            window.location.reload();
        },
        error: function() {
            alert('Erreur lors de la suppression.');
        }
    });
}

// Expose deleteFormation to the global scope (for onclick handlers)
window.deleteFormation = deleteFormation;