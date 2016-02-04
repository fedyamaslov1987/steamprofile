# SteamProfile Ajax #

## 2.0.4 ##
  * Added multi-language support. Currently, translated tool tips and error messages are available for English, German and Portuguese only. 17 more languages are available for basic translations, like status messages.
  * Added packed version of steamprofile.js for less traffic usage
  * Improved HTML templates to avoid 404 errors because of temporary images with wrong paths
  * Fixed [issue 6](https://code.google.com/p/steamprofile/issues/detail?id=6) - wrong user name links

## 2.0.3 ##
  * Updated jQuery to 1.4
  * Added option to hide tf2items.com icon in steamprofile.xml
  * Fixed PHP version and extension checking

## 2.0.2 ##
  * Improved HTTP handling (direct data output and caching instead of redirecting)
  * Changed directory structure
  * Added cURL extension checking

## 2.0.1 ##
  * Changed config file format to "cfg" (INI file format)
  * Renamed "steamcom" theme to "default"
  * Fixed wrong community server error detection
  * Fixed faulty exceptions

## 2.0 Final ##
  * Added banner to the background of the currently played game (can be turned off in steamprofile.xml)
  * Added XML trimming to discard XML data that is currently not used by SteamProfile (saves several kilobytes for some profiles)
  * Added comments in the steamprofile.xml
  * Optimized PNG images (smaller file size and no more gamma problems with Internet Exporer)
  * The slider menu can now turned off in the steamprofile.xml
  * Removed "Join Game" icon for single-player games
  * Some small CSS and HTML improvements

## 2.0 Beta 6 ##
  * Added a XML filter for control characters that are misleadingly send by the Steam Community API to avoid "Invalid community data"-errors for non-IE browsers
  * Stylesheet link tags in the header are no longer required
  * Added the "refresh"-function to support placeholders that are inserted via DOM manipulation
  * Added the "load"-function to load single profiles on the fly
  * Updated example page to introduce the new function

## 2.0 Beta 5 ##
  * Fixed permission issues with XML files
  * Fixed links to steamcommunity.com not set properly
  * Links to external sites now will open in a separate tab

## 2.0 Beta 4 ##
  * Improved stylesheet for loading state and error messages
  * Replaced labeled icons with simple icons in the slider menu
  * Added an backpack icon to the slider menu that is linked to tf2items.com
  * Improved error handling and added fallback system for the XML proxy
  * Default cache time set to 10 min.
  * Optimized Java-Script code
  * Added a client-side cache for duplicate profiles within one page
  * Profile IDs are now stored in the "title" attribute of the <div> so they're invisible to clients with deactivated Java-Script</li></ul>

<h2>2.0 Beta 3 ##
  * Added 'steamprofile.xml' for client-side configuration
    * Moved templates to steamprofile.xml
    * Added theme variable and automatic stylesheet loading
  * Fixed [issue 1](https://code.google.com/p/steamprofile/issues/detail?id=1) - XML proxy not working with safe\_mode/open\_basedir
  * Fixed broken references to XML proxy when embedded in a page outside the base folder
  * Fixed some minor stylesheet problems

## 2.0 Beta 2 ##
  * Replaced the arrow drop-down menu with a slider menu for the Steam Community links
  * Cleaned up template and themes
  * Fixed an error message when bXMLHttpRequestOnly is set to false

## 2.0 Beta 1 ##
  * First release