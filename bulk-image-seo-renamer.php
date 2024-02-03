<?php
/**
 * Plugin Name: Bulk Image SEO Renamer
 * Plugin URI: 
 * Description: This plugin allows you to bulk rename images in your Media Library to SEO optimised keywords of your choice.
 * Version: 2.0
 * Author: Vikram Rawat
 */
 if(!class_exists('BulkImageSeoRename'))
{
 class BulkImageSeoRename {
			public function __construct()
			{
			  ini_set('memory_limit','512M');
			  ini_set('max_execution_time', 600);
			
			   register_activation_hook( __FILE__, array( $this, 'bisr_register_activation' ) );
			   add_action('admin_menu', array($this,'bisr_media_menu'));
			   add_action('admin_head' , array($this, 'bisr_load_css'));
			   add_action('admin_footer', array($this,'bisr_bulk_admin_footer'));
			   add_action( 'load-upload.php',  array($this,'rename_media_bisr_action') );
			   add_action( 'admin_notices',  array($this,'rename_media_bisr_notices') );
				
			   
			}
			 public function bisr_load_css()
            {
        	
			wp_enqueue_style( 'bisr_style', plugins_url('bulkseoimage.css', __FILE__) ); 
			
		    } //
			public function bisr_register_activation() {
			
					global $wpdb;
					require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
						
					$bisr_category = "CREATE TABLE `".$wpdb->prefix."bisr_category` (
											`id` int(11) NOT NULL AUTO_INCREMENT,
											`name` varchar(255) NOT NULL,
											`slug` varchar(255) NOT NULL,
											`description` text DEFAULT NULL,
											`keywords` text DEFAULT NULL,
											 `when_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,						
											 PRIMARY KEY (`id`)
											) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
					dbDelta( $bisr_category );
					
		      }	
     
		   public  function bisr_media_menu() {
			   add_media_page('SEO Categories', 'SEO Categories', 'manage_options', 'bisr-category', array($this,'bisr_imagecategory_page'));
			  
			}
			
	
		
		
		public function bisr_bulk_admin_footer()
		{
		  global $pagenow;
	if($pagenow == 'upload.php' && !isset($_GET['page'])) {
		?>
		<script type="text/javascript">
		var opt = {
                        'Bulk SEO Rename':[
						<?php   
						 global $wpdb;
			  $table_seocategory = $wpdb->prefix .'bisr_category'; 
			  $all_socategory = $wpdb->get_results("select id,name from ".$table_seocategory."");
			  $seocategory_option = "";
			  if($all_socategory)	
			  {
			      foreach($all_socategory as $seocategory)
				{
						?>
						 {name:"bulkseorename_<?php echo $seocategory->id ?>", value: "<?php echo $seocategory->name ?>"},
						 
					 <?php
				}
					 
			  }
					 ?>
                            ],
                   };
			jQuery(document).ready(function() {
			
			
    jQuery.each(opt, function(key, value){
        var group = jQuery('<optgroup label="' + key + '" />');
        jQuery.each(value, function(){
 			jQuery('<option>').val(this.name).text(this.value).appendTo(group);
        });
        group.appendTo("select[name='action']");
    });
				

			});
		</script>
		<?php
	}
		}
	
		public function rename_media_bisr_action()
		 {
             if ( !isset( $_REQUEST['detached'] ) ) {

	
			// get the action
			$wp_list_table = _get_list_table('WP_Media_List_Table');  
			$action = $wp_list_table->current_action();
		
			 "\naction = $action\n</pre>";
			$newaction =  explode("_",$action);
		
			$allowed_actions = array("bulkseorename");
			if(!in_array($newaction[0], $allowed_actions)) return;
		
			// security check
		    //  ***check_admin_referer('bulk-posts'); REPLACE WITH:
			check_admin_referer('bulk-media'); 
		
			// make sure ids are submitted.  depending on the resource type, this may be 'media' or 'ids'
			if(isset($_REQUEST['media'])) {
			  $post_ids = array_map('intval', $_REQUEST['media']);
			}
		
			if(empty($post_ids)) return;

			// this is based on wp-admin/edit.php
			$sendback = remove_query_arg( array('exported', 'untrashed', 'deleted', 'ids'), wp_get_referer() );
			if ( ! $sendback )
			  $sendback = admin_url( "upload.php?post_type=$post_type" );
		
			$pagenum = $wp_list_table->get_pagenum();
			$sendback = add_query_arg( 'paged', $pagenum, $sendback );
		
			switch($newaction[0]) {
			  case 'bulkseorename':

						$renamedcounter = 0;
						$leftrenamecounter = 0;
						foreach( $post_ids as $post_id ) {
				
						  if ($this->bisr_image_rename($post_id,$newaction[1]) )
						  $renamedcounter++;
						else
						  $leftrenamecounter++;
				              }
							
						$sendback = add_query_arg( array('renamed' => $renamedcounter,'unchange' => $leftrenamecounter,'catid'=>$newaction[1], 'ids' => join(',', $post_ids) ), $sendback );
					  break;
				
					  default: return;
					}
				
					$sendback = remove_query_arg( array('action', 'action2','unchange','paged', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback );
			
					wp_redirect($sendback);
					exit();
				  }
         }
	
	   public function bisr_image_rename($post_ID,$renamecatID)
	    {
	      $post = get_post($post_ID);
          $file = get_attached_file($post_ID);
 	      $originalfile = esc_html( wp_basename( $file ) );
	      $originalfile_info = explode(".",$originalfile);
	      $originalfilename = $originalfile_info[0];
          $path = pathinfo($file);
        //dirname   = File Path
        //basename  = Filename.Extension
        //extension = Extension
        //filename  = Filename
		
		     global $wpdb;
			   $table_seocategory = $wpdb->prefix .'bisr_category'; 
			   $socategory_keywords = $wpdb->get_row("select keywords from ".$table_seocategory." where id = ".$renamecatID."");
			  //echo trim($socategory_keywords->keywords);
               $socategory_keywords = str_replace('</p>', ',',trim($socategory_keywords->keywords));
			   $socategory_keywords = strip_tags($socategory_keywords);
			   $socategory_keywordsarray = explode(',', rtrim($socategory_keywords,","));
			  
			   $socategory_filenames = $this->getCombinationsof_Keywords($socategory_keywordsarray);
			 
			 
			
		                  $counter_renamepost = 0;
							foreach($socategory_filenames as $newfilename)
				            {
							
							 $imagetitle= ucwords(str_replace("-"," ",$newfilename));    
							 $newfilename = strtolower($newfilename);
							  if(!empty($newfilename) && !in_array($originalfilename,$socategory_filenames))
							  {
									 if ( file_exists(  $path['dirname']."/".$newfilename.".".$path['extension']) ) 
									 {
									     continue;
									 }
									 else
									  {
										   $searches =  $this->bisr_get_attachment_urls($post_ID);
										   $newfile = $path['dirname']."/".$newfilename.".".$path['extension'];
		                                    
											rename($file, $newfile);    
											update_attached_file( $post_ID, $newfile );
											update_post_meta($post_ID,'_wp_attachment_image_alt',$newfilename);
											
											 $allimages_metadata = wp_get_attachment_metadata($post_ID);
											 
											 if(!empty($allimages_metadata))
											 {
											 
											   foreach($allimages_metadata['sizes'] as $image_metadata)
											   {
											  
												 if($image_metadata['file'])
												 unlink($path['dirname']."/".$image_metadata['file']);
											   }
											 }
										   
										 	if ($attach_data = wp_generate_attachment_metadata( $post_ID, $newfile)) {
                                                   wp_update_attachment_metadata($post_ID, $attach_data);
                                                 }
												$image_post = array(
												  'ID'           => $post_ID,
												  'post_title'   => $imagetitle,
												  'post_name'   => $newfilename,
											  );

								                // Update the post into the database
								                   wp_update_post( $image_post );
												   $counter_renamepost++;
												   
												   // replacing image name in  all post or page
												   // Replace the old with the new media link in the content of all posts and metas
										$replaces =  $this->bisr_get_attachment_urls($post_ID);
								
										$i = 0;
										$post_types = get_post_types();
										unset( $post_types['attachment'] );
										
										while ( $posts = get_posts(array( 'post_type' => $post_types, 'post_status' => 'any', 'numberposts' => 100, 'offset' => $i * 100 )) ) {
											foreach ($posts as &$post) {
												// Updating post content if necessary
												$new_post = array( 'ID' => $post->ID );
												$new_post['post_content'] = str_replace($searches, $replaces, $post->post_content);
												if ($new_post['post_content'] != $post->post_content) 
												 wp_update_post($new_post);
												
												// Updating post metas if necessary
												$metas = get_post_meta($post->ID);
												foreach ($metas as $key => $meta) {
													$meta[0] =  $this->bisr_unserialize_deep($meta[0]);
													$new_meta =  $this->bisr_replace_media_urls($meta[0], $searches, $replaces);
													if ($new_meta != $meta[0]) 
													update_post_meta($post->ID, $key, $new_meta, $meta[0]);
												}
											}
								
											$i++;
										}
								
										// Updating options if necessary
										$options =  $this->bisr_get_all_options();
										foreach ($options as $option) {
											$option['value'] = $this->bisr_unserialize_deep($option['value']);
											$new_option =  $this->bisr_replace_media_urls($option['value'], $searches, $replaces);
											if ($new_option != $option['value'])
											 update_option($option['name'], $new_option);
										}
																				   
																			   return true;
																		 
								 }
														
															   }
												 
				 }

    
	
	
	}	
	// Get all options
	 function bisr_get_all_options() {
	  global $wpdb;
		return $wpdb->get_results("SELECT option_name as name, option_value as value FROM {$wpdb->options}", ARRAY_A);
	}
	   
	  // Returns the attachment URL and sizes URLs, in case of an image
	  function bisr_get_attachment_urls($attachment_id) {
		$urls = array( wp_get_attachment_url($attachment_id) );
		if ( wp_attachment_is_image($attachment_id) ) {
			foreach (get_intermediate_image_sizes() as $size) {
				$image = wp_get_attachment_image_src($attachment_id, $size);
				$urls[] = $image[0];
			}
		}

		return array_unique($urls);
	}
	
	
	// Unserializes a variable until reaching a non-serialized value	
	 function bisr_unserialize_deep($var) {
		while ( is_serialized($var) ) {
			$var = unserialize($var);
		}

		return $var;
	}
	
	
	// Replace the media url and fix serialization if necessary
	 function bisr_replace_media_urls($subj, &$searches, &$replaces) {
		$subj = is_object($subj) ? clone $subj : $subj;

		if (!is_scalar($subj) && @count($subj)) {
			foreach($subj as &$item) {
				$item = $this->bisr_replace_media_urls($item, $searches, $replaces);
			}
		} else {
			$subj = is_string($subj) ? str_replace($searches, $replaces, $subj) : $subj;
		}
		
		return $subj;
	}
	
       public function getCombinationsof_Keywords($array) {

        //initalize array
        $results = [[]];

        //get all combinations
        foreach ($array as $k => $element) {
            foreach ($results as $combination)
                $results[] =  $combination + [$k => strtolower(trim($element))];
        }

        //return filtered array
          $allcombination =  array_values(array_filter($results));
		   foreach($allcombination as $v)
		   {
		  
		    $all_filename[] = implode("-", $v);
		   }
		  
		   return $all_filename;

    }
function rename_media_bisr_notices() {
    global $post_type, $pagenow;




    if($pagenow == 'upload.php' && isset($_REQUEST['renamed'])) {
	
        $message = sprintf( _n( 'Media attachments renamed.', '%d Media attachments renamed.', $_REQUEST['renamed'] ), number_format_i18n( $_REQUEST['renamed'] ) );
		
		if(number_format_i18n( $_REQUEST['renamed']) == 1 )
		$message = "".number_format_i18n( $_REQUEST['renamed'] )." Media attachment renamed.";
		else
		$message = "".number_format_i18n( $_REQUEST['renamed'] )." Media attachments renamed.";
		
		/*
		if($_REQUEST['unchange'] > 0 && number_format_i18n( $_REQUEST['renamed'] > 0))
		$message .= " and ".number_format_i18n( $_REQUEST['unchange'] )." Media attachments unchange.";*/
		
	      $postids = 	explode(",",$_REQUEST['ids']);
		   $remaing_images = (count($postids)-$_REQUEST['renamed']);
	      if($remaing_images > 0)
		  {
		     global $wpdb;
			 $table_seocategory = $wpdb->prefix .'bisr_category'; 
			  $socategory_keywords = $wpdb->get_row("select name from ".$table_seocategory." where id = ".$_REQUEST['catid']."");
			  
			  if($remaing_images == 1)
			  $message .= " ".$remaing_images." image was not renamed due to the lack amount of keywords provided for the <a href ='?action=edit&page=bisr-category&id=".$_REQUEST['catid']."'>".$socategory_keywords->name." </a>category.";
			  else
		    $message .= " ".$remaing_images." images were not renamed due to the lack amount of keywords provided for the <a href ='?action=edit&page=bisr-category&id=".$_REQUEST['catid']."'>".$socategory_keywords->name." </a>category.";
		  }
        echo "<div class=\"updated\"><p>{$message}</p></div>";
    }
}
	public function bisr_imagecategory_page()
			{
			
			        $error = "";
					$success = "";
					global $wpdb;
					
					
					$bisr_action = $_GET['action'];
					
					if(isset($_GET['action2']) && $_GET['action2'] == 'delete')
					$bisr_action = $_GET['action2'];
					
					switch ($bisr_action) {
						case 'edit':
								include_once('edit-seocategory.php');
								break;
								
						case 'delete':
								include_once('delete-seocategory.php');
								break;
								
					    case 'editkeyword':
								include_once('edit-seokeywords.php');
								break;		
								
					    default:
						   include_once('manage-seocategory.php');
						
			        }
			   
			
			
	}	
		
}

$BulkImageSeoRename = new BulkImageSeoRename();

}
if (!class_exists('WP_List_Table')) {

    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');

}


?>