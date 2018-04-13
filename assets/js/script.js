var $ = jQuery.noConflict();
$(document).on("click", "#signin", function () {
    
    var activeCollabTokenId = $('#activeCollabTokenId').val();
    var activeCollabCompanyName = $('#activeCollabCompanyName').val();
    var userName = $('#userName').val();
    var userPassword = $('#userPassword').val();
	    if(activeCollabTokenId != '' && activeCollabCompanyName != '' && userName != '' && userPassword != ''){
	    	$('#signin').attr('disabled', 'disabled');	
	    	$.ajax({
	        method:'POST',
	        url:ajax_object.ajax_url,
	        data:{
	            'action' :'a_tokenIdValidate',
	            'activeCollabTokenId' : activeCollabTokenId,
	            'activeCollabCompanyName' : activeCollabCompanyName,
	            'userName' : userName,
	            'userPassword' : userPassword,
	        },
	        success:function(data){
	            console.log(data)      
	        },
	        error:function (errorThrown) {
	            console.log(errorThrown)
	        }
	    });
    }
    else{
    	$('#submitErrorMsg').html('Submit All Mandatory Fields');
    }
    
    return false;
});

 /* $( function() {
    $( "#dialog" ).dialog({
      autoOpen: false,
      show: {
        effect: "blind",
        duration: 1000
      },
      hide: {
        effect: "explode",
        duration: 1000
      }
    });
 
    $( "#opener" ).on( "click", function() {
      $( "#dialog" ).dialog( "open" );
    });
  } );*/

