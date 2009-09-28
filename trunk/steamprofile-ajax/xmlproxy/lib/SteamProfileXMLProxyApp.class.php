<?php
/**
 *	This file is part of SteamProfile.
 *
 *	Written by Nico Bergemann <barracuda415@yahoo.de>
 *	Copyright 2009 Nico Bergemann
 *
 *	SteamProfile is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	SteamProfile is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with SteamProfile.  If not, see <http://www.gnu.org/licenses/>.
 */

class SteamProfileXMLProxyApp {
	const VERSION = '2.0b1';
	
	private $bXMLHttpRequestOnly = true;
	private $iCacheLifetime = 600;
	private $iTimeout = 10;
	
	public function setXMLHttpRequestOnly($bXMLHttpRequestOnly) {
		$this->bXMLHttpRequestOnly = (bool)$bXMLHttpRequestOnly;
	}
	
	public function setCacheLifetime($iCacheLifetime) {
		$this->iCacheLifetime = (int)$iCacheLifetime;
	}

	public function setTimeout($iTimeout) {
		$this->iTimeout = (int)$iTimeout;
	}

	public function run() {
		try {
			// response to XMLHttpRequest only
			if($this->bXMLHttpRequestOnly && (
				!isset($_SERVER['HTTP_X_REQUESTED_WITH'])
				|| $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest'
			)) {
				header('HTTP/1.1 204 No Content');
				return;
			}
		
			// get selected id
			if(isset($_GET['id']) && !empty($_GET['id'])) {
				$sID = $_GET['id'];
			} else {
				throw new Exception('No Steam-ID or Community-ID specified!');
			}
			
			$SteamID = new SteamID($sID);
			$sXMLUrl = 'http://steamcommunity.com/';

			// choose if we got a numeric id or an alias
			if(!$SteamID->isValid()) {
				// complain about invalid characters, if found
				if(!preg_match('/^[a-zA-Z0-9-_]+$/', $sID)) {
					throw new RuntimeException("Invalid profile alias");
				}
				
				$sXMLUrl .= 'id/'.$sID;
			} else {
				$sXMLUrl .= 'profiles/'.$SteamID->getSteamComID();
			}

			// add xml parameter so we get xml data (hopefully)
			$sXMLUrl .= '?xml=1';

			$Cache = new CacheManager('xml', $this->iCacheLifetime, 'xml');
			$CacheEntry = $Cache->getEntry($sXMLUrl);

			// do we have a cached version of the xml document?
			if(!$CacheEntry->isCached()) {
				$cURL = new SteamProfileDownloader($sXMLUrl, self::VERSION);
				$cURL->setOutputFile($CacheEntry->getPath());
				$cURL->setTimeout($this->iTimeout);
			
				try {
					if($cURL->start() === false) {
						throw new RuntimeException("Steam Community server offline");
					}

					if($cURL->getHTTPCode() != 200) {
						throw new RuntimeException("Steam Community server error");
					}
				} catch(Exception $e) {
					$cURL->close();
					throw $e;
				}
				
				$cURL->close();
			}
			
			// redirect to xml file
			$sHost = $_SERVER['HTTP_HOST'];
			$sUri = dirname($_SERVER['PHP_SELF']);
			$sFile = basename($CacheEntry->getPath());
			header("Location: http://$sHost$sUri/xml/$sFile");
		} catch(Exception $e) {
			// set content-type header
			header('Content-Type: text/xml', true);
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
			echo '<response><error><![CDATA['.$e->getMessage().']]></error></response>';
		}
	}
}
?>