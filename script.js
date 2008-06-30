$sb = jQuery.noConflict();
$sb(document).ready(function(){
	$sb("select").change(function(){
		var id = $sb(this).attr("id");
		var value = $sb("select#" + id).find("option:selected").attr("value");
		var url = 'admin.php?page=sidebars-api.php&method=' + value + "&id=" + id + " #return";
		id = "#" + id;
		$sb("div").filter(id).load(url);
	});
	$sb("table").after('<br class="clear" /><div align="right"><input type="submit" name="save"  id="post-query-submit" value="Save Configurations" class="button-secondary"  /></div>');
});