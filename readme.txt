=== Map Garmin Activities ===
Contributors:      coder-karen
Tags:              gutenberg, block, mapbox, garmin
Tested up to:      6.3
Stable tag:        1.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Display start locations and GPX routes from your Garmin-recorded outdoor activities.

== Description ==

This block allows you to set a date range and select specific Garmin-recorded outdoor activities (eg. running, walking) - the start locations for those activities will then be overlaid on a Mapbox map as beacons with clickable info boxes. If the activity is public it will link to the full Garmin information for that activity, otherwise the popup will include the date, distance travelled and moving time.

If you set a 'to' date in the future, those activities will automatically show up on the map after you complete each.

Customizations available include marker (beacon) color, map border color, map border padding, as well as activity type and distance emasurement

This block also allows you to upload a GPX file to the media library and the route will display on a Mapbox map. Customizations include the ability to change the track color, track thickness, colour and zoom. Custom

Customizations in both cases include the map style, as well as zoom, pitch, bearing, longitude and latitude which is only relevant for initial map load (also useful if you only want to display a map with no GPX or live data).

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/map-garmin-activities` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress


== Frequently Asked Questions ==

= How do I get a Mapbox API token? =

Visit https://mapbox.com to get a token.

= How many maps can I display on a post / page? =

Currently only one map per post / page will work.

== Changelog ==

= 1.0.0 =
* First Release
