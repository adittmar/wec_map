<?php
/***************************************************************
* Copyright notice
*
* (c) 2005 Foundation for Evangelism (info@evangelize.org)
* All rights reserved
*
* This file is part of the Web-Empowered Church (WEC) ministry of the
* Foundation for Evangelism (http://evangelize.org). The WEC is developing 
* TYPO3-based free software for churches around the world. Our desire 
* use the Internet to help offer new life through Jesus Christ. Please
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
 * Service 'Google Maps Address Lookup' for the 'wec_map' extension.
 *
 * @author	Web-Empowered Church Team <map@webempoweredchurch.org>
 */


require_once(PATH_t3lib.'class.t3lib_svbase.php');

/**
 * Service providing lat/long lookup via the Google Maps web service.  
 *
 * @author Web Empowered Church Team <map@webempoweredchurch.org>
 * @package TYPO3
 * @subpackage tx_wecmap
 */
class tx_wecmap_geocode_google extends t3lib_svbase {
	var $prefixId = 'tx_wecmap_geocode_google';		// Same as class name
	var $scriptRelPath = 'geocode_service/class.tx_wecmap_geocode_google.php';	// Path to this script relative to the extension dir.
	var $extKey = 'wec_map';	// The extension key.
	
	/**
	 * Performs an address lookup using the geocoder.us web service.
	 *
	 * @param	string	The street address.
	 * @param	string	The city name.
	 * @param	string	The state name.
	 * @param	string	The ZIP code.
	 * @param	string	Optional API key for accessing third party geocoder.
	 * @return	array		Array containing latitude and longitude.  If lookup failed, empty array is returned.
	 */
	function lookup($street, $city, $state, $zip, $country, $key='')	{
		/* @todo	Remove hardcoded key.  Probably need to read from TYPO3_CONF_VARS. */
		$key = "ABQIAAAAScd0pUOT9cBlpN4mGZvcdhT2yXp_ZAY8_ufC3CFXhHIE1NvwkxRoVw8gdqw8aa4vFYZ-nFW1gmhCPw";

		$url = 'http://maps.google.com/maps/geo?'.
				$this->buildURL('q', $street.' '.$city.', '.$state.' '.$zip.', '.$country).
				$this->buildURL('output', 'csv').
				$this->buildURL('key', $key);

		$csv = t3lib_div::getURL($url);
		
		$latlong = array();
		$csv = explode(',', $csv);
		
		if($csv[0] == 200) {
			$latlong['lat'] = $csv[2];
			$latlong['long'] = $csv[3];
			
			return $latlong;
		} else {
			return null;
		}
		
	}
	
	function buildURL($name, $value){
		if($value) {
			return $name."=".str_replace(' ', '+', $value)."&";
		}
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_google.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wec_map/geocode_service/class.tx_wecmap_geocode_google.php']);
}

?>