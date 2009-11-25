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

 /**
 * Class GDImage
 *
 * A basic GD object wrapper
 */
class GDImage {
	protected $rImage;
	
	public function __construct() {
		// make sure the GD extension is loaded
		if(!extension_loaded('gd') || !function_exists('gd_info')) {
			throw new RuntimeException('GD extension required');
		}
	}
	
	public function create($iWidth, $iHeight) {
		$this->rImage = imagecreatetruecolor($iWidth, $iHeight);
	}
	
	public function destroy() {
		imagedestroy($this->rImage);
	}
	
	public function getWidth() {
		return imagesx($this->rImage);
	}
	
	public function getHeight() {
		return imagesy($this->rImage);
	}
	
	public function getHandle() {
		return $this->rImage;
	}
	
	public function getInfo() {
		return gd_info();
	}
	
	public function drawText($sText, $iFont, $iColor, $iX, $iY) {
		$aText = explode("\n", $sText);
		$iLineHeight = imagefontheight($iFont);
		
		foreach($aText as $sLine) {
			imagestring($this->rImage, $iFont, $iX, $iY, $sLine, $iColor);
			$iY += $iLineHeight;
		}
	}
	
	public function drawTextFT($sText, $sFontFile, $fSize, $fAngle, $iColor, $iX, $iY, $aExtra = array()) {
		return imagefttext($this->rImage, $fSize, $fAngle, $iX, $iY, $iColor, $sFontFile, $sText, $aExtra);
	}
	
	public function drawTextTTF($sText, $sFontFile, $fSize, $fAngle, $iColor, $iX, $iY, $aExtra = array()) {
		return imagettftext($this->rImage, $fSize, $fAngle, $iX, $iY, $iColor, $sFontFile, $sText, $aExtra);
	}
	
	public function drawRectangle($iX1, $iY1, $iX2, $iY2, $iColor) {
		return imagerectangle($this->rImage, $iX1, $iY1, $iX2, $iY2, $iColor);
	}
	
	public function fill($iX, $iY, $iColor) {
		return imagefill($this->rImage, $iX, $iY, $iColor);
	}
	
	public function copy(GDImage $Image, $iX1, $iX2, $iY1, $iY2, $iWidth, $iHeight) {
		return imagecopy($this->rImage, $Image->getHandle(), $iX1, $iX2, $iY1, $iY2, $iWidth, $iHeight);
	}
	
	public function copyResize(GDImage $Image, $iX1, $iX2, $iY1, $iY2, $iDstWidth, $iDstHeight, $iWidth, $iHeight) {
		return imagecopyresampled($this->rImage, $Image->getHandle(), $iX1, $iX2, $iY1, $iY2, $iDstWidth, $iDstHeight, $iWidth, $iHeight);
	}
	
	public function setAntiAlias($bAntiAlias) {
		return imageantialias($this->rImage, $bAntiAlias);
	}
	
	public function setAlpha($bAlpha) {
		imagealphablending($this->rImage, $bAlpha);
		imagesavealpha($this->rImage, $bAlpha);
	}
	
	public function getColor($iR, $iG, $iB) {
		return imagecolorallocate($this->rImage, $iR, $iG, $iB);
	}
	
	public function getColorArray($aColor) {
		return imagecolorallocate($this->rImage, $aColor[0], $aColor[1], $aColor[2]);
	}
	
	public function getColorHex($sColor) {
		return $this->getColorArray(sscanf($sColor, '#%2x%2x%2x'));
	}
	
	public function loadGD($sFile) {
		$this->rImage = imagecreatefromgd($sFile);
	}
	
	public function loadGD2($sFile) {
		$this->rImage = imagecreatefromgd2($sFile);
	}
	
	public function loadPNG($sFile) {
		$this->rImage = imagecreatefrompng($sFile);
	}

	public function loadGIF($sFile) {
		$this->rImage = imagecreatefromgif($sFile);
	}
	
	public function loadJPEG($sFile) {
		$this->rImage = imagecreatefromjpeg($sFile);
	}
	
	public function loadString($sImage) {
		$this->rImage = imagecreatefromstring($sImage);
	}

	public function toPNG($sOutputFile = null) {
		if($sOutputFile == null) {
			header('Content-Type: image/png');
		}
		return imagepng($this->rImage, $sOutputFile);
	}

	public function toJPEG($sOutputFile = null, $iQuality = 80) {
		if($sOutputFile == null) {
			header('Content-Type: image/jpeg');
		}
		return imagejpeg($this->rImage, $sOutputFile, $iQuality);
	}

	public function toGIF($sOutputFile = null) {
		if($sOutputFile == null) {
			header('Content-Type: image/gif');
		}
		return imagegif($this->rImage, $sOutputFile);
	}
}
?>