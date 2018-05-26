<?php
/**
 * Face class, and associated classes.
 */
class Face {
	public $character;
	/* orientation is the way the face is facing. 0 means toward or
	 * away from the viewer; -1 and 1 are facing left and right, 
	 * respectively. */
	var $orientation = 1;
	/* is the face facing away from the viewer? */
	var $face_away = false;
	// $eyes is an array of Eye objects. Some may not be visible
	// depending on the orientation of the head.
	var $eyes;
	// For now we just use these:
	var $righteye;
	var $lefteye;
	var $strokecolor = 'brown';
	var $fillcolor = 'burlywood'; // #deb887

	function __construct(&$character, $orientation=1) {
		$this->character =& $character;
		$this->righteye = new Eye($this,Eye::RIGHT_X);
		$this->lefteye = new Eye($this,Eye::LEFT_X);
	}

	public function set_orientation($sx) {
		$this->orientation = $sx;
	}

	public function draw($doc,$x,$y) {
		$sx = $this->orientation;
		$startx = $x - (75*$sx);
		$starty = $y + 40;
		$face = $doc->createElement("path");
		$face->setAttribute("d",
			"M $startx,$starty ".
			"c ".(-50*$sx).",0 ".(-50*$sx).",-90 0,-100 ".
			"s ".(100*$sx).",0 ".(100*$sx).",50 ".
			"s ".(-25*$sx).",110 ".(-50*$sx).",90 ".
			"z");
		// Set up eyes
		$faceelements = Array(
			$this->lefteye->draw($doc,$x,$y+5), 
			$face,
			$this->righteye->draw($doc,$x,$y) );
		if ($this->orientation < 0) { 
			$faceelements = array_reverse($faceelements);
		}
		$node = $doc->createElement("g");
		$node->setAttribute("stroke",$this->strokecolor);
		$node->setAttribute("fill",$this->fillcolor);
		foreach ($faceelements as $elem) {
			$node->appendChild($elem);
		}
		return $node; 
	}
}

class Eye {
	// related objects
	public $face;
	private $pupil;
	private $eyelids;
	// basic default dimensions
	public $radius = 15;
	public $strokewidth = 1;
	private $irisradius = 0;
	private $iriscolor = 'yellow';
	// Placement on the face. Standard location is +-0.2
	private $placement_x;
	private $placement_y = 0;
	const LEFT_X = -0.2;
	const RIGHT_X = 0.2;

	function __construct(&$face,$x,$y=0) {
		$this->face =& $face;
		$placement_x = $x;
		$placement_y = $y;
		$this->pupil = new Pupil($this);
		$this->eyelids = new Eyelids($this);
	}

	function apply_eyelids($mood) {
	}

	function draw($doc,$x,$y) {
		$r = $this->radius;
		//$px = $this->pupiloffsetx;
		//$py = $this->pupiloffsety;
		$sx = $this->face->orientation;
		// eye
		$eye = $doc->createElement("circle");
		$eye->setAttribute("cx",$x);
		$eye->setAttribute("cy",$y);
		$eye->setAttribute("r",$this->radius);
		$eye->setAttribute("fill",'white');
		// pupil
		$pupil = $this->pupil->draw($doc, $x, $y);
		// eyelids
		$lids = $this->eyelids->draw($doc, $x, $y);
		// clipping
		$clipid = "eye".rand();
		$clippath = $doc->createElement("clipPath");
		$clippath->setAttribute("id",$clipid);
		$clipcircle = clone $eye;
		$clipcircle->setAttribute("fill",'black');
		$clippath->appendChild($clipcircle);
		//$eye->setAttribute("clip-path","url(#$clipid)");
		$pupil->setAttribute("clip-path","url(#$clipid)");
		// group
		$node = $doc->createElement("g");
		$node->setAttribute('stroke-width',$this->strokewidth);
		$node->setAttribute('stroke','black');
		$node->setAttribute('clip-rule','nonzero');
		$node->appendChild($clippath);
		$node->appendChild($doc->createComment("Globe of the eye"));
		$node->appendChild($eye);
		$node->appendChild($doc->createComment("pupil"));
		$node->appendChild($pupil);
		$node->appendChild($doc->createComment("eyelids"));
		$node->appendChild($lids);
		return $node;
	}
}
class Pupil {
	private $radius = 3;
	// offset, in radii, from centre.
	private $offset = 0.9;
	// offset direction, in increments of Pi radians
	// (0 is straight ahead, 0.5 is up, 1 is back, 1.5 is down)
	private $direction = 0;
	private $fill = 'black';
	private $stroke = 'black';
	private $eye;

	function __construct(&$eye) {
		$this->eye =& $eye;
	}

	function draw($doc, $x, $y) {
		$px = $this->offset
			*cos(M_PI*$this->direction)
			*$this->eye->radius
			*$this->eye->face->orientation;
		$py = $this->offset
			*sin(M_PI*$this->direction)
			*$this->eye->radius;
		$pupil = $doc->createElement("circle");
		$pupil->setattribute("cx",$x + $px);
		$pupil->setattribute("cy",$y + $py);
		$pupil->setattribute("r", $this->radius);
		$pupil->setAttribute("fill",$this->fill);
		$pupil->setAttribute("stroke",$this->stroke);
		return $pupil;
	}
}

class Eyelids {
	private $mood = Character::MOOD_DEFAULT;
	private $eye;
	private $character;

	function __construct(&$eye) {
		$this->eye =& $eye;
		$this->character =& $this->eye->face->character;
		$this->mood = $this->character->mood;
	}

	function draw($doc, $x, $y) {
		$r = $this->eye->radius + $this->eye->strokewidth;
		$sx = $this->eye->face->orientation;
		switch ($this->character->mood) {
		case Character::MOOD_DEFAULT:
			$theta0 = 0.25*M_PI;
			$theta1 = M_PI;
			$theta2 = null;
			$theta3 = null;
			break;
		case Character::MOOD_SLEEPY:
			$theta0 = 0.25*M_PI;
			$theta1 = M_PI;
			$theta2 = 1.1*M_PI;
			$theta3 = 1.5*M_PI;
			break;
		case Character::MOOD_SURPRISED:
			$theta0 = null;
			$theta1 = null;
			$theta2 = null;
			$theta3 = null;
			break;
		case Character::MOOD_DISTURBED:
			$theta0 = 0.25*M_PI;
			$theta1 = 0.85*M_PI;
			$theta2 = 1.05*M_PI;
			$theta3 = 1.95*M_PI;
			break;
		}
		$node = $doc->createElement("g");
		if ( $theta0 != null and $theta1 != null ) {
			$x0 = $x + $r*cos($theta0)*$sx;
			$y0 = $y - $r*sin($theta0);
			$x1 = $x + $r*cos($theta1)*$sx;
			$y1 = $y - $r*sin($theta1);
			$ulid = $doc->createElement("path");
			$ulid->setAttribute("d",
				"M $x0,$y0 ".
				"A $r,$r 0 0,".(0.5-$sx/2)." $x1,$y1 ".
				"z");
			$node->appendChild($ulid);
		}
		if ( $theta2 != null and $theta3 != null ) {
			$x2 = $x + $r*cos($theta2)*$sx;
			$y2 = $y - $r*sin($theta2);
			$x3 = $x + $r*cos($theta3)*$sx;
			$y3 = $y - $r*sin($theta3);
			$llid = $doc->createElement("path");
			$llid->setAttribute("d",
				"M $x2,$y2 ".
				"A $r,$r 0 0,".(0.5-$sx/2)." $x3,$y3 ".
				"z");
			$node->appendChild($llid);
		}
		return $node;
	}
}
