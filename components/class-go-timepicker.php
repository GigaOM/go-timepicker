<?php

class GO_Timepicker
{
	public $id_base = 'go-timepicker';

	private $map_data;
	private $offset_tz_map = array(
		-11 => 'Pacific/Midway',
		-10 => 'Pacific/Honolulu',
		 -9 => 'America/Anchorage',
		 -8 => 'America/Los_Angeles',
		 -7 => 'America/Denver',
		 -6 => 'America/Chicago',
		 -5 => 'America/New_York',
		 -4 => 'Atlantic/Bermuda',
		 -3 => 'America/Godthab',
		 -2 => 'America/Noronha',
		 -1 => 'Atlantic/Cape_Verde',
		  0 => 'Europe/London',
		  1 => 'CET',
		  2 => 'EET',
		  3 => 'Asia/Baghdad',
		  4 => 'Europe/Moscow',
		  5 => 'Indian/Maldives',
		  6 => 'Asia/Almaty',
		  7 => 'Asia/Bangkok',
		  8 => 'Asia/Shanghai',
		  9 => 'Asia/Tokyo',
		 10 => 'Australia/Sydney',
		 11 => 'Pacific/Guadalcanal',
		 12 => 'Pacific/Wake',
		 13 => 'Pacific/Enderbury',
		 14 => 'Pacific/Kiritimati',
	);

	private $timepicker_count = 0;
	private $version = 1;

	/**
	 * The constructor
	 */
	public function __construct()
	{
		add_action( 'init', array( $this, 'init' ) );

		// you can use the pickers either via singleton or these actions
		add_action( 'go_timepicker_timezone_picker', array( $this, 'timezone_picker' ), 10, 1 );
		add_action( 'go_timepicker_datetime_picker', array( $this, 'datetime_picker' ), 10, 1 );

		// allow overriding of the default offset timezone mapping
		$this->offset_tz_map = apply_filters( 'go_timepicker_offset_tz_map', $this->offset_tz_map );
	}//end __construct

	/**
	 * register the necessary scripts
	 */
	public function init()
	{
		wp_register_script(
			'jquery-maphilight',
			plugins_url( 'js/external/jquery.maphilight.min.js', __FILE__ ),
			array( 'jquery' ),
			$this->version,
			TRUE
		);

		wp_register_script(
			'jquery-timezone-picker',
			plugins_url( 'js/external/jquery.timezone-picker.min.js', __FILE__ ),
			array( 'jquery-maphilight' ),
			$this->version,
			TRUE
		);

		wp_register_script(
			'jquery-ui-slideraccess',
			plugins_url( 'js/external/timepicker-addon/jquery-ui-sliderAccess.min.js', __FILE__ ),
			array( 'jquery-ui-core' ),
			$this->version,
			TRUE
		);

		wp_register_style(
			'jquery-ui-smoothness',
			plugins_url( 'css/jquery-ui.min.css', __FILE__ ),
			FALSE,
			'1.10.3'
		);

		wp_register_script(
			'jquery-ui-timepicker-addon',
			plugins_url( 'js/external/timepicker-addon/jquery-ui-timepicker-addon.min.js', __FILE__ ),
			array( 'jquery-ui-datepicker', 'jquery-ui-slideraccess' ),
			$this->version,
			TRUE
		);

		wp_register_style(
			'jquery-ui-timepicker-addon',
			plugins_url( 'js/external/timepicker-addon/jquery-ui-timepicker-addon.css', __FILE__ ),
			array( 'jquery-ui-smoothness'),
			$this->version
		);

		wp_register_script(
			$this->id_base,
			plugins_url( 'js/go-timepicker.js', __FILE__ ),
			array( 'jquery-timezone-picker', 'jquery-ui-timepicker-addon' ),
			$this->version,
			TRUE
		);

		wp_register_style(
			$this->id_base,
			plugins_url( 'css/go-timepicker.css', __FILE__ ),
			array( 'jquery-ui-timepicker-addon' ),
			$this->version
		);
	}//end init

	/**
	 * enqueue the needed scripts
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script( $this->id_base );
		wp_localize_script( $this->id_base, 'go_timepicker_base', $this->id_base );

		wp_enqueue_style( $this->id_base );
	}//end enqueue_scripts

	/**
	 * output HTML for a date/time
	 *
	 * @param array of arguments
	 */
	public function datetime_picker( $args )
	{
		$this->timepicker_count++;

		$defaults = array(
			'field_id' => 'go-timepicker-' . $this->timepicker_count,
			'field_name' => 'timezone',
			'label' => 'Date/time',
			'map_id' => 'go-timezone-' . $this->timepicker_count,
			'value' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		$this->enqueue_scripts();

		?>
		<label for="<?php echo esc_attr( $args['field_id'] ); ?>"><?php esc_html( $args['label'] ); ?></label>
		<input type="text" class="datetime" id="<?php echo esc_attr( $args['field_id'] ); ?>" name="<?php echo esc_attr( $args['field_name'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" size="25" />
		<?php
	}//end datetime_picker

	/**
	 * output HTML for a timezone picker
	 *
	 * @param array of arguments
	 */
	public function timezone_picker( $args )
	{
		$defaults = array(
			'field_name' => 'timezone',
			'map_id' => $this->id_base . '-map',
			'show_map' => TRUE,
			'show_selector' => TRUE,
			'value' => FALSE,
			'before_select' => '', // before and after should both contain pre-sanitized html strings
			'after_select' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		$args['value'] = timezone_name_from_abbr( $args['value'] );

		if ( $args['show_selector'] )
		{
			$field_id = esc_attr( $this->id_base . '-timezone' );
			?>
			<div id="timezone-selector">
				<label for="<?php echo $field_id; ?>">Timezone</label>

				<?php echo $args['before_select']; ?>

				<select id="<?php echo $field_id; ?>" name="<?php echo esc_attr( $args['field_name'] ); ?>">
					<option value="">- None -</option>
					<?php
					foreach( $this->map_data() as $timezone_name => $timezone )
					{
						?>
						<option value="<?php echo esc_attr( $timezone_name );?>" <?php selected( $args['value'], $timezone_name ); ?>><?php echo esc_html( $timezone_name );?></option>
						<?php
					} // end foreach
					?>
				</select>

				<?php echo $args['after_select']; ?>

			</div>
			<?php
		}//end if
		else
		{
			?>
			<input type="hidden" id="<?php echo esc_attr( $args['field_id'] ); ?>" name="<?php echo esc_attr( $args['field_name'] ); ?>" value="<?php esc_attr( $args['value'] ); ?>" />
			<?php
		}//end else

		if ( $args['show_map'] )
		{
			// if we are going to show this, we'll need the associated JS
			$this->enqueue_scripts();

			?>
			<button class="button show-tz-map" value="Show map">Show Map</button>
			<div class="<?php echo esc_attr( $this->id_base ); ?>-map" id="timezone-picker" "<?php echo esc_attr( $args['map_id'] ); ?>">
				<img id="timezone-image" src="<?php echo plugins_url( 'images/gray-600.png', __FILE__ ); ?>" width="600" usemap="#timezone-map" />
				<img class="timezone-pin" src="<?php echo plugins_url( 'images/pin.png', __FILE__ ); ?>" />
				<map name="timezone-map" id="timezone-map">
					<?php
					foreach( $this->map_data() as $timezone_name => $timezone )
					{
						foreach ( $timezone['polys'] as $coords )
						{
							echo '<area data-timezone="' . esc_attr( $timezone_name ) . '" data-country="' . esc_attr( $timezone[ 'country' ] ) . '" data-pin="' . esc_attr( implode( ',', $timezone[ 'pin' ] ) ) . '" data-offset="' . esc_attr( $timezone[ 'offset' ] ) . '" shape="poly" coords="' . esc_attr( implode( ',', $coords ) ) . '" />';
						} // end foreach

						foreach ( $timezone['rects'] as $coords )
						{
							echo '<area data-timezone="' . esc_attr( $timezone_name ) . '" data-country="' . esc_attr( $timezone[ 'country' ] ) . '" data-pin="' . esc_attr( implode( ',', $timezone[ 'pin' ] ) ) . '" data-offset="' . esc_attr( $timezone[ 'offset' ] ) . '" shape="rect" coords="' . esc_attr( implode( ',', $coords ) ) . '" />';
						} // end foreach
					} // end foreach
					?>
				</map>
			</div>
			<?php
		}//end if
	}// end timezone_picker

	/**
	 * get the map data
	 *
	 * @return array of map data
	 */
	private function map_data()
	{
		if ( ! $this->map_data )
		{
			$json_data = file_get_contents( __DIR__ . '/js/external/data/timepicker.json' );

			$this->map_data = json_decode( $json_data, true );
		}//end if

		return $this->map_data;
	}// end map_data

	/**
	 * Lookup a predefined common timezone based on offset
	 */
	private function offset_to_tz( $offset )
	{
		if ( ! isset( $this->offset_tz_map[ $offset ] ) )
		{
			$offset = 0;
		}//end if

		return $this->offset_tz_map[ $offset ];
	}//end offset_to_tz
}// end class

/**
 * Singleton
 */
function go_timepicker()
{
	global $go_timepicker;

	if ( ! $go_timepicker )
	{
		$go_timepicker = new GO_Timepicker;
	}//end if

	return $go_timepicker;
}//end go_timepicker