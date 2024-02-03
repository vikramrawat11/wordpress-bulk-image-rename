<?php
				    $tablecategory = $wpdb->prefix."bisr_category";
				   if(isset($_POST['deleteategory']) && $_POST['deleteategory'] == 'Yes (Delete)')
				   {
				       if (!empty($_POST['delete_categoryids'])) {

                        $wpdb->query("DELETE FROM $tablecategory WHERE id IN(".$_POST['delete_categoryids'].")");
						 echo '<script type="text/javascript">
                                        window.location ="'.admin_url('/upload.php?page=bisr-category&deletemessage=1').'";
                                       </script>';
						 $success = "Image SEO Category Deleted Successfully.";

                      }
				   }
				   
				    $ids = isset($_REQUEST['id']) ? $_REQUEST['id'] : array();
				     $total_ids = count($ids);
				   if(is_array($ids))
				   $ids = implode(',', $ids);
				   
				   $category_label = "";
				   $heading_label = "";
				   if($total_ids == 1)
				   {
				   $heading_label = "Category";
				   $category_label = "this category";
				   $keyword_label  = "This will also delete the keyword list assigned to it.";
				   }
				   else
				   {
				    $heading_label = "these categories";
				   $category_label = "these categories";
				   $keyword_label  = "This will also delete the keyword lists assigned to them.";
				   }
				   ?>
				 
				     <div class="wrap">
				   <div id="icon-tools" class="icon32"></div>
				   <h2>Delete Image SEO <?php echo $heading_label;?></h2>
				
				   <br/>
				     <form method="post" action="" enctype="multipart/form-data">
					
					 <input type="hidden" name="delete_categoryids" value="<?php echo $ids ?>" />
					 <?php 
					
					     $allcategory= $wpdb->get_results("select name from ".$tablecategory." WHERE id IN($ids) " );
						$category_name = "";
						if($allcategory)
						{
						  foreach( $allcategory as $category)
						  $category_name .= $category->name.",";
						
						}
					  ?>
					  <?php
				   if($error)
				   echo '<div class="updated"><p class="bisr_error">'.$error.'</p><p><a href="upload.php?page=bisr-category">Back to Image SEO Categories</a></p></div>';
				   if($success)
				   echo '<div class="updated"><p>'.$success.'</p><p><a href="upload.php?page=bisr-category">Back to Image SEO Categories</a></p></div>';
				   
				   if(empty($success) && empty($error))
				   {
				   echo '<p><a href="upload.php?page=bisr-category">Back to Image SEO Categories</a></p>';?>
				   
				  
				   <div class="delete_confirm"><p>Are you sure you wish to delete  <?php echo $category_label;?>? <span class="seocategory_name">(<?php echo rtrim($category_name,","); ?>)</span>
				   <br/>
				   <?php  echo  $keyword_label ; ?>
				   </p>
				   
				  
					  <p class="submit"><input type="submit" value="Yes (Delete)" class="button button-primary" id="submit" name="deleteategory">
					
					  <a href="upload.php?page=bisr-category" class="delete_keep">No (Keep)</a>
					  </p>
				   </div>	
				  
						   
				   </form>
				   <?php } ?>