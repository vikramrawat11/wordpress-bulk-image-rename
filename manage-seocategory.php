<?php

				if(isset($_POST['addcategory']) && $_POST['addcategory'] == 'Add New Seo Image Category' )
					{
					
					     $categoryexist = $wpdb->get_row("select * from ".$wpdb->prefix."bisr_category where slug = '".sanitize_title($_POST['categoryname'])."'");
						  
						  if(empty($categoryexist))
						  {
								$tablecategory = $wpdb->prefix."bisr_category";
								$wpdb->insert( 
										$tablecategory, 
										array( 
											'name' => $_POST['categoryname'],
											'slug' => sanitize_title($_POST['categoryname']),
											'description' => $_POST['description'],
											'keywords' => $_POST['category_keywords'],
											
										), 
										array( 
										
											'%s',
											'%s',
											'%s',
										) 
										);
										
										$_POST['categoryname'] = "";
										$_POST['description'] = "";
										$_POST['category_keywords'] = "";
										
								  $message = "Image SEO Category Added Successfully.";
						  
						  }
						  else
						  {
						     $error = "Seo Image Category Already Exist.";
						  }
						  
						
				  }
				  
				  
				   

          
			   ?><?php    
				   
              

    $SeoImageCategoryTable = new SeoImageCategory_List_Table();

    $SeoImageCategoryTable->prepare_items();




   

    ?>

<div class="wrap">



    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>

    <h2><?php _e('Image SEO Categories', 'seo_category_table')?> 

    </h2>
<div>
    <?php
	if($_GET['message'] || $message )
	echo '<div class="updated"><p>Image SEO Category Updated Successfully.</p></div>';
	if($_GET['error'] || $error  )
	echo '<div class="updated"><p class="bisr_error">Image SEO Category Already Exist.</p></div>';
		if($_GET['deletemessage'])
	echo '<div class="updated"><p>Image SEO Category Deleted Successfully.</p></div>';
	?>
</div>

    <form id="seo_category_table" method="GET">

        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>"/>

        <?php $SeoImageCategoryTable->display(); ?>

    </form>



</div>
		
			    <div class="wrap">
				   <div id="icon-tools" class="icon32"></div>
				   <h2>Add Image SEO Category</h2>
				  
				   <form method="post" action="" enctype="multipart/form-data">
				   <table class="form-table">
					
					
						 <tr>
						<th scope="row"><label for="categoryname">Category Name :</label> *</th>
						<td><input type="text" name="categoryname" required value="<?php echo $_POST['categoryname']; ?>"></td>
						</tr>
						 <tr>
						<th scope="row"><label for="description">Description :</label></th>
						<td><textarea name="description" rows="5" cols="40"><?php echo $_POST['description']; ?></textarea></td>
						</tr>
						<tr>
						<th scope="row"><label for="Keywords">Keywords </label>(1 word per line) : </th>
						<td>
						<?php
						                                $content = $_POST['category_keywords'];
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
				   <p class="submit"><input type="submit" value="Add New Seo Image Category" class="button button-primary" id="submit" name="addcategory"></p>
				   
				   </form>
				   </div>
				
			   <?php
class SeoImageCategory_List_Table extends WP_List_Table

{

    function __construct()

    {

        global $status, $page;



        parent::__construct(array(

            'singular' => 'link',

            'plural' => 'link',

        ));

    }



    function column_default($item, $column_name)

    {

        return $item[$column_name];

    }



    function column_name($item)

    {
        $actions = array(

            'edit' => sprintf('<a href="?action=edit&page=bisr-category&id=%s">%s</a>', $item['id'], __('Edit', 'seo_category_table')),

            'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Delete', 'seo_category_table')),

        );



        return sprintf('%s %s','<a href="?action=edit&page=bisr-category&id='.$item['id'].'">'

            .$item['name'].'</a>',

            $this->row_actions($actions)

        );

		

    }



    function column_cb($item)

    {

        return sprintf(

            '<input type="checkbox" name="id[]" value="%s" />',

            $item['id']

        );

    }



    function get_columns()

    {

        $columns = array(

            'cb' => '<input type="checkbox" />',

            'name' => __('Category Name', 'seo_category_table'),
			
			'description' => __('Description', 'seo_category_table'),
			
			'when_added' => __('Date', 'seo_category_table'),
			
			

        );

        return $columns;

    }

  function column_when_added($item)
  {
    echo date('Y/m/d',strtotime($item['when_added']));
  }

    function get_sortable_columns()

    {

        $sortable_columns = array(


            'name' => array('Category Name', false),

            'description' => array('Description', true),
			
			'when_added' => array('Date', true)


        );

        return $sortable_columns;

    }



    function get_bulk_actions()

    {

        $actions = array(

            'delete' => 'Delete'

        );

        return $actions;

    }



   
   



    function prepare_items()

    {

        global $wpdb;

        $table_name = $wpdb->prefix .'bisr_category'; 

        $per_page = 10;



        $columns = $this->get_columns();

        $hidden = array();

        $sortable = $this->get_sortable_columns();



        $this->_column_headers = array($columns, $hidden, $sortable);



        $this->process_bulk_action();



        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");



        $paged = isset($_REQUEST['paged']) ? ($_REQUEST['paged']-1)*$per_page : 0;

        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';

        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

		
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT id,name,slug,description,when_added FROM $table_name ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);



        $this->set_pagination_args(array(

            'total_items' => $total_items,

            'per_page' => $per_page,

            'total_pages' => ceil($total_items / $per_page)

        ));

    }

}		
			
?>