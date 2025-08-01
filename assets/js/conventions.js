import $ from 'jquery';
import 'datatables.net';
import 'datatables.net-dt';
import 'datatables.net-responsive';
import 'datatables.net-responsive-dt';
import 'bootstrap';

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

    $('#submissionsTable').DataTable({
        language: { url: langue_file },
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,
        order: [[1, 'desc']],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100]
    });
});

function deleteSubmission(url, token) {
    $.ajax({
        url: url,
        type: 'POST',
        data: { '_token': token },
        success: function() {
            window.location.reload();
        },
        error: function(xhr) {
            let errorMsg = 'Erreur lors de la suppression';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || errorMsg;
            } catch (e) {
                errorMsg = xhr.responseText || errorMsg;
            }
            alert(errorMsg);
            console.error('Delete failed:', xhr.status, xhr.responseText);
        }
    });
}

window.deleteSubmission = deleteSubmission;