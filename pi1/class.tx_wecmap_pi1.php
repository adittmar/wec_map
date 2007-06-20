<?php
/***************************************************************
* Copyright notice
*
* (c) 2007 Foundation For Evangelism (info@evangelize.org)
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC)
* (http://webempoweredchurch.org) ministry of the Foundation for Evangelism
* (http://evangelize.org). The WEC is developing TYPO3-based
* (http://typo3.org) free software for churches around the world. Our desire
* is to use the Internet to help offer new life through Jesus Christ. Please
* see http://WebEmpoweredChurch.org/Jesus.
*
* You can redistribute this file and/or modify it under the terms of the
* GNU General Public License as published by the Free Software Foundation;
* either version 2 of the License, or (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This file is distributed in the hope that it will be useful for ministry,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the file!
***************************************************************/

/**
 * Plugin 'Map' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');

/**
 * Simple frontend plugin for displaying an address on a map.  
 *
 * @author Web-Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_pi1 extends tslib_pibase {
	var $prefixId = 'tx_wecmap_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_wecmap_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	var $pi_checkCHash = TRUE;
	
	/**
	 * Draws a Google map based on an address entered in a Flexform.
	 * @param	array		Content array.
	 * @param	array		Conf array.
	 * @return	string	HTML / Javascript representation of a Google map.
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		/* Initialize the Flexform and pull the data into a new object */
		$this->pi_initPIflexform();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		
		// get configuration from flexform or TS. Flexform values take
		// precedence.
		$apiKey = $this->pi_getFFvalue($piFlexForm, 'apiKey', 'mapConfig');
		empty($apiKey) ? $apiKey = $conf['apiKey']:null;

		$width = $this->pi_getFFvalue($piFlexForm, 'mapWidth', 'mapConfig');
		empty($width) ? $width = $conf['width']:null;
		
		$height = $this->pi_getFFvalue($piFlexForm, 'mapHeight', 'mapConfig');
		empty($height) ? $height = $conf['height']:null;
		
		$mapControlSize = $this->pi_getFFvalue($piFlexForm, 'mapControlSize', 'mapControls');
		(empty($mapControlSize) || $mapControlSize == 'none') ? $mapControlSize = $conf['controls.']['mapControlSize']:null;

		$overviewMap = $this->pi_getFFvalue($piFlexForm, 'overviewMap', 'mapControls');
		empty($overviewMap) ? $overviewMap = $conf['controls.']['showOverviewMap']:null;
				
		$mapType = $this->pi_getFFvalue($piFlexForm, 'mapType', 'mapControls');
		empty($mapType) ? $mapType = $conf['controls.']['showMapType']:null;
		
		$initialMapType = $this->pi_getFFvalue($piFlexForm, 'initialMapType', 'mapConfig');
		empty($initialMapType) ? $initialMapType = $conf['initialMapType']:null;
				
		$scale = $this->pi_getFFvalue($piFlexForm, 'scale', 'mapControls');
		empty($scale) ? $scale = $conf['controls.']['showScale']:null;

		$showDirs = $this->pi_getFFvalue($piFlexForm, 'showDirections', 'mapConfig');
		empty($showDirs) ? $showDirs = $conf['showDirections']:null;

		$showWrittenDirs = $this->pi_getFFvalue($piFlexForm, 'showWrittenDirections', 'mapConfig');
		empty($showWrittenDirs) ? $showWrittenDirs = $conf['showWrittenDirections']:null;
				
		$prefillAddress = $this->pi_getFFvalue($piFlexForm, 'prefillAddress', 'mapConfig');
		empty($prefillAddress) ? $prefillAddress = $conf['prefillAddress']:null;
		
		$centerLat = $conf['centerLat'];
		
		$centerLong = $conf['centerLong'];
		
		$zoomLevel = $conf['zoomLevel'];
		
		$mapName = $conf['mapName'];
		if(empty($mapName)) $mapName = 'map'.$this->cObj->data['uid'];

		// get this from flexform only. If empty, we check the TS, see below.
		$street = $this->pi_getFFvalue($piFlexForm, 'street', 'default');
		$city = $this->pi_getFFvalue($piFlexForm, 'city', 'default');
		$state = $this->pi_getFFvalue($piFlexForm, 'state', 'default');
		$zip = $this->pi_getFFvalue($piFlexForm, 'zip', 'default');
		$country = $this->pi_getFFvalue($piFlexForm, 'country', 'default');
		$title = $this->pi_getFFvalue($piFlexForm, 'title', 'default');
		$description = $this->pi_getFFvalue($piFlexForm, 'description', 'default');

		/* Create the map class and add markers to the map */
		include_once(t3lib_extMgm::extPath('wec_map').'map_service/google/class.tx_wecmap_map_google.php');
		$className = t3lib_div::makeInstanceClassName('tx_wecmap_map_google');
		$map = new $className($apiKey, $width, $height, $centerLat, $centerLong, $zoomLevel, $mapName);	

		// evaluate config to see which map controls we need to show
		if($mapControlSize == 'large') {
			$map->addControl('largeMap');	
		} else if ($mapControlSize == 'small') {
			$map->addControl('smallMap');	
		} else if ($mapControlSize == 'zoomonly') {
			$map->addControl('smallZoom');	
		}
		
		if($scale) $map->addControl('scale');
		if($overviewMap) $map->addControl('overviewMap');
		if($mapType) $map->addControl('mapType');
		if($initialMapType) $map->setType($initialMapType);
		
		// check whether to show the directions tab and/or prefill addresses and/or written directions
		if($showDirs && $showWrittenDirs && $prefillAddress) $map->enableDirections(true, $mapName.'_directions');
		if($showDirs && $showWrittenDirs && !$prefillAddress) $map->enableDirections(false, $mapName.'_directions');
		if($showDirs && !$showWrittenDirs && $prefillAddress) $map->enableDirections(true);
		if($showDirs && !$showWrittenDirs && !$prefillAddress) $map->enableDirections();
		
		// determine if an address has been set through flexforms. If not, process TS		
		if(empty($zip) && empty($state) && empty($city)) {

			// loop through markers
			foreach($conf['markers.'] as $marker) {
				
				// determine if address was entered by string or separated
				if(array_key_exists('address', $marker)) {

					$title = $this->makeTitle($marker);
					$description = $this->makeDescription(array('description'=> $marker['description']));
					$address = $this->wrapAddressString($marker['address']);
					$description = $description.$address;
					
					// add address by string
					$map->addMarkerByString($marker['address'], $title, $description);

				} else {

					$title = $this->makeTitle($marker);
					$address = $this->makeAddress($marker);
					$description = $this->makeDescription($marker);

					$description = $description . $address;
					
					// add the marker to the map
					$map->addMarkerByAddress($marker['street'], $marker['city'], $marker['state'], 
											 $marker['zip'], $marker['country'], $title, 
											 $description);	
				}
			}
		} else {		
			// put all the data into an array
			$data['city'] = $city;
			$data['state'] = $state;
			$data['street'] = $street;
			$data['zip'] = $zip;
			$data['country'] = $country;
			$data['title'] = $title;
			$data['description'] = $description;

			$title = $this->makeTitle($data);
			$address = $this->makeAddress($data);
			$description = $this->makeDescription($data);
			
			$description = $description . $address;
			
			// add the marker to the map
			$map->addMarkerByAddress($street, $city, $state, $zip, $country, $title, $description);			
		}
		

		// draw the map
		$content = $map->drawMap();
		
		// add directions div if applicable
		if($showWrittenDirs) $content .= '<div id="'.$mapName.'_directions"></div>';

		return $this->pi_wrapInBaseClass($content);
	}
	
	function makeDescription($row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle( $this->conf['marker.']['description'], $this->conf['marker.']['description.'] );
		return $output;
	}
	
	function makeAddress($row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle( $this->conf['marker.']['address'], $this->conf['marker.']['address.'] );
		return $output;
	}
	
	function makeTitle($row) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->cObjGetSingle( $this->conf['marker.']['title'], $this->conf['marker.']['title.'] );
		return $output;
	}

	function wrapAddressString($address) {
		$local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
		$local_cObj->start($row, 'fe_users' );
		$output = $local_cObj->stdWrap($address, $this->conf['marker.']['address.'] );		
		return $output;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/pi1/class.tx_wecmap_pi1.php']);
}

?>