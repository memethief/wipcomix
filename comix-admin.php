<?php echo '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n"; ?>
<!DOCTYPE html>
<html>
<head>
<title>Programmatical comic editing</title>
</head>
<body>
  <?php
$admin_url = "comix-admin.php";

/* Determine which strip we're looking at */
$request_params = array_intersect_key($_REQUEST, array_flip(array(
    's',
    'd'
)));

$request_parts = array_map(function ($key, $value) {
    return "$key=$value";
}, array_keys($request_params), array_values($request_params));
$request_string = implode('&', $request_parts);

$comic_url = "comic.svg.php?$request_string";

require_once ("comic.php");
$strip_number = getStripNumber();
$strip = Strip::fetch_by_id($strip_number);
?>
  <object data="<?php echo $comic_url ?>" type="image/svg+xml"
		id="comic_object" width="<? echo $strip->getWidth() ?>"
		height="<? echo $strip->getHeight() ?>">
		[<a href="<?php echo $comic_url ?>"><abbr>SVG</abbr> comic</a>] (Using
		this link may launch a standalone SVG viewer)
	</object>
	<!-- comic editing form -->
    <p>
		[<a href="." title="Main page">back</a>] [<a
			href="<?php echo $admin_url ?>?action=new" title="New Strip">new</a>]
		[<a href="comic.psvg<?php echo $request_string ?>">SVG</a>] [<a href="#"
			onclick="reloadComic('<?php echo $comic_url ?>'); return false;">reload</a>]
	</p>
	<p>admin page</p>
  <?php
    if (isset($_REQUEST['action'])) {
        run_action($_REQUEST['action']);
    }

    edit_strip_form($strip);

/* utility functions */
function run_action($action)
{
    echo "<p>Action: $action</p>";
}

function edit_strip_form($strip)
{
    $stripno = $strip->getStripNumber();
    ?>
<div>
		<p>Edit strip <?php echo $stripno ?></p>
		<script type="text/javascript" src="ajax/update_strip.js"></script>
		<form id="edit_strip_form" action="<? echo $admin_url ?>"
			method="post">
			<div>
				<label>Title: <input type="text" name="strip_title"
					value="<?php echo $strip->getTitle() ?>" /></label>
			</div>
			<div>
				Date: <input type="text" name="strip_date"
					value="<?php echo $strip->getDate() ?>" />
			</div>
			<div>
				<input type="submit" name="strip_update" value="Save Strip" />
			</div>
		</form>
	</div>
	<div style="border: 1px solid #008;">
		<label>Panels: <select name="panel_number"
			onchange="showPanelInfo(<?php echo $stripno; ?>,this.value)">
				<option value="0" selected="selected">New</option>
	<?php foreach ($strip->getPanels() as $panel) if ($panel->getId() && $panel->getNumber()) { ?>
	    <option value="<?php echo $panel->getId() ?>"><?php echo $panel->getNumber() ?></option>
	<?php } ?>
	  </select></label>
		<div id="panel_info_element"></div>
		<div id="panel_characters_element"></div>
		<script type="text/javascript"> showPanelInfo(1,0); </script>
	</div>
<?php
}

function action_edit_panel()
{}

?>

 </body>
</html>