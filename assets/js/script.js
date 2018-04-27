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
        
    }
}
