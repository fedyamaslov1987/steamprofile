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

class CacheManager {
	private $sCacheDir = '';
	private $iDefaultLifetime = -1;
	private $sDefaultExtension = -1;

	public function __construct($sCacheDir, $iDefaultLifetime = -1, $sDefaultExtension = 'dat') {
		if(!file_exists($sCacheDir)) {
			throw new RuntimeException("Cache directory \"$sCacheDir\" does not exist.");
		}

		if(!is_writable($sCacheDir)) {
			throw new RuntimeException("Cache directory \"$sCacheDir\" is not writable.");
		}

		$this->sCacheDir = $sCacheDir;
		$this->iDefaultLifetime = $iDefaultLifetime;
		$this->sDefaultExtension = $sDefaultExtension;
	}

	public function getCacheDir() {
		return $this->sCacheDir;
	}
	
	public function setCacheDir($sCacheDir) {
		$this->sCacheDir = $sCacheDir;
	}

	public function getDefaultLifetime() {
		return $this->iDefaultLifetime;
	}
	
	public function setDefaultLifetime($iDefaultLifetime) {
		$this->iDefaultLifetime = $iDefaultLifetime;
	}

	public function getDefaultExtension() {
		return $this->iDefaultLifetime;
	}
	
	public function setDefaultExtension($sDefaultExtension) {
		$this->sDefaultExtension = $sDefaultExtension;
	}

	public function getEntry($sIdentifier, $iLifetime = null, $sExtension = null) {
		$sPath = $this->sCacheDir.DIRECTORY_SEPARATOR.md5($sIdentifier).'.'.($sExtension === null ? $this->sDefaultExtension : $sExtension);
		return new CacheEntry($sPath, ($iLifetime === null)? $this->iDefaultLifetime : $iLifetime);
	}
}

class CacheEntry {
	private $sPath;
	private $iLifetime = -1;

	public function __construct($sPath, $iLifetime = -1) {
		$this->sPath = $sPath;
		$this->setLifetime($iLifetime);
	}

	public function setLifetime($iLifetime = -1) {
		$this->iLifetime = (int)$iLifetime;
	}

	public function isCached() {
		$sCachePath = $this->getPath();

		return file_exists($sCachePath) && ($this->iLifetime == -1 || time() - filemtime($sCachePath) <= $this->iLifetime);
	}

	public function getPath() {
		return $this->sPath;
	}

	public function copyFrom($sPath) {
		return copy($sPath, $this->sPath);
	}

	public function copyTo($sPath) {
		return copy($this->sPath, $sPath);
	}
	
	public function remove() {
		return unlink($this->sPath);
	}
}
?>