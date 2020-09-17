<?php

	//This should be run on a 5 minute CRON job.
	//E.g.
	//  */5 * * * *     /usr/bin/php /var/www/html/loop-server/plugins/aj_install_updater/index.php 
			
	function main_api_updates($json)
	{
		//Run through each update.
		//For testing: print_r($json);
		
		//Complete exit out
		if(isset($json['allowAutoUpdates'])) {
			if($json['allowAutoUpdates'] != true) {
				return false;
			}
		}
		
		//Check an update for the main server.
		if(isset($json['mainServer'])) {
			$run_now = true;
			
			if($json['stable'] == true) {
				if(rand(1,12) == 1){
					//Yes run now. Once in every 12 attempts, approx once an hour.
				} else {
					echo "Skipping main Messaging Server update check: " . $json['plugins'][$cnt]['folder'] . "\n";
					$run_now = false;
				}
			}
			
			if($run_now == true) {
				echo "Checking for updates on Messaging Server\n";
				chdir($json['apiPath']);
			
				if($json['mainServer'] == "latest") {
					$tag = shell_exec("sudo git tag --sort=committerdate | tail -1");
					$fetch = $json['gitMainBranch']; 	//Usually "origin master"	
				} else {
					$tag = $json['mainServer'];
					$fetch = $json['gitMainBranch'];
				}
				
						
				$git_response = shell_exec("sudo git fetch " . $fetch . "; sudo git diff " . $tag); 		//Note: this was 'origin'
				if((strpos($git_response, "+++") !== false) ||
						(strpos($git_response, "---") !== false)) {
			
					
			
						//Yep, updates here, retrieve them
						$reset_cmd = "sudo git stash; sudo git fetch " . $fetch . "; sudo git checkout tags/" . $tag; 
						echo $reset_cmd . "\n";
						exec($reset_cmd); 	
						
						
						//And rewrite the main inner js file with new language entries.
						if(file_exists("js/")) {          //looking for chat-inner*, but so long as the dir exists a file should be there
							//Put the chat-inner* file into a string
							$file_array = glob("js/chat-inner*");
							if($file_array[0]) {
									$message_script_str = file_get_contents(__DIR__ . "/chat-inner-messages.json"); 	//Get the new messages for this js file
									$js_script_str = file_get_contents($file_array[0]);
									$new_js_script_str = preg_replace('#lsmsg = (.*?)var lang#s',"lsmsg = " . $message_script_str . "\nvar lang", $js_script_str);
									//Write out the new live js file (not version controlled), alongside the old one (which is version controlled). The config.json points at this live version.
									$new_js_filename = str_replace("chat-inner-", "chat-inner-live-" , $file_array[0]);
									file_put_contents($new_js_filename, $new_js_script_str);
									
									//And make sure the config is using this latest version
									$new_js_filename = "/" . $new_js_filename;
									$new_js_filename_escaped = str_replace("/", "\/", $new_js_filename);
									$replacer_command = "sudo sed -ie 's/\"chatInnerJSFilename\": \".*\"/\"chatInnerJSFilename\": \"" . $new_js_filename_escaped . "\"/g' config/config.json";
									echo $replacer_command . "\n";
									exec($replacer_command);
							}
						}
				}
			}
		}
		
		//Now loop through all of the plugins and do much the same
		if(isset($json['plugins'])) {
			for($cnt = 0; $cnt < count($json['plugins']); $cnt++) {
				
				$run_now = true;
			
				if($json['plugins'][$cnt]['stable'] == true) {
					if(rand(1,12) == 1){
						//Yes run now. Once in every 12 attempts, approx once an hour.
					} else {
						echo "Skipping plugin-update check: " . $json['plugins'][$cnt]['folder'] . "\n";
						$run_now = false;
					}
				}
				
				if($run_now == true) {
					echo "Checking for updates on Messaging plugin " . $json['plugins'][$cnt]['folder'] . "\n";
					if($json['plugins'][$cnt]['folder']) {
						chdir($json['apiPath'] . 'plugins/' . $json['plugins'][$cnt]['folder']);
					
						if($json['plugins'][$cnt]['ver'] == "latest") {
							$tag = shell_exec("git tag --sort=committerdate | tail -1");
							$fetch = $json['gitMainBranch']; 	//Usually "origin master";	
						} else {
							$tag = $json['plugins'][$cnt]['ver'];
							$fetch = $json['gitMainBranch']; 	//Usually "origin master";
						}
					
					}
				
					$git_response = shell_exec("sudo git fetch " . $fetch . "; sudo git diff " . $tag); 
					if((strpos($git_response, "+++") !== false) ||
							(strpos($git_response, "---") !== false)) {
			
							//Yep, updates here, retrieve them
							$reset_cmd = "sudo git fetch " . $fetch . "; sudo git checkout tags/" . $tag;
							echo $reset_cmd . "\n";
							exec($reset_cmd); 	
					}
				}
			
			}
		
		}
	}
	
	chdir(__DIR__);		//Get back into main dir
		
	//Now check for 
	echo "Main AtomJump Messaging update check\n";
	$data = file_get_contents ("config/messaging-versions.json");
	$versions_json = json_decode($data, true);
	main_api_updates($versions_json);	
	
	
?>	
				