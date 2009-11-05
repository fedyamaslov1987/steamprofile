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

class ErrorImage extends GDImage {
    public function __construct($sMessage) {
		$sMessage = wordwrap(strip_tags($sMessage), 40, "\n", true);
		
		parent::create(254, 48);
	
        $iFontColor = $this->getColor(255, 0, 0);
        $iBorderColor = $this->getColor(255, 0, 0);
        $iBGColor = $this->getColor(255, 255, 255);
        $iPadding = 4;
		
		$this->fill(0,0, $iBGColor);
		$this->drawRectangle(0, 0, $this->getWidth() - 1, $this->getHeight() - 1, $iBorderColor);
		$this->drawText($sMessage, 2, $iFontColor, $iPadding, $iPadding);
    }
}
?>