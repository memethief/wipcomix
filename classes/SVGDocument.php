<?php
/**
 * class to describe the root of an SVG document
 */
class SVGDocument extends DOMDocument implements SVGObject {
	private $svgroot;

	function __construct($text=null) {
		DOMDocument::__construct('1.0','iso-8859-1');
		$this->formatOutput = true;
		$impl = new DOMImplementation;
		$dtd = $impl->createDocumentType('svg','-//W3C//DTD SVG 1.1//EN',
			"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd");
		$this->appendChild($dtd);
		$this->svgroot = $this->createElement('svg');
		$this->svgroot->setAttribute('version', '1.1');
		$this->svgroot->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
		if ($text != null) {
			$this->appendChild($this->createComment($text));
			$desc = $this->createElement("desc");
			$desc->appendChild($this->createTextNode($text));
			$this->svgroot->appendChild($desc);
		}
	}

	function setDimensions($width, $height) {
		$this->svgroot->setAttribute('width', $width.'px');
		$this->svgroot->setAttribute('height', $height.'px');
	}

	function appendObject(SVGObject $node) {
		$this->svgroot->appendChild($node->draw($this));
	}

	function draw(DOMDocument $doc) {
		$this->appendChild($this->svgroot);
		return $this->saveXML();
	}
}
?>
