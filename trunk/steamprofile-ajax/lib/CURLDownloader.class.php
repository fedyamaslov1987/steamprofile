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
 
class CURLDownloader {
	private $cURLSession;
	private $rOutputFile;

	public function __construct($sURL) {
		$this->cURLSession = curl_init($sURL);
	}

	protected function setOption($iOpt, $value) {
		curl_setopt($this->cURLSession, $iOpt , $value);
	}

	protected function getInfo($iOpt) {
		return curl_getinfo($this->cURLSession, $iOpt);
	}

	public function setOutputFile($file) {
		if(is_resource($file)) {
			$this->setOption(CURLOPT_FILE, $file);
		} else {
			$this->rOutputFile = fopen($file, 'w+b');
			$this->setOption(CURLOPT_FILE, $this->rOutputFile);
		}
	}

	public function setReturnTransfer($bReturn) {
		$this->setOption(CURLOPT_RETURNTRANSFER, $bReturn);
	}

	public function setUserAgent($sUA) {
		$this->setOption(CURLOPT_USERAGENT, $sUA);
	}

	public function getHTTPCode() {
		return $this->getInfo(CURLINFO_HTTP_CODE);
	}

	public function start() {
		return curl_exec($this->cURLSession);
	}

	public function close() {
		curl_close($this->cURLSession);

		if(is_resource($this->rOutputFile)) {
			fclose($this->rOutputFile);
		}
	}
}
?>