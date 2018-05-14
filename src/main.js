var $ = jQuery.noConflict();
$(function () {
	$('#activeCollabTokenId').keydown(function (e) {
		if (e.shiftKey || e.ctrlKey || e.altKey) {
			e.preventDefault();
		} else {
		var key = e.keyCode;
			if (!((key == 8) || (key == 46) || (key >= 35 && key <= 40) || (key >= 48 && key <= 57) || (key >= 96 && key <= 105))) {
				e.preventDefault();
			}
			else{
				
			}
		}
	});
});

$(document).on("click", "#signin", function () {
	var formvalid;
	var activeCollabTokenId = $('#activeCollabTokenId').val();
    var activeCollabCompanyName = $('#activeCollabCompanyName').val();
    var userName = $('#userName').val();
    var userPassword = $('#userPassword').val();
    var activeCollabProjectId = $('#activeCollabProjectId').val();
    formvalid = $('#aSettingsForm').valid();
    if (formvalid && activeCollabTokenId != '' && activeCollabCompanyName != '' && userName != '' && userPassword != '' && activeCollabProjectId != '') {
    	$.ajax({
	        method:'POST',
	        url:ajax_object.ajax_url,
	        data:{
	            'action' :'a_activecollab_settings_saved',
	            'activeCollabTokenId' : activeCollabTokenId,
	            'activeCollabCompanyName' : activeCollabCompanyName,
	            'userName' : userName,
	            'userPassword' : userPassword,
	            'activeCollabProjectId' : activeCollabProjectId,
	        },
	        success:function(data){
	        	window.location.href = data;
	        },
	        error:function (errorThrown) {
	            console.log(errorThrown)
	        }
	    });	
    }
});

$(document).ready(function(){
	$("#aSettingsForm").validate({
		rules: {
		    activeCollabTokenId: {
		      required: true,
		      minlength:5,
		    },
		    activeCollabCompanyName :{
		    	required:true,
		    },
		    userName:{
		    	required:true,
		    },
		    userPassword:{
		    	required:true,
		    },
		    activeCollabProjectId:{
		    	required:true,
		    }
		},
		messages:{
		 	activeCollabTokenId:{
		 		required:'Token Id required',
		 		minlength:'Token Id should be atleast 5 numbers'
		 	},
		 	activeCollabCompanyName:{
		 		required:'Company Name required as per activecollab',
		 	},
		 	userName:{
		 		required:'provide usernmae of activecollab/username required',
		 	},
		 	userPassword:{
		 		required:'Provide password of activecollab/password required',	
		 	},
		 	activeCollabProjectId:{
		 		required:'Provide Active Collab Project Id',	
		 	}
		}
	});
});
