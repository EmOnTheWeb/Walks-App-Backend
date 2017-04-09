<?php
header("Access-Control-Allow-Origin: *");
defined('BASEPATH') OR exit('No direct script access allowed');

class GpxFiles extends CI_Controller {

	public function index()
	{
		
	}

	public function listWalks() {
		$iterator = new FilesystemIterator("./gpx_files");
		$filelist = array();
		foreach($iterator as $entry) {
		        $filelist[] = $entry->getFilename();
		}
		echo json_encode($filelist); 
	}

	public function getDirections($filename) {
		//add back extension
		$filename .= '.gpx'; 
		$filepath = "./gpx_files/".$filename; 
		$fileContents = file_get_contents($filepath); 
		//parse file contents
		$xml = simplexml_load_string($fileContents);
		var_dump($xml->rte); 
	}
}