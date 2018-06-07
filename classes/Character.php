<?php
/**
 * Class to deal with putting together and drawing characters.
 *
 * Object methods visible to the outside should be:
 * [instance]
 * - draw() : return an svg node of this character
 */

class Character extends PanelObject {
	public $name;
	public $ID;
	public $top;
	public $left;

	/**
	 * Constructor
	 */
	function __construct() {
		$this->face = new Face($this);
	}
	
	function set_skin_colour(string $colour)
	{
	    $face = $this->face;
	    $face->fillcolor = $colour;
	}
	
	/**
	 * Output method. Here we return an XML node that contains a drawing
	 * of the character.
	 * 
	 * TODO Add torso, arms, legs, etc.
	 */
	public function draw(DOMDocument $doc): DOMElement {
		$node = $doc->createElement("g");
		$node->setAttribute("id", "character-" . $this->name . "-" . rand());
		$node->setAttribute("class", "mood-" . $this->mood->name);
		$node->appendChild($this->face->draw($doc, $this->left,$this->top));
		return $node;
	}

}

?>
