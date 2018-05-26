<?php
/**
 * Class to describe a single panel of a strip. 
 * Ideally this is self-contained; coordinates are relative to the panel, and
 * little or nothing depends on the enclosing strip.
 */
class Panel implements SVGObject {
	//TODO implement a "preferences" object, so these can be set per comic
	private $frame_width = 3;
	private $frame_color = "blue";
	public $background = "lightsteelblue";

	public $id;
	public $number;
	public $left;
	public $top;
	public $width;
	public $height;

	public $characters = Array();
	public $elements = Array();

	/**
	 * Constructor function
	 * TODO this should probably have no parameters, or else have an 
	 * optional Panel parameter (for cloning)
	 */
	function __construct($dbObj=null) {
		if ($dbObj) {
			$this->id = $dbObj->panel_ID;
			$this->number = $dbObj->panel_number;
			$this->left = $dbObj->panel_left;
			$this->top = $dbObj->panel_top;
			$this->width = $dbObj->panel_width;
			$this->height = $dbObj->panel_height;
			foreach ($dbObj->chars as $char) {
				$this->characters[] = new Character($char);
			}
		}
	}

	/**
	 * return an SVG representation of this object.
	 */
	public function draw(DOMDocument $doc) {
		// Create group and establish coordinate system
		$node = $doc->createElement('g');
		$node->setAttribute("transform", "translate($this->left,$this->top)");
	        $node->appendChild($doc->createComment("Panel $this->number"));
	        // create the border of the panel
	        $border = $doc->createElement('rect');
		//$border->setAttribute('x', $this->left);
		//$border->setAttribute('y', $this->top);
	        $border->setattribute('width',$this->width);
	        $border->setattribute('height',$this->height);
		$border->setAttribute("fill", "none");
		// clipping
		$clipid = 'panel'.rand();
		$clippath = $doc->createElement("clipPath");
                $clippath->setAttribute("id",$clipid);
                $cliprect = clone $border;
	        $cliprect->setattribute('width',$this->width+1);
	        $cliprect->setattribute('height',$this->height+1);
		$cliprect->setAttribute('x', -1);
		$cliprect->setAttribute('y', -1);
		$cliprect->setAttribute("fill", "none");
		$clippath->appendChild($cliprect);
		$node->appendChild($clippath);
		// background
		$background = clone $border;
	        $background->setAttribute('fill','lightsteelblue');
	        $node->appendChild($background);
		// Draw in characters
	        $node->appendChild($doc->createComment("frame characters"));
		$chars = $doc->createElement('g');
		$chars->setAttribute("clip-path", "url(#$clipid)");
		foreach ($this->characters as $char) {
		       	$chars->appendChild($char->draw($doc));
		}
	        $node->appendChild($chars);
		// text boxes and other elements
	        $node->appendChild($doc->createComment("frame elements"));
		$elems = $doc->createElement('g');
		$elems->setAttribute("clip-path", "url(#$clipid)");
		foreach ($this->elements as $element) {
			//debug("Panel text element:<pre>".print_r($element)."</pre><br />");
			$elems->appendChild($element->draw($doc));
		}
		$node->appendChild($elems);
		// frame
		$frame = clone $border;
	        $frame->setAttribute('stroke','blue');
	        $frame->setAttribute('stroke-width',$frame_width);
		$frame->setAttribute("fill", "none");
	        $node->appendChild($frame);
		// return the whole thing
		return $node;
	}

	public static function fetch_by_id() {
	}

	/* Getters and Setters */

	/**
	 * Add a character to this panel.
	 * The expected structure of $char is a Character object
	 */
	public function add_character($char) {
		$this->characters[$char->ID] = $char;
	}

	/**
	 * Add a text box to this frame
	 * Eventually this may be distinct from a speech bubble, or perhaps 
	 * not.
	 */
	public function add_text(TextPanel $text) {
		$this->elements[] = $text;
	}

	public function getNumber() { return $this->number; }
	public function getId() { return $this->id; }
}

