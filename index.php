<?php echo '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n"; ?>
<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>Programmatical comic test</title>
		<link rel="stylesheet" type="text/css" href="de.css" />
	</head>
	<body>
		<?php
$admin_url = "comix-admin.php";
/* Determine which strip we're looking at */
$joiner = "?";
$request = "";
if (isset($_REQUEST['s'])) {
	$request .= $joiner . "s=".$_REQUEST['s'];
	$joiner = '&';
}
if (isset($_REQUEST['d'])) {
	$request .= $joiner . "d=".$_REQUEST['d'];
	$joiner = '&';
}
$comic_url = "comic.psvg$request";
require_once("comic.php");
$strip_number = getStripNumber();
echo "\n<!-- Strip number: $strip_number -->\n";
//$strip = Strip::fetch_by_id($strip_number);
$factory = get_ComixFactory();
try {
	$strip = $factory->fetch_strip($strip_number);
		?>
		<object 
			data="<?php echo $comic_url ?>" 
			type="image/svg+xml" id="comic_object"
			width="<? echo $strip->getWidth() ?>"
			height="<? echo $strip->getHeight() ?>">
			[<a href="<?php echo $comic_url ?>"><acronym>SVG</acronym> comic</a>]
			(Using this link may launch a standalone SVG viewer)
		</object>
		<?php
} catch (Exception $e) {
		?>
		<div class="error">
		<p>
		Warning: an error occurred while trying to fetch this
		strip. The error reported was:
		</p>
		<pre><?php echo print_r($e, true) ?></pre>
		</div>
		<?php
}
if (preg_match("/$admin_url/i",$_SERVER['SCRIPT_NAME'])) { 
		?>
		<!-- comic editing form -->
		<p>
			[<a href="." title="Main page">back</a>]
			[<a href="<? echo $admin_url ?>?action=new" title="New Strip">new</a>]
			[<a href="comic.psvg<?php echo $request ?>">SVG</a>]
			[<a href="#" onclick="reloadComic('<?php echo $comic_url ?>'); return false;">reload</a>]
		</p>
		<p>admin page</p>
		<?php
	if (isset($_REQUEST['action'])) { run_action($_REQUEST['action']); }
	edit_strip_form($strip);
		?>
		<?php 
} else { ?>
		<p>[<a href="<? echo $admin_url ?>" title="Edit this">edit</a>]</p>
		<?php 
} ?>
	</body>
</html>
<?php

/* utility functions */

function run_action($action) {
	echo "<p>Action: $action</p>";
}

/**
 * Print out a form to edit a strip
 */
function edit_strip_form($strip) {
	$stripno = $strip->getStripNumber();
	echo "<p>Edit strip $stripno </p>";
	?>
	<script type="text/javascript" src="ajax/update_strip.js" ></script>
	<form name="edit_strip_form" action="<? echo $admin_url ?>" method="post" >
	  Title:
	  <input type="text" name="strip_title" value="<? echo $strip->getTitle() ?>" /><br />
	  Date:
	  <input type="text" name="strip_date" value="<? echo $strip->getDate() ?>" /><br />
	  <input type="submit" name="strip_update" value="Save Strip" />
	</form>
	<div style="border:1px solid #008;">
	  Panels: <select name="panel_number" onchange="showPanelInfo(<?php echo $stripno; ?>,this.value)"> 
	    <option value="0" selected="selected">New</option>
	<?php foreach ($strip->getPanels() as $panel) if ($panel->getId() && $panel->getNumber()) { ?>
	    <option value="<? echo $panel->getId() ?>" ><? echo $panel->getNumber() ?></option>
	<?php } ?>
	  </select>
	  <div id="panel_info_element"></div>
	  <div id="panel_characters_element"></div>
	  <script type="text/javascript"> showPanelInfo(1,0); </script>
	</div>
	<?php
}

function action_edit_panel() {
	
}

?>
