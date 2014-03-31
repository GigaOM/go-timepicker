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
	 * toggle the map
	 */
	go_timepicker.toggle_timezone_map = function( e ) {
		e.preventDefault();
		var $button = $( this );

		var $map = $button.next();
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

		var current_timezone = go_timepicker.$timezone_select.val();
		var $area = $map.find( "area[data-timezone='" + current_timezone + "']" );
		go_timepicker.move_pin( $area );
	};

	/**
	 * move the pin to a new area
	 */
	go_timepicker.move_pin = function ( $area ) {
		var $pin = $area.closest( '.go-timepicker-map' ).find( '.timezone-pin' );
		$pin.css('display', 'block');

		var pinCoords = $area.data( 'pin' ).split( ',' );
		var pinWidth = parseInt( $pin.width() / 2 );
		var pinHeight = $pin.height();

		$pin.css({
			position: 'absolute',
			left: ( pinCoords[0] - pinWidth ) + 'px',
			top: ( pinCoords[1] - pinHeight ) + 'px'
		});
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

		$timezone_image.timezonePicker({
			target: '.timezone-picker-select',
			fillColor: '55a0d3'
		} );
		$timezone_image.addClass( 'loaded' );

		if ( current_timezone ) {
			// if they already have a timezone set, auto-select it
			$timezone_image.timezonePicker( 'updateTimezone', current_timezone );
		} else if ( ! go_timepicker.timezone_detected ) {//end if
			// We have to wait for the map to be shown
			// Auto-detect geolocation. (will prompt user)
			$timezone_image.timezonePicker( 'detectLocation' );

			// Don't reset the damn location each time they open the map!
			go_timepicker.timezone_detected = true;
		}//end else if
	};

	$( function() {
		// doing direct binds because timezonePicker is using triggerHandler (which does not propagate)
		$( 'area' ).bind( 'click', function() {
			go_timepicker.move_pin( $( this ) );
		} );

		$( document ).on( 'click', '.show-tz-map', go_timepicker.toggle_timezone_map );

		go_timepicker.$timezone_select = $( '.timezone-picker-select' );

		go_timepicker.timezone_map( $( '.go-timepicker-map.show img.timezone-image' ) );

		go_timepicker.date_picker();
	});
})( jQuery );