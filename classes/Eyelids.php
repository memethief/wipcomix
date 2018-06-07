<?php
/**
 * Class representing a pair of eyelids. Depending on the mood, either or both may be visible or not.
 */
class Eyelids extends PanelObject
{
    
    private $eye_radius;
    
    private $eye_strokewidth;
    
    function __construct(Eye &$eye)
    {
        $this->eye_radius = $eye->radius;
        $this->eye_strokewidth = $eye->strokewidth;
    }
    
    function draw($doc) : DOMElement
    {
        $x = $this->x;
        $y = $this->y;
        $r = $this->eye_radius + $this->eye_strokewidth;
        $sx = $this->orientation;
        // Get the mood-based eyelid settings
        $theta0 = $this->mood->eyelid_theta0;
        $theta1 = $this->mood->eyelid_theta1;
        $theta2 = $this->mood->eyelid_theta2;
        $theta3 = $this->mood->eyelid_theta3;
        // Create the SVG
        $node = $doc->createElement("g");
        $node->setAttribute("id", "eyelids-" . rand());
        $node->setAttribute("class", "mood-" . $this->mood->name);
        // Calculate eyelid positions
        if ($theta0 != null and $theta1 != null) {
            $x0 = $x + $r * cos($theta0) * $sx;
            $y0 = $y - $r * sin($theta0);
            $x1 = $x + $r * cos($theta1) * $sx;
            $y1 = $y - $r * sin($theta1);
            $ulid = $doc->createElement("path");
            $ulid->setAttribute("d", "M $x0,$y0 " . "A $r,$r 0 0," . (0.5 - $sx / 2) . " $x1,$y1 " . "z");
            $node->appendChild($ulid);
        }
        if ($theta2 != null and $theta3 != null) {
            $x2 = $x + $r * cos($theta2) * $sx;
            $y2 = $y - $r * sin($theta2);
            $x3 = $x + $r * cos($theta3) * $sx;
            $y3 = $y - $r * sin($theta3);
            $llid = $doc->createElement("path");
            $llid->setAttribute("d", "M $x2,$y2 " . "A $r,$r 0 0," . (0.5 - $sx / 2) . " $x3,$y3 " . "z");
            $node->appendChild($llid);
        }
        return $node;
    }
}
