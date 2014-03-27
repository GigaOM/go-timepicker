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
		$( '#timezone-image-1' ).each( function() {
			var $image = $( this );
			var timezone_field = $image.data( 'timezone-field' );
			$image.timezonePicker({
				target: timezone_field,
				fillColor: '55a0d3'
			});
		});

		$( '#timezone-image-2' ).each( function() {
			var $image = $( this );
			var timezone_field = $image.data( 'timezone-field' );
			$image.timezonePicker({
				target: timezone_field,
				fillColor: '55a0d3'
			});
		});

		// Show/hide map
		$( document ).on( 'click', '.show-tz-map', function( e ) {
			e.preventDefault();
			var $button = $( this );

			go_timepicker.tz_button_text = 'Show map' == $button.text() ? 'Hide map' : 'Show map';

			$button.text( go_timepicker.tz_button_text );

			var $map = $button.next();
			$map.toggle();

			var $timezone_image = $map.find( 'img.timezone-image' );

			var current_timezone = $( $timezone_image.data( 'timezone-field' ) ).val();

			if ( current_timezone ) {
				// if they already have a timezone set, auto-select it
				$timezone_image.timezonePicker( 'updateTimezone', current_timezone );
			} else if( ! go_timepicker.timezone_detected ) {//end if
				// We have to wait for the map to be shown
				// Auto-detect geolocation. (will prompt user)
				$timezone_image.timezonePicker( 'detectLocation' );

				// Don't reset the damn location each time they open the map!
				go_timepicker.timezone_detected = true;
			}//end else if
		});
	};

	$( function() {
		go_timepicker.timezone_picker();
		go_timepicker.date_picker();
	});
})( jQuery );