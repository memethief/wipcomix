<?php
/* SQL queries used by AJAX functions */

function mk_panel_insert_query() {
	$insert = true;
	$fields = Array("panel_strip","panel_number","panel_left","panel_top","panel_width","panel_height");
	$values = Array();
	foreach ($fields as $f)
		if (isset($_REQUEST[$f]) and is_numeric($_REQUEST[$f]))	$values[] = $_REQUEST[$f];
		else $insert = false;
	$q = "INSERT INTO comix_panel (".implode(", ", $fields).") VALUES (".implode(", ", $values).")";
	if ($insert) return $q;
	else return $false;
}
function mk_panel_update_query() {
	$q = "UPDATE comix_panel SET";
	$joiner = " ";
	$update = false;
	$update_fields = Array("panel_number","panel_left","panel_top","panel_width","panel_height");
	foreach ($update_fields as $f) {
		$o = $f."_old";
		if (isset($_REQUEST[$f]) and isset($_REQUEST[$o]) and $_REQUEST[$f] != $_REQUEST[$o]) {
			$q .= $joiner . "$f = '$_REQUEST[$f]'";
			$joiner = ", ";
			$update = true;
		}
	}
	$q .= " WHERE panel_ID = '".$_REQUEST['panel_ID']."'";
	if ($update) return $q;
	else return $false;
}

?>
