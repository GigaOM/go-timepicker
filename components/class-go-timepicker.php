<?php

class GO_Timepicker
{
	public $id_base = 'go-timepicker';

	private $map_data = array();

	private $timepicker_count = 0;
	private $timezonepicker_count = 0;
	private $date_range_picker_count = 0;
	private $version = 1;
	private $resources_registered = FALSE;
	private $resources_enqueued = FALSE;

	/**
	 * The constructor
	 */
	public function __construct()
	{
		add_action( 'init', array( $this, 'register_resources' ) );

		// you can use the pickers either via singleton or these actions
		add_action( 'go_timepicker_timezone_picker', array( $this, 'timezone_picker' ), 10, 1 );
		add_action( 'go_timepicker_datetime_picker', array( $this, 'datetime_picker' ), 10, 1 );
		add_action( 'go_timepicker_date_range_picker', array( $this, 'date_range_picker' ), 10, 1 );
	}//end __construct

	/**
	 * register the necessary scripts
	 */
	public function register_resources()
	{
		if ( $this->resources_registered )
		{
			return;
		}// end if
		$this->resources_registered = TRUE;

		$script_config = apply_filters( 'go-config', array( 'version' => 1 ), 'go-script-version' );

		$js_min = ( defined( 'GO_DEV' ) && GO_DEV ) ? 'lib' : 'min';

		wp_register_script(
			'jquery-maphilight',
			plugins_url( 'js/' . $js_min . '/external/jquery.maphilight.js', __FILE__ ),
			array( 'jquery' ),
			$script_config['version'],
			TRUE
		);

		wp_register_script(
			'jquery-timezone-picker',
			plugins_url( 'js/' . $js_min . '/external/jquery.timezone-picker.js', __FILE__ ),
			array( 'jquery-maphilight' ),
			$script_config['version'],
			TRUE
		);

		wp_register_script(
			'jquery-ui-slideraccess',
			plugins_url( 'js/' . $js_min . '/external/timepicker-addon/jquery-ui-sliderAccess.min.js', __FILE__ ),
			array( 'jquery-ui-core' ),
			$script_config['version'],
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
			plugins_url( 'js/' . $js_min . '/external/timepicker-addon/jquery-ui-timepicker-addon.min.js', __FILE__ ),
			array( 'jquery-ui-datepicker', 'jquery-ui-slideraccess' ),
			$script_config['version'],
			TRUE
		);

		wp_register_style(
			'jquery-ui-timepicker-addon',
			plugins_url( 'js/' . $js_min . '/external/timepicker-addon/jquery-ui-timepicker-addon.css', __FILE__ ),
			array( 'jquery-ui-smoothness' ),
			$script_config['version']
		);

		wp_register_script(
			$this->id_base,
			plugins_url( 'js/' . $js_min . '/go-timepicker.js', __FILE__ ),
			array( 'jquery-timezone-picker', 'jquery-ui-timepicker-addon' ),
			$script_config['version'],
			TRUE
		);

		wp_register_style(
			$this->id_base,
			plugins_url( 'css/go-timepicker.css', __FILE__ ),
			array( 'jquery-ui-timepicker-addon' ),
			$script_config['version']
		);

		wp_register_style(
			'bootstrap-daterangepicker',
			plugins_url( 'js/' . $js_min . '/external/bootstrap-daterangepicker/daterangepicker-bs3.css', __FILE__ ),
			array(),
			$script_config['version']
		);

		wp_register_script(
			'moment',
			plugins_url( 'js/' . $js_min . '/external/moment.min.js', __FILE__ ),
			array(),
			$script_config['version'],
			TRUE
		);

		// fiscal quarter momentjs plugin
		wp_register_script(
			'moment-fquarter',
			plugins_url( 'js/' . $js_min . '/external/moment-fquarter.min.js', __FILE__ ),
			array( 'moment' ),
			$script_config['version'],
			TRUE
		);

		// from https://github.com/dangrossman/bootstrap-daterangepicker
		wp_register_style(
			'go-timepicker-daterangepicker',
			plugins_url( 'css/go-timepicker-daterangepicker.css', __FILE__ ),
			array(),
			$script_config['version']
		);

		wp_register_script(
			'bootstrap-daterangepicker',
			plugins_url( 'js/' . $js_min . '/external/bootstrap-daterangepicker/daterangepicker.min.js', __FILE__ ),
			array( 'jquery', 'moment-fquarter' ),
			$script_config['version'],
			TRUE
		);

		wp_localize_script( $this->id_base, 'go_timepicker_base', $this->id_base );
	}//end register_resources

	/**
	 * enqueue the needed scripts
	 */
	public function enqueue_scripts()
	{
		if ( $this->resources_enqueued )
		{
			return;
		}// end if
		$this->resources_enqueued = TRUE;

		wp_enqueue_script( $this->id_base );
		wp_localize_script( $this->id_base, 'go_timepicker_base', $this->id_base );

		wp_enqueue_style( $this->id_base );
	}//end enqueue_scripts

	/**
	 * output HTML for a date/time
	 *
	 * @param array $args of arguments
	 */
	public function datetime_picker( $args )
	{
		$this->enqueue_scripts();

		$this->timepicker_count++;

		$defaults = array(
			'field_id' => $this->id_base . '-datetime-' . $this->timepicker_count,
			'field_name' => 'datetime',
			'label' => 'Date/time',
			'value' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		?>
		<label for="<?php echo esc_attr( $args['field_id'] ); ?>"><?php esc_html( $args['label'] ); ?></label>
		<input type="text" class="datetime" id="<?php echo esc_attr( $args['field_id'] ); ?>" name="<?php echo esc_attr( $args['field_name'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" size="25" />
		<?php
	}//end datetime_picker

	/**
	 * output HTML for a date range picker
	 *
	 * @param array $args of arguments
	 */
	public function date_range_picker( $args )
	{
		$this->enqueue_scripts();

		$this->date_range_picker_count++;

		$defaults = array(
			'start' => '',
			'start_field_id' => $this->id_base . '-daterange-start-' . $this->date_range_picker_count,
			'start_field_name' => 'daterange_start',
			'end' => '',
			'end_field_id' => $this->id_base . '-daterange-end-' . $this->date_range_picker_count,
			'end_field_name' => 'daterange_end',
		);
		$args = wp_parse_args( $args, $defaults );

		if ( ! $args['start'] || ! $args['end'] )
		{
			$args['start'] = date( 'Y-m-d', strtotime( '-30 days' ) );
			$args['end'] = date( 'Y-m-d' );
		}//end if

		?>
		<div class="date-range" class="pull-right">
			<i class="fa fa-calendar fa-lg"></i>
			<span><?php echo date( 'F j, Y', strtotime( $args['start'] ) ); ?> - <?php echo date( 'F j, Y', strtotime( $args['end'] ) ); ?></span>
			<i class="fa fa-angle-down"></i>
			<input type="hidden" class="daterange-start" id="<?php echo esc_attr( $args['start_field_id'] ); ?>" name="<?php echo esc_attr( $args['start_field_name'] ); ?>" value="<?php echo esc_attr( $args['start'] ); ?>"/>
			<input type="hidden" class="daterange-end" id="<?php echo esc_attr( $args['end_field_id']  ); ?>" name="<?php echo esc_attr( $args['end_field_name'] ); ?>" value="<?php echo esc_attr( $args['end'] ); ?>"/>
		</div>
		<?php
	}//end date_range_picker

	/**
	 * output HTML for a timezone picker
	 *
	 * @param array $args of arguments
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
					foreach ( $map_data as $timezone_name => $timezone )
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
					foreach ( $map_data as $timezone_name => $timezone )
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
	 * @param string $size the string based name for size
	                       currently only supports 600, 300, and 328
	                       - it's our plugin, it might as well support our arbitrary size needs
	 * @param array $data (optional) if you pass data, it will cache that data for the given size
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
				$json_data = file_get_contents( __DIR__ . '/js/lib/data/timepicker-' . absint( $size ) . '.json' );
				$this->map_data[ $size ] = json_decode( $json_data, TRUE );
			}//end else
		}//end if

		return $this->map_data[ $size ];
	}// end map_data
}// end class

/**
 * Singleton
 *
 * @global GO_Timepicker $go_timepicker
 * @return GO_Timepicker
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
