<?php

/**
 * class to handle XML-run comics
 */
class ComixXML implements ComixFactory {
	private static $basedir = ".";
	private $stripdir;
	private $playerdir;
	public static $singleton = null;
	public $title = "";

	function __construct() {
		$this->stripdir = ComixXML::$basedir . "/strips";
		$this->playerdir = ComixXML::$basedir . "/players";
	}

	/* factory method */
	static function get() {
		if (ComixXML::$singleton == null) 
			ComixXML::$singleton = new ComixXML();
		return ComixXML::$singleton;
	}

	/**
	 * fetch a strip and return its object.
	 * If $id is passed, we look for a file that matches it, and 
	 * throw an exception if none is found.
	 * If no $id is passed, we take the most recent strip.
	 * TODO get smart about identifying unpublished strips
	 * TODO make sure code injection is impossible
	 */
	public function fetch_strip($id=null) {
		//echo("fetch_strip($id)<br />");
		$stripfile = null;
		// Find the latest strip
		if ($id == null) { $id = 'latest'; }
		// list strips named like strip_000.xml
		if ($id == null) { $id = '*'; }
		$pattern = $this->stripdir."/strip_$id.xml";
		$strips = glob($pattern);
		//echo "<!-- Strips available in '$pattern': \n";
		//print_r($strips);
		//echo "-->";
		if (array_count_values($strips) != 0) {
			rsort($strips);
			$stripfile = $strips[0];
		}
		if ($stripfile == null) throw new Exception ("No strip found with ID '$id'!");
		$strip = $this->parse_strip($stripfile);
		//echo("print.<pre>".print_r($obj,true)."</pre><br />");
		return $strip;
	}
	
	/**
	 * Given a filename, we wish to parse it into an object suitable to 
	 * send into a Strip object.
	 */
	function parse_strip($file) {
		$xml = new SimpleXMLElement(file_get_contents($file));
		$strip = new Strip();
		debug("\$xml<pre>".print_r($xml,true)."</pre><br />");
		if ($xml->striptitle) {
			$strip->title = ((string) $xml->striptitle);
		}
		if ($xml->strippubdate) {
			$strip->date = ((string) $xml->strippubdate);
		}
		foreach ($xml->playerdef as $xmlplayer) {
			$id = ((string) $xmlplayer['id']);
			$source = ((string) $xmlplayer['source']);
			$href = ((string) $xmlplayer['href']);
			$player = $this->parse_player($id, $source, $href);
			debug("Strip characters<pre>".print_r($strip->characters,true)."</pre><br />");
			$strip->characters[$id] = $player;
		}
		foreach ($xml->panel as $xmlpanel) {
			$panelnumber = ((int)$xmlpanel->panel_number);
			$panel = $strip->add_panel($panelnumber);
			//$panel = new Panel();
			//$panel->number = ((int)$xmlpanel->panel_number);
			$panel->left = ((int)$xmlpanel['xoffset']);
			$panel->top = ((int)$xmlpanel['yoffset']);
			$panel->width = ((int)$xmlpanel['xsize']);
			$panel->height = ((int)$xmlpanel['ysize']);
			foreach ($xmlpanel->player as $xmlplayer) {
				$player = new Character();
				$player->ID = ((string) $xmlplayer['name']);
				$player->left = ((int) $xmlplayer['xoffset']);
				$player->top = ((int) $xmlplayer['yoffset']);
				if (isset($xmlplayer['mood'])) {
					$player->mood = ((int) $xmlplayer['mood']);
				}
				$player->orientation = ((float) $xmlplayer['facing']);
				$panel->add_character($player);
			}
			foreach ($xmlpanel->paneltext as $xmltext) {
				$txt = new TextPanel();
				$txt->left = ((int) $xmltext['xoffset']);
				$txt->top = ((int) $xmltext['yoffset']);
				$txt->width = ((int) $xmltext['xsize']);
				$txt->height = ((int) $xmltext['ysize']);
				$txt->content = ((string) $xmltext);
				$panel->add_text($txt);
			}
			//$strip->add_panel($panel);
		}
		foreach ($xml->paneltext as $xmltext) {
			$txt = $strip->add_text();
			$txt->left = ((int) $xmltext['xoffset']);
			$txt->top = ((int) $xmltext['yoffset']);
			$txt->width = ((int) $xmltext['xsize']);
			$txt->height = ((int) $xmltext['ysize']);
			$txt->content = ((string) $xmltext);
			//$strip->add_panel($txt);
		}
		debug("Strip<pre>".print_r($strip,true)."</pre><br />");
		return $strip;
	}

	/**
	 * Parse character information from an XML data file
	 * we take in an ID and information on where to find the 
	 * definition of this character. $source will determine what
	 * we do with $href:
	 *   $source    action
	 *   local      use $href as a local path
	 *   http       use $href as a URL and fetch it from the network
	 * TODO implement 'http' source, possibly others such as ftp or xml-rpc
     */
	function parse_player($id, $source, $href) {
		$player = null;
		switch ($source) {
		case 'local':
			// read local file
			$file = $this->playerdir."/$href";
			if (!file_exists($file)) {
				throw new Exception("File not found: $file");
			}
			$xml = new SimpleXMLElement(file_get_contents($file));
			$player = new Character();
			if ($xml->name) {
				$player->name = ((string) $xml->name);
			}
			if ($xml->defaultmood) {
				$player->mood = ((int) $xml->defaultmood);
			}
			if ($xml->facecolour) {
				$player->face->fillcolor = ((string) $xml->facecolour);
			}
			foreach ($xml->mood as $xmlmood) {
				// TODO create 'Mood' object, and add it to player
			}
			return $player;
			break;
		default:
			throw new Exception ("Character data source '$source' not implemented");
		}
	}
}
