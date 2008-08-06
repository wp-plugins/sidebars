<?php
/*
Plugin Name: Sidebars
Plugin URI: http://www.nexterous.com/plugins/sidebars/
Description: Create sidebars through an admin interface and apply widgets. Using conditions, apply the sidebars to certain pages / posts depending on what you select.
Version: 1.0
Author: Daniel
Author URI: http://www.nexterous.com

Dependencies: ExtendWP API (www.nexterous.com/scripts/extendwp/)
*/

# Define the initial constants / variables
define('sidebars_table', $wpdb->prefix . 'sidebars');
define('sidebars_edit', '../wp-content/plugins/sidebars/edit-sidebars.php');
define('extendwp_path', '../wp-content/plugins/sidebars/extendwp.php');

# Load ExtendWP API (www.nexterous.com/scripts/extendwp/) if it is not already set
add_action('admin_head', 'load_extendwp');
function load_extendwp(){
	require_once(sidebars_edit);
	if(!is_object($extend)){
		$extend = TRUE;
		GLOBAL $extend;
		require(extendwp_path);
		$extend = new extend;
	}
}

# Install the tables and make sure everything is in orders
add_action('admin_head', 'sidebars_install');
function sidebars_install(){
	GLOBAL $extend; 
	if($extend->check_page('plugins.php')){
		GLOBAL $wpdb; $table = sidebars_table;
		if($wpdb->get_var("SHOW TABLES LIKE '" . sidebars_table . "'") != $table) {
			$sql = "CREATE TABLE `$table` (`sidebar` smallint(6) NOT NULL auto_increment, `slug` varchar(20) collate utf8_unicode_ci NOT NULL, `description` tinytext collate utf8_unicode_ci NOT NULL, `lastedited` timestamp NOT NULL default CURRENT_TIMESTAMP, `condition` enum('notset','default','category','parent','id') collate utf8_unicode_ci NOT NULL default 'notset', `limit` tinyint(4) NOT NULL, PRIMARY KEY  (`sidebar`), KEY `slug` (`slug`), KEY `limit` (`limit`)) ENGINE=MyISAM";
			$wpdb->query($sql);
			$sql = "INSERT INTO `" . sidebars_table . "` VALUES (1, 'default', 'This sidebar is enabled by default through plugin activation. You may delete and edit this sidebar.', CURRENT_TIMESTAMP, 'default', 0)";
			$wpdb->query($sql);
		}
	}
}

# Place JQuery scripts on correct pages
add_action('admin_head', 'jquery');
function jquery(){
	GLOBAL $extend; 
	if($extend->check_page('manage-rules.php')){
		wp_enqueue_script('jquery');
		$pluginurl = get_option('siteurl') . '/wp-content/plugins/sidebars/script.js';
		echo "<script type='text/javascript' src='$pluginurl'></script>";
	}
}

# Hook for adding admin menus
add_action('admin_menu', 'pages');

# Action function for above hook
function pages(){
	add_menu_page('Manage Sidebars', 'Sidebars', 10, __FILE__, 'show_sidebars');
	add_submenu_page(__FILE__, 'Add / Edit Sidebars', 'Add Sidebars', 10, 'edit-sidebars.php', 'work_sidebars');
	add_submenu_page(__FILE__, 'Configure Sidebars', 'Configure Sidebars', 10, 'manage-rules.php', 'configure_sidebars');
	add_submenu_page(__FILE__, 'Add Content / Widgets', 'Add Content / Widgets', 10, 'sidebars-widgets.php', 'widgetext_sidebars');
	add_submenu_page(__FILE__, 'Sidebars API', '', 10, 'sidebars-api.php', 'api_sidebars');
}

# Function to display the sidebars
function show_sidebars(){
	GLOBAL $wpdb; GLOBAL $extend;
	$query = 'SELECT `sidebar`, `slug`, `description`, `lastedited` FROM `' . sidebars_table . '` ORDER BY `sidebar` DESC';
	$results = $wpdb->get_results($query, ARRAY_A);
	
	$results = $extend->arc($results, 'sidebar');
	$values = $extend->formulate($results, '[slug]', '<a href="admin.php?page=edit-sidebars.php&slug=[slug]">Edit</a>', 'slug');
	$results = $extend->aac($results, $values);
	$values = $extend->formulate($results, '[slug]', '<a href="admin.php?page=edit-sidebars.php&delete=[slug]">Delete</a>', 'slug');
	$results = $extend->aac($results, $values);
	
	$extend->table('Manage Sidebars', '', array('Sidebar Title', 'Description', 'Last Edited', 'Edit', 'Delete'), $results);
}

# Function to configure the sidebars
function configure_sidebars(){
	if(!empty($_POST)){
		pconfigure_sidebars($_POST);
	}
	GLOBAL $wpdb; GLOBAL $extend;
	$description = '<p>To configure the sidebars, select from the dropdown menus below. To display a sidebar, the page must match the exact condition and limit. If no sidebar matches the page, and the page is still requesting a sidebar, the default sidebar will be used. 
	<form action="admin.php?page=manage-rules.php" method="POST">';
	$select = '<select class="condition" id="[id]">
				<option value="0">Not Set / Leave</option>
				<option value="1">Default</option>
				<option value="2">Post Category</option>
				<option value="3">Page Parent</option>
				<option value="4">Is Page/Post</option>
				<option value="5">Disable</option>
			</select>';
	
	$query = 'SELECT `sidebar`, `slug`, `condition`, `limit` FROM `' . sidebars_table . '` ORDER BY `sidebar` DESC';
	$results = $wpdb->get_results($query, ARRAY_A);
	
	$values = $extend->formulate($results, '[id]', $select, 'sidebar');
	$results = $extend->aac($results, $values);
	$values = $extend->formulate($results, '[id]', '<div class="limit" id="[id]">Please set a condition first.</div>', 'sidebar');
	$results = $extend->aac($results, $values);
	$results = $extend->arc($results, 'sidebar');
	
	$values = $extend->formulate($results, '[cc]', 'Display sidebar when <strong>[cc]</strong> is <strong>[limit]</strong>', 'condition', '[limit]', 'limit');
	$results = $extend->aac($results, $values);
	$results = $extend->arc($results, 'condition');
	$results = $extend->arc($results, 'limit');
	
	$extend->table('Configure Sidebars', $description, array('Sidebar', 'Condition', 'Limit', 'Currently'), $results);
}

function widgetext_sidebars(){
	GLOBAL $extend;
	$description = '<p>To add widgets or content to the sidebars, please go to the <a href="widgets.php">Widgets Screen</a> where you can use the Wordpress built-in widget system to fill in the new sidebars with content and widgets.</p>';
	$extend->page('Add Content / Widgets', $description);
}

function api_sidebars(){
	$method = $_GET['method']; $id = $_GET['id'];
	echo "<div id='return'>\n";
	echo "<!-- Returned by JQuery into the Configure Sidebars Page -->\n";
	switch($method){
		case 0:
			echo 'Please set a condition first.';
			break;
		case 1:
			echo "This sidebar will be saved as default.\n";
			echo "<input type='hidden' name='$id' value='default-$id' /> \n";
			break;
		case 2:
?>
<select name="<?php echo $id; ?>"> 
	<option value="0"><?php echo attribute_escape(__('Select Category')); ?></option> 
<?php 
  $categories = get_categories(); 
  foreach ($categories as $cat) {
  	$option = "<option value='category-{$cat->cat_ID}'>{$cat->cat_name}</option>";
	echo $option;
  }
 ?>
</select>
<?php
			break;
		case 3:
			$pages = get_pages();
			foreach($pages as $p){
				if(is_numeric($p->post_parent) AND $p->post_parent > 0){
					$parents[] = $p->post_parent;
				}
			}
			unset($pages); $pages = array();
			if(is_array($parents)){
				$parents = array_unique($parents);
				$parents = implode(',', $parents);
				$defaults = array('child_of' => 0, 'sort_order' => 'ASC', 'sort_column' => 'post_title', 'hierarchical' => 1, 'exclude' => '', 'include' => $parents, 'meta_key' => '', 'meta_value' => '', 'authors' => '');
				$pages = get_pages($defaults);
?>
<select name="<?php echo $id; ?>"> 
	<option value="0"><?php echo attribute_escape(__('Select Parent')); ?></option> 
<?php

	foreach($pages as $page){
	  	$option = "<option value='parent-{$page->ID}'>{$page->post_title}</option>";
		echo $option;
	}
	echo '</select>';
	} else {
		echo 'There are no available page parents.';
	}
		break;
?>
<?php				
		case 4:
			echo "Enter ID: &nbsp; <input type='text' name='$id' style='width: 5%;' />";
			break;
		case 5:
			echo 'This sidebar will be disabled.';
			echo "<input type='hidden' name='$id' value='notset-$id' /> \n";
			break;
	}
	echo "<!-- End of API Loading -->\n</div>\n";
}

function pconfigure_sidebars($data){
	GLOBAL $wpdb; $table = sidebars_table;
	unset($data['save']);
	foreach($data as $key => $type){
		$check = (int) $type;
		if($check > 0){
			$id = $type;
			$type = 'id';
		} else {
			$temp = explode('-', $type);
			$type = $temp[0];
			$id = $temp[1];
		}
		if($type == 'default'){
			$id = 0;
		}
		$query = "UPDATE `$table` SET `condition` = '$type', `limit` = '$id' WHERE `sidebar` = '$key' LIMIT 1";

		$result = $wpdb->query($query);
		if($result){ echo "<div class='wrap'><em>Sidebar $key has been saved. </em></div>"; }
	}
}

# Register all the sidebars
add_action('admin_init', 'all_sidebars');
add_action('get_header', 'all_sidebars');
function all_sidebars(){
	GLOBAL $wpdb; $table = sidebars_table;
	$results = $wpdb->get_results("SELECT `slug` FROM $table ORDER BY `sidebar` DESC", ARRAY_A);
	foreach($results as $result){
		$array = array(
			'name'=>$result['slug'],
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '<h2 class="widgettitle">',
			'after_title' => "</h2>"
		);
		register_sidebar($array);
	}
}

# Control System
function go_sidebar(){
	GLOBAL $posts; GLOBAL $wpdb; $table = sidebars_table;
	foreach($posts as $post){
		$id = $post->ID; 
		$parent = $post->post_parent;
		$category = get_query_var('cat');
	}
	
	## Reference: $query = "SELECT `slug` FROM `$tables` WHERE (`condition` = '$condition' AND `limit` = '$limit')"; ##
	
	# Condition 1 : Match the ID
	$query = "SELECT `slug` FROM `$table` WHERE (`condition` = 'id' AND `limit` = '$id') LIMIT 1";
	$results = $wpdb->get_results($query, ARRAY_A);
	
	if(!is_array($results) OR empty($results)){
	
		# Condition 2: Match the Page Parent
		$query = "SELECT `slug` FROM `$table` WHERE (`condition` = 'parent' AND `limit` = '$parent') LIMIT 1";
		$results = $wpdb->get_results($query, ARRAY_A);
		
		if(!is_array($results) OR empty($results)){
		
			# Condition 3: Match the Category if its numeric
			
			if(is_numeric($category)){
				$query = "SELECT `slug` FROM `$table` WHERE (`condition` = 'category' AND `limit` = '$category') LIMIT 1";
				$results = $wpdb->get_results($query, ARRAY_A);
				
				if(!is_array($results) OR empty($results)){
				
					# Condition 4 : Get the default
					
					$query = "SELECT `slug` FROM `$table` WHERE (`condition` = 'default' AND `limit` = '0') LIMIT 1";
					$results = $wpdb->get_results($query, ARRAY_A);
					
					if(!is_array($results) OR empty($results)){
						echo '<p>No sidebar condition matched this page / post. Attempted to load default. There is no default sidebar set. Please set a default. </p>';
					} else {
						foreach($results as $result){
						$slug = $result['slug'];
						}
					}	
				} else {
					foreach($results as $result){
					$slug = $result['slug'];
					}
				}
			} else {
				# Condition 4 : Get the default
					
				$query = "SELECT `slug` FROM `$table` WHERE (`condition` = 'default' AND `limit` = '0') LIMIT 1";
				$results = $wpdb->get_results($query, ARRAY_A);
				
				if(!is_array($results) OR empty($results)){
					echo '<p>No sidebar condition matched this page / post. Attempted to load default. There is no default sidebar set. Please set a default. </p>';
				} else {
					foreach($results as $result){
					$slug = $result['slug'];
					}
				}
			}
		} else {
			foreach($results as $result){
				$slug = $result['slug'];
			}
		}
	} else {
		foreach($results as $result){
			$slug = $result['slug'];
		}
	}
	dynamic_sidebar($slug);
}
?>