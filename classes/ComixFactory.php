<?php
/**
 * Abstract class representing for generators of comics from different data
 * sources.
 */
interface ComixFactory {
	public function fetch_strip($id=null);
}

?>
