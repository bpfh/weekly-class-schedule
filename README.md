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


## Credits

The [Weekly Class Schedule](https://wordpress.org/plugins/weekly-class-schedule/developers/) plugin is created by [Pulsar Web Design](http://pulsarwebdesign.com/weekly-class-schedule/) and released under the GPLV2 license.

