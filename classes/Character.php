<?php
/**
 * Class to deal with putting together and drawing characters.
 *
 * Object methods visible to the outside should be:
 * [static]
 * - fetch_by_id($db, $id) : fetch a character from the database by ID
 * - fetch_by_name($db, $name) : fetch a character from the database by name
 * [instance]
 * - draw() : return an svg node of this character
 */

class Character {
	const MOOD_DEFAULT = 0;
	const MOOD_SLEEPY = 1;
	const MOOD_SURPRISED = 2;
	const MOOD_DISTURBED = 3;

	public $name;
	public $ID;
	public $face;
	public $top;
	public $left;
	public $mood = Character::MOOD_DEFAULT;
	public $orientation = 1;

	/**
	 * Constructor
	 * TODO remove DB object handling, and put it elsewhere
	 */
	function __construct($dbObj=null) {
		if ($dbObj != null) {
			$this->ID = $dbObj->character_ID;
			$this->name = $dbObj->character_name;
			$this->top = $dbObj->character_y;
			$this->left = $dbObj->character_x;
			if (isset($dbObj->character_mood)) $this->mood = $dbObj->character_mood;
			if (isset($dbObj->character_facing)) $this->orientation = $dbObj->character_facing;
		}
		$this->face = new Face($this);
	}

	/**
	 * factory method. Supposed to be called statically.
	 */
	public static function fetch_by_id($db, $id) {
		$charObj = $db->fetch_character_by_id($id);
		$char = new Character();
		$char->name = $charObj->character_name;
		$char->ID = $charObj->character_ID;
		return $char;
	}

	/**
	 * Output method. Here we return an XML node that contains a drawing
	 * of the character.
	 */
	public function draw($doc, $x=null,$y=null, $sx=null) {
		if ($x == null) $x = $this->left;
		if ($y == null) $y = $this->top;
		if ($sx == null) $sx = $this->orientation;
		$node = $doc->createElement("g");
		$this->face->set_orientation($sx);
		$node->appendChild($this->face->draw($doc, $x,$y));
		return $node;
		//return $path;
	}

}

?>
