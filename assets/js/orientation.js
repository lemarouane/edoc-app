(function($) {
    var form = $('#jsForm');
        var order_string = "";
        if( $("#ouvert").val() == 'false' ){
            $("#sortable li").hover(function() {$(this).css("cursor","not-allowed");});
            $("#sortable").sortable("option", "disabled");
            $("button.activechoix").hover(function() {$(this).css("cursor","not-allowed");});
            $(".activechoix").prop('disabled', true);
        }else{

        $("button.activechoix").hover(function() {$(this).css("cursor","pointer");});
        $(".activechoix").prop('disabled', false);
        $("#sortable").sortable({

            update: function(e, ui) { 
                  var itemOrder = $('#sortable').sortable("toArray");
                    for (var i = 0; i < itemOrder.length; i++) {
                        document.getElementById(itemOrder[i]).innerHTML =(i+1)+"- "+itemOrder[i];
                    }
                }
        });

        $('#js-envoyer').button().click(function() {	
            var itemOrder = $('#sortable').sortable("toArray");
            var order_string = 'order='+itemOrder;
            var url='orderChoix_1111';
            jsFormUrl = url.replace("1111", itemOrder);
            $.ajax({ 
                  type: "POST", 
                  data: form.serialize(),
                  url: jsFormUrl, 
                  success: function(data){    
                     alert(data);
                  },
                  error:function(){
                      alert('service denied');
                }
            });
            return false;
        });    
        $("#sortable li").on("click",function() {
            var omyFrame = document.getElementById("myFrame");
            omyFrame.style.display="block";
            omyFrame.src = "uploads/pdf/"+this.id+".pdf";
            
          });
      }

})(jQuery);