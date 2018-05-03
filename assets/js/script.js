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
                //console.log(data)     
                window.location.reload(true);
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

function getTasksListById(id,urlPath,projectId,userId,taskId){
    $.ajax({
        method:'POST',
        url:ajax_object.ajax_url,
        data:{
            'action' :'a_TaskListByDetailsId',
             'id':id,
             'urlPath':urlPath,
             'projectId':projectId,
             'userId':userId,
             'taskId':taskId,
        },
        success:function(data){
            $('.modal-body > .subTasks').html(data);
            $('#myModal').modal('show');    
        },
        error:function (errorThrown) {
            console.log(errorThrown)
        }
  });  
}

$(document).ajaxStart(function(){
    $("#loadingDiv").css("display", "block");
});
$(document).ajaxComplete(function(){
    $("#loadingDiv").css("display", "none");
});

$(document).ready(function(){
    $('.collapse').on('shown.bs.collapse', function(){
        $(this).parent().find(".glyphicon-plus").removeClass("glyphicon-plus").addClass("glyphicon-minus");
    }).on('hidden.bs.collapse', function(){
        $(this).parent().find(".glyphicon-minus").removeClass("glyphicon-minus").addClass("glyphicon-plus");
    });
});

