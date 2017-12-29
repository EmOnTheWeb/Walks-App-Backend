<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
defined('BASEPATH') OR exit('No direct script access allowed');

class Gpxfiles extends CI_Controller {

	public function index()
	{
		
	}

	public function listWalks() {
		// $iterator = new FilesystemIterator("./gpx_files");
		// $filelist = array();
		// foreach($iterator as $entry) {
		// 	if($entry->getFilename() !== '.DS_Store') {
	 //        	$filelist[] = $entry->getFilename();
		// 	}
		// }

		$this->load->database();
		$this->db->select('name,image_url,description');
        $this->db->from('walks');

        $query = $this->db->get();
    	print_r(json_encode($query->result())); 
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
	 	
	 	$this->load->driver('cache', array('adapter' => 'file', 'backup' => 'file'));

	 	if ( ! $mapboxDirections = $this->cache->get($filename.'_mbdirections')) {
	        
	        $mapboxRequestUrl = "https://api.mapbox.com/directions/v5/mapbox/walking/".$coordinatesString."?steps=true&access_token=pk.eyJ1IjoiZW1pbGllZGFubmVuYmVyZyIsImEiOiJjaXhmOTB6ZnowMDAwMnVzaDVkcnpsY2M1In0.33yDwUq670jHD8flKjzqxg"; 

			$curl = curl_init();
			
			curl_setopt_array($curl, array(
		    	CURLOPT_RETURNTRANSFER => 1,
		    	CURLOPT_URL => $mapboxRequestUrl
			));
			
			$mapboxDirections = curl_exec($curl); 
			curl_close($curl);

		    // Save into the cache for 1 week 
		    $this->cache->save($filename.'_mbdirections', $mapboxDirections,604800);

		}

		$response_array = array(
			'turn-by-turn' => $mapboxDirections,
			'waypoint-coordinates' => $coordinatesString
		); 

		print_r(json_encode($response_array)); //send me off	
	}

	public function getLandmarks($walkName) {
		$walkName .= '.txt'; 
		$filepath = "./landmark_descriptions/".$walkName; 
		$fileContents = file_get_contents($filepath); 

		print_r(json_encode($fileContents)); 	
	}

	public function getWaypointImages($waypointFilename) {
		$iterator = new FilesystemIterator("./landmark_descriptions/images");
		$imgPathList = array();
		foreach($iterator as $entry) {
			if($entry->getFilename() !== '.DS_Store') {
				if(strpos($entry->getFilename(), $waypointFilename) !== false) {
	        		$imgPathList[] = $entry->getPathname();
	        	}
			}
		}
		print_r(json_encode($imgPathList)); 
	} 
}