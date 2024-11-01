<?php

	
class Today_Gift_Widget extends WP_Widget {

	// Main constructor
	public function __construct() {
		parent::__construct(
			'today_gift_init',
			__( 'Today Gift Widget', 'text_domain' ),
			array(
				'customize_selective_refresh' => true,
			)
		);
		
	}

	// The widget form (for the backend )
	public function form( $instance ) {

		// Set widget defaults
		$defaults = array(
			'title'    => '',
			'text'     => '',
		);
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<?php // Widget Title ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php // Text Field ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php _e( 'Text:', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" value="<?php echo esc_attr( $text ); ?>" />
		</p>

	<?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['text']     = isset( $new_instance['text'] ) ? wp_strip_all_tags( $new_instance['text'] ) : '';
		return $instance;
		
	}

	// Display the widget
	public function widget( $args, $instance ) {
		extract( $args );

		// Check the widget options
		$title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$text     = isset( $instance['text'] ) ? $instance['text'] : '';

		// WordPress core before_widget hook (always include )
		echo $before_widget;

	   // Display the widget
	   echo '<div class="widget-text wp_widget_plugin_box">';

			// Display widget title if defined
			if ( $title ) {
				echo $before_title .$title ." <img src='".plugin_dir_url( __FILE__ )."/gift.png' width='20' height='20'> ". $after_title;
			}

			// Display text field
			if ( $text ) 
			{
				echo '<a href="today-gift" class="btn">' . $text . '</a>';
			}

		echo '</div>';

		// WordPress core after_widget hook (always include )
		echo $after_widget;
		
	}
}
function today_gift_page()
{
	$detailPageId = get_option('today_gift_details_page_id');
	register_widget( 'Today_Gift_Widget' );
	global $post;
	if($post != null && $post->ID == $detailPageId)
	{
		$file = dirname(__file__).'/setting.txt';
				$setting = "";
				if (file_exists($file)) 
				{
					$setting = file_get_contents($file);
				}
				//echo $setting;	
				if($setting =="database" || $setting="")
				{
					global $wpdb;
			
					$table_name = $wpdb->prefix . "tbl_hadees";
					$id = Date('Y-m-d');
					$results = $wpdb->get_results( "SELECT * FROM $table_name where d_time = '$id' limit 1", OBJECT );
					$uploads = wp_upload_dir();
							
					if($wpdb->num_rows >0)
					{
						foreach($results as $data)
						{
		
?>
	
						<div class="col-sm-12">
						
							<div class="row">
							    
							    <?php if($data->ayat != ""){?>
								<div class="col-sm-12">
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Ayat</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<a href="#"><img id="myImg2" style="width:100%;  height:100%;" src="<?php echo wp_get_attachment_url($data->ayat);?>"/></a>
										</div>
									</div>
								</div>
								<?php } ?>
								
								<?php if($data->hadees != ""){?>
								<div class="col-sm-12">
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Hadees</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<a href="#"><img id="myImg" style="width:100%;  height:100%;"  src="<?php echo wp_get_attachment_url($data->hadees); ?>"/></a>
										</div>
									</div>
								</div>
								<?php } ?>
								
								
								<?php if($data->quote != ""){?>
								<div class="col-sm-12">
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Quote</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<a href="#"><img id="myImg3" style="width:100%;  height:100%;"  src="<?php echo wp_get_attachment_url($data->quote); ?>"/></a>
										</div>
									</div>
								</div>
								<?php } ?>
								
								
								
								<?php if($data->link!= ""){?>
								<div class="col-sm-12" style="overflow: hidden; white-space: nowrap;text-overflow: ellipsis;">
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Video</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<iframe style="width:100%;  height:100%;" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen src="<?php echo $data->link;?>">
											</iframe>
										</div>
									</div>
								</div>
								<?php } ?>
							</div>
							
							
							<div class="row">
							
								<div class="col-sm-12" >
									<?php if($data->naat!= ""){?>
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Audio</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<audio style="width:100%;" controls><source src="<?php echo wp_get_attachment_url($data->naat);?>" type="audio/mpeg"></audio>
										</div>
									</div>
									<?php } ?>
									<?php if($data->link_optional!= ""){?>
									<div class="form-group" >
										<header class="home-post-header"><h1 class="home-post-title">Useful Link</h1></header>
										<div class="col-sm-12" style="overflow: hidden; white-space: nowrap;text-overflow: ellipsis;padding:10px;">
											<a target="_blank" href="<?php echo $data->link_optional;?>" ><?php echo $data->link_optional;?></a>
										</div>
									</div>
									<?php } ?>
								</div>
								
								
								
							</div>
						</div>
<?php
						}
							
					}
					else
					{
?>					<div class="col-sm-12">
							<div class="row">
								<div class="col-sm-12">
									<img style="width:100%;  height:100%;" src="https://i2.wp.com/www.northstarcharter.org/wp-content/uploads/2018/01/coming-soon.png?fit=791%2C359">
								</div>
							</div>					
						</div>
<?php					
					}
				}
				else
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
						
					$hadees_file = dirname(__file__)."/hadees.txt";
					$ayat_file = dirname(__file__)."/ayat.txt";
					$quote_file = dirname(__file__)."/quote.txt";
					$naat_file = dirname(__file__)."/naat.txt";
					$link_file = dirname(__file__)."/link.txt";
					$link_optional_file = dirname(__file__)."/link_optional.txt";
					$id = Date('d-m');
					if (file_exists($naat_file)) 
					{
						$hadees = json_decode(file_get_contents($hadees_file),true);
						$ayats = json_decode(file_get_contents($ayat_file),true);
						$quotes = json_decode(file_get_contents($quote_file),true);
						$naats = json_decode(file_get_contents($naat_file),true);
						$links = json_decode(file_get_contents($link_file),true);
						$link_optional = json_decode(file_get_contents($link_optional_file),true);
							

						$h = "";
						$a = "";
						$q = "";
						$n = "";
						$l = "";
						$l_p = "";
						if(isset($hadees))
						{
							foreach($hadees as $ob)
							{
								$path_parts = pathinfo($ob["name"]);
								if($id == $path_parts['filename'])
								{
									$h = $ob["id"];
									//break;
								}
							}
							
						}
						if(isset($ayats))
						{
							foreach($ayats as $ob)
							{
								$path_parts = pathinfo($ob["name"]);
								if($id == $path_parts['filename'])
								{
									$a = $ob["id"];
									//break;
								}
							}
						}
						if(isset($quotes))
						{
							foreach($quotes as $ob)
							{
								$path_parts = pathinfo($ob["name"]);
								if($id == $path_parts['filename'])
								{
									$q = $ob["id"];
									//break;
								}
							}
						}
						
						if(isset($naats))
						{
							foreach($naats as $ob)
							{
								$path_parts = pathinfo($ob["name"]);
								if($id == $path_parts['filename'])
								{
									$n = $ob["id"];
									//break;
								}
							}
						}
						if(isset($links))
						{
							foreach($links as $ob)
							{
								$path_parts = pathinfo($ob["name"]);
								if($id == $path_parts['filename'])
								{
									$l = file_get_contents('https://drive.google.com/uc?export=download&id='.$ob["id"]) ;
									//break;
								}
							}
						}
						if(isset($link_optional))
						{
							foreach($link_optional as $ob)
							{
								$path_parts = pathinfo($ob["name"]);
								if($id == $path_parts['filename'])
								{
									$l_p = file_get_contents('https://drive.google.com/uc?export=download&id='.$ob["id"]) ;
									//break;
								}
							}
						}
?>
						
						<div class="col-sm-12">
						
							<div class="row">
							    <?php if($a !=""){?>
								<div class="col-sm-12">
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Ayat</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<a href="#"><img id="myImg2" style="width:100%;  height:100%;" src="https://drive.google.com/uc?export=download&id=<?php echo $a;?>"/></a>
										</div>
									</div>
								</div>
								<?php } ?>
								<?php if($h !=""){?>
								<div class="col-sm-12">
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Hadees</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<a href="#"><img id="myImg"  style="width:100%;  height:100%;" src="https://drive.google.com/uc?export=download&id=<?php echo $h;?>"/></a>
										</div>
									</div>
								</div>
								<?php } ?>
								
                                <?php if($q !=""){?>
								<div class="col-sm-12">
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Quote</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<a href="#"><img id="myImg3"  style="width:100%;  height:100%;" src="https://drive.google.com/uc?export=download&id=<?php echo $q;?>"/></a>
										</div>
									</div>
								</div>
								<?php } ?>
								
								<?php if($l !=""){?>
								<div class="col-sm-12" style="overflow: hidden; white-space: nowrap;text-overflow: ellipsis;">
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Video</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<iframe style="width:100%;  height:100%;" src="<?php echo $l;?>">
											</iframe>
										</div>
									</div>
								</div>
								<?php } ?>
							</div>
							
							
							<div class="row">
							
								<div class="col-sm-12">
									<?php if($n !=""){?>
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Audio</h1></header>
										<div class="col-sm-12" style="padding:10px;">
											<audio style="width:100%;" controls><source src="https://drive.google.com/uc?export=download&id=<?php echo $n;?>" type="audio/mpeg"></audio>
										</div>
									</div>
									<?php } ?>
									<?php if($l_p !=""){?>
									<div class="form-group">
										<header class="home-post-header"><h1 class="home-post-title">Useful Link</h1></header>
										<div class="col-sm-12" style="overflow: hidden; white-space: nowrap;text-overflow: ellipsis;padding:10px;">
											<a target="_blank" href="<?php echo $l_p;?>" ><?php echo $l_p;?></a>
										</div>
									</div>
									<?php } ?>
								</div>
								
								
							</div>
							
												
						</div>
	<?php							
					}
				}
	}

?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<div id="myModal" class="modal" style="display: none;position: fixed;z-index: 9999;padding-top:100px;left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgb(0,0,0); background-color: rgba(0,0,0,0.9);">

	  <!-- The Close Button -->
	  <span class="close" style="position: absolute; top: 45px; right: 35px; color:white !important; font-size: 100px; font-weight: bold; transition: 0.3s;">&times;</span>

	  <!-- Modal Content (The Image) -->
	  <img class="modal-content" id="img01" style="margin: auto;display: block; width: 80%; height: 80%; max-width: 700px; animation-name: zoom; animation-duration: 0.6s;">
	</div>
	
	<script>
	
	$(document).ready(function(){
		// Get the modal
		var modal = document.getElementById('myModal');
		if(document.getElementById('myImg') !== null)
		{
			// Get the image and insert it inside the modal - use its "alt" text as a caption
			var img = document.getElementById('myImg');
			var modalImg = document.getElementById("img01");
			img.onclick = function(){
			  modal.style.display = "block";
			  modalImg.src = this.src;
			}
		}
		
		if(document.getElementById('myImg2') !== null)
		{
			var myImg2 = document.getElementById('myImg2');
			myImg2.onclick = function(){
			  modal.style.display = "block";
			  modalImg.src = this.src;
			}
		}

        if(document.getElementById('myImg3') !== null)
		{
			var myImg3 = document.getElementById('myImg3');
			myImg3.onclick = function(){
			  modal.style.display = "block";
			  modalImg.src = this.src;
			}
		}
		
		// Get the <span> element that closes the modal
		var span = document.getElementsByClassName("close")[0];

		// When the user clicks on <span> (x), close the modal
		span.onclick = function() { 
		  modal.style.display = "none";
		}

	});

	</script>
	
<?php	
}
add_action( 'widgets_init', 'today_gift_page' );