<?php

/**
 * Abstract class representing an object with an associated SVG node. 
 * Defines methods for building and printing the node.
 */
interface SVGObject
{
    /**
     * Given a DOMDocument object, use it to generate a DOMElement representing this whole object. This element can
     * then be turned into an XML string by calling the method `saveXML()` on it.
     * 
     * @param DOMDocument $doc used for DOM rendering
     * @return DOMElement representing the object
     */
    public function draw(DOMDocument $doc): DOMElement;
}

?>
