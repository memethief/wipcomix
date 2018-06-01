<?php echo '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n"; ?>
<!DOCTYPE html>
<html>
<head>
<title>Programmatical comic test</title>
<link rel="stylesheet" type="text/css" href="de.css" />
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
echo "\n<!-- Strip number: $strip_number -->\n";
$factory = get_ComixFactory();

try {
    $strip = $factory->fetch_strip($strip_number);
    ?>
    <div class="comic">
		<object data="<?php echo $comic_url ?>" type="image/svg+xml"
			id="comic_object" width="<?php echo $strip->getWidth() ?>"
			height="<?php echo $strip->getHeight() ?>">
			[<a href="<?php echo $comic_url ?>"><abbr>SVG</abbr> comic</a>]
			(Using this link may launch a standalone SVG viewer)
		</object>
	</div>
<?php
} catch (Exception $e) {
    ?>
	<div class="error">
		<p>Warning: an error occurred while trying to fetch this strip. The
			error reported was:</p>
		<pre><?php echo print_r($e, true) ?></pre>
	</div>
		<?php
}

?>
	<p>
		[<a href="<?php echo $admin_url ?>" title="Edit this">edit</a>]
	</p>
	</body>
</html>
