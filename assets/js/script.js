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

//registerTime(projectid,userid,taskid,time,description,jobid);
//,description,jobId+"description"+description,"Job Id"+jobId

function registerTime(projectId,userId,taskId,time,description,jobId){
  
  //alert("Project Id"+projectId+"userId"+userId+"taskId"+taskId+"Time"+time+"description"+description+"Job Id"+jobId);
  
  $.ajax({
        method:'POST',
        url:ajax_object.ajax_url,
        data:{
            'action' :'a_registerTime',
            'projectId' : projectId,
            'userId' : userId,
            'taskId' : taskId,
            'time' : time,
            'description' : description,
            'jobId' : jobId,
        },
        success:function(data){
            console.log(data)     
        },
        error:function (errorThrown) {
            console.log(errorThrown)
        }
  });
}