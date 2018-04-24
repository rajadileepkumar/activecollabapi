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
 */
//define('WP_DEBUG', true);
//ini_set('MAX_EXECUTION_TIME', 800000);
require_once __DIR__ . '/vendor/autoload.php';
class ActiveCollabAPI{

	function __construct(){
		add_action( 'admin_menu', array($this,'a_collab_menu'), 10, 1 ); //admin menu
		//add_action( 'admin_enqueue_scripts', array($this,'a_collab_custom_scripts'), 10, 1 ); //load scripts
		add_action( 'wp_ajax_nopriv_a_registerTime', array($this,'a_registerTime')); //insert and validate
		add_action( 'wp_ajax_a_registerTime', array($this,'a_registerTime'));//insert and validate
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
	
	function a_collab_scripts(){
		wp_enqueue_script('jquery');
			wp_register_script('bootstrap-js',plugins_url('assets/js/bootstrap.min.js', __FILE__),array(),true,false);
			wp_enqueue_script('bootstrap-js');
		

		wp_register_script('script-js',plugins_url('assets/js/script.js', __FILE__),array(),true,false);
		wp_enqueue_script('script-js');
		wp_localize_script( 'script-js', 'ajax_object',
		                array( 'ajax_url' => admin_url( 'admin-ajax.php' )) );
        
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

	function a_collab_settings(){
		if(isset($_POST['signin'])){
			$userId = get_current_user_id();
			$activeCollabTokenId = (int)sanitize_text_field($_POST['activeCollabTokenId']);
			$activeCollabCompanyName = sanitize_text_field($_POST['activeCollabCompanyName']);
			$userName = sanitize_text_field($_POST['userName']);
			$userPassword = sanitize_text_field($_POST['userPassword']);
			$activeCollabProjectId = sanitize_text_field($_POST['activeCollabProjectId']);

			$active_Serilizedarray = serialize(array($userId,$activeCollabTokenId,$activeCollabCompanyName,$userName,$userPassword,$activeCollabProjectId));

			$optionName = 'active_collab_setting_'.$userId;
			$option_exists = (get_option($optionName, null) !== null);
			if($option_exists){
				update_option( $optionName, $active_Serilizedarray, 'yes');
				self::a_configuration_settings();
			}
			else{
				add_option($optionName, $active_Serilizedarray, '', 'yes');
				self::a_configuration_settings();
				echo "Inserted";
			}
		}
		?>	
			<div class="wrap">
				<h2>Active Collab Settings</h2>
			</div>
			<form action="" method="post" id="aSettingsForm">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="activeCollabTokenId">Token Id</label>
							</th>
							<td>
								<input type="text" name="activeCollabTokenId" id="activeCollabTokenId" placeholder="Token Id" class="regular-text" required value="173387" maxlength="6" minlength="6">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="activeCollabCompanyName">Company Name</label>
							</th>
							<td>
								<input type="text" name="activeCollabCompanyName" id="activeCollabCompanyName" placeholder="Company Name" class="regular-text" required value="Utthunga">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="userLogin">User Login</label>
							</th>
							<td>
								<input type="email" name="userName" id="userName" placeholder="Email" class="regular-text" required>
							</td>	
						</tr>
						<tr>
							<th scope="row">
								<label for="userPassword">User Password</label>
							</th>
							<td>
								<input type="password" value="Kumdilrajutt@123" name="userPassword" id="userPassword" placeholder="Password" class="regular-text" required>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="activeCollabProjectId">Project Id</label>
							</th>
							<td>
								<input type="text" value="" name="activeCollabProjectId" id="activeCollabProjectId" placeholder="Project Id" class="regular-text" required maxlength="3">
							</td>
						</tr>
					</tbody>
				</table>
				<p>
					<input type="submit" name="signin" id="signin" value="Save" class="button button-primary">
					<p id="submitErrorMsg"></p>
				</p>
			</form>
		<?php
	}

	function a_collab_menu_page(){

		$token = self::a_configuration_settings();
		$client;
		if($token){
			$client = new \ActiveCollab\SDK\Client($token['0']); 
			$projectName = $client->get('projects/'.$token['1'])->getJson();
			$user = $token['2'];
			$token_Id =  explode("-",$token['0']->getToken());//userid
		}

		?>
			<div class="wrap">
				<h2>Active Collab<sub>/<?php echo $user['first_name']." ".$user['last_name'];?><sub>/ <?php echo $projectName['single']['name']; ?></sub>
				</h2>
				<div class="postbox-container">
					<?php
						$taskList = $client->get('projects/'.$token['1'].'/tasks')->getJson();
						$taskarray = array();
						foreach ($taskList['tasks'] as $tsklist) {
							$taskarray[$tsklist['name']]['taskid'] = $tsklist['task_list_id'];
							$taskarray[$tsklist['name']]['label'] = $tsklist['labels'][0]['name'];
							$taskarray[$tsklist['name']]['color'] = $tsklist['labels'][0]['color'];
							$taskarray[$tsklist['name']]['assignee_name'] = $tsklist['fake_assignee_name'];
							$taskarray[$tsklist['name']]['open_sub_task'] = $tsklist['open_subtasks'];
							$taskarray[$tsklist['name']]['task_url_path'] = $tsklist['url_path'];
							$taskarray[$tsklist['name']]['close_sub_task'] = $tsklist['completed_subtasks'];
							$taskarray[$tsklist['name']]['job_type_id'] = $tsklist['job_type_id'];
						}
						//var_dump($taskarray);
						foreach ($taskList['task_lists'] as $list) {
							?>
								<div class="postbox">
									<h2 class="hndle ui-sortable-handle	">
										<?php
											$task_list_id = $list['id'];
											$task_list_total = $list['open_tasks']; //count of tasks
										?>
										<span><?php echo $list['name'];?> (<?php echo $task_list_total;?>)</span>
									</h2>
									<div class="inside">
										<div class="main">
											<?php  
												foreach ($taskarray as $key => $value) {
													if($value['taskid'] == $task_list_id){
														$taskclass = str_replace(' ', '-', strtolower($key));
														if(!empty($value['assignee_name'])){
															$assignee = $value['assignee_name'];	
														}
														echo '<div data-toggle="modal" class="task" data-target="#tasklist-'.$value['taskid'].'-'.$taskclass.'">';
															
															echo '<span class="task-name">'.$key.'<sub>-'.$assignee.'</span>';
															
															if(!empty($value['label'])){
																echo '<span class="task-label" style="color:'.$value['color'].'">'.$value['label'].'</span>';
															}

															if(!empty($value['open_sub_task'])){
																echo '<span class="task-icons dashicons dashicons-editor-ul">'.$value['open_sub_task'].'</span>';
															}

															/*if(!empty($value['close_sub_task'])){
																echo '<span class="task-icons dashicons dashicons-editor-ul">'.$value['close_sub_task'].'</span>';
															}*/
															
														echo '</div>';

														?>	
														<?php			
													}
													?>
														<div id="tasklist-<?php echo $value['taskid'].'-'.$taskclass ?>" class="modal fade" role="dialog">
															<div class="modal-dialog">
														    	<div class="modal-content">
															    	<div class="modal-header">
															        	<button type="button" class="close" data-dismiss="modal">&times;</button>
															        	<h4 class="modal-title">Modal Header</h4>
															      	</div>
															    	<div class="modal-body">
															        	<h2><?php echo $key; ?></h2>
															        	<?php
															        		echo "Job Type Id" .$value['job_type_id'];
															        		$subtask_List = $client->get($value['task_url_path'])->getJson();//subtasklist

															        		$subtaskArray = array();
															        		$commentsArray = array();
															        		if(!empty($subtask_List['subtasks'])){
															        			self::subtasks_tasks($subtask_List['subtasks']); //opentask	
															        		}
															        		if(!empty($subtask_List['comments'])){
															        			self::subtasks_comments($subtask_List['comments']);//discussion	
															        		}
															        		?>
															        			<button class="button button-primary" data-toggle="collapse" data-target="#time-tasklist-<?php echo $value['taskid'].'-'.$taskclass ?>">+Add Time</button>

															        			<div id="time-tasklist-<?php echo $value['taskid'].'-'.$taskclass ?>" class="collapse">
															        				
														        					<form>
														        						<textarea rows="4" cols="50" class="form-control time-control recordingTime"  id = "text-tasklist-<?php echo $value['taskid'].'-'.$taskclass ?>" placeholder="Enter Time 1.30 or 1.5" required></textarea>
														        					</form>
														        					<button class="btn btn-primary addTimeRecord" id="button-tasklist-<?php echo $value['taskid'].'-'.$taskclass ?>" name="addTimeRecord" onclick="javascript:registerTime('<?php echo $token['1'] ?>',<?php echo $value['taskid'] ?>,document.getElementById('text-tasklist-<?php echo $value['taskid'].'-'.$taskclass ?>',).value,<?php echo $value['job_type_id']?>,<?php echo $token_Id['0']?>)">Add Time Record</button>
															        			</div>
															        		<?php

															        	?>
															      	</div>
															    	<div class="modal-footer">
															        	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
															      	</div>
														   		 </div><!--modal-content-->
														  	</div><!--modal-dialog-->
														</div><!--modal-->
													<?php
												}
											?>
										</div><!--main-->	
									</div><!--inside-->
								</div><!--postbox-->
							<?php
						}
					?>
				</div><!--postbox-container-->
			</div><!--wrap-->
		<?php
	}

	private function subtasks_tasks($subtask_List){
		//print_r($subtask_List);
		echo '<div class="subtasks">';
			echo '<h4>Sub Tasks</h4>';
    		foreach ($subtask_List as $slist) {
    			if($slist['is_completed'] == ''){
    				echo '<p>'.$slist['name'].'</p>';	
    			}

    			if($slist['is_completed'] != ''){
    				echo '<div class="c-sub">';
    					echo '<p>'.$slist['name'].'</p>';		
    				echo '</div>';
    			}

    		}
		echo '</div>';
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
		$active_Unserilizedarry = unserialize($settings);
		$authenticator = new \ActiveCollab\SDK\Authenticator\Cloud($active_Unserilizedarry['2'], 'My Awesome Application',$active_Unserilizedarry['3'],$active_Unserilizedarry['4']);

		$token = $authenticator->issueToken((int) $active_Unserilizedarry['1']);
		$user = $authenticator->getUser();
		return array($token,$active_Unserilizedarry['5'],$user);
	}

	function a_registerTime(){
	  $projectId = $_POST['projectId'];
	  $task_id = $_POST['task_id'];
	  $value = $_POST['value'];
	  $job_id = $_POST['job_id'];
	  $userId = $_POST['userId'];
	  $token = self::a_configuration_settings();
	  $date  = date("Y-m-d");
	  $params = array(
	  	  "task_id" => $task_id,
	      "value" => $value,
	      "user_id" => $userId,
	      "job_type_id" => $job_id,
	      "record_date" => $date
	  );
	  print_r($params);
	  //exit;
	  $client;
	  if($token){
	   $client = new \ActiveCollab\SDK\Client($token['0']);
	   $logTime = $client->post('/projects/'.$projectId.'/time-records',$params);
	   //$logTime = $client->post($projectId.'/timerecords',$params);
	   if($logTime){
	   	print_r($logTime);
	   }
	  }
	  
	  die();
	}

	
}

$obj = new ActiveCollabAPI();


?>