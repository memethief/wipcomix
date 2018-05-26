<?php
/**
 * Abstract class representing an object with an associated SVG node. 
 * Defines methods for building and printing the node.
 */
interface SVGObject {
	public function draw(DOMDocument $doc);
}

?>
