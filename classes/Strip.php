<?php
/**
 * Classes to describe strips as a whole
 */

class Strip implements SVGObject {
	const SOURCE_XML = "xml";
	const SOURCE_DB = "db";
	const DEFAULT_SOURCE = SOURCE_XML;
	public $id;
	public $title;
	public $date;
	public $panels = Array();
	public $texts = Array();
	public $characters = Array();
	public $frame_width=3;

	public function __construct($dbObj=null) {
		if ($dbobj != null) {
			$this->id = $dbObj->strip_ID;
			$this->title = $dbObj->strip_title;
			$this->date = $dbObj->strip_date;
			foreach ($dbObj->panels as $panel) {
				$this->panels[] = new Panel($panel);
			}
			foreach ($dbObj->texts as $text) {
				//$this->texts[$text->getOrder()] = $text;
				$this->panels[] = $text;
			}
		}
	}

	// TODO remove this
	public static function fetch_by_id($id,$source=Strip::SOURCE_XML) {
		try {
			if ($source==Strip::SOURCE_XML)
				return ComixXML::get()->fetch_strip_by_id($id);
			else
				return ComixDB::get()->fetch_strip_by_id($id);
		} catch (Exception $e) {
			return new SVGException($e);
		}
		//$strip = new Strip();
		//return $strip;
	}

	public function draw(DOMDocument $doc) {
	        $root = $doc->createElement('g');
		// Black background to the whole thing
		$bg = $doc->createElement('rect');
		$bg->setAttribute("fill","black");
		$bg->setAttribute("x",0);
		$bg->setAttribute("y",0);
		$bg->setAttribute("width","100%");
		$bg->setAttribute("height","100%");
		$root->appendChild($bg);
		// Title at the top
		$root->appendChild($this->getTitleNode($doc));
		//group for the panels
		$panelgroup = $doc->createElement('g');
		$panelgroup->setAttribute("transform", 
			"translate(".($this->frame_width+1).",".($this->getHeaderHeight()+1).")");
		foreach ($this->panels as $panel) {
			$panelgroup->appendChild($panel->draw($doc));
		}
		$root->appendChild($panelgroup);
		// text nodes
		foreach ($this->texts as $text) { $root->appendChild($text->draw($doc)); }
		//
		return $root;
	}

	private function getTitleNode(DOMDocument $doc) {
		$title = $doc->createElement('text');
		$title->appendChild($doc->createTextNode($this->getTitle()));
		$title->setAttribute("font-family","verdana");
		$title->setAttribute("font-weight","bold");
		$title->setAttribute("fill","white");
		$title->setAttribute("y",15+$this->frame_width);
		$title->setAttribute("x",$this->frame_width);
		return $title;
	}

	/* public getters & setters */

	/**
	 * create a new Panel object with the specified panel number.
	 * we set the panel's internal panel number, insert it in the right spot
	 * in our panel array, and give it a copy of our character array.
	 */
	public function add_panel($panelnumber=null) {
		$panel = new Panel();
		foreach ($this->characters as $key=>$char) {
			$panel->characters[$key] = clone $char;
		}
		if ($panelnumber == null) {
			$this->panels[] = $panel;
		} else {
			$panel->number = $panelnumber;
			echo("Panel number: ".print_r($panelnumber,true)."</pre><br />");
			$this->panels[$panelnumber] = $panel;
		}
		return $panel;
	}

	/**
	 * create a new TextPanel object and add it to our panel array.
	 * the text of the panel may optionally be specified.
	 * TODO fix panel number handling so we don't risk overwriting
	 */
	public function add_text($content=null) {
		$text = new TextPanel();
		if ($content != null) {
			$text->content = $content;
		}
		$this->panels[] = $text;
		return $text;
	}

	/**
	 * TODO rename to fit scheme
	 */
	public function getStripNumber() {
		return $this->id;
	}
	public function setStripNumber(int $id) {
		$this->id = $id;
	}
	public function getTitle() {
		return $this->title;
	}
	public function setTitle($title) {
		$this->title = $title;
	}
	public function getDate() {
		return $this->date;
	}
	public function setDate($date) {
		$this->date = $date;
	}

	public function getWidth() {
		$width = 0;
		foreach ($this->panels as $panel) {
			$width = max($width, $panel->left + $panel->width + $this->frame_width);
		}
		return $width + $this->frame_width*2 +1;
	}

	public function getHeight() {
		$height = 0;
		foreach ($this->panels as $panel) {
			$height = max($height, $panel->top + $panel->height + $this->frame_width);
		}
		return $height + $this->getHeaderHeight() + $this->frame_width;
	}

	private function getHeaderHeight() {
		return 20 + $this->frame_width*2;
	}

	public function getPanels() { return $this->panels; }
}

