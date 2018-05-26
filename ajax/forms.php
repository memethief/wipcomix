<?php
/* Functions to build form elements */

function mk_form($doc, $name, $action, $onclick=NULL, $method="POST") {
	$form = $doc->createElement("form");
	$form->setAttribute("action",$action);
	$form->setAttribute("name",$name);
	$form->setAttribute("method",$method);
	if ($onclick) $form->setAttribute("onclick",$onclick);
	return $form;
}
function mk_readonly($doc, &$form, $label, $name, $value) {
	mk_input($doc, $form, "hidden",$name,$value);
	$form->appendChild($doc->createElement("span","$label: $value"));
}
function mk_text($doc, &$form, $label, $name, $value) {
	$id = $name."_".rand();
	$l = $doc->createElement("label",$label);
	$l->setAttribute("for",$id);
	$form->appendChild($l);
	mk_input($doc, $form, "hidden",$name."_old",$value);
	mk_input($doc, $form, "text", $name, $value, $id);
}
function mk_input($doc, &$form, $type, $name, $value, $id=null, $attributes=null) {
	$i = $doc->createElement("input");
	$i->setAttribute("type",$type);
	$i->setAttribute("name",$name);
	$i->setAttribute("value",$value);
	if ($id) $i->setAttribute("id",$id);
	if ($attributes) foreach ($attributes as $name=>$value) {
		$i->setAttribute($name,$value);
	}
	$form->appendChild($i);
}
function mk_br($doc, &$form) {
	$form->appendChild($doc->createElement("br"));
}

?>
