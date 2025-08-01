var $collectionHolder;

var $addNewItem= $('<a href="#" class="btn btn-info bi bi-plus d-flex align-items-center" style="float: center ;width:40px;margin-left:45%"></a>');


$(document).ready(function(){

	$collectionHolder = $('#clubs_list');
	// append the add new item to the collectionHolder
	$collectionHolder.append($addNewItem);

	//add remove button to existing items
	$collectionHolder.find('.panel').each(function(item){

	 addRemoveButton($(this));	
	});

	//
	$addNewItem.click(function(e){
		e.preventDefault();
		$collectionHolder.data('index',$collectionHolder.find('.panel').length);
		addNewForm();
		


	});


	
});
//add new items (engagement forms)
function addNewForm(){

	//create the form
	var prototype= $collectionHolder.data('prototype');

	var index = $collectionHolder.data('index');
    var i =index;
	 //create form
	 var newForm = prototype;

	 newForm = newForm.replace(/__name__/g, index);

	 $collectionHolder.data('index', index++);

	 //create panel

	 var $panel= $('<div class="panel form-group "></div>');
	 //creat the panel body

	 var $label = $('<div class="row panalClubs"></div>').append(newForm);

	 $panel.append($label);

	 addRemoveButton($panel);

	 $addNewItem.before($panel);
     $('#laureats_clubs_'+i).addClass('row g-3');
}
//remove them
function addRemoveButton($panel){

	//create remove button
	var $removeButton=$('<a href="#" class="btn btn-danger bi bi-dash d-flex align-items-center" style="float:right"></a><hr>');

	var $panelFooter= $('<div style="width:100%; height:30px ; margin-top:10px;"></div>').append($removeButton);

	$removeButton.click(function(e){
		e.preventDefault();
		$(e.target).parents('.panel').slideUp(1000,function(){
			$(this).remove();
		});
	});

	$panel.append($panelFooter);
	//
}


