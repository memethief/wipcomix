<?php
/**
 * Class to describe a single panel of a strip. 
 * Ideally this is self-contained; coordinates are relative to the panel, and
 * little or nothing depends on the enclosing strip.
 */
class TextPanel extends Panel {
	private $frame_width = 1;
	private $frame_color = "black";

	private $strip_id;
	private $order;
	public $content;
	private $styles = Array();
	public $left;
	public $top;
	public $width;
	public $height;
	public $background = "white";

	function __construct($initObj=null) {
		if (is_array($initObj)) {
			$attrs = Array('order','left','top','width','height','content','styles');
			foreach ($attrs as $aname) {
				if (isset($initObj[$aname])) {
					$this->$aname = $initObj[$aname];
				}
			}
		} else if (is_object($initObj)) {
			$this->strip_id = $initObj->text_strip_ID;
			$this->order = $initObj->text_order;
			$this->left = $initObj->text_left;
			$this->top = $initObj->text_top;
			$this->width = $initObj->text_width;
			$this->height = $initObj->text_height;
			$this->content = $initObj->text_content;
			$styles = explode(";",$dbObj->text_style);
			foreach ($styles as $style) {
				list($name,$value) = explode(":",$style,2);
				$this->styles[$name] = $value;
			}
		}
	}

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
	        $background->setAttribute('fill',$this->background);
	        $node->appendChild($background);
		// Draw text
	        $node->appendChild($doc->createComment("text element"));
		$text = $doc->createElement('text',$this->content);
		$text->setAttribute('x',floor($this->width/2));
		$text->setAttribute('y',16);
		$text->setAttribute('font-family','verdana, sans-serif');
		$text->setAttribute('text-anchor','middle');
		foreach ($this->styles as $name=>$value) {
			if ($name) {
		        	$node->appendChild($doc->createComment("text attribute [$name = $value]"));
				$text->setAttribute($name,$value);
			}
		}
	        $node->appendChild($text);
		// frame
		$frame = clone $border;
	        $frame->setAttribute('stroke',$this->frame_color);
	        $frame->setAttribute('stroke-width',$this->frame_width);
		$frame->setAttribute("fill", "none");
	        $node->appendChild($doc->createComment("text frame"));
	        $node->appendChild($frame);
		// return the whole thing
		return $node;
	}

	public static function fetch_by_id() {}

	/* Getters and Setters */

	public function getOrder() { return $this->order; }
	public function getId() { return $this->id; }
}

