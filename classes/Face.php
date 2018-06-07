<?php

/**
 * Class representing a character's drawn face
 */
class Face extends PanelObject
{

    /* is the face facing away from the viewer? */
    var $face_away = false;

    var $strokecolor = 'brown';

    var $fillcolor = 'burlywood';
 // #deb887

    function __construct(Character &$character)
    {
        $this->righteye = new Eye($this, Eye::RIGHT_X);
        $this->lefteye = new Eye($this, Eye::LEFT_X);
    }
    
    public function draw($doc) : DOMElement
    {
        $sx = $this->orientation;
        $startx = $this->x - (75 * $sx);
        $starty = $this->y + 40;
        $face = $doc->createElement("path");
        $face->setAttribute("d", "M $startx,$starty " . "c " . (- 50 * $sx) . ",0 " . (- 50 * $sx) . ",-90 0,-100 " . "s " . (100 * $sx) . ",0 " . (100 * $sx) . ",50 " . "s " . (- 25 * $sx) . ",110 " . (- 50 * $sx) . ",90 " . "z");
        // Set up eyes
        $faceelements = Array(
            $this->lefteye->draw($doc, $this->x, $this->y + 5),
            $face,
            $this->righteye->draw($doc, $this->x, $this->y)
        );
        if ($this->orientation < 0) {
            $faceelements = array_reverse($faceelements);
        }
        $node = $doc->createElement("g");
        $node->setAttribute("stroke", $this->strokecolor);
        $node->setAttribute("fill", $this->fillcolor);
        $node->setAttribute('class', 'mood-'.$this->mood->name);
        foreach ($faceelements as $elem) {
            $node->appendChild($elem);
        }
        return $node;
    }
}

 