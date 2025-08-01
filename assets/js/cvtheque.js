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
            $('#myModal').modal('show');
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
   
     $('#cvtheque_situation').on('change', function(e){
        if($('#cvtheque_situation').find(":selected").val()=='0'){
            $('.affiche').hide();
            $('.cvtheque_autreSituation_label').show();
            $('.poste').hide();

            $('#cvtheque_dateDebut').prop('required',false);
            $('#cvtheque_dateFin').prop('required',false);
            $('#cvtheque_paysVille').prop('required',false);
            $('#cvtheque_organisme').prop('required',false);
            $('#cvtheque_departement').prop('required',false);
            $('#cvtheque_poste').prop('required',false);
            $('#cvtheque_rhContact').prop('required',false);
            $('#cvtheque_rhPhone').prop('required',false);
            $('#cvtheque_rhEmail').prop('required',false);

            $('#cvtheque_autreSituation').prop('required',true);

            $('#cvtheque_disciplineDoc').prop('required',false);
            $('#cvtheque_cedDoc').prop('required',false);
            $('#cvtheque_specialiteDoc').prop('required',false);
            $('#cvtheque_universiteDoc').prop('required',false);

         }else if($('#cvtheque_situation').find(":selected").val()=='2'){
            $('.affiche').show();
            $('.cvtheque_autreSituation_label').hide();
            $('.poste').hide();

            $('#cvtheque_disciplineDoc').prop('required',true);
            $('#cvtheque_cedDoc').prop('required',true);
            $('#cvtheque_specialiteDoc').prop('required',true);
            $('#cvtheque_universiteDoc').prop('required',true);

            $('#cvtheque_autreSituation').prop('required',false);

            $('#cvtheque_dateDebut').prop('required',false);
            $('#cvtheque_dateFin').prop('required',false);
            $('#cvtheque_paysVille').prop('required',false);
            $('#cvtheque_organisme').prop('required',false);
            $('#cvtheque_departement').prop('required',false);
            $('#cvtheque_poste').prop('required',false);
            $('#cvtheque_rhContact').prop('required',false);
            $('#cvtheque_rhPhone').prop('required',false);
            $('#cvtheque_rhEmail').prop('required',false);
            
         }
         else if($('#cvtheque_situation').find(":selected").val()=='3'){
            $('.affiche').hide();
            $('.cvtheque_autreSituation_label').hide();
            $('.poste').show();

            $('#cvtheque_dateDebut').prop('required',true);
            $('#cvtheque_paysVille').prop('required',true);
            $('#cvtheque_organisme').prop('required',true);
            $('#cvtheque_departement').prop('required',true);
            $('#cvtheque_poste').prop('required',true);
            $('#cvtheque_rhContact').prop('required',true);
            $('#cvtheque_rhPhone').prop('required',true);
            $('#cvtheque_rhEmail').prop('required',true);

            $('#cvtheque_autreSituation').prop('required',false);
            
            $('#cvtheque_disciplineDoc').prop('required',false);
            $('#cvtheque_cedDoc').prop('required',false);
            $('#cvtheque_specialiteDoc').prop('required',false);
            $('#cvtheque_universiteDoc').prop('required',false);

         }else{
            $('.affiche').hide();
            $('.cvtheque_autreSituation_label').hide();
            $('.poste').hide();

            $('#cvtheque_autreSituation').prop('required',false);

            $('#cvtheque_dateDebut').prop('required',false);
            $('#cvtheque_dateFin').prop('required',false);
            $('#cvtheque_paysVille').prop('required',false);
            $('#cvtheque_organisme').prop('required',false);
            $('#cvtheque_departement').prop('required',false);
            $('#cvtheque_poste').prop('required',false);
            $('#cvtheque_rhContact').prop('required',false);
            $('#cvtheque_rhPhone').prop('required',false);
            $('#cvtheque_rhEmail').prop('required',false);

            $('#cvtheque_disciplineDoc').prop('required',false);
            $('#cvtheque_cedDoc').prop('required',false);
            $('#cvtheque_specialiteDoc').prop('required',false);
            $('#cvtheque_universiteDoc').prop('required',false);
         }
       
     });
        if($('#cvtheque_situation').find(":selected").val()=='0'){
            $('.affiche').hide();
            $('.cvtheque_autreSituation_label').show();
            $('.poste').hide();

            $('#cvtheque_dateDebut').prop('required',false);
            $('#cvtheque_dateFin').prop('required',false);
            $('#cvtheque_paysVille').prop('required',false);
            $('#cvtheque_organisme').prop('required',false);
            $('#cvtheque_departement').prop('required',false);
            $('#cvtheque_poste').prop('required',false);
            $('#cvtheque_rhContact').prop('required',false);
            $('#cvtheque_rhPhone').prop('required',false);
            $('#cvtheque_rhEmail').prop('required',false);

            $('#cvtheque_autreSituation').prop('required',true);

            $('#cvtheque_disciplineDoc').prop('required',false);
            $('#cvtheque_cedDoc').prop('required',false);
            $('#cvtheque_specialiteDoc').prop('required',false);
            $('#cvtheque_universiteDoc').prop('required',false);

         }else if($('#cvtheque_situation').find(":selected").val()=='2'){
            $('.affiche').show();
            $('.cvtheque_autreSituation_label').hide();
            $('.poste').hide();

            $('#cvtheque_disciplineDoc').prop('required',true);
            $('#cvtheque_cedDoc').prop('required',true);
            $('#cvtheque_specialiteDoc').prop('required',true);
            $('#cvtheque_universiteDoc').prop('required',true);

            $('#cvtheque_autreSituation').prop('required',false);

            $('#cvtheque_dateDebut').prop('required',false);
            $('#cvtheque_dateFin').prop('required',false);
            $('#cvtheque_paysVille').prop('required',false);
            $('#cvtheque_organisme').prop('required',false);
            $('#cvtheque_departement').prop('required',false);
            $('#cvtheque_poste').prop('required',false);
            $('#cvtheque_rhContact').prop('required',false);
            $('#cvtheque_rhPhone').prop('required',false);
            $('#cvtheque_rhEmail').prop('required',false);
            
         }
         else if($('#cvtheque_situation').find(":selected").val()=='3'){
            $('.affiche').hide();
            $('.cvtheque_autreSituation_label').hide();
            $('.poste').show();

            $('#cvtheque_dateDebut').prop('required',true);
            $('#cvtheque_dateFin').prop('required',true);
            $('#cvtheque_paysVille').prop('required',true);
            $('#cvtheque_organisme').prop('required',true);
            $('#cvtheque_departement').prop('required',true);
            $('#cvtheque_poste').prop('required',true);
            $('#cvtheque_rhContact').prop('required',true);
            $('#cvtheque_rhPhone').prop('required',true);
            $('#cvtheque_rhEmail').prop('required',true);

            $('#cvtheque_autreSituation').prop('required',false);
            
            $('#cvtheque_disciplineDoc').prop('required',false);
            $('#cvtheque_cedDoc').prop('required',false);
            $('#cvtheque_specialiteDoc').prop('required',false);
            $('#cvtheque_universiteDoc').prop('required',false);

         }else{
            $('.affiche').hide();
            $('.cvtheque_autreSituation_label').hide();
            $('.poste').hide();

            $('#cvtheque_autreSituation').prop('required',false);
            
            $('#cvtheque_dateDebut').prop('required',false);
            $('#cvtheque_dateFin').prop('required',false);
            $('#cvtheque_paysVille').prop('required',false);
            $('#cvtheque_organisme').prop('required',false);
            $('#cvtheque_departement').prop('required',false);
            $('#cvtheque_poste').prop('required',false);
            $('#cvtheque_rhContact').prop('required',false);
            $('#cvtheque_rhPhone').prop('required',false);
            $('#cvtheque_rhEmail').prop('required',false);

            $('#cvtheque_disciplineDoc').prop('required',false);
            $('#cvtheque_cedDoc').prop('required',false);
            $('#cvtheque_specialiteDoc').prop('required',false);
            $('#cvtheque_universiteDoc').prop('required',false);
         }
})(jQuery);
 