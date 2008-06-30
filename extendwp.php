<?php 
/*
Script Name: ExtendWP API
Script URI: http://www.nexterous.com/scripts/extendwp/
Description: An API created to offer more simplicity in working with Wordpress backend. Allows plugin developers to developer better and cleaner code / plugins.
Version: 1.0
Author: Daniel
Author URI: http://www.nexterous.com
*/


class extend{

function version(){
	$version = 10;
	return $version;
}

function check_page($page){
	if(isset($_GET['page'])){
		if($page == $_GET['page']){
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		$url = parse_url($_SERVER['REQUEST_URI']);
		$url = explode('/', $url['path']);
		if($url[3] == $page){
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
	
function table($header, $text, $labels, $data){
?>
	<div class="wrap">
		<h2><?php echo $header; ?></h2>
		<?php echo $text; ?>	
		<br class="clear" />
	<table class="widefat">
		<thead><tr>
<?php foreach($labels as $label){ ?>
		<th scope="col"><div align="center"><?php echo $label; ?></div></th>
<?php } ?>
		</tr></thead>
		<tbody>
<?php
	if(is_array($data)){
		foreach($data as $key => $result){
			if(substr_count($n = $key / 2, '.') > 0){
				$alternate = 'alternate';
			} else {
				$alternate = '';
			}
?>
		<tr class='<?php echo $alternate; ?> author-self status-publish' valign="top">
<?php	foreach($result as $datapiece){	?>
			<td><div align="center"><?php echo $datapiece; ?></div></td>
<?php }}} else {	?>
		<tr><td><?php echo $data; ?></td></tr>
<?php } ?>
		</tbody>
	</table>
	</div>
<?php }

function arc($array, $key){
	foreach($array as $data){
		unset($data[$key]);
		$final[] = $data;
	}
	return $final;
}

function aac($array, $data){
	foreach($data as $key => $piece){
		$array[$key][] = $piece;
	}
	return $array;
}

function formulate($results, $what, $where, $with, $what2 = NULL, $with2 = NULL){
	foreach($results as $data){
		$row = str_replace($what, $data[$with], $where);
		if(isset($what2)){
			$row = str_replace($what2, $data[$with2], $row);
		}
		$final[] = $row;
	}
	return $final;
}

function page($title, $text){
?>
<div class="wrap">
<h2><?php echo $title; ?></h2>
<br class="clear" />
<?php echo $text; ?>
</div>
<?php
}
} ?>