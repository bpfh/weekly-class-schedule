=== Weekly Class Schedule ===
Contributors: ty_pwd
Tags: schedule, weekly, class schedule
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 3.11
Donate link: http://pulsarwebdesign.com/weekly-class-schedule/
License: GPLv2 or later

Generate a weekly schedule of classes.

== Description ==

Weekly Class Schedule generates a weekly schedule of classes using an ultra-simple interface.

= How to transfer the data from version 2 =
Under the "Schedule" tab you will find the "Import/Update" page. Go there and click the button.

= Main Features =
* Easily manage and update schedule entries (classes).
* Manage and update the classes, instructors, and locations (classrooms) database.
* Easy customization of schedule appearance and colors.
* Selectable first day of week.
* Includes "Today's Classes" widget.
* Fully supports both 12-hour and 24-hour clocks.
* Use simple shortcode attributes to switch between standard and list layout.
* Use a simple templating system to customize the class details display.
* Supports multiple classrooms/schedules.
* Switchable "Instructor collision detection" and "Location collision detection".
* Display class and instructor details directly on the schedule using qTip2.

= Weekly Class Schedule Needs Your Support =

If you enjoy using this plugin and find it useful, please consider [__making a donation__](http://pulsarwebdesign.com/weekly-class-schedule/). Your donation will help encourage and support the plugin's continued development and better user support.

== Installation ==

1. Upload the entire `weekly-class-schedule` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

You will find a 'Schedule' menu item as well as 'Classes', 'Instructors', and 'Locations' in your WordPress admin panel.

For basic usage, check the plugin options page or have a look at the [plugin homepage](http://pulsarwebdesign.com/weekly-class-schedule).

For a demonstration visit [wcs.pulsarwebdesign.com/schedule](http://wcs.pulsarwebdesign.com/schedule).

== Screenshots ==

1. Schedule Management
1. Color Customization
1. Standard Layout
1. List Layout

== Changelog ==
= 3.11 =
* Fixed javascript bug.

= 3.10 =
* Fixed issue with default settings.

= 3.09 =
* Column width is now consistent (CSS issue).
* Fixed installation issues.
* Fixed issue where classes appear twice on the regular schedule
* Minor bug fixes.

= 3.08 =
* Quick FF fix.

= 3.07 =
* Fixed admin area Firefox bug.
* Allow HTML in template and full HTML in notes.
* Other bug fixes.

= 3.06 =
* Added import/update button (under the "Schedule" tab you will find the "Import/Update" page. Go there and click the button.)

= 3.05 =
* Fixed a potential javascript issue on the front end.
* Fixed uninstallation procedure.
* Better update procedure.

= 3.04 =
* Fixed issue where the update procedure wasn't called when updating the plugin from the main repo (for those who have already updated, de-activating and re-activating the plugin should call the update procedure).

= 3.0 =
* The plugin has been re-written from the ground up and fixes many bugs and other issues.
* The plugin is now tightly coupled with WordPress' core and offers much better stability.
* All new Ajax-powered schedule management interface.
* Added location placeholder to template.
* Fixed "Today's Classes" widget.
* NOTE: The timezone feature has been discontinued. Do not update to this version if you are using this feature.
* NOTE: The horizontal layout feature has been discontinued.

= 2.0.5 =
* Fixed compatibility issue with WordPress 3.5.

= 2.0.4 =
* Added Spanish support.
* Fixed I18n issue where language files weren't getting picked up by the plugin.
* Fixed backward compatibility issue (PHP < 5.3)
* Fixed MySQL 'SHOW TABLES IN...' error.
* Fixed issue where Today's Classes widget display hidden classes.

= 2.0.3 =
* Added support for single and double quotes in class, instructor, and classroom name field.
* Fixed multiple classes at the same time CSS/JS issue.

= 2.0.2 =
* Fixed qTip (hover effect) issue with WordPress 3.4.

= 2.0.1 =
* Fixed issue with class order in Today's Classes widget.
* Fixed bug where schedule entries cannot be deleted.
* Added switchable "instructor collision detection".

= 2.0 =
* The entire plugin has been re-written using MVC.
* All reported issues (up to the release date) have been addressed (jQuery version, etc...).
* See plugin description for a full list of features added in this release.

= 1.2.5.2 =
* Attempts to fix "Layout is Too Wide" issue for themes that are not using the #content div.
* Fixed a small issue in the "Today's Classes" widget.
 
 ** NOTE **
 This update adds lines to the wcs_style.css file and may affect the schedule styling. Test on a development machine before updating.


= 1.2.5.1 =
* Fixed issue (double slashes) which prevented from the schedule to get cached.

= 1.2.5 =
* Fixed visibility (visible/hidden status) bug. 
* Moved "Add Schedule Entry" section to the top of the page.
* Added option for unescaped notes. This allows for adding anything to the notes field (links, PayPal buttons, etc...). This options has security implications so make sure you know what you're doing.

 ** NOTE **
 Make sure you delete all the unescaped notes before turning the option off.

= 1.2.4 =
* Fixed an issue with "Today's Classes" widget where classes are not in the correct order when using multiple classrooms.

= 1.2.3 =
* Fixed issue where text would not appear above the schedule.
* Fixed issue where the schedule appears above the header when using Simple Facebook Connect plugin.
* NOTICE: This update changes the way the shortcode is being printed and may affect the schedule styling.

= 1.2.2 =
* Added "Today's Classes" widget

= 1.2.1 =
* Added support for multi-site setup

= 1.2 =
* Added multiple classrooms/schedules support
* Fixed issue with 24h mode when updating schedule entries
* Added timezones support 
* Added visibility support (hide or display classes without deleting entries from the database)

= 1.1.1 = 
* Tagging issue fixed

= 1.1 = 
* Added 24 hrs support