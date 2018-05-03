<?php  
/**
 * Plugin Name:Active Collab API
 * Version:0.1
 * Plugin URI:https://www.github.com/rajadileepkumar
 * AUthor:Raja Dileep Kumar
 * Author URI:https://www.github.com/rajadileepkumar
 * Description:Integration Activecollab
 * 
 * 
 * 
 * 
 */
require_once __DIR__ . '/vendor/autoload.php';
class ActiveCollabAPI{

	function __construct(){
		add_action( 'admin_menu', array($this,'a_collab_menu'), 10, 1 ); //admin menu
		add_action( 'admin_enqueue_scripts', array($this,'a_collab_custom_scripts')); //load scripts
		add_action( 'wp_ajax_nopriv_a_registerTime', array($this,'a_registerTime')); //insert and validate
		add_action( 'wp_ajax_a_registerTime', array($this,'a_registerTime'));//insert and validate
		
		add_action( 'wp_ajax_nopriv_a_activecollab_settings_saved', array($this,'a_activecollab_settings_saved')); //insert and validate
		add_action( 'wp_ajax_a_activecollab_settings_saved', array($this,'a_activecollab_settings_saved'));//insert and validate

		add_action( 'wp_ajax_nopriv_a_TaskListByDetailsId', array($this,'a_TaskListByDetailsId')); //insert and validate
		add_action( 'wp_ajax_a_TaskListByDetailsId', array($this,'a_TaskListByDetailsId'));//insert and validate
		
		add_action( 'admin_bar_menu', array($this,'a_toolbar_link_page'),999);//add admin node

	}

	function a_collab_menu(){
		$menu = add_menu_page( 'Active Collab', 'Active Collab', 'manage_options', 'a_collab_menu_slug', array($this,'a_collab_menu_page'), '', null );
		add_submenu_page( 'a_collab_menu_slug', 'Active Collab Settings', 'Settings','manage_options','a_collab_menu_settings', array($this,'a_collab_settings'));
		add_submenu_page( '','Project Task List', 'Task List', 'manage_options', 'a_task_list', array($this,'a_task_list'));

		add_action( 'admin_print_styles-' . $menu, array($this,'a_collab_styles')); //to load styles page level
		add_action('admin_print_scripts-' . $menu,array($this,'a_collab_scripts')); //to load scripts page level

	}

	
	function a_collab_styles(){
		wp_enqueue_style( 'bootstrap-css', plugins_url('assets/css/bootstrap.css', __FILE__));
		wp_enqueue_style( 'custom-css', plugins_url('assets/css/custom.css', __FILE__));
	}

	function a_collab_custom_scripts(){
		wp_enqueue_style( 'main-css', plugins_url('assets/css/main.css', __FILE__));

		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-validate',plugins_url('assets/js/jquery.validate.min.js',__FILE__),array(),true,false);
		wp_register_script('main-js',plugins_url('assets/js/main.js', __FILE__),array(),true,false);
		wp_enqueue_script('main-js');
		
		wp_localize_script( 'main-js', 'ajax_object',
		                array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
	}
	
	function a_collab_scripts(){
		
		wp_register_script('bootstrap-js',plugins_url('assets/js/bootstrap.min.js', __FILE__),array(),true,false);
		wp_enqueue_script('bootstrap-js');
		

		wp_register_script('script-js',plugins_url('assets/js/script.js', __FILE__),array(),true,false);
		wp_enqueue_script('script-js');
		wp_localize_script( 'script-js', 'ajax_object',
		                array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
        
	}

	function a_toolbar_link_page($wp_admin_bar){
		$args = array(
			'id'    => 'active_collab',
			'title' => 'Active Collab',
			'href'  => admin_url('admin.php?page=a_collab_menu_slug'),
			'meta'  => array( 'class' => 'my-toolbar-page' )
		);
		
		$wp_admin_bar->add_node( $args );

		$args = array(
            'parent' => 'active_collab',
            'id'     => 'activecollab-setting',
            'title'  => 'Settings',
            'href'   => esc_url( admin_url( 'admin.php?page=a_collab_menu_settings' ) ),
            'meta'   => false        
        );
        $wp_admin_bar->add_node( $args );
	}

	function a_activecollab_settings_saved(){
		$userId = get_current_user_id();
		$activeCollabTokenId = (int)sanitize_text_field($_POST['activeCollabTokenId']);
		$activeCollabCompanyName = sanitize_text_field($_POST['activeCollabCompanyName']);
		$userName = sanitize_text_field($_POST['userName']);
		$userPassword = sanitize_text_field($_POST['userPassword']);
		$activeCollabProjectId = sanitize_text_field($_POST['activeCollabProjectId']);

		//echo $userId.$activeCollabTokenId.$activeCollabCompanyName.$userName.$userPassword.$activeCollabProjectId;
		$active_Serilizedarray = serialize(array($userId,$activeCollabTokenId,$activeCollabCompanyName,$userName,$userPassword,$activeCollabProjectId));
		
		$optionName = 'active_collab_setting_'.$userId; //generating settings name
		$option_exists = (get_option($optionName, null) !== null); 
		
		if($option_exists){
			update_option( $optionName, $active_Serilizedarray, 'yes'); //update settings
			echo "Updated Settings";
		}
		else{
			add_option($optionName, $active_Serilizedarray, '', 'yes'); //insert settings
			echo "Settings Inserted";
		}
		die();
	}

	function a_TaskListByDetailsId(){
		$id = $_POST['id'];
		$urlPath = sanitize_text_field($_POST['urlPath']);
		$projectId = sanitize_text_field($_POST['projectId']);
		$userId = sanitize_text_field($_POST['userId']);
		$taskId = sanitize_text_field($_POST['taskId']);
		
		$token = self::a_configuration_settings();
		$client;
		if($token){
			$client = new \ActiveCollab\SDK\Client($token['0']); 
			$subtask_List = $client->get($urlPath)->getJson();
			$time_records = $client->get('/projects/'.$projectId.'/tasks/'.$taskId.'/time-records')->getJson();
		}

		$totalTimeSpend = self::getTotalTimeRecordsByTask($time_records['time_records']);

		$subtaskArray = array();
		$commentsArray = array();
		echo '<h4 class="modal-title task_main_heading">';
			echo $subtask_List['single']['name'];
			if(!empty($totalTimeSpend)){
				echo '('.$totalTimeSpend.'Hours)';
			}
		echo '</h4>';

		if(!empty($subtask_List['subtasks'])){
			if(!empty($subtask_List['single']['open_subtasks'])){
				echo '<p class="task_heading">Sub Tasks</p>';
				echo '<ul>';
					self::subtask_open($subtask_List['subtasks']);//opensubtask 
				echo '</ul>'; 
			}

			if(!empty($subtask_List['single']['completed_subtasks'])){
				echo '<a data-toggle="collapse" data-target="#closed-tasklist">Closed Tasks ('.$subtask_List['single']['completed_subtasks'].')</a>';
				echo '<ul id="closed-tasklist" class="collapse">';
					self::subtask_close($subtask_List['subtasks']);//closedsubtask	
				echo '</ul>';
			}
		}

		if(!empty($subtask_List['comments'])){
			self::subtasks_comments($subtask_List['comments']);//discussion	
		}

		?>
			<div class="time-task">
    			<button class="button button-primary" data-toggle="collapse" data-target="#time-tasklist">+Add Time</button>
    			<div id="time-tasklist" class="collapse">
    				<form id="timeLogTaskById" method="post">
    					<div class="form-group">
    						<label for="timeLogRecord">Time Log (<span class="required-field">*</span>)</label>
							<input type="text" class="keycontrol form-control control" name="timeLogRecord" id="timeLogRecord" placeholder="Enter Time 1.30 or 1.5" required maxlength="5" minlength="1">
    					</div>
    					<div class="form-group">
    						<label for="timeLogDescription">Description (<span class="required-field">*</span>)</label>
							<textarea rows="4" cols="50" class="form-control control"  id = "timeLogDescription" name = "timeLogDescription" placeholder="Description"></textarea>
    					</div>
    					<div class="form-group">
    						<label for="job_type_id">Select Job Type (<span class="required-field">*</span>)</label>
							<select class="form-control control" id="job_type_id" name="job_type_id">
    							<?php  
        							$job_types = $client->get('job-types')->getJson(); 
        							$jobtypes = self::jobtypes($job_types);
        						?>
							</select>
    					</div>
    					<input type="button" class="btn btn-primary addTimeRecord" id="addTimeTaskById" name="addTimeTaskById" value="Add Time Record" onclick="javascript:registerTime(<?php echo $projectId?>,<?php echo $userId;?>,<?php echo $taskId?>, document.getElementById('timeLogRecord').value,document.getElementById('timeLogDescription').value,document.getElementById('job_type_id').value)">
    				</form>
    				<div class="time_records" id="time-records">
	    				<?php 
							
							if(!empty($time_records)){
								self::getTimeRecordsByTask($time_records['time_records']);
							}
						?>
	    			</div>	
				</div>
    		</div>
		<?php
		
		die();
	}
	
	function a_collab_settings(){
		?>	
			<div class="wrap">
				<h2>Active Collab Settings</h2>
			</div>
			<form action="" method="post" id="aSettingsForm">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="activeCollabTokenId">Token Id (<span class="required-field">*</span>)</label>
							</th>
							<td>
								<input type="text" name="activeCollabTokenId" id="activeCollabTokenId" placeholder="Token Id" class="regular-text" required value="173387" maxlength="10" minlength="5">
								<p id="msg"></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="activeCollabCompanyName">Company Name (<span class="required-field">*</span>)</label>
							</th>
							<td>
								<input type="text" name="activeCollabCompanyName" id="activeCollabCompanyName" placeholder="Company Name" class="regular-text" required value="Utthunga">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="userLogin">User Login (<span class="required-field">*</span>)</label>
							</th>
							<td>
								<input type="email" name="userName" id="userName" placeholder="Email" class="regular-text" required>
							</td>	
						</tr>
						<tr>
							<th scope="row">
								<label for="userPassword">User Password (<span class="required-field">*</span>)</label>
							</th>
							<td>
								<input type="password" value="Kumdilrajutt@123" name="userPassword" id="userPassword" placeholder="Password" class="regular-text" required>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="activeCollabProjectId">Project Id (<span class="required-field">*</span>)</label>
							</th>
							<td>
								<input type="text" value="" name="activeCollabProjectId" id="activeCollabProjectId" placeholder="Project Id" class="regular-text" required>
							</td>
						</tr>
					</tbody>
				</table>
				<p>
					<input type="button" name="signin" id="signin" value="Save" class="button button-primary">
					<p id="submitErrorMsg"></p>
				</p>
			</form>
		<?php
	}

	function a_collab_menu_page(){
		?>
			<div class="wrap">
				<?php 
					$token = self::a_configuration_settings();
					$client;
					if($token){
						$client = new \ActiveCollab\SDK\Client($token['0']); 
						$projectName = $client->get('projects/'.$token['1'])->getJson();
						$user = $token['2'];
						$token_Id =  explode("-",$token['0']->getToken());//userid
					}
				?>
				<h2>Active Collab<span> --<?php echo $user['first_name']." ".$user['last_name'];?></span><span> --<?php echo $projectName['single']['name']; ?></span>
				</h2>
				<div class="panel-group" id="accordion">
					<?php
						$taskList = $client->get('projects/'.$token['1'].'/tasks')->getJson();
						//echo '<pre>' . print_r($taskList, true) . '</pre>';
						//exit;
						$taskarray = array();
						foreach ($taskList['tasks'] as $tsklist) {
							$taskarray[$tsklist['name']]['taskid'] = $tsklist['task_list_id'];
							$taskarray[$tsklist['name']]['label'] = $tsklist['labels'][0]['name'];
							$taskarray[$tsklist['name']]['color'] = $tsklist['labels'][0]['color'];
							$taskarray[$tsklist['name']]['assignee_name'] = $tsklist['fake_assignee_name'];
							$taskarray[$tsklist['name']]['open_sub_task'] = $tsklist['open_subtasks'];
							$taskarray[$tsklist['name']]['url_path'] = $tsklist['url_path'];
							$taskarray[$tsklist['name']]['close_sub_task'] = $tsklist['completed_subtasks'];
							$taskarray[$tsklist['name']]['job_type_id'] = $tsklist['job_type_id'];
							$taskarray[$tsklist['name']]['id'] = $tsklist['id'];
							$taskarray[$tsklist['name']]['comments_count'] = $tsklist['comments_count'];
							$taskarray[$tsklist['name']]['created_by_name'] = $tsklist['created_by_name'];
							$taskarray[$tsklist['name']]['created_by_email'] = $tsklist['created_by_email'];
							
						}
						foreach ($taskList['task_lists'] as $list) {
							?>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title">
											<?php
												$task_list_id = $list['id'];
												$task_list_total = $list['open_tasks']; //count of tasks
											?>
											<a data-toggle="collapse" data-parent="#accordion" href="#collapse-<?php echo $list['id']; ?>">
	        									<?php echo $list['name'];?> (<?php echo $task_list_total;?>)
	        									<i class="more-less glyphicon glyphicon-plus"></i>
	        								</a>
										</h4>
									</div>
									<div id="collapse-<?php echo $list['id']; ?>" class="inside panel-collapse collapse">
										<ul class="main list-group">
											<?php  
												foreach ($taskarray as $key => $value) {
													if($value['taskid'] == $task_list_id){
														$value['id']."taskid";
														$url_path = "'".$value['url_path']."'";	
														$taskclass = str_replace(' ', '-', strtolower($key));
														if(!empty($value['assignee_name'])){
															$assignee = $value['assignee_name'];
														}

														if(!empty($value['created_by_name'])){
															$created_by_name = $value['created_by_name'];
														}

														echo '<div class="task" id="list-'.$value['id'].'-'.$taskclass.'" onclick="javascript:getTasksListById(this.id,'.$url_path.','.$token['1'].','.$token_Id['0'].','.$value['id'].')" data-id="'.$key.'">';
															
															echo '<span class="task-name">'.$key.'<sub>-'.$created_by_name.'</span>';
															echo '<span class="glyphicon glyphicon-triangle-top pull-right"></span>';
															if(!empty($value['label'])){
																echo '<span class="task-label" style="color:'.$value['color'].'">'.$value['label'].'</span>';
															}

															if(!empty($value['open_sub_task'])){
																echo '<span class="task-icons glyphicon glyphicon-tasks"></span>';
																echo '<span class="count-task">'.$value['open_sub_task'].'</span>';
															}

															if(!empty($value['close_sub_task'])){
																echo '<span class="task-icons glyphicon glyphicon-menu-hamburger"></span>';
																echo '<span class="count-task">'.$value['close_sub_task'].'</span>';
															}

															if(!empty($value['comments_count'])){
																echo '<span class="task-icons glyphicon glyphicon-comment"></span>';
																echo '<span class="count-task">'.$value['comments_count'].'</span>';
															}
															
														echo '</div>';
													}
												}
											?>
										</ul><!--main-->
										<div class="panel-footer">
											<?php add_thickbox(); ?>
											<a href="#TB_inline?width=600&height=320px&inlineId=modal-window-id" class="thickbox" title="Add Task">+ Add a Task</a>
										</div>	
									</div><!--inside-->
								</div><!--postbox-->
							<?php
						}
					?>
					<div class="modal-backdrop in" id="loadingDiv">
						<img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/images/loading.jpg'; ?>">
					</div>
					<div id="myModal" class="modal fade" role="dialog">
						<div class="modal-dialog">
					    	<div class="modal-content">
						    	<div class="modal-header">
						        	<button type="button" class="close" data-dismiss="modal">&times;</button>
						        	<h4 class="modal-title">List Of Task Details</h4>
						      	</div>
						    	<div class="modal-body">
						    		<div class="subTasks"></div>
						    	</div>
						    	<div class="modal-footer">
						        	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						      	</div>
						    </div>
						</div>
					</div>
					<div class="taskAdd collapse" id="modal-window-id">
						<form id="taskAddForm">
							<div class="form-group">
								<label for="addTaskName">Task Name</label>
								<input type="text" name="addTaskName" id="addTaskName" class="form-control" placeholder="Task Name" required>
							</div>
							<div class="form-group">
								<label for="addTaskAssign">Assignee</label>
								<select name="addTaskAssign" id="addTaskAssign" class="form-control">
									<?php  
	        							$allMembers = $client->get('users')->getJson(); 
	        							$assignee = self::getAllUsers($allMembers);
	        						?>
								</select>
							</div>
							<div class="form-group">
								<label for="addTaskLabels">Labels</label>
								<select class="form-control" name="addTaskLabels" id="addTaskLabels">
									<?php 
										$alllabels = $client->get('labels')->getJson(); 
										self::getProjectAllLabels($alllabels);
									?>
								</select>
							</div>
							<input type="button" name="addTask" id="addTask" value="Add Task" class="button button-primary">
						</form>
					</div>
				</div><!--postbox-container-->
			</div><!--wrap-->
		<?php
	}

	private function subtask_open($subtask_List){
		   foreach ($subtask_List as $slist) {
    			if($slist['is_completed'] == ''){
    				echo '<li>'.$slist['name'].'</li>';	
    			}
    		}
	}

	private function subtask_close($subtask_List){
		//echo '<pre>' . print_r($subtask_List, true) . '</pre>';
		foreach ($subtask_List as $slist) {
			if($slist['is_completed'] != ''){
				echo '<li>'.$slist['name'].'</li>';	
			}
		}
			
	}
	


	private function subtasks_comments($comments){
		//print_r($comments);
		echo '<div class="discussion">';
			echo '<h4>Discussion</h4>';
			echo '<ul class="comments">';
    			foreach ($comments as $cmts) {
    				echo '<li>';
    					echo '<p><a href="mailto:"'.$cmts['created_by_email'].'">'.$cmts['created_by_email'].'</a>';
    						echo '<sub>'.gmdate('r', $cmts['updated_on']).'</sub>'; //date and time
    					echo '</p>';
    					echo '<p>'.$cmts['body_formatted'].'</p>';

	    				if(!empty($cmts['attachments'])){
					          //print_r($cmts['attachments']);
				          foreach ($cmts['attachments'] as $value) {
				           ?>
				            <img src="<?php echo $value['preview_url']; ?>" class="img-thumbnail">
				            <p><?php echo $value['name']; ?></p>
				            <p><?php $size = $value['size']/1024; echo round($size,3).'KB'; ?></p>
				            <p><a href="<?php echo $value['download_url']; ?>">Download</a></p>
				           <?php
				          }
					    }
					echo '</li>';
    			}
			echo '</ul>';
		echo '</div>';
	}

	private function a_configuration_settings(){
		global $wpdb;
		$userId = get_current_user_id();
		
		$optionName = 'active_collab_setting_'.$userId; 
		$settings =get_option($optionName, false);
		if($settings){
			$active_Unserilizedarry = unserialize($settings);
			$authenticator = new \ActiveCollab\SDK\Authenticator\Cloud($active_Unserilizedarry['2'], 'My Awesome Application',$active_Unserilizedarry['3'],$active_Unserilizedarry['4']);
				$token = $authenticator->issueToken((int) $active_Unserilizedarry['1']);
				if($token){
					$user = $authenticator->getUser();
					return array($token,$active_Unserilizedarry['5'],$user);		
				}
				else{
					print "Invalid response\n";
	    			die();
				}
		}
		else{
			echo "Update/Create Active Collab Settings";
		}
	}

	function a_registerTime(){
	  $projectId = $_POST['projectId'];
	  $userId = $_POST['userId'];
	  $taskId = $_POST['taskId'];
	  $time = $_POST['time'];
	  $description = $_POST['description'];
	  $jobId = $_POST['jobId'];
	  
	  $token = self::a_configuration_settings();
	  $date  = date("Y-m-d");
	  
	  $params = array(
	  	  "task_id" => $taskId,
	      "value" => $time,
	      "user_id" => $userId,
	      "job_type_id" => $jobId,
	      "record_date" => $date,
	      "summary" => $description
	  );
	  /*print_r($params);
	  exit;*/
	  $client;
	  if($token){
	   $client = new \ActiveCollab\SDK\Client($token['0']);
	   $logTime = $client->post('/projects/'.$projectId.'/time-records',$params);
	   if($logTime){
	   	//print_r($logTime);										        			
	   }
	  }
	  
	  die();
	}

	//jobtypes
	private function jobtypes($job_types){
		
		echo '<option id="">Choose Job Type</option>';
		foreach ($job_types as $jobs) {
			?>
				<option id="job_type_id" value="<?php echo $jobs['id']?>"><?php echo $jobs['name']?></option>
			<?php
		}
	}

	//get users
	private function getAllUsers($allMembers){
		
		echo '<option id="">Choose Assignee</option>';
		foreach ($allMembers as $users) {
			?>
				<option id="user_<?php echo $users['id']?>" value="<?php echo $users['id']?>"><?php echo $users['display_name']?></option>
			<?php
		}
	}

	private function getTimeRecordsByTask($time_records){
		$total_time_records = array();
		echo '<div>';
			foreach ($time_records as $time) {
				//$token = self::getUserAvatar($time['user_id']);
				echo '<div class="col-md-12 time-records">';
					echo '<p class="col-md-3">'.gmdate('M-d.Y', $time['record_date']).'</p>';
						$total_time_records[] = $time['value'];
					echo '<p class="col-md-3">'.$time['value'].'</p>';
					echo '<p class="col-md-3">'.$time['created_by_name'].'</p>';
					echo '<p class="col-md-3">'.$time['summary'].'</p>';
				echo '</div>';
			}
			//print_r($total_time_records);
		echo '</div>';
		return array_sum($total_time_records);
	}

	private function getTotalTimeRecordsByTask($time_records){
		$total_time_records = array();
		foreach ($time_records as $time) {
			//$token = self::getUserAvatar($time['user_id']);
			$total_time_records[] = $time['value'];
		}
		//print_r($total_time_records);
		return array_sum($total_time_records);
	}

	private function getUserAvatar($userid){
		$token = self::a_configuration_settings();
		if($token){
	   		$client = new \ActiveCollab\SDK\Client($token['0']);
	   		$avatar_url = $client->get('users/'.$user_id)->getJson();
	   		foreach ($avatar_url as $avatar) {
	   			return $avatar['avatar_url'];
	   		}
	   	}

	}

	private function getProjectAllLabels($alllabels){
		echo '<option id="">Choose Assignee</option>';
		foreach ($alllabels as $labels) {
			?>
				<option id="color_<?php echo $labels['id']?>" value="<?php echo $labels['id']?>" style="color: <?php echo $labels['color']?>">
					<?php echo $labels['name']?>
				</option>
			<?php
		}
	}
	
}

$obj = new ActiveCollabAPI();


?>