function registerTime(projectId,userId,taskId,time,description,jobId){
  //alert("Project Id"+projectId+"userId"+userId+"taskId"+taskId+"Time"+time+"description"+description+"Job Id"+jobId);
    if(time != '' && description != '' && jobId != ''){
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
    else{
        alert('All Mandatory Fields required');
    }
}

/*$(function () {
    $('.keycontrol').keydown(function (e) {
        if (e.shiftKey || e.ctrlKey || e.altKey) {
            e.preventDefault();
        } else {
        var key = e.keyCode;
            if (!((key == 8) || (key == 46) || (key >= 35 && key <= 40) || (key >= 48 && key <= 57) || (key >= 96 && key <= 105))) {
                e.preventDefault();
            }
        }
    });
});*/

$(document).ready(function() {
    $(".keycontrol").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl+A, Command+A
            (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
             // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
});

/*$('.validForm').submit(function(event){
    var allInputsAreValid = true;
    var form = null;
    $('.validForm input').each(function(){
        switch($(this).attr('class')){
            case 'keycontrol':
                if($(this).val() == "") {
                    allInputsAreValid = false;
                }
            break;
            case 'newsletter':
                 allInputsAreValid = false;
            break;
        }
        if(!allInputsAreValid) {
            form = $(this).parent();
            break;
        }
    });
    if(allInputsAreValid){
      // everything is valid, transfer data
    } else {
        event.preventDefault();
        $(form).children('.error').text(errorMessage);
    }
});*/

function getTasksListById(id,urlPath){
    $.ajax({
        method:'POST',
        url:ajax_object.ajax_url,
        data:{
            'action' :'a_TaskListByDetailsId',
             'id':id,
             'urlPath':urlPath
        },
        success:function(data){
             $('.modal-body').html(data);
             $('#'+id).modal('show');    
        },
        error:function (errorThrown) {
            console.log(errorThrown)
        }
  });  
}
