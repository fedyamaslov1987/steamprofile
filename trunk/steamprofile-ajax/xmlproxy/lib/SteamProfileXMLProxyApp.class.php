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
	public function run() {
		try {
			// load config
			$Config = Config::load('xmlproxy.cfg');
			
			$iCacheLifetime = $Config->getInteger('cache.lifetime', 600);
			$sCacheDir= $Config->getString('cache.dir', 'cache');
			$bXMLHttpRequestOnly = $Config->getBoolean('proxy.check_header', true);
			$iDownloaderTimeout	= $Config->getInteger('downloader.timeout', 10);
			
			// response to XMLHttpRequest only
			if($bXMLHttpRequestOnly && (
				!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
				$_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest'
			)) {
				header('HTTP/1.1 204 No Content');
				return;
			}
		
			// get selected id
			if(isset($_GET['id']) && !empty($_GET['id'])) {
				$sID = $_GET['id'];
			} else {
				throw new Exception('No profile ID assigned');
			}
			
			$SteamID = new SteamID($sID);
			$sXmlUrl = 'http://steamcommunity.com/';

			// choose if we got a numeric id or an alias
			if(!$SteamID->isValid()) {
				// complain about invalid characters, if found
				if(!preg_match('/^[a-zA-Z0-9-_]+$/', $sID)) {
					throw new RuntimeException("Invalid profile alias");
				}
				
				$sXmlUrl .= 'id/'.$sID;
			} else {
				$sXmlUrl .= 'profiles/'.$SteamID->getSteamComID();
			}

			// add xml parameter so we get xml data (hopefully)
			$sXmlUrl .= '?xml=1';
			
			$XmlCache = new Cache($sCacheDir, $iCacheLifetime, 'xml');
			$XmlFile = $XmlCache->getFile($sXmlUrl);

			// do we have a cached version of the xml document?
			if(!$XmlFile->isCached()) {
				try {
					// initialize the downloader
					$Downloader = new SteamProfileXMLDownloader($sXmlUrl, 'Ajax');
					$Downloader->setTimeout($iDownloaderTimeout);
					$Downloader->setTrimExtra(true);
					$Downloader->setFilterCtlChars(true);
					
					$sXml = '';
					
					try {
						// try to download the XML file
						$sXml = $Downloader->start();
					} catch(Exception $e) {
						// didn't work, close cURL handle
						$Downloader->close();
						throw $e;
					}
					
					// close cURL handle
					$Downloader->close();
					
					// save document to cache
					$XmlFile->saveString($sXml);
				} catch(Exception $e) {
					// downloading failed, but maybe we can redirect to the old file
					if(!$XmlFile->exists()) {
						// no, we can't
						throw $e;
					}
				}
			}
			
			// redirect to xml file
			$sHost = $_SERVER['HTTP_HOST'];
			$sUri = dirname($_SERVER['PHP_SELF']);
			$sFile = basename($XmlFile->getPath());
			header("Location: http://$sHost$sUri/$sCacheDir/$sFile");
		} catch(Exception $e) {
			// set content-type header
			header('Content-Type: text/xml', true);
			echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
			echo '<response><error><![CDATA['.$e->getMessage().']]></error></response>';
		}
	}
}
?>