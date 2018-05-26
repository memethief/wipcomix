<?php
/**
 * When we catch an exception, sometimes we want to print out an image with
 * the exception message in it.
 */
class SVGException implements SVGObject {
	private $exception;
	private $width = 700;
	private $height = 300;

	function __construct(Exception $e) {
		$this->exception = $e;
	}

	public function draw(DOMDocument $doc) {
		//die($this->exception->toString());
		$node = $doc->createElement("text");
		$node->appendChild($doc->createTextNode("Error: ".$this->exception->getMessage()));
		$node->setAttribute('x', '10');
		$node->setAttribute('y', '20');
		return $node;
	}

	public function getHeight() { return $this->height; }
	public function getWidth() { return $this->width; }
}
