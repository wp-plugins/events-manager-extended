=== Events Manager Extended ===  
Contributors: liedekef
Donate link: http://www.e-dynamics.be/wordpress
Tags: events, manager, booking, calendar, gigs, concert, maps, geotagging  
Requires at least: 3.0.0
Tested up to: 3.0.3
Stable tag: 3.2.12

Manage and display events. Includes recurring events; locations; widgets; Google maps; RSVP; ICAL and RSS feeds. 
             
== Description ==
Events Manager Extended is a full-featured event management solution for Wordpress. Events Manager Extended supports public, private, draft and recurring events, locations management, RSVP (+ approval if wanted) and maps. With Events Manager Extended you can plan and publish your event, or let people reserve spaces for your weekly meetings. You can add events list, calendars and description to your blog using multiple sidebar widgets or shortcodes; if you are a web designer you can simply employ the template tags provided by Events Manager Extended. 

Events Manager Extended (EME) is a fork (NOT an extension) of the older Events Manager (EM) version 2.2.2 (April 2010). After months, the original plugin came back to life with a new codebase, but I added so much features already that it is very hard to go back to being one plugin. Read here for the differences since 2.2.2: http://www.e-dynamics.be/wordpress/?page_id=2 
Events Manager Extended integrates with Google Maps; thanks the geocoding, Events Manager Extended can find the location of your events, and accordingly display a map. 

Events Manager Extended provides also a RSS and ICAL feed, to keep your subscribers updated about the events you're organising. 

Events Manager Extended is fully customisable; you can customise the amount of data displayed and their format in events lists, pages and in the RSS/ICAL feed. You can choose to show or hide the events page, and change its title.  

Events Manager Extended is fully localisable and already partially localised in Italian, Spanish, German, Swedish, French and Dutch. 

For more information visit the [Documentation Page](http://www.e-dynamics.be/wordpress/) and [Support Forum](http://www.e-dynamics.be/bbpress/). 

== Installation ==

Always take a backup of your db before doing the upgrade, just in case ...  
1. Upload the `events-manager-extended` folder to the `/wp-content/plugins/` directory  
2. Activate the plugin through the 'Plugins' menu in WordPress  
3. Add events list or calendars following the instructions in the Usage section.  
== Upgrade from the older Events Manager plugin ==

Events Manager Extended is completely backwards compatible with the old data from Events Manager 2.2.2. Just deactivate the old plugin, remove the files if you want, and proceed with the Events Manager Extended installation as usual. Events Manager Extended takes care of your events database migration automatically. 
Again my note of warning: Events Manager Extended (EME) is a fork (NOT an extension) of the older Events Manager (EM) version 2.2.2 (April 2010). After months, the original plugin came back to life with a new codebase, but I added so much features already that it is very hard to go back to being one plugin. Read here for the differences since 2.2.2: http://www.e-dynamics.be/wordpress/?page_id=2

== Usage == 

After the installation, Events Manager Extended add a top level "Events" menu to your Wordpress Administration.

*  The *Events* page lets you edit or delete the events. The *Add new* page lets you insert a new event.  
	In the event edit page you can specify the number of spaces available for your event. You just need to turn on RSVP for the event and specify the spaces available in the right sidebar box.  
	When a visitor responds to your events, the box sill show you his reservation. You can remove reservation by clicking on the *x* button or view the respondents data in a printable page.
	You can also specify the category the event is in, if you activated the Categories support in the Settings page.  
	Also fine grained control of the RSVP mails and the event layout are possible here, if the defaults you configured in the Settings page are not ok for this specific event.  
*  The *Locations* page lets you add, delete and edit locations directly. Locations are automatically added with events if not present, but this interface lets you customise your locations data and add a picture. 
*  The *Categories* page lets you add, delete and edit categories (if Categories are activated in the Settings page). 
*  The *People* page serves as a gathering point for the information about the people who reserved a space in your events. 
*  The *Pending approvals* page is used to manage registrations/bookings for events that require approval 
*  The *Change registration* page is used to change bookings for events 
*  The *Settings* page allows a fine-grained control over the plugin. Here you can set the [format](#formatting-events) of events in the Events page.
*  Access control is in place for managing events and such: 
        - a user with role "Editor" can do anything 
        - with role "Author" you can only add events or edit existing events for which you are the author or the contact person 
        - with role "Contributor" you can only add events *in draft* or edit existing events for which you are the author or the contact person 

Events list and calendars can be added to your blogs through widgets, shortcodes and template tags. See the full documentation at the [Events Manager Extended Support Page](http://www.e-dynamics.be/wordpress/).
 
== Frequently Asked Questions ==

= I enabled the Google Maps integration, but instead of the map there is a green background. What should I do? =

I call that "the green screen of death", but it's quite easy to fix your issue. If you see that green background, your theme has a little problem that should be fixed. Open the `header.php` page of your theme; if your theme hasn't any `header.php` page, just open the `index.php page` and/or any page containing the `<head>` section of the html code. Make sure that the page contains a line like this:              

    <?php wp_head(); ?>              

If your page(s) doesn't contain such line, add it just before the line containing `</head>`. Now everything should work allright.    
For curiosity's sake, `<?php wp_head(); ?>` is an action hook, that is a function call allowing plugins to insert their stuff in Wordpress pages; if you're a theme maker, you should make sure to include `<?php wp_head(); ?> ` and all the necessary hooks in your theme.

= How do I resize the single events map? Or change the font color or any style of the balloon? = 

Create a file called 'myown.css' in the plugin directory and put in there eg.:  
  
.eme-location-map {  
width: 600px;  
height: 400px;  
}  
.eme-location-balloon {  
        color: #FF7146;  
}  

You can start from events_manager.css as a base and just change the parts you want.  
Warning: when wordpress updates a plugin automatically, it removes the plugin directory completely. So be sure to have a backup of myown.css somewhere to put back in place afterwards.
  
For the multiple locations map, see the shortcode [locations_map] with its possible parameters on the documentation site.

= Can I customise the event page? =

Sure, you can do that by editing the page and changing its [template](http://codex.wordpress.org/Pages#Page_Templates). For heavy customisation, you can use the some of the plugin's own conditional tags, described in the *Template Tags* section.

= How does Events Manager Extended work? =   

When installed, Events Manager Extended creates a special "Events" page. This page is used for the dynamic content of the events. All the events link actually link to this page, which gets rendered differently for each event.

= Are events posts? =

Events aren't posts. They are stored in a different table and have no relationship whatsoever with posts.

= Why aren't events posts? =

I decided to treat events as a separate class because my priority was the usability of the user interface in the administration; I wanted my users to have a simple, straightforward way of inserting the events, without confusing them with posts. I wanted to make my own simple event form.  
If you need to treat events like posts, you should use one of the other excellent events plugin.

= Is Events Manager Extended available in my language? = 

At this stage, Events Manager Extended is only available in English and Italian. Yet, the plugin is fully localisable; I will welcome any translator willing to add to this package a translation of Events Manager Extended into his mother tongue.

== Screenshots ==

1. A default event page with a map automatically pulled from Google Maps through the #_MAP placeholder.
2. The events management page.
3. The Events Manager Extended Menu.

== Changelog ==

= Older versions =
* See the Changelog of the Events Manager plugin

= 3.0.0 =
* Bugfix: Fix for green screen caused by newlines in the location balloon
* Bugfix: Fix for rsvp contact mail (new: #_PLAIN_CONTACTEMAIL)
* Change: #_BOOKEDSEATS en #_AVAILABLESEATS are deprecated, in favor of #_RESERVEDSPACES and #_AVAILABLESPACES
* Change: The "add booking form" now shows only the number of available seats, not just the number 10
* Change: In order to not show a dropdown of 1000, we limit the number of seats you can book to a max of 10 default settings were not being set when activating the plugin
* Bugfix: Event_id, person_id in bookings table are not tinyints, also removed the
* Bugfix: remove the limit of tinyint for the number of seats
* Change: No seats available anymore? Then no booking form as well.
* Change: Now an error is returned to the user if on a booking form not all required fields are filled in
* Feature: Captcha added for booking form
* Bugfix: The shortcode [locations_map] once again works, failure was also due to newlines in the location balloon (fix in function eme_global_map_json in dbem_people.php)
* Rewrite of the widgets to the api used from wordpress 2.8 onwards, resulting in cleaner code and multi-instance widgets
* Bugfix: Some html cleanup for w3 markup validation
* Change: If the location name is empty: we don't show the map for the event
* Feature: You can now use normal placeholders in custom attribute values. Eg, in a template, you just add #_{MYOWNDATE} to the template. And then in the event, you can define this attribute with the value "#l #F #j, #Y" or with a complete string to your liking.
* Feature: You can now use custom attributes in email templates as well (eg. for different payment options per event).
* Bugfix: AM/PM notation now correct when using #_12HSTARTTIME and #_12HENDTIME as placeholders
* Feature: You can now have custom email settings and custom page formats per event, very convenient if the default is not ok for a special event.
* Feature: Recursion has been made a bit more complete: you can now have recursion based on the current day of the month. This makes it now possible to have eg. yearly recursion for a birthday or so (just start on the correct day and choose 12 months for recursion).
* Bugfix: Some change to the DB for recursion description to be correct (recurrence_byday is in fact a comma-seperated string containing the days of the week this event happens on)
* Bugfix: the shortcode [locations_map] once again accepts "scope" as a parameter. Eg. [locations_map eventful=true scope=future]
* Change: submenu pagename cleanup, html cleanup
* Bugfix: small category fix on the event overview/edit page (the event_id was used instead of event_category_id)

= 3.0.1 =
* Feature: now you can choose a category in the events widget, so only events of that category are shown

= 3.0.2 =
* Feature (for real now): now you can choose a category in the events widget, so only events of that category are shown  
  If you disable categories, the widget will show all events again as well.

= 3.0.3 =
* Change: now the single event formatting works also for recurring events
* Change: lots of code cleanups and extra checks
* Bugfix: editing a recurrence instance now changes it to a normal event as expected
* Bugfix: settings dbem_small_calendar_event_title_format and dbem_small_calendar_event_title_seperator are no longer ignored
* Bugfix: location deletion works again
* Bugfix/feature: more than one map on one page is now possible (for single/global maps mixed as well)

= 3.0.4 =
* Improvement: add Dutch translation (thanks to Paul Jonker)
* Feature: use google maps API v3, no more API key needed. But: 
  ==> no more IE6 support in API v3, so please don't ask me about it
* Feature: better CSS, create in the plugindir the file 'myown.css' if you want to override the CSS in events_manager.css (see the FAQ section)  
  ==> read the FAQ about how to size/style the balloon in the google map
* Bugfix: the RSVP form was only shown when google maps integration was active, now it is correctly shown when RSVP is wanted

= 3.0.5 =
* Improvement: for single events editing, the format windows are in the state closed by default
* Feature: #_LOCATION now also possible in the calendar title formatting
* Improvement: map only shown if location name/address/town all have a value
* Improvement: if any of event_single_event_format, event_page_title_format, event_contactperson_email_body, event_respondent_email_body is empty: display default value on focus, and if the value hasn't changed from the default: empty it on blur
* Improvement: make it more clear that a page needs to be chosen to show the events on
* Advertise that showing the event page itself is going to be deprecated
* Feature: captcha can be disabled now if you want, plus the session is hopefully started earlier so other plugins can't interfere anymore

= 3.1.0 =
* Bugfix: stripslashes needed for custom attributes
* Bugfix: when using scope=today, the sql query was wrong and thus ignored other conditions
* Bugfix: characters now get escaped ok in locations as well
* Improvement: changed the document to include better info concerning custom attributes
* Feature: you can now choose whether or not registrations need approvements, and then manage pending registrations
* Feature: you can now edit the number of seats somebody registered for, in case they change their minds
* Improvement: force the use of the datepicker for start/end dates by making the field readonly, so no more empty dates

= 3.1.1 =
* Improvement: use constants DBEM_PLUGIN_URL and DBEM_PLUGIN_DIR
* Feature: categories possible for events_calendar widget and events_calendar shortcode
* Bugfix: javascript error fix when editing/creating an event

= 3.1.2 = 
* Feature: qtranslate can now be used together with Events Manager Extended
* Bugfix: better checking for special characters used in events name/location/...
* Bugfix/feature: event attributes are now also taken into account for recurring events
* Bugfix: language setting now happens on init action, better for qtranslate and all
* Bugfix: autocomplete is working again for locations when creating an event
* Bugfix: sort by day and time for the full calendar
* Improvement: English, French languages updates (thanks to Sebastian), Dutch updated by me

= 3.1.3 = 
* Improvement: French, German language updates (thanks to Sebastian), Spanish language updates (thanks to Ricardo)
* Workaround: hopefully no more google balloon scrollbars
* Feature: events can belong to multiple categories now
* Feature: #_CATEGORIES shortcode available, will return a comma-seperated list of categories an event is in
* Feature: #_DIRECTIONS shortcode available, so you can ask for driving directions to an event/location
* Feature: new shortcode available: [display_single_event], eg: [display_single_event id=23]
* Feature: show month or day in events_list if wanted (new parameter for shortcode [events_list]: showperiod=daily|monthly)
* Feature: the attribute 'scope' for the shortcode [events_list] can now contain a date range, eg. [events_list scope=2010-00-00--2010-12-31 limit=200] 
* Feature: "limit=0" now shows all events (pending other restrictions) for the shortcode [events_list]
* Bugfix: updating a recurrent event with booking enabled, deleted all existing bookings for each event of the recurrence

= 3.1.4 = 
* Improvement: use the wordpress defined charset and collation for the DB tables, this will benefit those with weird character sets
* Bugfix: Changing the registration (number of reserved places) of a user for an event works again
* Bugfix: the showperiod option to the [events_list] resulted in non-translated names for month/day. Has been fixed.
* Bugfix: the special events page no longer changes the menu title
* Feature: #_ATTENDEES shortcode available, will return a html-list of names attending the event
* Feature: when editing an event, you can now make it recurrent

= 3.1.5 = 
* Improvement: if you forget to deactivate/activate the plugin for needed DB updates, you'll get a warning now
* Bugfix: don't overwrite widget content anymore
* Bugfix: calendar ajax fixes for full, long_events and category options
* Cleanup: strip many trailing spaces, and resolve all possible php warnings
* Feature: honeypot field implemented, this is a hidden field that humans can't see, but a bot will enter something in it and that's something we can check on
* Feature: you can now require that people need to be registered to wordpress in order to make a booking
* Feature: next to "OR" for categories, you can now have "AND" as well: [events_list category=1,3] is for "OR", [events_list category=1+3] is for "AND"

= 3.1.6 = 
* Bugfix: booking name/email fields were readonly, has been fixed

= 3.2.0 = 
* Bugfix: tablenav issue caused events list to dissapear in the admin interface using IE7
* Bugfix: when duplicating an event, we now edit the duplicate event afterwards, not the original mixed in
* Bugfix: ajax fix for calendar (thanks to wsherliker)
* Feature: status field for events: Public, Private, Draft. Private events are only visible for logged in users, draft events are not visible from the front end.
* Feature: permissions now being checked for creation/editing of events:
	- a user with role "Editor" can do anything
	- with role "Author" you can only add events or edit existing events for which you are the author or the contact person
	- with role "Contributor" you can only add events *in draft* or edit existing events for which you are the author or the contact person
* Renamed all dbem_* functions to eme_ functions, just not the DB tables yet (later). As a result there are some actions required:
	- people using the API in their templates will need to change these to match the new naming convention (just rename "dbem_" to "eme_")
	- people using their own CSS will need to change these as well ((just rename "dbem_" to "eme_")

= 3.2.1 =
* Bugfix: typo fix for capabilities for categories, the categories menu didn't show up in the admin menu

= 3.2.2 =
* Bugfix: add/delete location now works again
* Bugfix: when duplicating an event, the author of the new event is now set correctly
* Bugfix: categories working again
* Bugfix: languages working again

= 3.2.3 =
* Bugfix: sending mails works again
* Feature: new parameter for shortcode [events_list]: author, so you can show only events created by a specific person. Eg: [events_list author=admin] to show events from author with loginname "admin", [events_list author=admin,admin2] for authors admin OR admin2
* Feature: ical subscription is now possible for public events. Just use "?eme_ical=public" after your WP url, and you'll get the ical feed. Eg.: http://www.e-dynamics.be/wordpress/?eme_ical=public. Shortcode [events_ical_link] has been created for your convenience.

= 3.2.4 =
* Improvement: CSS fixes
* Feature: new placeholder #_ICALLINK for a single event, so you get a link to an ical event just for that link. The shortcode [events_ical_link] can of course still be used.
* Feature: calendar and event list widgets now also support author as a filter
* Feature: you can now customize the date format for the monthly period in the EME Settings page, used when you give the option "showperiod=monthly" to the shortcode [events_list]
* Feature: specifying a closing day for RSVP is now possible
* Feature: you can now change the text on the submit buttons for RSVP forms in the EME Settings page.

= 3.2.5 = 
* Bugfix: make location autocomplete work again when editing an event
* Bugfix: when creating an event, the location map was not updated automatically anymore
* Feature: #_DIRECTIONS now also possible for the location infowindow (balloon). I don't recommend it though, since you need to increase the size of the balloon way too much using extra html break-tags.
* Feature: if you use "scope=this_month" as a parameter to the [events_list] shortcode, it will now show all events in the current month
* Feature: if you use "scope=0000-04" as a parameter to the [events_list] shortcode, it will now show all events in month 04 of the current year
* Feature: for bookings where you need to be a WP member, the phone number is no longer required.
* Feature: the format of the attendees list can now be customized
* Improvement: show RSVP info in the events list in the admin backend as well
* Improvement: you can use #_DETAILS as an alternative to #_NOTES as placeholder for the event description
* Improvement: when deleting a booking using the provided form, now only the booking for that event gets deleted, no longer all bookings for that person
* Improvement: the RSVP closing date now also stops showing the delete booking form
* Improvement: for RSVP that require WP membership, the user info (mail/name) is always gotten again from WP info when showing the RSVP members and such. So when a user changes his name/email it immediately shows on the list

= 3.2.6 =
* Bugfix: location table gets created again

= 3.2.7 =
* Bugfix: rsvp is by default allowed until the hour the event starts, not just the day
* Bugfix: booking name/email once again shows correctly (was bugged and fixed in 3.2.5, but the fix didn't make it in 3.2.6)

= 3.2.8 =
* Bugfix: deleting a category now works again
* Bugfix: use plugins_url() the get the url, this is https safe (apparently WP_PLUGIN_URL is not)
* Bugfix: one could only add phonenumbers to profiles other than his own, has been fixed
* Bugfix: shortcode #_ATTENDEES should be an empty string if the event is not RSVP-able
* Bugfix: use .text() for jquery in eme_calendar.php, works in Chrome also
* Bugfix: take server timezone into account for ical and calendar
* Bugfix: the title of an event wasn't escaped properly when editing
* Bugfix: the calendar wouldn't show events on months later/before the current day
* Bugfix: correct pagination
* Bugfix: show all events of a day if that day is requested (calendar_day scope) and not just 10
* Feature: 5th occurence of a weekday in a monthly recurrence can now be chosen
* Feature: direct link to the printable list of reservations shown from the page with the list of events
* Feature: you can now enable registrations by default for new events
* Feature: you can now specify a default number of spaces for RSVP-able events
* Feature: added an admin option to delete all EME tables/option when uninstalling, usefull as well when EM was in the picture

= 3.2.9 =
* Bugfix: small bugfix in javascript for other languages when generating the unique map ID
* Bugfix: RSS and ICAL feeds don't need the booking forms, it messes up the layout
* Bugfix: show events on current day (bug created in 3.2.8)
* Bugfix: in the admin itf, not all editable events were shown when logged in as an author
* Bugfix in datepicker jquery for Finnish language
* Feature: "scope=next_month" now possible as a parameter to the [events_list] shortcode
* Feature: when using the dropdown for locations, you can now select an empty location as well

= 3.2.10 =
* Bugfix: let the includes happen later on, so all init code happens first
* Bugfix: better placeholder matching/replacing
* Bugfix: don't use mysql2date, it doesn't respect the "G" for the date function, we now use date_i18n+strtotime
* Feature: added an option to remove leading zeros from minutes: 09 becomes 9, 00 becomes empty
* Feature: you can now hide RSVP-able events that have no more spaces available
* Feature: filter event list by category in admin interface is now possible
* Feature: pagination in the events is now possible in the frontend using a new parameter for shortcode [events_list]: paging=1 (default=0)
* Feature: you can now remove people and their associated bookings via the people page in the admin backend

= 3.2.11 =
* Bugfix: make limit=0 work again
* Bugfix: again better placeholder matching/replacing

= 3.2.12 =
* Security fix: extra code so no html can be inserted into a name/phone/comment when doing a booking
* Minor bugfix: remove function br2nl, not used and can conflict with other plugins
* Minor bugfix: some label corrections
* Minor bugfix: jquery for ajax calendar now supports the language as well
* Minor bugfix: show events spanning over multiple days (long events) as such when using the show_period option in [events_list]
* Minor bugfix: when editing your profile, the phonenumber of admin was always shown, even though you changed it correctly to your own
* Minor bugfix: full calendar also supports multiple categories now
* Bugfix: eme_install could fail, corrected
* Improvement: better multiline support for ICAL events and a bit support for outlook 2003
* Improvement: the categories of an event are now shown if any in the RSS feed
* Improvement: add locales nn and nb to localised_date_format info
* Improvement: NL language update (thanks to Paul Jonker)
* Improvement: CSS added for navigation arrows in the frontend (eme_nav_left and eme_nav_right)
* Feature: you can now specify the number of events to be shown in the RSS feed, as well as specify the order, category, author and scope (like for eme_get_events)
* Feature: better CSS adaptation possible: create in your theme CSS dir the file 'eme.css' if you want to override the CSS in events_manager.css
  (see the FAQ section)  
* Deprecated: the use of 'myown.css' in the plugin dir (use 'eme.css' in your theme CSS dir)

= 3.2.12 =
* Bugfix: showperiod works correctly again
* Bugfix: paging count in events_list was wrong if you used paging=1 and showperiod options together
* Feature: added long_events to eme_events (shortcode and template function) as well
* Feature: scope=this_week now possible for shortcode [events_list]
* Feature: period paging is now possible if you use paging=1, limit=0 and scope=today,this_week,this_month in the [events_list] shortcode. Eg:
  [events_list paging=1 limit=0 scope=this_week]
* Improvement: the google map javascript code will now only get loaded if/when needed at the bottom of pages, and no longer always at the top
* Improvement: show the database error if event inserting fails
