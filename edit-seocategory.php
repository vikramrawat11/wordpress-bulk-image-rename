<?php
//phpinfo();
 if(isset($_POST['updatecategory']) && $_POST['updatecategory'] == 'Update Image SEO Category' )
					{
					
					     $categoryexist = $wpdb->get_row("select * from ".$tablecategory." where slug = '".sanitize_title($_POST['categoryname'])."' and id != ".$_GET['id']."");
					
						
						  if(empty($categoryexist))
						  {
							
										$wpdb->update( 
								$tablecategory, 
								array( 
									'name' => $_POST['categoryname'],
									'slug' => sanitize_title($_POST['categoryname']),
								    'description' => $_POST['description'],
									'keywords' => $_POST['category_keywords'],
								), 
								array( 'id' => $_GET['id'] ), 
								array( 
								
									'%s',
									'%s',
									'%s',
									'%s',
								),
								array( '%d' )
								);
										$_POST['categoryname'] = "";
										$_POST['description'] = "";
										
								  $success = "Image SEO Category Updated Successfully.";
								  echo '<script type="text/javascript">
                                        window.location ="'.admin_url('/upload.php?page=bisr-category&message=1').'";
                                       </script>';
						  
						  }
						  else
						  {
						     $error = "Image SEO Category Already Exist.";
							  echo '<script type="text/javascript">
                                        window.location ="'.admin_url('/upload.php?page=bisr-category&error=1').'";
                                       </script>';
						  }
						  
						
				  }
				
				
			 
			   $categoryinfo = $wpdb->get_row("select * from ".$wpdb->prefix."bisr_category where id = '".$_GET['id']."'");
			 ?>
			 <div>
			   <div class="wrap" id="edit_category">
				   <div id="icon-tools" class="icon32"></div>
				   <h2>Edit Image SEO Category</h2>
				   <?php 
				   if($error)
				   echo '<div class="updated"><p class="bisr_error">'.$error.'</p><p><a href="upload.php?page=bisr-category">Back to Image SEO Categories</a></p></div>';
				   if($success)
				   echo '<div class="updated"><p>'.$success.'</p><p><a href="upload.php?page=bisr-category">Back to Image SEO Categories</a></p></div>';
				   
				   if(empty($success) && empty($error))
				   echo '<p><a href="upload.php?page=bisr-category">Back to Image SEO Categories</a></p>';
				   ?>
				   <form method="post" action="" enctype="multipart/form-data">
				   <table class="form-table">
					
					
						 <tr>
						<th scope="row"><label for="categoryname">Category Name :</label> *</th>
						<td><input type="text" name="categoryname" required value="<?php echo $categoryinfo->name; ?>"></td>
						</tr>
						 <tr>
						<th scope="row"><label for="description">Description :</label></th>
						<td><textarea name="description" rows="5" cols="40"><?php echo $categoryinfo->description; ?></textarea></td>
						</tr>
						
						<tr>
						<th scope="row"><label for="Keywords">Keywords </label>(1 word per line) : </label></th>
						<td>
						<?php
						                                $content = $categoryinfo->keywords;
													$editor_id = 'category_keywords';
														$settings = array(
														     'wpautop' =>false,
															'textarea_rows' => 15,
														);
													wp_editor( $content, $editor_id, $settings );
						 ?>
						</td>
						</tr>
						
					 </table>
				   <p class="submit"><input type="submit" value="Update Image SEO Category" class="button button-primary" id="submit" name="updatecategory"></p>
				   
				   </form>
				   </div>
				   