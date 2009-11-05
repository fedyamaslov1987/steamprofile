<?php
/**
 *	This file is part of SteamProfile.
 *
 *	Written by Nico Bergemann <barracuda415@yahoo.de>
 *	Copyright 2008 Nico Bergemann
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

class SteamProfileImage extends GDImage {
    public function createProfile($sProfileUrl, $sCurrentTheme) {
		// load global config
		$Config = Config::load('image.cfg');
		
		$sDefaultTheme = $Config->getString('theme.default', 'default');
		$sDownloaderTimeout	= $Config->getInteger('downloader.timeout', 10);
	
		// set theme path
		$sDefaultThemePath = "themes/$sDefaultTheme";
		$sCurrentThemePath = "themes/$sCurrentTheme";
		
		if(!file_exists($sDefaultThemePath)) {
			throw new RuntimeException('Default theme folder not found');
		}
		
		if(!file_exists($sCurrentThemePath)) {
			$sCurrentThemePath = $sDefaultThemePath;
		}
		
		// the required files for the default theme
		$aThemeFiles = array(
			'background'			=> 'background.png',
			'background_fade'		=> 'background_fade.png',
			'default_avatar'		=> 'default_av.jpg',
			'error'					=> 'error.png',
			'iconholder_ingame'		=> 'iconholder_ingame.png',
			'iconholder_offline'	=> 'iconholder_offline.png',
			'iconholder_online'		=> 'iconholder_online.png'
		);
		
		// check for existing theme files
		foreach($aThemeFiles as $sKey => $sFile) {
			if(!file_exists("$sDefaultThemePath/$sFile")) {
				throw new RuntimeException("Missing default theme file '$sDefaultThemePath/$sFile'");
			}
		
			if(file_exists("$sCurrentThemePath/$sFile")) {
				$aThemeFiles[$sKey] = "$sCurrentThemePath/$sFile";
			} else {
				$aThemeFiles[$sKey] = "$sDefaultThemePath/$sFile";
			}
		}
		
		// set theme config paths
		$sDefaultThemeConfigFile = "$sDefaultThemePath/theme.cfg";
		$sCurrentThemeConfigFile = "$sCurrentThemePath/theme.cfg";
		
		if(!file_exists($sDefaultThemeConfigFile)) {
			throw new RuntimeException('Default theme config not found');
		}
		
		// load default config
		$ThemeConfig = Config::load($sDefaultThemeConfigFile);
		
		// merge default config with selected theme config, if existing
		if($sCurrentTheme !== $sDefaultTheme && file_exists($sCurrentThemeConfigFile)) {
			$ThemeConfig->merge(Config::load($sCurrentThemeConfigFile));
		}
		
		$iTextBaseX			= $ThemeConfig->getInteger('theme.position.text.x');
		$iTextBaseY			= $ThemeConfig->getInteger('theme.position.text.y');
		$iTextPadding		= $ThemeConfig->getInteger('theme.position.text.padding');
		$iFontSize			= $ThemeConfig->getInteger('theme.text.size');
		$iFontSizeTitle		= $ThemeConfig->getInteger('theme.text.size.title');
		$sFontFile			= $ThemeConfig->getString('theme.text.font');
		$sFontFileTitle		= $ThemeConfig->getString('theme.text.font.title');
		$fLineSpacing		= $ThemeConfig->getFloat('theme.text.line-spacing');
		$sFontColorOffline	= $ThemeConfig->getString('theme.text.color.offline');
		$sFontColorOnline	= $ThemeConfig->getString('theme.text.color.online');
		$sFontColorInGame	= $ThemeConfig->getString('theme.text.color.ingame');
		$sFontColorError	= $ThemeConfig->getString('theme.text.color.error');
		$bShowGameBG		= $ThemeConfig->getBoolean('theme.background.game');
		
		try {
			$sCacheDirAvatars = $Config->getString('cache.dir.avatars', 'cache/avatars');
			$sCacheDirGames = $Config->getString('cache.dir.games', 'cache/games');
			
			// load XML data
			$XmlLoader = new SteamProfileXMLDownloader($sProfileUrl, 'Image');
			$XmlLoader->setTimeout($sDownloaderTimeout);
			$XmlLoader->setFilterCtlChars(true);
			$XmlLoader->setTrimExtra(true);
			$sXml = $XmlLoader->start();
			$XmlLoader->close();
			$XmlData = simplexml_load_string($sXml);
			
			// use the background as reference
			$this->loadPNG($aThemeFiles['background']);
			
			// enable alpha
			$this->setAlpha(true);
			
			// set default title and content
			$sTitle = (string)$XmlData->steamID;
			$sContent = "\n";
			
			// check if the profile is private
			if((string)$XmlData->privacyState == 'friendsonly') {
				$sStatus = 'offline';
				$sContent .= 'This profile is private';
				$iFontColor = $this->getColorHex($sFontColorOffline);
			} else {
				// get the player's status for text and icon holder color
				switch((string)$XmlData->onlineState) {
					case 'in-game':
						$sStatus = 'ingame';
						$sCurrentGame = $XmlData->inGameInfo == null? '' : (string)$XmlData->inGameInfo->gameName;
						$sContent .= "In-Game\n$sCurrentGame";
						$iFontColor = $this->getColorHex($sFontColorInGame);
						break;

					case 'online':
						$sStatus = 'online';
						$sContent .= 'Online';
						$iFontColor = $this->getColorHex($sFontColorOnline);
						break;

					case 'offline':
						$sStatus = 'offline';
						$sContent .= (string)$XmlData->stateMessage;
						$iFontColor = $this->getColorHex($sFontColorOffline);
						break;

					default:
						throw new RuntimeException('Unable to determinate player status.');
				}
			}
			
			if($bShowGameBG && $XmlData->inGameInfo->gameLogoSmall !== null) {
				// load game background
				$sGameBGUrl = (string)$XmlData->inGameInfo->gameLogoSmall;
				$GameBGCache = new Cache($sCacheDirGames, -1, 'jpg');
				$GameBGFile = $GameBGCache->getFile($sGameBGUrl);
				
				// do we already have a cached version of the game image?
				if(!$GameBGFile->isCached()) {
					$GameBGLoader = new SteamProfileDownloader($sGameBGUrl, 'Image');
					$GameBGLoader->setTimeout($sDownloaderTimeout);
					$GameBGLoader->setOutputFile($GameBGFile->getPath());
					$GameBGLoader->start();
					$GameBGLoader->close();
				}
				
				try {
					// draw game background
					$GameBGImage = new GDImage();
					$GameBGImage->loadJPEG($GameBGFile->getPath());
					$this->copyResize($GameBGImage, $this->getWidth() - 128, 0, 0, 0, 128, 48, 120, 45);
					$GameBGImage->destroy();
					
					// draw fade background over game background
					$fadeBGImage = new GDImage();
					$fadeBGImage->loadPNG($aThemeFiles['background_fade']);
					$this->copy($fadeBGImage, $this->getWidth() - 128, 0, 0, 0, 128, 48);
					$fadeBGImage->destroy();
				} catch(Exception $e) {
					// the game background doesn't work, but we don't mind
				}
			}
			
			// draw icon holder
			$iconHolderImage = new GDImage();
			$iconHolderImage->loadPNG($aThemeFiles["iconholder_$sStatus"]);
			$this->copy($iconHolderImage, 4, 4, 0, 0, 40, 40);
			$iconHolderImage->destroy();
			
			// load avatar icon
			$AvatarUrl = (string)$XmlData->avatarIcon;
			$AvatarCache = new Cache($sCacheDirAvatars, -1, 'jpg');
			$AvatarFile = $AvatarCache->getFile($AvatarUrl);
			
			// do we already have a cached version of the game image?
			if(!$AvatarFile->isCached()) {
				$AvatarLoader = new SteamProfileDownloader($AvatarUrl, 'Image');
				$AvatarLoader->setTimeout($sDownloaderTimeout);
				$AvatarLoader->setOutputFile($AvatarFile->getPath());
				$AvatarLoader->start();
				$AvatarLoader->close();
			}
			
			// draw avatar icon
			$avatarIcon = new GDImage();
			
			// loading avatars might fail, so be prepared for the default avatar
			try {
				$avatarIcon->loadJPEG($AvatarFile->getPath());
			} catch(Exception $e) {
				$avatarIcon->loadJPEG($aThemeFiles['default_avatar']);
			}
			
			$this->copy($avatarIcon, 8, 8, 0, 0, 32, 32);
			$avatarIcon->destroy();

			// draw text
			$this->drawTextFT($sTitle, $sFontFileTitle, $iFontSizeTitle, 0, $iFontColor, $iTextBaseX, $iTextBaseY);
			$this->drawTextFT($sContent, $sFontFile, $iFontSize, 0, $iFontColor, $iTextBaseX, $iTextBaseY, array('linespacing' => $fLineSpacing));
		} catch(Exception $e) {
			// use the background as reference
			$this->loadPNG($aThemeFiles['background']);
			
			// draw error icon
			$errorIcon = new GDImage();
			$errorIcon->loadPNG($aThemeFiles["error"]);
			$this->copy($errorIcon, 4, 4, 0, 0, 16, 16);
			$errorIcon->destroy();
			
			// draw text
			$sMessage = wordwrap($e->getMessage(), 38, "\n", true);
			$this->drawTextFT($sMessage, $sFontFileTitle, $iFontSizeTitle, 0, $this->getColorHex($sFontColorError), 24, $iTextBaseY);
			
			// re-throw exception so the application
			// knows that something's wrong
			throw new SteamProfileImageException('', 0, $e);
		}
	}
}
?>