var go_timepicker = {
	base: go_timepicker_base,
	timezone_detected: false
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
	 * Sets up timezone picker and associated events
	 */
	go_timepicker.timezone_picker = function() {
		go_timepicker.$timezone_image = $( '#timezone-image' );

		// Set up the picker to update target timezone list.
		go_timepicker.$timezone_image.timezonePicker({
			target: '#' + go_timepicker.base + '-timezone',
			fillColor: '55a0d3'
		});

		// Show/hide map
		$( document ).on( 'click', '.show-tz-map', function( e ) {
			e.preventDefault();
			$button = $( this );

			go_timepicker.tz_button_text = 'Show Map' == $button.text() ? 'Hide Map' : 'Show Map';

			$button.text( go_timepicker.tz_button_text );

			$map_div = $( '#timezone-picker' );
			$map_div.toggle();

			var current_timezone = $( '#' + go_timepicker.base + '-timezone' ).val();

			if ( current_timezone ) {
				// if they already have a timezone set, auto-select it
				go_timepicker.$timezone_image.timezonePicker( 'updateTimezone', current_timezone );
			} else if( ! go_timepicker.timezone_detected ) {//end if
				// We have to wait for the map to be shown
				// Auto-detect geolocation. (will prompt user)
				go_timepicker.$timezone_image.timezonePicker( 'detectLocation' );

				// Don't reset the damn location each time they open the map!
				go_timepicker.timezone_detected = true;
			}//end else if
		});
	};

	go_timepicker.timezone_picker();
	go_timepicker.date_picker();

})( jQuery );