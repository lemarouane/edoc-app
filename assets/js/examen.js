
(function($) {
    
    
        $.ajax({
            url:'examPlanning',
            //dataType: 'json',
            type: "POST",
            //contentType: "image/png",
            success: function(data){
                $(".planning").html(data) ;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) { 
                alert("Status: " + textStatus); alert("Error: " + errorThrown); 
            } 
        });
       
   
     
})(jQuery);
 