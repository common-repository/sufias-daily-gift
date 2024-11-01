<?php
   /*
   Plugin Name: today-gift-sufias
   Plugin URI: https://developer.wordpress.org/plugins/today-gift-sufias/
   Description: Basic WordPress Plugin Header Comment
   Version: 1.0.0
   Author: Sufias
   Author URI: https://sufias.org/
   License: GPL2
   License URI: https://www.gnu.org/licenses/gpl-2.0.html
   Text Domain: today_gift_sufias
   Domain Path: languages
    */
	
	require_once("today_gift_widget.php");
	
	
	require_once("google-api/vendor/autoload.php");
	
	function getClient()
	{
		
		 $options['verify'] = false;
		 $httpClient = new GuzzleHttp\Client($options);
		 
		$client = new Google_Client();
		
		$client->setApplicationName('today-gift-sufias');
		$client->setHttpClient($httpClient);
		
		$client->setScopes(Google_Service_Drive::DRIVE);
		$client->setAuthConfig(dirname(__file__).'/google-api/credentials.json');
		
		$client->setAccessType('offline');
		
		$client->setPrompt('select_account consent');
		//$client->request('GET', '/', ['verify' => true]);

		$tokenPath = dirname(__file__).'/google-api/token.json';
		$authUrl = $client->createAuthUrl();
		//echo "<h4 style='display:inline'>Open the following link in your browser: </h4>";
		//echo "<h5 style='display:inline'><a href='".$authUrl."' target='_blank'>Click Here To Get Key Link</a></h5>";
		if (file_exists($tokenPath)) 
		{	
			$accessToken = json_decode(file_get_contents($tokenPath), true);
			$client->setAccessToken($accessToken);
		}
		// If there is no previous token or it's expired.
		if ($client->isAccessTokenExpired()) 
		{
			
			// Refresh the token if possible, else fetch a new one.
			if ($client->getRefreshToken()) 
			{
				$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			} 
			else 
			{
				// Request authorization from the user.
				$authUrl = $client->createAuthUrl();
				echo "<h4 style='display:inline'>Open the following link in your browser: </h4>";
				echo "<h5 style='display:inline'><a href='".$authUrl."' target='_blank'>Click Here To Get Key Link</a></h5>";
				$key_file = dirname(__file__)."/key_file.txt";
				$key = "";
				if(!file_exists($key_file))
				{
					return $client; 
				}
				else
				{
					$key = file_get_contents($key_file);
				}
				if($key !="")
				//$authCode = trim("AIzaSyAde-poYlnDujx_tqFBEFRsC-DDCAul-pM");
				$authCode = trim($key);
				// Exchange authorization code for an access token.
				$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
				$client->setAccessToken($accessToken);
				// Check to see if there was an error.
				if (array_key_exists('error', $accessToken)) {
					throw new Exception(join(', ', $accessToken));

				}
				
			}
			// Save the token to a file.
			if (!file_exists(dirname($tokenPath))) {
				mkdir(dirname($tokenPath), 0700, true);
			}
			file_put_contents($tokenPath, json_encode($client->getAccessToken()));
		} 
		return $client; 
	}
	
	add_action('init', 'my_init_method');
	add_action('admin_menu',"today_gift_sufias");
	add_shortcode('today_gift_details', 'today_gift_page');

   
	function my_init_method() 
	{
		wp_deregister_style( 'style' );
		wp_enqueue_style( 'style', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css');
		
		
		wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js');
		
		wp_deregister_script( 'bootstrap' );
		wp_register_script( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js');
	} 

	register_activation_hook(__FILE__, 'today_gift_sufias_activation');

	function today_gift_sufias_activation() 
	{
		$detailPage = array(
                  'post_title'    => wp_strip_all_tags('Today Gift'),
                  'post_content'  => '[today_gift_details]',
                  'post_status'   => 'publish',
                  'post_author'   => 1,
                  'post_type'     => 'page',
                );
		$detailPageId = wp_insert_post( $detailPage, false);
        update_option('today_gift_details_page_id', $detailPageId );
		
		if (! wp_next_scheduled ( 'today_gift_sufias_event' )) 
		{
			wp_schedule_event(time(), 'daily', 'today_gift_sufias_event');
		}
	}

	add_action('today_gift_sufias_event', 'do_this_hourly');

	function do_this_hourly() 
	{
		$file = dirname(__file__).'/output.txt';
    
		$data = "Hallo, Its " . date('d/m/Y H:i:s'). "\n";
		
		file_put_contents($file,$data, FILE_APPEND);
		
		global $wpdb;
		$table_name = $wpdb->prefix . "tbl_hadees";
        $id = Date('d');
        $time = Date('Y-m-d');
        $wpdb->query("DELETE FROM ".$table_name." WHERE id < '$id' AND d_time = $time;");
			
	}

	register_deactivation_hook(__FILE__, 'today_gift_sufias_deactivation');

	function today_gift_sufias_deactivation() 
	{
		$detailPageId = get_option('today_gift_details_page_id');
		
		if($detailPageId) {
			wp_delete_post($detailPageId);
		}
		update_option('today_gift_details_page_id', '');
		
		wp_clear_scheduled_hook('today_gift_sufias_event');
			
		global $wpdb;
		
		$table_name = $wpdb->prefix . "tbl_hadees";
		
        $wpdb->query("DROP table $table_name");
		
		$file = dirname(__file__).'/deactivation.txt';
    
		$data = " today_gift_sufias_deactivation(".date('d/m/Y H:i:s').")\n";
		
		file_put_contents($file,$data, FILE_APPEND);
		
	}

   function today_gift_sufias()
   {
		global $wpdb;
		
		$table_name = $wpdb->prefix . "tbl_hadees";
		
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
		{
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
			  id int(11) NOT NULL AUTO_INCREMENT,
			  hadees varchar(100),
			  ayat varchar(100),
			  naat varchar(100),
			  link varchar(100),
			  link_optional varchar(100),
			  quote varchar(100),
			  d_time date DEFAULT '0000-00-00' NOT NULL,
			  PRIMARY KEY (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
		else
		{	
            
            $charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
			  id int(11) NOT NULL AUTO_INCREMENT,
			  hadees varchar(100),
			  ayat varchar(100),
			  naat varchar(100),
			  link varchar(100),
			  link_optional varchar(100),
			  quote varchar(100),
			  d_time date DEFAULT '0000-00-00' NOT NULL,
			  PRIMARY KEY (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
			
		}
		
		//add_options_page('add hadees', 'Add Hadees','manage_options','add_hadees','add_hadees_function');
   }

   function add_hadees_function()
   {
   	add_handle_post();
   ?>	
		<div class="container" style="padding:20px;">
		   <div class="col-sm-6">
				<form class="form-horizontal" method="post">
				<h3>Switch To Change Data Source</h3>
				<div class="col-sm-12">
				<div class="form-group">
					
					<?php
						$file = dirname(__file__).'/setting.txt';
						$setting = "";
						if (file_exists($file)) 
						{
							$setting = file_get_contents($file);
						}
						
						if(!file_exists($file))
						{
							echo '<input type="radio" checked name="data_source" value="database"><label>Database </label>';
							echo '&nbsp;';
							echo '<input type="radio"  name="data_source" value="drive"><label>Drive </label>';
						}
						else if(file_exists($file))
						{
							if($setting=="database")
							{
								echo '<input type="radio" checked name="data_source" value="database"><label>Database </label>';
								echo '&nbsp;';
								echo '<input type="radio"  name="data_source" value="drive"><label>Drive </label>';
							}
							else
							{
								echo '<input type="radio"  checked name="data_source" value="drive"><label>Drive </label>';
								echo '&nbsp;';
								echo '<input type="radio"  name="data_source" value="database"><label>Database </label>';
							}
						}
						?>
					
					<?php submit_button('Refresh') ?>
				 </div>
				 </div>
					
				 </form>
			
			<?php
				if(!file_exists($file) || $setting=="database")
				{
            		
			?>
			  <form class="form-horizontal" method="post" enctype="multipart/form-data">
			      <div class="form-group">
					<div class="col-sm-5" style="padding:0px">    
					<label for="d_time">Date</label>
					<input class="form-control" type='date' name='d_time' required=""/>
					</div>
				 </div>
				 <div class="form-group">
					<label for="hadees">Hadees</label>
					<input class="btn btn-primary" type='file' name='hadees'></input>
				 </div>
				 <div class="form-group">
					<label for="hadees">Ayat</label>
					<input class="btn btn-primary" type='file' name='ayat' ></input>
				 </div>
				 <div class="form-group">
					<label for="quote">Quote</label>
					<input class="btn btn-primary" type='file' name='quote' ></input>
				 </div>
				 <div class="form-group">
					<label for="hadees">Audio</label>
					<input class="btn btn-primary" type='file' name='naat' ></input>
				 </div>
				 <div class="form-group">
					<label for="focusedInput">Video</label>
					<input class="form-control"  name='link' type="text">
				 </div>
				 <div class="form-group">
					<label for="focusedInput">Link (Optional)</label>
					<input class="form-control"  name='link_optional' type="text">
				 </div>
				 <?php submit_button('Upload') ?>
			  </form>
			 <?php
				}
				else if(file_exists($file) || $setting=="drive")
				{
					$key_file = dirname(__file__)."/key_file.txt";
					$folder_file = dirname(__file__)."/folder_file.txt";
			
					$key = "";
					$hadees = "";
					$ayat = "";
					$quote ="";
					$naat = "";
					$link = "";
					$link_optional = "";
						
					$hadees_file = dirname(__file__)."/hadees.txt";
					$ayat_file = dirname(__file__)."/ayat.txt";
					$quote_file = dirname(__file__)."/quote.txt";
					$naat_file = dirname(__file__)."/naat.txt";
					$link_file = dirname(__file__)."/link.txt";
					$link_optional_file = dirname(__file__)."/link_optional.txt";
					
					
					
					if (file_exists($key_file)) 
					{
						$key = file_get_contents($key_file);
					}
					
					if (file_exists($folder_file)) 
					{
						$folder_data = file_get_contents($folder_file);
						
						$folder_data = explode("|",$folder_data);
						
						if(isset($folder_data[0]))
							$hadees = $folder_data[0];
						if(isset($folder_data[1]))
							$ayat = $folder_data[1];
						if(isset($folder_data[2]))
							$quote = $folder_data[2];
						if(isset($folder_data[3]))
							$naat = $folder_data[3];
						if(isset($folder_data[4]))
							$link = $folder_data[4];
						if(isset($folder_data[5]))
							$link_optional = $folder_data[5];
					}
					
					$client = getClient();
					$service = new Google_Service_Drive($client);
					
					if($key != "")
					{
						if($hadees !="")
						{
							$optParams = array(
							  'q' => "'".$hadees."' in parents",
							  'fields' => 'files(id, name)'
							);
							$results = $service->files->listFiles($optParams);
							if (count($results->getFiles()) == 0) 
							{
								file_put_contents($hadees_file,json_encode(["error"=>'No files found.']));
							} 
							else 
							{
								file_put_contents($hadees_file,json_encode($results->getFiles()));
							
							}
							
						}
						
						if($ayat !="")
						{
							$optParams = array(
							  'q' => "'".$ayat."' in parents",
							  'fields' => 'files(id, name)'
							);
							$results = $service->files->listFiles($optParams);
							if (count($results->getFiles()) == 0) 
							{
								file_put_contents($ayat_file,json_encode(["error"=>'No files found.']));
							} 
							else 
							{
								file_put_contents($ayat_file,json_encode($results->getFiles()));
							}
						
						}
						
						if($quote !="")
						{
							$optParams = array(
							  'q' => "'".$quote."' in parents",
							  'fields' => 'files(id, name)'
							);
							$results = $service->files->listFiles($optParams);
							if (count($results->getFiles()) == 0) 
							{
								file_put_contents($quote_file,json_encode(["error"=>'No files found.']));
							} 
							else 
							{
								file_put_contents($quote_file,json_encode($results->getFiles()));
							}
						
						}
						
						if($naat !="")
						{	
							$optParams = array(
							  'q' => "'".$naat."' in parents",
							  'fields' => 'files(id, name)'
							);
							$results = $service->files->listFiles($optParams);
							if (count($results->getFiles()) == 0) 
							{
								file_put_contents($naat_file,json_encode(["error"=>'No files found.']));
							} 
							else 
							{
								file_put_contents($naat_file,json_encode($results->getFiles()));
							}
						}
						
						if($link !="")
						{
							$optParams = array(
							  'q' => "'".$link."' in parents",
							  'fields' => 'files(id, name)'
							);
							$results = $service->files->listFiles($optParams);
							if (count($results->getFiles()) == 0) 
							{
								file_put_contents($link_file,json_encode(["error"=>'No files found.']));
							} 
							else 
							{
								file_put_contents($link_file,json_encode($results->getFiles()));
							}
							
						}
						if($link_optional !="")
						{
							$optParams = array(
							  'q' => "'".$link_optional."' in parents",
							  'fields' => 'files(id, name)'
							);
							$results = $service->files->listFiles($optParams);
							if (count($results->getFiles()) == 0) 
							{
								file_put_contents($link_optional_file,json_encode(["error"=>'No files found.']));
							} 
							else 
							{
								file_put_contents($link_optional_file,json_encode($results->getFiles()));
							} 
						}
					}
					?> 
				<form class="form-horizontal" method="post">
					<div class="form-group">
						<label for="hadees">Key</label>
						<input class="form-control" type='text' name='key' value="<?php echo $key; ?>" required=""></input>
					 </div>
					<div class="form-group">
						<label for="hadees">Hadees Id</label>
						<input class="form-control" type='text' name='hadees' value="<?php echo $hadees; ?>" required=""></input>
					 </div>
					 <div class="form-group">
						<label for="hadees">Ayat Id</label>
						<input class="form-control" type='text' name='ayat' value="<?php echo $ayat; ?>" required="" ></input>
					 </div>
					 <div class="form-group">
						<label for="hadees">Quote Id</label>
						<input class="form-control" type='text' name='quote' value="<?php echo $quote; ?>" required="" ></input>
					 </div>
					 <div class="form-group">
						<label for="hadees">Audio Id</label>
						<input class="form-control" type='text' name='naat' value="<?php echo $naat; ?>" required=""></input>
					 </div>
					 <div class="form-group">
						<label for="focusedInput">Video Id</label>
						<input class="form-control"  type="text" name='link'  value="<?php echo $link; ?>" required=""></input>
					 </div>
					 <div class="form-group">
						<label for="focusedInput">Link(Optional) Id</label>
						<input class="form-control"  type="text" name='link_optional'  value="<?php echo $link_optional; ?>" required=""></input>
					 </div>
					 <?php submit_button('Upload') ?>
				</form>	 
			<?php 
				}
			?>
		   </div>
		</div>
		<?php
			if(!file_exists($file) || $setting=="database")
			{
		?>
		<div class="container">
		   <h2>Uploaded Data</h2>
		   <table class="table table-striped" width="100%">
			  <thead>
				 <tr>
					<th>Hadees</th>
					<th>Ayat</th>
					<th>Quote</th>
					<th>Audio</th>
					<th>Video</th>
					<th>Link (Optional)</th>
					<th>Created At</th>
					<th>Remove</th>
				 </tr>
			  </thead>
			  <tbody>
					<?php
						global $wpdb;
		            
						$table_name = $wpdb->prefix . "tbl_hadees";
						$results = $wpdb->get_results( "SELECT * FROM $table_name", OBJECT );
						$uploads = wp_upload_dir();
						
						foreach($results as $data)
						{
							
							echo '<tr valign="center">';
							if($data->hadees != "")
								echo '<td><img width="50" width="50" src="'.wp_get_attachment_url($data->hadees).'"></td>';
							else
							    echo '<td>Not Found</td>';

							if($data->ayat != "")    
							    echo '<td><img width="50" width="50" alt="ayat" src="'.wp_get_attachment_url($data->ayat) .'"></td>';
							else
							    echo '<td>Not Found</td>';
                            
                            if($data->quote != "")    
							    echo '<td><img width="50" width="50" alt="ayat" src="'.wp_get_attachment_url($data->quote) .'"></td>';
							else
							    echo '<td>Not Found</td>';
							    
							if($data->naat != "")
								echo '<td><audio controls><source src="'.wp_get_attachment_url($data->naat) .'" type="audio/mpeg"></audio></td>';
							else
							    echo '<td>Not Found</td>';
                            
							echo '<td><a target="_blank" href="'.$data->link.'" >Open Link</a></td>';
							echo '<td><a target="_blank" href="'.$data->link_optional.'" >Open Link</a></td>';
							echo '<td>'.$data->d_time .'</td>';
							echo '<td><form method="post"><input hidden type="text" name="delete_id" value="'.$data->id.'"><input class="btn" type="submit" value="&times"></form></td>';
							echo '</tr>';
						}
					?>
			  </tbody>
		   </table>
		</div>
		<?php
			}
			?>
<?php
   }
   
   
   function add_handle_post()
   {
	   if(isset($_POST["data_source"]))
	   {
		   $setting = $_POST["data_source"];
		   $file = dirname(__file__).'/setting.txt';
		   file_put_contents($file,$setting);
	   }
	   
	    if(isset($_POST["delete_id"]))
	    {
	        global $wpdb;
    		$table_name = $wpdb->prefix . "tbl_hadees";
            $id = $_POST["delete_id"];
            $wpdb->query("DELETE FROM ".$table_name." WHERE id = '$id';");
        
	    }
		else if(isset($_POST['d_time']))
		{
		    $time = $_POST['d_time'];
            $hadees = $_FILES['hadees']['name'];
   			$ayat = $_FILES['ayat']['name'];
			$naat = $_FILES['naat']['name'];
			$quote = $_FILES['quote']['name'];
			$link= $_POST['link'];
			$link_optional= $_POST['link_optional'];
			$errorIs = false;

			$uploaded = media_handle_upload('hadees', 0);

			if(is_wp_error($uploaded))
			{
                echo "Error uploading file: ". $uploaded->get_error_message();
				$errorIs = true;
            }
			else
			{
				$hadees = $uploaded;
			}
			
			if(isset($ayat) && $ayat != "")
			{
				$uploaded = media_handle_upload('ayat', 0);

				if(is_wp_error($uploaded))
				{
					echo "Error uploading file: ". $uploaded->get_error_message();
					$errorIs = true;
				}
				else
				{
					$ayat = $uploaded;
				}
				
				
			}
   			
   			if(isset($quote) && $quote != "")
			{
				$uploaded = media_handle_upload('quote', 0);

				if(is_wp_error($uploaded))
				{
					echo "Error uploading file: ". $uploaded->get_error_message();
					$errorIs = true;
				}
				else
				{
					$quote = $uploaded;
				}
				
				
			}
			
			if(isset($naat) && $naat != "")
			{
				$uploaded = media_handle_upload('naat', 0);

				if(is_wp_error($uploaded))
				{
					echo "Error uploading file: ". $uploaded->get_error_message();
					$errorIs = true;
				}
				else
				{
					$naat = $uploaded;
				}
			}
			 
			if(!$errorIs) 
			{
				global $wpdb;
				$table_name = $wpdb->prefix . "tbl_hadees";
				//echo $id;
				$sql = "INSERT INTO $table_name (hadees, ayat, naat, link, link_optional, quote, d_time) VALUES ('$hadees', '$ayat', '$naat', '$link', '$link_optional', '$quote','$time')";
                //echo $sql;
				if($wpdb->query($sql)) 
				{
					echo 'Data Inserted Successfully';
				}
				else
				{
					echo 'Something Went Wrong While Uploading File(s). Please Try Again.';
				} 
			}
		


		}
		else if(isset($_POST['key']))
		{
			$key = $_POST['key'];
			$hadees = $_POST['hadees'];
   			$ayat = $_POST['ayat'];
			$quote = $_POST['quote'];
			$naat = $_POST['naat'];	
			$link = $_POST['link'];
			$link_optional= $_POST['link_optional'];
			
			$folder_data = $hadees.'|'.$ayat.'|'.$quote.'|'.$naat.'|'.$link.'|'.$link_optional;
			
			$key_file = dirname(__file__)."/key_file.txt";
			$folder_file = dirname(__file__)."/folder_file.txt";
			
			file_put_contents($key_file,$key);
			file_put_contents($folder_file,$folder_data);
			
			echo 'Data Inserted Successfully';
			
			
		}
   }