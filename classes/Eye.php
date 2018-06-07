<?php
/**
 * Class representing a character's eye
 */
class Eye extends PanelObject
{
    
    // basic default dimensions
    public $radius = 15;
    
    public $strokewidth = 1;
    
    private $irisradius = 0;
    
    private $iriscolor = 'yellow';
    
    // Placement on the face. Standard location is +-0.2
    private $placement_x = 0;
    
    private $placement_y = 0;
    
    const LEFT_X = - 0.2;
    
    const RIGHT_X = 0.2;
    
    function __construct(Face &$face, $x, $y = 0)
    {
        $this->placement_x = $x;
        $this->placement_y = $y;
        $this->pupil = new Pupil($this);
        $this->eyelids = new Eyelids($this);
    }
    
    function draw($doc) : DOMElement
    {
        $sx = $this->orientation;
        // eye
        $eye = $doc->createElement("circle");
        $eye->setAttribute("cx", $this->x + 0);
        $eye->setAttribute("cy", $this->y + 0);
        $eye->setAttribute("r", $this->radius);
        $eye->setAttribute("fill", 'white');
        // clipping
        $clipid = "eye-" . rand();
        $clippath = $doc->createElement("clipPath");
        $clippath->setAttribute("id", $clipid);
        $clipcircle = clone $eye;
        $clipcircle->setAttribute("fill", 'black');
        $clippath->appendChild($clipcircle);
        $this->pupil->set_clippath_id($clipid);
        // group
        $node = $doc->createElement("g");
        $node->setAttribute('stroke-width', $this->strokewidth);
        $node->setAttribute('stroke', 'black');
        $node->setAttribute('clip-rule', 'nonzero');
        $node->setAttribute('class', 'mood-'.$this->mood->name);
        $node->appendChild($clippath);
        $node->appendChild($doc->createComment("Globe of the eye"));
        $node->appendChild($eye);
        $node->appendChild($doc->createComment("pupil"));
        $node->appendChild($this->pupil->draw($doc));
        $node->appendChild($doc->createComment("eyelids"));
        $node->appendChild($this->eyelids->draw($doc));
        return $node;
    }
}
