langue = $('#langue').val() ;
if(langue=='ar-AR'){langue='ar'}
langue_file = "https://cdn.datatables.net/plug-ins/1.13.1/i18n/"+langue+".json" ;
//langue_file = "https://localhost:8000/build/"+langue+".json" ;
//$.fn.dataTable.ext.errMode = 'none';

$('#example').DataTable( {
    language: {
        url: langue_file,
    }
} );

$('#example3').DataTable( {
    language: {
        url: langue_file,
    },
        'paging'      : true,
        'lengthChange': true,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false
    
} );

$('#example4').DataTable( {
    language: {
        url: langue_file,
    },
        'paging'      : true,
        'lengthChange': true,
        'searching'   : true,
        'ordering'    : true,
        'info'        : true,
        'autoWidth'   : false
 
} );

