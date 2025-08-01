(function($) {
  if($("#stage_entreprise option:selected").text()== 'Autre'){
      $('#divstage_intitule').show();
      $("#stage_intitule").prop('required', true);
  }else{
    $("#stage_intitule").prop('required', false);
    $('#stage_intitule').prop('value',null);
    $('#divstage_intitule').hide();
  }

  $('#stage_entreprise').on('change',function(){
    if($("#stage_entreprise option:selected").text() == 'Autre')
      {
        $('#divstage_intitule').show();
        $("#stage_intitule").prop('required', true);
      }
    else
      {
        $("#stage_intitule").prop('required', false);
        $('#stage_intitule').prop('value',null);
        $('#divstage_intitule').hide();
      }

  });
})(jQuery);