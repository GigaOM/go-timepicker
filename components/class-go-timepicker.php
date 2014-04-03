<?php

class GO_Timepicker
{
	public $id_base = 'go-timepicker';

	private $map_data = array();
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
	private $timezonepicker_count = 0;
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
			plugins_url( 'js/lib/external/jquery.maphilight.js', __FILE__ ),
			array( 'jquery' ),
			$this->version,
			TRUE
		);

		wp_register_script(
			'jquery-timezone-picker',
			plugins_url( 'js/lib/external/jquery.timezone-picker.js', __FILE__ ),
			array( 'jquery-maphilight' ),
			$this->version,
			TRUE
		);

		wp_register_script(
			'jquery-ui-slideraccess',
			plugins_url( 'js/lib/external/timepicker-addon/jquery-ui-sliderAccess.min.js', __FILE__ ),
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
			plugins_url( 'js/lib/external/timepicker-addon/jquery-ui-timepicker-addon.min.js', __FILE__ ),
			array( 'jquery-ui-datepicker', 'jquery-ui-slideraccess' ),
			$this->version,
			TRUE
		);

		wp_register_style(
			'jquery-ui-timepicker-addon',
			plugins_url( 'js/lib/external/timepicker-addon/jquery-ui-timepicker-addon.css', __FILE__ ),
			array( 'jquery-ui-smoothness'),
			$this->version
		);

		wp_register_script(
			$this->id_base,
			plugins_url( 'js/lib/go-timepicker.js', __FILE__ ),
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
			'field_id' => $this->id_base . '-datetime-' . $this->timepicker_count,
			'field_name' => 'datetime',
			'label' => 'Date/time',
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
		$this->timezonepicker_count++;

		$defaults = array(
			'field_id' => $this->id_base . '-timezone-' . $this->timezonepicker_count,
			'field_name' => 'timezone',
			'map_id' => $this->id_base . '-map-' . $this->timezonepicker_count,
			'map_size' => 600,
			'map_image' => FALSE,
			'map_data' => FALSE,
			'show_map' => TRUE,
			'show_map_button' => TRUE,
			'show_selector' => TRUE,
			'value' => FALSE,
			'before_select' => '', // before and after should both contain pre-sanitized html strings
			'after_select' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		$args['value'] = timezone_name_from_abbr( $args['value'] );
		$button_shown = FALSE;

		$map_data = $this->map_data( $args['map_size'], $args['map_data'] );
		if ( ! $map_data )
		{
			return;
		}//end if

		?>
		<div class="<?php echo esc_attr( $this->id_base ); ?>">
		<?php
		if ( $args['show_selector'] )
		{
			?>
			<div class="timezone-selector">
				<label for="<?php echo esc_attr( $args['field_id'] ); ?>">Time zone</label>

				<?php echo $args['before_select']; ?>

				<select id="<?php echo esc_attr( $args['field_id'] ); ?>" name="<?php echo esc_attr( $args['field_name'] ); ?>" class="timezone-picker-select">
					<option value="">- None -</option>
					<?php
					foreach( $map_data as $timezone_name => $timezone )
					{
						?>
						<option value="<?php echo esc_attr( $timezone_name );?>" <?php selected( $args['value'], $timezone_name ); ?>><?php echo esc_html( $timezone_name );?></option>
						<?php
					} // end foreach
					?>
				</select>

				<?php
				echo $args['after_select'];

				if ( $args['show_map'] && $args['show_map_button'] )
				{
					$button_shown = TRUE;
					?>
					<button class="button show-tz-map" value="Show map">Show map</button>
					<?php
				}//end if
				?>
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

			$args['map_image'] = $args['map_image'] ?: plugins_url( 'images/gray-' . $args['map_size'] . '.png', __FILE__ );

			if ( ! $button_shown && $args['show_map_button'] )
			{
				?>
				<button class="button show-tz-map" value="Show map">Show map</button>
				<?php
			}//end if

			?>
			<div class="<?php echo esc_attr( $this->id_base ); ?>-map<?php echo ! $args['show_map_button'] ? ' show' : ''; ?>">
				<img
					class="timezone-image"
					data-timezone-field="#<?php echo esc_attr( $args['field_id'] ); ?>"
					id="timezone-image-<?php echo absint( $this->timezonepicker_count ); ?>"
					src="<?php echo esc_url( $args['map_image'] ); ?>"
					usemap="#<?php echo esc_attr( $args['map_id'] ); ?>"
					width="<?php echo absint( $args['map_size'] ); ?>"
				/>
				<img class="timezone-pin" src="<?php echo plugins_url( 'images/pin.png', __FILE__ ); ?>" style="width: 13px; height:21px;" />
				<map id="<?php echo esc_attr( $args['map_id'] ); ?>" name="<?php echo esc_attr( $args['map_id'] ); ?>">
					<?php
					foreach( $map_data as $timezone_name => $timezone )
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
		?>
		</div>
		<?php
	}// end timezone_picker

	/**
	 * get the map data
	 * to get additional map data, use: http://timezonepicker.com/json.php?w=300 and adjust the "w" parameter
	 *
	 * @param $size string the string based name for size
	                       currently only supports 600, 300, and 328
	                       - it's our plugin, it might as well support our arbitrary size needs
	 * @param $data array (optional) if you pass data, it will cache that data for the given size
	 * @return array of map data
	 */
	private function map_data( $size, $data = FALSE )
	{
		if ( ! isset( $this->map_data[ $size ] ) )
		{
			if ( $data )
			{
				$this->map_data[ $size ] = $data;
			}//end if
			else
			{
				$json_data = file_get_contents( __DIR__ . '/js/data/timepicker-' . absint( $size ) . '.json' );
				$this->map_data[ $size ] = json_decode( $json_data, TRUE );
			}//end else
		}//end if

		return $this->map_data[ $size ];
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
