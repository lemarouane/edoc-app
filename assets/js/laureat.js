 (function($) {
    
    $('body').on('click',function (event) 
    {
        if($(event.target).closest('#myModal').length && $(event.target).is('#myModal')) {
            $('#originale').prop('checked',false);
            //$(".modalDialog").hide();
        } 
        if($(event.target).closest('#modalCarteForm').length && $(event.target).is('#modalCarteForm')) {
         $('#originaleC').prop('checked',false);
         //$(".modalDialog").hide();
     }     
    });

    $('#originale').on('change', function(e){
        if(e.target.checked){
          //  $('#myModal').modal('show');
        }
       
     });
     $('#modalClose').on('click', function(e){
        
            $('#myModal').modal('hide');
            $('#originale').prop('checked',false);
       
     });

     $('#originaleC').on('change', function(e){
      if(e.target.checked){
          $('#modalCarteForm').modal('show');
      }
     
   });

   $('#modalCloseCarte').on('click', function(e){
        
      $('#modalCarteForm').modal('hide');
      $('#originaleC').prop('checked',false);
 
   });


   $('#ordre_form').on("submit", function(event){  
      event.preventDefault();  
      jsFormUrl = $('#path-to-upload-image').data("href");
      form = $('#ordre_form');
       var formData = new FormData(this);
      $.ajax({ 
          type: "POST", 
          data: formData,
          url: jsFormUrl,
           contentType: false,
          processData: false, 
          success: function(data){ 
            $('#modalCarteForm').modal('hide');   
            
          },
          error:function(){
              alert('service denied');
          }
      });

   });
   
     $('#laureats_situation').on('change', function(e){
        if($('#laureats_situation').find(":selected").val()=='0'){
            $('.affiche').hide();
            $('.laureats_autreSituation_label').show();
            $('.poste').hide();

            $('#laureats_dateDebut').prop('required',false);
            $('#laureats_dateFin').prop('required',false);
            $('#laureats_paysVille').prop('required',false);
            $('#laureats_organisme').prop('required',false);
            $('#laureats_departement').prop('required',false);
            $('#laureats_poste').prop('required',false);
            $('#laureats_rhContact').prop('required',false);
            $('#laureats_rhPhone').prop('required',false);
            $('#laureats_rhEmail').prop('required',false);

            $('#laureats_autreSituation').prop('required',true);

            $('#laureats_disciplineDoc').prop('required',false);
            $('#laureats_cedDoc').prop('required',false);
            $('#laureats_specialiteDoc').prop('required',false);
            $('#laureats_universiteDoc').prop('required',false);

         }else if($('#laureats_situation').find(":selected").val()=='2'){
            $('.affiche').show();
            $('.laureats_autreSituation_label').hide();
            $('.poste').hide();

            $('#laureats_disciplineDoc').prop('required',true);
            $('#laureats_cedDoc').prop('required',true);
            $('#laureats_specialiteDoc').prop('required',true);
            $('#laureats_universiteDoc').prop('required',true);

            $('#laureats_autreSituation').prop('required',false);

            $('#laureats_dateDebut').prop('required',false);
            $('#laureats_dateFin').prop('required',false);
            $('#laureats_paysVille').prop('required',false);
            $('#laureats_organisme').prop('required',false);
            $('#laureats_departement').prop('required',false);
            $('#laureats_poste').prop('required',false);
            $('#laureats_rhContact').prop('required',false);
            $('#laureats_rhPhone').prop('required',false);
            $('#laureats_rhEmail').prop('required',false);
            
         }
         else if($('#laureats_situation').find(":selected").val()=='3'){
            $('.affiche').hide();
            $('.laureats_autreSituation_label').hide();
            $('.poste').show();

            $('#laureats_dateDebut').prop('required',true);
            $('#laureats_paysVille').prop('required',true);
            $('#laureats_organisme').prop('required',true);
            $('#laureats_departement').prop('required',true);
            $('#laureats_poste').prop('required',true);
            $('#laureats_rhContact').prop('required',true);
            $('#laureats_rhPhone').prop('required',true);
            $('#laureats_rhEmail').prop('required',true);

            $('#laureats_autreSituation').prop('required',false);
            
            $('#laureats_disciplineDoc').prop('required',false);
            $('#laureats_cedDoc').prop('required',false);
            $('#laureats_specialiteDoc').prop('required',false);
            $('#laureats_universiteDoc').prop('required',false);

         }else{
            $('.affiche').hide();
            $('.laureats_autreSituation_label').hide();
            $('.poste').hide();

            $('#laureats_autreSituation').prop('required',false);

            $('#laureats_dateDebut').prop('required',false);
            $('#laureats_dateFin').prop('required',false);
            $('#laureats_paysVille').prop('required',false);
            $('#laureats_organisme').prop('required',false);
            $('#laureats_departement').prop('required',false);
            $('#laureats_poste').prop('required',false);
            $('#laureats_rhContact').prop('required',false);
            $('#laureats_rhPhone').prop('required',false);
            $('#laureats_rhEmail').prop('required',false);

            $('#laureats_disciplineDoc').prop('required',false);
            $('#laureats_cedDoc').prop('required',false);
            $('#laureats_specialiteDoc').prop('required',false);
            $('#laureats_universiteDoc').prop('required',false);
         }
       
     });
        if($('#laureats_situation').find(":selected").val()=='0'){
            $('.affiche').hide();
            $('.laureats_autreSituation_label').show();
            $('.poste').hide();

            $('#laureats_dateDebut').prop('required',false);
            $('#laureats_dateFin').prop('required',false);
            $('#laureats_paysVille').prop('required',false);
            $('#laureats_organisme').prop('required',false);
            $('#laureats_departement').prop('required',false);
            $('#laureats_poste').prop('required',false);
            $('#laureats_rhContact').prop('required',false);
            $('#laureats_rhPhone').prop('required',false);
            $('#laureats_rhEmail').prop('required',false);

            $('#laureats_autreSituation').prop('required',true);

            $('#laureats_disciplineDoc').prop('required',false);
            $('#laureats_cedDoc').prop('required',false);
            $('#laureats_specialiteDoc').prop('required',false);
            $('#laureats_universiteDoc').prop('required',false);

         }else if($('#laureats_situation').find(":selected").val()=='2'){
            $('.affiche').show();
            $('.laureats_autreSituation_label').hide();
            $('.poste').hide();

            $('#laureats_disciplineDoc').prop('required',true);
            $('#laureats_cedDoc').prop('required',true);
            $('#laureats_specialiteDoc').prop('required',true);
            $('#laureats_universiteDoc').prop('required',true);

            $('#laureats_autreSituation').prop('required',false);

            $('#laureats_dateDebut').prop('required',false);
            $('#laureats_dateFin').prop('required',false);
            $('#laureats_paysVille').prop('required',false);
            $('#laureats_organisme').prop('required',false);
            $('#laureats_departement').prop('required',false);
            $('#laureats_poste').prop('required',false);
            $('#laureats_rhContact').prop('required',false);
            $('#laureats_rhPhone').prop('required',false);
            $('#laureats_rhEmail').prop('required',false);
            
         }
         else if($('#laureats_situation').find(":selected").val()=='3'){
            $('.affiche').hide();
            $('.laureats_autreSituation_label').hide();
            $('.poste').show();

            $('#laureats_dateDebut').prop('required',true);
            $('#laureats_dateFin').prop('required',true);
            $('#laureats_paysVille').prop('required',true);
            $('#laureats_organisme').prop('required',true);
            $('#laureats_departement').prop('required',true);
            $('#laureats_poste').prop('required',true);
            $('#laureats_rhContact').prop('required',true);
            $('#laureats_rhPhone').prop('required',true);
            $('#laureats_rhEmail').prop('required',true);

            $('#laureats_autreSituation').prop('required',false);
            
            $('#laureats_disciplineDoc').prop('required',false);
            $('#laureats_cedDoc').prop('required',false);
            $('#laureats_specialiteDoc').prop('required',false);
            $('#laureats_universiteDoc').prop('required',false);

         }else{
            $('.affiche').hide();
            $('.laureats_autreSituation_label').hide();
            $('.poste').hide();

            $('#laureats_autreSituation').prop('required',false);
            
            $('#laureats_dateDebut').prop('required',false);
            $('#laureats_dateFin').prop('required',false);
            $('#laureats_paysVille').prop('required',false);
            $('#laureats_organisme').prop('required',false);
            $('#laureats_departement').prop('required',false);
            $('#laureats_poste').prop('required',false);
            $('#laureats_rhContact').prop('required',false);
            $('#laureats_rhPhone').prop('required',false);
            $('#laureats_rhEmail').prop('required',false);

            $('#laureats_disciplineDoc').prop('required',false);
            $('#laureats_cedDoc').prop('required',false);
            $('#laureats_specialiteDoc').prop('required',false);
            $('#laureats_universiteDoc').prop('required',false);
         }
})(jQuery);
 