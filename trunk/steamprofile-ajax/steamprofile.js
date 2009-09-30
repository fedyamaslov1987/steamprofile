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

$(document).ready(function() {
	SteamProfile = new SteamProfile();
});

function SteamProfile() {
	var basePath;
	var themePath;
	var profiles = new Array();
	var profileCache = new Object();
	var configData;
	var profileTpl;
	var loadingTpl;
	var errorTpl;

	this.init = function() {
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
				configData = $(request.responseXML);
				loadConfig();
			}
		});
	}
	
	function loadConfig() {
		// set theme stylesheet
		themePath = basePath + "themes/" + configData.find("theme").text() + "/";
		$("link#steamprofile-theme").attr("href", themePath + "style.css");
		
		// load templates
		profileTpl = $(configData.find("templates > profile").text());
		loadingTpl = $(configData.find("templates > loading").text());
		errorTpl   = $(configData.find("templates > error").text());
		
		// set loading image
		loadingTpl.find("img").attr("src", themePath + "images/loading.gif");

		// select profile placeholders
		profiles = $(".steamprofile[title]");
		
		// are there any profiles to build?
		if(profiles.length == 0) {
			return;
		}

		// store profile id for later usage
		profiles.each(function() {
			var profile = $(this);
			profile.data("profileID", $.trim(profile.attr("title")));
			profile.removeAttr("title");
		});

		// replace placeholders with loading template and make them visible
		profiles.empty().append(loadingTpl);

		// load first profile
		loadProfile(0);
	}

	function loadProfile(profileIndex) {
		// check if we have loaded all profiles already
		if(profileIndex >= profiles.length) {
			return;
		}
		
		var profile = $(profiles[profileIndex++]);
		var profileID = profile.data("profileID");
		
		if(profileCache[profileID] == null) {
			// load xml data
			jQuery.ajax({
				type: "GET",
				url: basePath + "xmlproxy/xmlproxy.php?id=" + escape(profileID),
				dataType: "xml",
				complete: function(request, status) {
					// build profile and cache DOM for following IDs
					profileCache[profileID] = createProfile($(request.responseXML));
					// replace placeholder with profile
					profile.empty().append(profileCache[profileID]);
				}
			});
		} else {
			// the profile was build previously, just copy it
			profile.empty().append(profileCache[profileID]);
		}
		
		// load next profile
		loadProfile(profileIndex);
	}

	function createProfile(profileData) {
		if (profileData.find("profile").length != 0) {
			if (profileData.find("profile > steamID").text() == "") {
				// the profile doesn't exists yet
				return createError("This user has not yet set up their Steam Community profile.");
			} else {
				// profile data looks good
				var profile = profileTpl.clone();
				var onlineState = profileData.find("profile > onlineState").text();
				
				// set state class, avatar image and name
				profile.addClass("sp-" + onlineState);
				profile.find(".sp-avatar img").attr("src", profileData.find("profile > avatarIcon").text());
				profile.find(".sp-info a").append(profileData.find("profile > steamID").text());
				
				// set state message
				if (profileData.find("profile > visibilityState").text() == "1") {
					profile.find(".sp-info").append("This profile is private.");
				} else {
					profile.find(".sp-info").append(profileData.find("profile > stateMessage").text());
				}
				
				// add icons
				profile.find(".sp-addfriend img").attr("src", themePath + "images/icon_add_friend.png");
				profile.find(".sp-viewitems img").attr("src", themePath + "images/icon_view_items.png");

				if (onlineState == "in-game") {
					// add "Join Game" link href
					profile.find(".sp-joingame")
						.attr("href", profileData.find("profile > inGameInfo > gameJoinLink").text())
						.find("img").attr("src", themePath + "images/icon_join_game.png");
				} else {
					// the user is not ingame, remove "Join Game" link
					profile.find(".sp-joingame").remove();
				}
				
				// add "View Items" link href
				profile.find(".sp-viewitems")
					.attr("href", "http://tf2items.com/profiles/" + profileData.find("profile > steamID64").text());
				
				// // add "Add Friend" link href
				profile.find(".sp-addfriend")
					.attr("href", "steam://friends/add/" + profileData.find("profile > steamID64").text());
				
				// add other link hrefs
				profile.find("sp-avatar a, sp-info a")
					.attr("href", "http://steamcommunity.com/profiles/" + profileData.find("profile > steamID64").text());
				
				// add events for menu
				profile.find(".sp-handle").click(function() {
					profile.find(".sp-content").toggle(200);
				});
			}
			
			return profile;
		} else if (profileData.find("response").length != 0) {
			// steam community returned a message
			return createError(profileData.find("response > error").text());
		} else {
			// we got invalid xml data
			return createError("Invalid community data.");
		}
	}

	function createError(message) {
		var errorTmp = errorTpl.clone();
		errorTmp.find(".sp-error")
			.append(message)
			.find("img").attr("src", themePath + "images/cross.png");
		return errorTmp;
	}
	
	this.init();
};





