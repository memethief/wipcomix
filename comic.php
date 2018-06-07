<?php

/**
 * Autoload any classes we need
 */
function my_autoload($class_name)
{
    require_once ("classes/$class_name.php");
}

spl_autoload_register('my_autoload');

$strip_source = isset($_REQUEST['f']) ? $_REQUEST['f'] : 'xml';
$strip_number = isset($_REQUEST['s']) ? $_REQUEST['s'] : null;
$strip_date = isset($_REQUEST['d']) ? $_REQUEST['d'] : null;

function getStripSource()
{
    return isset($_REQUEST['f']) ? $_REQUEST['f'] : 'xml';
}

function getStripNumber()
{
    return isset($_REQUEST['s']) ? $_REQUEST['s'] : null;
}

function drawStrip()
{
    // TODO: allow strip scripts to be saved as XML files
}

function drawStripNumber($strip_number = null)
{
    header("Content-type: image/svg+xml");
    // die("foo");
    if ($strip_number == null)
        $strip_number = getStripNumber();
    $factory = get_ComixFactory();
    $strip = $factory->fetch_strip($strip_number);
    $doc = new SVGDocument(""); // "strip number ".$strip->getStripNumber().": '".$strip->getTitle()."'");
    $doc->setDimensions($strip->getWidth(), $strip->getHeight());
    $doc->appendObject($strip);
    echo $doc->saveXML();
}

function get_ComixFactory()
{
    $source = getStripSource();
    switch ($source) {
        case 'xml':
            return new ComixXML();
        case 'db':
        default:
            throw new Exception("Unknown comic source passed: [$source]");
    }
}

/* useful functions */

/**
 * create a head object, with specified coordinates, dimensions and facing.
 * $file = XmlWriter object
 * cx = "centre-x", x-coordinate of centre of head
 * cy = "centre-y", y-coordinate of centre of head
 * scalex = ear-to-ear scale factor
 * scaley = chin-to-crown scale factor
 * scalez = nose-to-occiput scale factor
 * facing = direction the head is facing:
 * 0 = toward viewer
 * 1 = 45 degrees CCW of 0 (as seen from above)
 * 2 = 45 degrees CCW of 1 (as seen from above)
 * 3 = 45 degrees CCW of 2 (as seen from above)
 * 4 = 45 degrees CCW of 3 (as seen from above)
 * -1 = 45 degrees CW of 0 (as seen from above)
 * -2 = 45 degrees CW of 1 (as seen from above)
 * -3 = 45 degrees CW of 2 (as seen from above)
 */
function draw_head($file, $cx, $cy, $facing = 0, $scalex = 1, $scaley = 1, $scalez = 1)
{
    $default = Array(
        'facewd' => 1.0,
        'faceht' => 2.0,
        'facedp' => 1.0,
        'skulwd' => 1.2,
        'skulht' => 1.5,
        'skuldp' => 1.8
    );
    $facings = Array(
        0 => Array(
            'theta' => 0,
            'phi' => 0
        ),
        1 => Array(
            'theta' => pi() / 4,
            'phi' => 0
        ),
        2 => Array(
            'theta' => pi() / 2,
            'phi' => 0
        ),
        3 => Array(
            'theta' => 3 * pi() / 4,
            'phi' => 0
        ),
        4 => Array(
            'theta' => pi(),
            'phi' => 0
        ),
        - 3 => Array(
            'theta' => 5 * pi() / 4,
            'phi' => 0
        ),
        - 2 => Array(
            'theta' => 3 * pi() / 2,
            'phi' => 0
        ),
        - 1 => Array(
            'theta' => 7 * pi() / 4,
            'phi' => 0
        )
    );
    $theta = $facings[$facing][theta];
    $phi = $facings[$facing][phi];
    // dimensions: scale the three dimensions of each part by the
    // specified factor
    $facewd = $default['facewd'] * $scalex; // cm
    $faceht = $default['faceht'] * $scaley; // cm
    $facedp = $default['facedp'] * $scalez; // cm
    $skulwd = $default['skulwd'] * $scalex; // cm
    $skulht = $default['skulht'] * $scaley; // cm
    $skuldp = $default['skuldp'] * $scalez; // cm
                                            // calculate the visible width of the face and skull
    $facevw = visible_diameter($facewd, $facedp, $theta);
    $skulvw = visible_diameter($skulwd, $skuldp, $theta);
    // calculate the offset of the face from centre. There is a circle
    // along which the centre of the face moves as the head rotates --
    // this circle has a radius approximately equal to the difference
    // between the depths of the face and skull, halved. There is a bit
    // of an extra offset, given as a proportion of skull depth.
    $offsetproportion = 0.95;
    $offsetradius = ($skuldp * $offsetproportion - $facedp) / 2;
    // now we figure out, based on the offset radius and the current theta,
    // how far over we should translate the face.
    $faceoffset = $offsetradius * sin($theta);
    // coordinates
    $facecx = $cx + $faceoffset;
    $facecy = $cy;
    $skulcx = $cx;
    $skulcy = $cy - ($faceht - $skulht) / 2.0;
    $file->startElement('g');
    $file->writeElement('title', 'SVG comic test');
    $file->writeElement('desc', 'This is the SVG comic test');
    ellipse($file, $facecx, $facecy, $facevw, $faceht); // face
    ellipse($file, $skulcx, $skulcy, $skulvw, $skulht); // skull
    $file->endElement('g');
}

function ellipse($xml, $cx, $cy, $rx, $ry)
{
    $xml->startElement('ellipse');
    $xml->writeAttribute('cx', $cx . "cm");
    $xml->writeAttribute('cy', $cy . "cm");
    $xml->writeAttribute('rx', ($rx / 2) . "cm");
    $xml->writeAttribute('ry', ($ry / 2) . "cm");
    $xml->endElement('ellipse');
}

/**
 * determine the visible diameter of an ellipse, seen at angle $theta.
 * $wd is the width of the ellipse seen head-on (at $theta = 0)
 * $dp is the depth of the ellipse -- the dimension perpendicular to $wd and
 * the width of the ellipse seen at $theta = pi/2
 * $theta is the angle away from straight-on
 */
function visible_diameter($wd, $dp, $theta)
{
    $a = $wd / 2.0;
    $b = $dp / 2.0;
    // echo "\n<!-- width $wd; a $a; depth $dp; b $b; theta $theta -->\n";
    // $visible = 1;
    $visible = 2 * ($a * $b) / (sqrt(pow($a * sin($theta), 2) + pow($b * cos($theta), 2)));
    // echo "\n<!-- width $wd; depth $wd; theta $theta; visible diameter is $visible -->\n";
    return $visible;
}

/* debug stuff */
function debug($message)
{
    if (isset($_REQUEST['debug']))
        echo ($message);
}

?>
