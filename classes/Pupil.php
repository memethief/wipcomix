<?php
/**
 * Class representing the pupil of a character's eye
 */
class Pupil extends PanelObject
{
    
    private $eye_radius;
    
    private $radius = 3;
    
    // offset, in eye radii, from centre.
    private $offset = 0.9;
    
    // offset direction, in increments of Pi radians
    // (0 is straight ahead, 0.5 is up, 1 is back, 1.5 is down)
    private $direction = 0;
    
    private $fill = 'black';
    
    private $stroke = 'black';
    
    private $clippath_id;
    
    function __construct(Eye &$eye)
    {
        $this->eye_radius = $eye->radius;
    }
    
    function set_clippath_id($clippath_id)
    {
        $this->clippath_id = $clippath_id;
    }
    
    function draw($doc) : DOMElement
    {
        $px = $this->offset * cos(M_PI * $this->direction) * $this->eye_radius * $this->orientation;
        $py = $this->offset * sin(M_PI * $this->direction) * $this->eye_radius;
        $pupil = $doc->createElement("circle");
        $pupil->setAttribute("cx", $this->x + $px);
        $pupil->setAttribute("cy", $this->y + $py);
        $pupil->setAttribute("r", $this->radius);
        $pupil->setAttribute("fill", $this->mood->pupil_colour ? $this->mood->pupil_colour : $this->fill);
        $pupil->setAttribute("stroke", $this->stroke);
        if ($this->clippath_id)
            $pupil->setAttribute("clip-path", "url(#$this->clippath_id)");
            return $pupil;
    }
}
