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

class SteamProfileImageApp {
	public function run() {
		try {		
			// get selected id
			if(isset($_GET['id']) && !empty($_GET['id'])) {
				$sID = $_GET['id'];
			} else {
				throw new Exception('No Steam-ID or Community-ID specified!');
			}
			
			$SteamID = new SteamID($sID);
			$sXmlUrl = 'http://steamcommunity.com/';

			// choose if we got a numeric id or an alias
			if(!$SteamID->isValid()) {
				// complain about invalid characters, if found
				if(!preg_match('/^[a-zA-Z0-9-_]+$/', $sID)) {
					throw new RuntimeException("Invalid profile alias: $sID");
				}
				
				$sXmlUrl .= 'id/'.$sID;
			} else {
				$sXmlUrl .= 'profiles/'.$SteamID->getSteamComID();
			}

			// add xml parameter so we get xml data (hopefully)
			$sXmlUrl .= '?xml=1';
			
			// load config
			$Config = Config::load('image.cfg');
			
			$bImageFallback = $Config->getString('image.fallback', true);
			$bImageRedirect = $Config->getString('image.redirect', true);
			$sDefaultTheme = $Config->getString('theme.default', 'default');
			$iCacheLifetime = $Config->getInteger('cache.lifetime', 600);
			$sCacheDirProfiles = $Config->getString('cache.dir.profiles', 'cache/profiles');
			
			$sTheme = (isset($_GET['theme']) && ctype_alnum($_GET['theme']))? $_GET['theme'] : $sDefaultTheme;
			$ImageCache = new Cache($sCacheDirProfiles, $iCacheLifetime, 'png');
			$ImageFile = $ImageCache->getFile($_SERVER['QUERY_STRING']);
				
			try {
				// do we have a cached version of the profile image?
				if(!$ImageFile->isCached()) {
					$ProfileImage = new SteamProfileImage();
					// try to generate the profile image
					$ProfileImage->createProfile($sXmlUrl, $sTheme);
					// save it to cache
					$ProfileImage->toPNG($ImageFile->getPath());
				}
				
				$this->displayImage($ImageFile, $bImageRedirect);
			} catch(SteamProfileImageException $e) {
				if(isset($_GET['debug'])) {
					throw $e->getPrevious();
				}
				// an exception was thrown in SteamProfileImage,
				// but a themed error image could have been generated
				try {
					// try a fallback to the cached image first
					if($bImageFallback && $ImageFile->exists()) {
						$this->displayImage($ImageFile, $bImageRedirect);
					} else {
						// try to display the error image
						$ProfileImage->toPNG();
					}
				} catch(Exception $f) {
					// didn't work, re-throw the source exception
					throw $e->getPrevious();
				}
			} catch(Exception $e) {
				// an exception was thrown in SteamProfileImage,
				// but we could try a fallback to the cached image
				if($bImageFallback && $ImageFile->exists()) {
					// redirect to cached image file
					$this->displayImage($ImageFile, $bImageRedirect);
				} else {
					// nothing cached, re-throw exception
					throw $e;
				}
			}
		} catch(Exception $e) {
			if(isset($_GET['debug'])) {
				header('Content-Type: text/plain');
				echo "$e\n";
			} else {
				$ErrorImage = new ErrorImage($e->getMessage());
				$ErrorImage->toPNG();
			}
		}
	}
	
	private function displayImage(File $ImageFile, $bRedirect) {
		if($bRedirect) {
			// redirect to cached image file
			$sFile = $ImageFile->getPath();
			$sHost = $_SERVER['HTTP_HOST'];
			$sUri = dirname($_SERVER['PHP_SELF']);
			header("Location: http://$sHost$sUri/$sFile");
		} else {
			// print cached image file
			header('Content-Type: image/png');
			$ImageFile->readToStdOut();
		}
	}
}