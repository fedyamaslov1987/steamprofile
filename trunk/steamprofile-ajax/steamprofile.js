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

var basePath;
var profiles = new Array();
var profileIndex = 0;
var profileTpl;
var loadingTpl;
var errorTpl;

$(document).ready(function() {
	// get our <script>-tag
	var scriptElement = $("script[src$=\"steamprofile.js\"]");
	
	// in rare cases, this script could be included without <script>
	if(scriptElement.length == 0) {
		return;
	}
	
	// extract the path from the src attribute
	basePath = scriptElement.attr("src").replace("steamprofile.js", "");
	
	// load xml config
	jQuery.ajax({
		type: "GET",
		url: basePath + "steamprofile.xml",
		dataType: "html",
		complete: function(request, status) {
			var configData = $(request.responseXML);

			// set css theme
			var cssTheme = configData.find("theme").text();
			$("link#steamprofile-theme").attr("href", basePath + "themes/" + cssTheme + "/style.css");
			
			// select templates
			profileTpl = $(configData.find("templates > profile").text());
			loadingTpl = $(configData.find("templates > loading").text());
			errorTpl   = $(configData.find("templates > error").text());

			// select profile placeholders
			profiles = $(".steamprofile");

			// store profile id for later usage
			profiles.each(function() {
				var profile = $(this);
				profile.data("profileID", $.trim(profile.text()));
			});

			// replace placeholders with loading template and make them visible
			profiles.empty().append(loadingTpl);

			// load first profile
			loadProfile();
		}
	});
});

function loadProfile() {
	// check if we have loaded all profiles already
	if(profileIndex >= profiles.length) {
		return;
	}
	
	var profile = $(profiles[profileIndex++]);
	var profileID = profile.data("profileID");
	
	// load xml data
	jQuery.ajax({
		type: "GET",
		url: basePath + "xmlproxy/xmlproxy.php?id=" + escape(profileID),
		dataType: "xml",
		complete: function(request, status) {
			createWidget(request, status, profile);
		}
	});
}

function createWidget(request, status, profile) {
	var profileData = $(request.responseXML);
	
	if (profileData.find("profile").length != 0) {
		if (profileData.find("profile > steamID").text() == "") {
			// the profile doesn't exists yet
			var profileTmp = errorTpl.clone();
			profileTmp.append("This user has not yet set up their Steam Community profile.");
			profile.empty().append(profileTmp);
		} else {
			// profile data looks good
			var profileTmp = profileTpl.clone();
			var onlineState = profileData.find("profile > onlineState").text();
			
			// set state class, avatar image and name
			profileTmp.addClass(onlineState);
			profileTmp.find(".avatar img").attr("src", profileData.find("profile > avatarIcon").text());
			profileTmp.find(".info a").append(profileData.find("profile > steamID").text());
			
			// set state message
			if (profileData.find("profile > visibilityState").text() == "1") {
				profileTmp.find(".info").append("This profile is private.");
			} else {
				profileTmp.find(".info").append(profileData.find("profile > stateMessage").text());
			}

			if (onlineState == "in-game") {
				// add "Join Game" link href
				profileTmp.find(".joingame a")
					.attr("href", profileData.find("profile > inGameInfo > gameJoinLink").text());
			} else {
				// the user is not ingame, remove "Join Game" link
				profileTmp.find(".joingame").remove();
			}
			
			// add other link hrefs
			profileTmp.find(".addfriend a")
				.attr("href", "steam://friends/add/" + profileData.find("profile > steamID64").text());
			profileTmp.find("a[rel=external]")
				.attr("href", "http://steamcommunity.com/profiles/" + profileData.find("profile > steamID64").text());
			
			// replace placeholder with profile
			profile = profile.empty().append(profileTmp);
			
			// add events for menu
			profile.find(".handle").click(function() {
				profile.find(".content").toggle(200);
			});
		}
	} else if (profileData.find("response").length != 0) {
		// steam community returned a message
		createError(profile, profileData.find("response > error").text());
	} else {
		// we got invalid xml data
		createError(profile, "Invalid community data.");
	}
	
	// load next profile
	loadProfile();
}

function createError(profile, message) {
	var errorTmp = errorTpl.clone();
	errorTmp.find(".error").append(message);
	profile.empty().append(errorTmp);
}