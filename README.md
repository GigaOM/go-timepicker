Gigaom Timepicker
=============

Utility plugin to add a date/time picker or timezone picker to an interface.  This is accomplished by using the [Timezone Picker](http://timezonepicker.com), [Map Hilight](https://github.com/kemayo/maphilight/) and [Timepicker Addon](https://github.com/quicksketch/timezonepicker) jQuery plugins.

Usage
-----

Use this shortcode for a date/time picker (defaults shown):
```
do_action( 'go_timepicker_datetime_picker', array(
	// id to use for the select
	'field_id' => 'go-timepicker-datetime-1',
	'field_name' => 'timezone',
	'field_name' => 'datetime',
	// set this to be the contents fo the associated label tag
	'label' => 'Date/time',
	// value to default the date/time to
	'value' => '',
) );
```

Use this shortcode for a timezone picker (defaults shown):
```
do_action( 'go_timepicker_timezone_picker', array(
	// id to use for the select
	'field_id' => 'go-timepicker-timezone-1',
	// name to use for the select
	'field_name' => 'timezone',
	// id for map element
	'map_id' => 'go-timepicker-map-1',
	// size of the map, also supports 300 or 328, see "Hacking" for more details
	'map_size' => 600,
	// specify a URL to override the image used for the map
	'map_image' => FALSE,
	// should the map be shown?
	'show_map' => TRUE,
	// determines whether the map should be toggleable with a "Show Map" button
	'show_map_button' => TRUE,
	// show the timezone selector
	'show_selector' => TRUE,
	// value to default the timezone selector to
	'value' => '',
	// html to wrap the select
	'before_select' => '',
	'after_select' => '',
) );
```

Works With
----------

* [Gigaom Alerts](https://github.com/gigaom/go-alerts)

Hacking
-------

Due to a few of our requirements, some modifications were needed to the external jQuery plugins.  These modification have been requested back against their respective repositories:
* Fix a typo: https://github.com/quicksketch/timezonepicker/pull/12
* More efficient resize: https://github.com/quicksketch/timezonepicker/pull/13
* To allow the map to be responsive: https://github.com/kemayo/maphilight/pull/44

The `go_timepicker.move_pin` function should not be needed, but it was the only reliable way to move a pin when there could be two maps on the page simultaneously.

While it is possible for two timezone maps to exist on the page simultaneously, they will be syncronized.  Changing one will change the other.  We would love them to be independent, but the use case is extremely narrow and the underlying jQuery plugins do not explicitly support it (though they could with another fairly simple modification).

Rather than rely on prompting users for their timezone in the browser, we wanted to be able to make a best guess with `go_timepicker.default_timezone`.  This takes the timezone offset, rounds it to the nearest integer, and makes a determination of what might be the best timezone to select using a curated list of one timezone for each offset.  To make this respect daylight savings time, we also added `Date.prototype.stdTimezoneOffset` which gets an offset that is not adjusted.

The map requires a static file to define the image map regions for the timezone.  As such, these need to be pre-rendered.  This currently only supports 600, 300, and 328.  We needed 328, so we added this odd size as a default for optimum performance.  It is entirely possible to select 600 and then set whatever size in CSS that you want and it will be resized down (or up) appropriately, but performance is slightly reduced.