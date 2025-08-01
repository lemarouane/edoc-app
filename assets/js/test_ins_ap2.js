

$.ajax({
    type: "POST",
    dataType: "json",
    url: $('#path-to-test-ap2').data("href"),
    success: function(data){

        if(data=='1'){
            $('#orientation').show();
        }else{
            $('#orientation').hide();
        }
    },
    error:function(){
      //  alert("er");
    }
  });