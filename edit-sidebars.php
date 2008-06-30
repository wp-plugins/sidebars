<?php
/*
Plugin Package: Sidebars
Plugin URI: http://www.nexterous.com/plugins/sidebars/
Description: Contains the display the form to add a sidebar
Version: 1.0
Author: Daniel
Author URI: http://www.nexterous.com
*/

# Display function for displaying the managing the sidebars
function work_sidebars() {
	$table = sidebars_table;
	if(isset($_POST['save'])){
		$slug = $_POST['title'];
		$description = $_POST['description'];
		$slug = sanitize_title($slug);
		
		GLOBAL $wpdb;
		$slug = $wpdb->escape($slug);
		$description = $wpdb->escape($description);
		
		if(isset($_GET['edit'])){
			$id = $_GET['edit'];
			$query = "UPDATE `$table` SET `description` = '$description', `slug` = '$slug', 
			`lastedited` = NOW( ) WHERE `sidebar` = $id LIMIT 1";
		} else {
			$query = "INSERT INTO `$table` VALUES (NULL , '$slug', '$description', CURRENT_TIMESTAMP, 'notset', 0)";
		}
		$true = $wpdb->query($query);
	} if (isset($_GET['slug'])){
		$edit = TRUE;
		$slug = $_GET['slug'];
		
		GLOBAL $wpdb;
		$sidebar = $wpdb->get_row("SELECT * FROM `$table` WHERE (`slug` = '$slug')", ARRAY_A);
	}	
	if(isset($_GET['delete'])){
		GLOBAL $wpdb;
		$result = $wpdb->query("DELETE FROM $table WHERE (`slug` = '{$_GET['delete']}')");
		if($result){
			echo "<div class='wrap'>{$_GET['delete']} sidebar was deleted.</div> \n";
		} else {
			echo "<div class='wrap'>{$_GET['delete']} sidebar was NOT deleted.</div> \n";
		}
			echo "<div class='wrap'>Return to <a href='admin.php?page=sidebars/sidebars.php'>Manage Sidebars</a>.</div> \n";
	} else {
?>
<form action="admin.php?page=edit-sidebars.php<?php if($edit){ echo "&slug={$slug}&edit={$sidebar['sidebar']}"; } ?>" method="post">
<div class="wrap">
<h2>Add Sidebars</h2>
<br class="clear" />
	<div id="poststuff">
	<div class="submitbox" id="submitpost">
	<div id="previewview" style="color: white; font-weight: bold;"><?php if($true){ echo "You have successfully saved $slug."; } ?></div>
	<div class="inside">
		<p>When you are finished with the title and description, you must click "Save" to keep the sidebar. If you do not 
		do this then you will lose the sidebar. </p>
	</div>
	<p class="submit">
		<input type="submit" name="save" id="save-post" value="Save" class="button button-highlighted" />
		<br class="clear" />
	</p>
</div>
<div id="post-body">
<div class="box">
	<div id="titlediv">
	<h3>Sidebar Title</h3>
	<div id="titlewrap">
		<input type="text" name="title" size="30" tabindex="1" value="<?php echo $sidebar['slug']; ?>" id="title" />
	</div>
</div>
<div class="box">
	<div id="titlediv">
	<h3>Description</h3>
		<textarea name="description" style="width: 98%;"><?php echo $sidebar['description']; ?></textarea>
</div>
</div>
</div>
</div>
</div></div>
</form>
<?php	}}	?>