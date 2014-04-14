var go_timepicker = {
	base: go_timepicker_base,
	timezone_detected: false,
	timezone_detect: false
};

( function( $ ) {
	/**
	 * Sets up date and time pickers
	 */
	go_timepicker.date_picker = function() {
		$.datepicker.setDefaults({
			dateFormat: 'M d, yy'
		});

		$( '.datetime' ).each( function() {
			$( this ).datetimepicker({
				changeMonth: true,
				changeYear: true,
				controlType: 'select',
				defaultDate: $( this ).val(),
				defaultValue: $( this ).val(),
				minDateTime: new Date( 2006, 1, 1, 0, 0 ),
				timeFormat: 'hh:mm:ss TT',
				showTimezone: false,
				stepMinute: 5
			});

			// gets around not being able to reopen datepicker after hitting esc key.
			$( this ).on( 'click', function() {
				$( this ).datetimepicker( 'show' );
			});
		});

		//link event pickers if they have a -start and -end extension
		$( '#' + go_timepicker.base + '-start' ).datepicker({
			onClose: function( selectedDate ) {
				$( '#' + go_timepicker.base + '-end' ).datepicker( 'option', 'minDate', selectedDate );
			}
		});

		$( '#' + go_timepicker.base + '-end' ).datepicker({
			onClose: function( selectedDate ) {
				$( '#' + go_timepicker.base + '-start' ).datepicker( 'option', 'maxDate', selectedDate );
			}
		});
	};

	/**
	 * toggle the map
	 */
	go_timepicker.toggle_timezone_map = function( e ) {
		e.preventDefault();
		var $button = $( this );

		var $map = $button.closest( '.go-timepicker' ).find( '.go-timepicker-map' );
		var $timezone_image = $map.find( 'img.timezone-image' );

		var hiding = 'Hide map' == $button.text() ? true : false;
		if ( hiding ) {
			$button.text( 'Show map' );
			$button.removeClass( 'visible' );
		}//end if
		else
		{
			$button.text( 'Hide map' );
			$( '.show-tz-map.visible' ).trigger( 'click' );
			$button.addClass( 'visible' );
		}// end else

		go_timepicker.timezone_map( $timezone_image );

		$map.toggle();
	};

	/**
	 * move the pins to a new area
	 */
	go_timepicker.move_pin = function ( $area ) {
		var $pin = $area.closest( '.go-timepicker' ).find( '.timezone-pin' );

		var pinCoords = $area.data( 'pin' ).split( ',' );
		var pinWidth = parseInt( $pin.width() / 2, 10 );
		var pinHeight = $pin.height();

		$pin.css({
			position: 'absolute',
			left: ( pinCoords[0] - pinWidth ) + 'px',
			top: ( pinCoords[1] - pinHeight ) + 'px',
			display: 'block'
		});

		$pin.trigger( 'go-timepicker-moved-pin' );
	};

	/**
	 * setup a timezone picker map
	 */
	go_timepicker.timezone_map = function( $timezone_image ) {
		var current_timezone = go_timepicker.$timezone_select.val();

		// if it has already been loaded, don't bother reloading the timezone picker
		if ( $timezone_image.hasClass( 'loaded' ) ) {
			$timezone_image.timezonePicker( 'updateTimezone', current_timezone );
			return;
		}// end if

		$timezone_image.timezonePicker( {
			target: '.timezone-picker-select',
			maphilight: false
		} );
		$timezone_image.addClass( 'loaded' );

		if ( ! current_timezone ) {
			if ( go_timepicker.timezone_detect && ! go_timepicker.timezone_detected ) {//end if
				// Auto-detect geolocation. (will prompt user)
				$timezone_image.timezonePicker( 'detectLocation' );

				// Don't reset the damn location each time they open the map!
				go_timepicker.timezone_detected = true;
			}//end else if
			else {
				current_timezone = go_timepicker.default_timezone();
			}
		}

		$timezone_image.timezonePicker( 'updateTimezone', current_timezone );

		$timezone_image.timezonePicker( 'resize' );

		// manually set the width attribute so maphilight gets a correct value
		$timezone_image.attr( 'width', parseInt( $timezone_image.css('width'), 10 ) );

		// we are doing the maphighlight here so it is after the image is resized
		// and will render properly
		$timezone_image.maphilight( {
			fade: false,
			stroke: true,
			strokeColor: 'FFFFFF',
			strokeOpacity: 0.4,
			fillColor: '55a0d3',
			fillOpacity: 0.4,
			groupBy: 'data-offset'
		});
	};

	/**
	 * get a default timezone
	 *
	 * Note: this is way less accurate than geolocation, but will not prompt the user, so, pick your poison
	 */
	go_timepicker.default_timezone = function()
	{
		var default_timezones = {
			'-11': 'Pacific/Midway',
			'-10': 'Pacific/Honolulu',
			 '-9': 'America/Anchorage',
			 '-8': 'America/Los_Angeles',
			 '-7': 'America/Denver',
			 '-6': 'America/Chicago',
			 '-5': 'America/New_York',
			 '-4': 'Atlantic/Bermuda',
			 '-3': 'America/Godthab',
			 '-2': 'America/Noronha',
			 '-1': 'Atlantic/Cape_Verde',
			  '0': 'Europe/London',
			  '1': 'CET',
			  '2': 'EET',
			  '3': 'Asia/Baghdad',
			  '4': 'Europe/Moscow',
			  '5': 'Indian/Maldives',
			  '6': 'Asia/Almaty',
			  '7': 'Asia/Bangkok',
			  '8': 'Asia/Shanghai',
			  '9': 'Asia/Tokyo',
			 '10': 'Australia/Sydney',
			 '11': 'Pacific/Guadalcanal',
			 '12': 'Pacific/Wake',
			 '13': 'Pacific/Enderbury',
			 '14': 'Pacific/Kiritimati',
		}

		var d = new Date();
		var offset = d.stdTimezoneOffset();
		offset = parseInt( ( ( offset * -1 ) / 60 ), 10 );

		return default_timezones[ offset ];
	};

	$( function() {
		$( document ).on( 'click', '.show-tz-map', go_timepicker.toggle_timezone_map );

		go_timepicker.$timezone_select = $( '.timezone-picker-select' );

		// do this with an event so other plugins can hook into it
		$( document ).on( 'go-timepicker-show', function() {
			$( '.go-timepicker-map.show img.timezone-image:visible' ).each( function () {
				go_timepicker.timezone_map( $( this ) );
			} );
		} );
		$( document ).trigger( 'go-timepicker-show' );

		// doing direct binds because timezonePicker is using triggerHandler (which does not propagate)
		$( 'area' ).bind( 'click', function() {
			go_timepicker.move_pin( $( this ) );
		} );

		go_timepicker.date_picker();
	});
})( jQuery );

// adding to the date object a new method to allow us to get a
// consistent time zone offset regardless of daylight savings time
Date.prototype.stdTimezoneOffset = function() {
    var jan = new Date(this.getFullYear(), 0, 1);
    var jul = new Date(this.getFullYear(), 6, 1);
    return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
}
