=== Upcoming.org Public Events ===
Contributors: 
Tags: upcoming, upcoming.org, events, local, local events, public events
Requires at least:
Tested up to: 2.8
Stable tag: 1.0 

Fetches Events from Upcoming.org by location via shortcode.

== Description ==

This plugin is very simple. It fetches events from upcoming.org based on a given location. You need the (free) upcoming.org API key which you can find here: http://upcoming.yahoo.com/services/api/keygen.php
To include the local events, use this shortcode in a post/page: [upcoming.org_events]San Francisco, CA[/upcoming.org_events]. You can use other formats of locations as well, such as ZIP codes.
In the settings you can define:
* Number of events to show (will obviously be lower if not that many events are available for your query).
* Radius to search around the given location
* Show (or don't show) the event images if they are available 
* Display the upcoming.org logo with the results or not

To edit the CSS, modify the upcoming.org_events.css file in the plugin folder upcoming.org_events/

== Installation ==

1. Install via wordpress' plugin directory or upload the upcoming.org_events.zip through the admin interface
1. Make sure your file access settings are set to 755 for the php & css file.

== Screenshots ==

1. Example of 3 events fetched for San Francisco

== Changelog ==

= 1.0 =
* First version
