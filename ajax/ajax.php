<?php
/* Functions to be called via ajax */

echo ajax_dispatch();

/**
 * Read in the query string and decide which function to call
 */
function ajax_dispatch() {
	$action = $_REQUEST['action'];
	if ($action == "getpanelinfo") { return ajax_get_panel_info(); }
	if ($action == "edit_panel") { return ajax_edit_panel(); }
	return ajax_default();
}

/**
 * ajax_error: generic function to print out an XML error and then
 * exit.
 */
function ajax_error($errmsg = "Unknown error") {
	$doc = new DOMDocument();
	$doc->appendChild($doc->createElement("p",$errmsg));
	return $doc->saveXML();
}

/**
 * ajax_default: the default function for when there is a missing or
 * invalid action variable.
 *
 * Returns an XML object with an error in it
 */
function ajax_default() {
	$errortext = "Error calling AJAX function: invalid action '".$_REQUEST['action']."'";
	return ajax_error($errortext);
}

/**
 * ajax_edit_panel: save panel information to the database
 */
function ajax_edit_panel() {
	require_once("queries.php");
	if ($_REQUEST['panel_ID']) $q = mk_panel_update_query();
	else $q = mk_panel_insert_query();
	$ret = "<pre>\nRequest:\n";
	foreach ($_REQUEST as $name=>$value) { $ret .= "[$name] = [$value]\n"; }
	$ret .= "query: $q\n";
	if ($q) { ComixDB::get()->query($q) or $ret .= ajax_error(mysqli_error()); }
	else $ret .= ajax_error("Could not process request. Check your values.");
	return "$ret\n</pre>\n".ajax_get_panel_info();
}

/**
 * ajax_get_panel_info: return an XML object representing a panel of
 * a strip.
 * 
 * If the panel ID is not specified in the request headers an error document is returned.
 */
function ajax_get_panel_info() {
	$panel_id = $_REQUEST['panel_ID'];
	$strip_id = $_REQUEST['panel_strip'];
	if ($panel_id) try { $panel = ComixDB::get()->fetch_panel_by_id($panel_id); } 
	catch (Exception $e) { return ajax_error("Error in ajax_get_panel_info(): ".$e->getMessage()); } 
	else if ($strip_id) { $panel->panel_strip = $strip_id; } 
	else { return ajax_error("Error: no strip ID specified"); }
	// Create an HTML form and return it
	// form-building functions
	require_once("forms.php");
	$doc = new DomDocument();
	$form = mk_form($doc, "form_edit_panel", "comix-admin.php", "updatePanelInfo(this);return false");
	$elements = Array();
	// strip & panel ID
	mk_readonly($doc, $form, "Strip ID", "panel_strip", $panel->panel_strip);
	mk_readonly($doc, $form, "Panel ID", "panel_ID", $panel->panel_ID);
	mk_br($doc, $form);
	// panel number
	mk_text($doc, $form, "Panel #", "panel_number", $panel->panel_number);
	mk_br($doc, $form);
	// panel coordinates and dimensions
	mk_text($doc, $form, "Left", "panel_left", $panel->panel_left);
	mk_text($doc, $form, "Top", "panel_top", $panel->panel_top);
	mk_br($doc, $form);
	mk_text($doc, $form, "Width", "panel_width", $panel->panel_width);
	mk_text($doc, $form, "Height", "panel_height", $panel->panel_height);
	mk_br($doc, $form);
	// panel characters
	if ($panel->chars) foreach ($panel->chars as $character) {
		$form->appendChild($doc->createTextNode(
			"Character $character->character_name: ID = $character->character_ID"
			));
		mk_br($doc, $form);
	}
	// submit button
	mk_input($doc, $form, "submit", "panel_submit", "Save Panel", "panel_submit_$panel->panel_ID");
	// 
	$doc->appendChild($form);
	return $doc->saveXML();
}

/**
 * Autoload any classes we need
 */
function __autoload($class_name) {
        require_once("../classes/$class_name.php");
}

?>
