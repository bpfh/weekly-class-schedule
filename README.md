# Weekly Class Schedule plugin with additional filter, templating, and list formatting support

This repository contains a modified Weekly Class Schedule WordPress plugin with support for filtering classes by instructor,
class, and weekday.

Should the feature be merged upstream, this fork can go away.

See `readme.txt` for the original README.

## Installation

Download the plugin package and place it in the `wp-content/plugins` directory, replacing the original Weekly Class Schedule plugin.

## Usage

Filter classes by instructor using the following shortcode:

```
[wcs instructor="John Doe"]
```

Or by class name:

```
[wcs class="Kickboxing"]
```

Or by weekday:

```
[wcs weekday="Monday"]
[wcs weekday="today"]
```

There is a new list format, unimaginatively called "list2", which does have a separate heading for every day. This
coordinates with a new template variable, `[weekday]`, and the ability to provide a custom template for every wcs
invocation. For example:

```
[wcs class="Kickboxing" layout="list2" template="<weekday> from <start time> to <end time> with <instructor>"]
```

might produce a listing such as:

  * Monday from 11:15 am to 12:30 pm with Richard
  * Tuesday from 5:30 pm to 7:00 pm with Shane
  * Saturday from 10:00 am to 11:00 am with Richard

This helps customize the schedule when you want to display limited subsets of your overall schedule, and do
so "in context" of a certain class or instructor, so that information about the class name is not needlessly
repeated.

## Advanced Queries

It is now possible to query locations, class names, instructors using a much wildcards and alternatives.

For example, if you want to query a list of instructors, rather than just one:

```
[wcs instuctor="Dave|Bill|Jane"]
```

will find classes taught by of the given instructors. This "alteratives" function still requires precise
matches with the instructor names, just like a single search would.

The wildcard feature takes this further. Say you teach a variety of yoga, Pilates, and fitness classes.
You want to display all the yoga classes--but just yoga. The wildcard search will do that.

```
[wcs class="%yoga%"]
```

will find any class with "yoga" in the name. So "Yoga for Beginners," "Advanced Yoga," and "Healing Yoga
for Back Pain" all qualify--but your other classes without "yoga" in the name will not. As with exact searches,
they are case-insensitive; "yoga" and "Yoga" are considered equal.

Feel free to combine wildcards and alternation:

```
[wcs class="%yoga%|%fitness%"]
```

finds any classes with "yoga" *or* "fitness" in their names.

Weekdays are a bit of a special case. You can search by alternation, but not by wildcard. For example:

```
[wcs weekday="Mon|Tue"]
```

finds Monday and Tuesday classes. You can use either the full day names, or their first three letter forms (e.g. "Mon").
The search function also understands all symbolic day names (e.g. "today," "tomorrow", and "yesterday") and
relative dates--basically, everything that
PHP's `strtotime()` function understands. So for example, to list all classes
happening in the next three days, you could request:

```
[wcs weekday="today|tomorrow|+2 days"]
```

## Credits

The [Weekly Class Schedule](https://wordpress.org/plugins/weekly-class-schedule/developers/) plugin
is created by [Pulsar Web Design](http://pulsarwebdesign.com/weekly-class-schedule/) and
released under the GPLV2 license.

The list2 layout and extended querying of instructors, classes, and weekdays were
added by [Jonathan Eunice](mailto:jonathan.eunice@gmail.com), and are released
under the same GPLv2 license. To contribute to further development,
[donate at GitTip](https://www.gittip.com/jeunice/).
