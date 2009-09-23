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

var profiles = new Array();
var profileIndex = 0;
var profileTpl;
var loadingTpl;
var errorTpl;

$(document).ready(function() {
	// select templates
	profileTpl = $("#steamprofile-template .profile");
	loadingTpl = $("#steamprofile-template .loading");
	errorTpl = $("#steamprofile-template .error");
	
	// select profile placeholders
	profiles = $(".steamprofile");
	
	// store profile id for later usage
	profiles.each(function() {
		var profile = $(this);
		profile.data("profileID", $.trim(profile.text()));
	});
	
	// replace placeholders with loading template and make them visible
	profiles.empty().append(loadingTpl).css("visibility", "visible");
	
	// load first profile
	loadProfile();
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
		url: "xmlproxy.php?id=" + escape(profileID),
		dataType: "xml",
		complete: function(request, status) {
			createProfile(profile, request, status);
		}
	});
}

function createProfile(profile, request, status) {
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
			profileTmp.removeClass("profile").addClass(onlineState);
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
				profileTmp.find(".menu .joingame")
					.attr("href", profileData.find("profile > inGameInfo > gameJoinLink").text());
			} else {
				// the user is not ingame, remove "Join Game" link
				profileTmp.find(".menu .joingame").parent().remove();
			}
			
			// add other link hrefs
			profileTmp.find(".menu .addfriend")
				.attr("href", "steam://friends/add/" + profileData.find("profile > steamID64").text());
			profileTmp.find("a[rel=external]")
				.attr("href", "http://steamcommunity.com/profiles/" + profileData.find("profile > steamID64").text());
			
			// replace placeholder with profile
			profile = profile.empty().append(profileTmp);
			
			// add events for menu
			profile.find(".menu ul").hide();
			profile.find(".menu span > span").click(function() {
				profile.find(".menu ul").slideToggle("fast"); 
			});
		}
	} else if (profileData.find("response").length != 0) {
		// steam community returned a message
		var profileTmp = errorTpl.clone();
		profileTmp.append(profileData.find("response > error").text());
		profile.empty().append(profileTmp);
	} else {
		// we got invalid xml data
		var profileTmp = errorTpl.clone();
		profileTmp.append("Invalid community data.");
		profile.empty().append(profileTmp);
	}
	
	// load next profile
	loadProfile();
}