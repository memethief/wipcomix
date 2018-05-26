<?php

/**
 * class to handle database requests
 */
class ComixDB implements ComixFactory {
	private static $host = "localhost";
	private static $name = "god_comix";
	private static $user = "god_comix";
	private static $pass = "{|y8hUwq";
	private $dbObj;
	public static $singleton = null;

	function __construct($host,$user,$pass,$name) {
		$this->dbObj = mysqli_connect(
			$host, $user, $pass, $name)
			or die ("Cannot connect to server on $host: ".
				mysql_error());
	}

	/* factory method */
	static function get() {
		if (ComixDB::$singleton == null) 
			ComixDB::$singleton = new ComixDB(ComixDB::$host, ComixDB::$user, ComixDB::$pass, ComixDB::$name);
		return ComixDB::$singleton;
	}

	function query($query_string) { return $this->dbObj->query($query_string); }

	function fetch_character_by_id($id) {
		$q =	"SELECT * FROM comix_character ".
			"WHERE character_ID = $id";
		$res = $this->dbObj->query($q);
		return $res->fetch_object();
	}

	function fetch_strip_by_id($id) {
		$q = 	"SELECT * from comix_strip ";
		if ($id != null) { $q .= "WHERE strip_ID = $id "; }
		$q .=	"ORDER BY strip_date desc LIMIT 1 ";
		//die($q);
		$res = $this->dbObj->query($q);
		if (!$res or $res->num_rows == 0) throw new Exception("No strip found with ID '$id'");
		$obj = $res->fetch_object();
		$obj->panels = $this->fetch_panels_by_strip_id($obj->strip_ID);
		$obj->texts = $this->fetch_text_by_strip_id($obj->strip_ID);
		return new Strip($obj);
	}

	function fetch_panel_by_id($id) {
		$q = 	"SELECT * FROM comix_panel ".
			"WHERE panel_ID = $id ".
			"ORDER BY panel_number";
		$res = $this->dbObj->query($q);
		if (!$res or $res->num_rows == 0) throw new Exception("No panel found with ID '$id'");
		$panel = $res->fetch_object();
		$panel->chars = $this->fetch_characters_by_panel_id($panel->panel_ID);
		return $panel;
	}

	function fetch_panels_by_strip_id($id) {
		$q = 	"SELECT * FROM comix_panel ".
			"WHERE panel_strip = $id ".
			"ORDER BY panel_number";
		$res = $this->dbObj->query($q);
		$panels = Array();
		//if (!$res) throw new Exception("No panels found for strip '$id'");
		if ($res) { while ($obj = $res->fetch_object()) {
			$obj->chars = $this->fetch_characters_by_panel_id($obj->panel_ID);
			$panels[] = $obj;
		} }
		return $panels;
	}

	function fetch_text_by_strip_id($id) {
		$q =	"SELECT * FROM comix_text ".
			"WHERE text_strip_ID = $id ".
			"ORDER BY text_order ";
		$res = $this->dbObj->query($q);
		$texts = Array();
		if ($res) { while ($obj = $res->fetch_object()) {
			$texts[] = new TextPanel($obj);
		} }
		return $texts;
	}

	function fetch_characters_by_panel_id($id) {
		$q = 	"SELECT ".
			" l.panelcharacters_ID panelcharacters_ID, ".
			" c.character_ID character_ID, ".
			" c.character_name character_name, ".
			" l.character_x character_x, ".
			" l.character_y character_y, ".
			" l.character_mood character_mood, ".
			" l.character_facing character_facing ".
			"FROM comix_character c, comix_panelcharacters l ".
			"WHERE c.character_ID = l.character_ID ".
			" AND l.panel_ID = $id ";
		$res = $this->dbObj->query($q) or die ("Could not fetch characters: \n$q\n".mysql_error());
		$chars = Array();
		while ($obj = $res->fetch_object()) {
			$chars[] = $obj;
		}
		return $chars;
	}
}
