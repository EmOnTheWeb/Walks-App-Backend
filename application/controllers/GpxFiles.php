<?php
header("Access-Control-Allow-Origin: *");
defined('BASEPATH') OR exit('No direct script access allowed');

class Gpxfiles extends CI_Controller {

	public function index()
	{
		
	}

	public function listWalks() {
		$iterator = new FilesystemIterator("./gpx_files");
		$filelist = array();
		foreach($iterator as $entry) {
			if($entry->getFilename() !== '.DS_Store') {
	        	$filelist[] = $entry->getFilename();
			}
		}
		print_r(json_encode($filelist)); 
	}

	public function getDirections($filename) {
		//add back extension
		$filename .= '.gpx'; 
		$filepath = "./gpx_files/".$filename; 
		$fileContents = file_get_contents($filepath); 
		//parse file contents
		$xml = simplexml_load_string($fileContents);
		$coordinates = $xml->rte->rtept; 

		//build coordinates string for api 
		$coordinatesString=''; 

		$i=0; 
		foreach($coordinates as $coordinate) {
			$coordinatesString .= $coordinate['lon'].','.$coordinate['lat']; 
			if($i !== sizeof($coordinates)-1) {
				$coordinatesString .= ';'; 
			}
			$i++; 
		}

		$mapboxRequestUrl = "https://api.mapbox.com/directions/v5/mapbox/walking/".$coordinatesString."?steps=true&access_token=pk.eyJ1IjoiZW1pbGllZGFubmVuYmVyZyIsImEiOiJjaXhmOTB6ZnowMDAwMnVzaDVkcnpsY2M1In0.33yDwUq670jHD8flKjzqxg"; 

		$curl = curl_init();
		
		curl_setopt_array($curl, array(
	    	CURLOPT_RETURNTRANSFER => 1,
	    	CURLOPT_URL => $mapboxRequestUrl
		));
		
		$response = curl_exec($curl); 
		curl_close($curl);
		print_r($response); //send me off	
	}

	public function getLandmarks($walkName) {
		$walkName .= '.txt'; 
		$filepath = "./landmark_descriptions/".$walkName; 
		$fileContents = file_get_contents($filepath); 

		print_r($fileContents); 	
	}
}