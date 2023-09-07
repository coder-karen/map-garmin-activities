<?php
/**
 * Plugin Name:       Map Garmin Activities
 * Description:       Display Garmin activity start locations on a Mapbox map, plus GPX tracks.
 * Requires at least: 6.0
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Karen Attfield
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       map-garmin-activities
 */

 define( 'MGA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function map_garmin_activities_block_init( ) {

	register_block_type( __DIR__ );

    register_post_meta( '', 'mapboxApiTokenField', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
    ) );
	
	register_post_meta( '', 'garmin_account_email_field', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
    ) );
	register_post_meta( '', 'garmin_account_password_field', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
    ) );

	register_post_meta( '', 'mapGarminActivitiesTextField', array(
		'show_in_rest' => true,
		'single'       => true,
		'type'         => 'string',
	) );

 }

add_action( 'init', 'map_garmin_activities_block_init' );

/**
 * Defines assets for both the front and back-end.
 *
 */
function map_garmin_activities_block_assets( ) { 

	$default_attributes =  array(
		'mapboxApiTokenField' => '',
		'mapTitle'            => '',
		'isTokenValid'        => false,
		'mapStyle'            => 'mapbox://styles/mapbox/satellite-streets-v12',
		'trackColour'         => '#888888',
		'trackThickness'      => 5,
		'markerColour'        => '#FF0000',
		'markerBorderColour'  => 'white',
		'zoom'                => 13,
		'longitude'           =>  -3.647891,
		'latitude'            => 57.120085,
		'pitch'               => 80,
		'bearing'             => 10,
		'showStartLocations'  => false,
		'trackUrl'            => '',
		'trackCoords'         => '',
		'gpxAsJSON'           => '',
		'dateFrom'            => '',
		'dateTo'              => '',
		'padding'             => 30,
		'activityType'        => 'walking',
		'distanceMeasurement' => 'km',
		'markerStrokeWidth'   => 2,
		'markerRadius'        => 6,
		'mapboxApiTokenField' => get_post_meta( get_the_ID(), 'mapboxApiTokenField', true) ? get_post_meta( get_the_ID(), 'mapboxApiTokenField', true) : null,
	 );

	wp_enqueue_style(
		'map-garmin-activities-mapbox-style',
		'https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.css',
		array( 'wp-editor' ),
		'2.11.0',
	);

	// Mapbox script.
	wp_enqueue_script(
		'map-garmin-activities-mapbox-gl-js',
		'https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.js',
		array(),
		'2.11.0',
	);

	// The map build script.
	wp_enqueue_script(
		'map-garmin-activities-build',
		plugins_url( 'src/map-block.js', __FILE__ ),
		array('map-garmin-activities-mapbox-gl-js'),
		null,
		true
	);


	$new_arr = define_block_attributes( $default_attributes );

	// Ensuring all the relevant map and attribute data is available on the front-end.
	wp_add_inline_script( 'map-garmin-activities-build', 'let blockAttributes =' .  json_encode( $new_arr ), 'before' );
	wp_add_inline_script( 'map-garmin-activities-build', 'let garminActivityData =' .  json_encode( get_garmin_activity_details( $new_arr ) ), 'before' );

}

/**
 * Returns all block attributes as an array.
 *
 * @param array $default_attributes
 *
 * @return array
 *
 */
function define_block_attributes( $default_attributes ) {
	global $post;
	$blocks = parse_blocks( $post->post_content );
	$new_arr = array();

	foreach( $blocks as $block ) {
		if ( $block['blockName'] === 'map-garmin/map-garmin-activities' ) {
			$attributes = $block['attrs'];
			$new_arr = array_replace( $default_attributes, $attributes );
		}
	}

	return $new_arr;
}

/**
 * Connects to Garmin and retrieves relevant activity details
 *
 * @param array $new_arr The array with up-to-date attribute values.
 *
 * @return array|null 
 */
function get_garmin_activity_details( $new_arr ) {

	$garmin_account_password = get_post_meta( get_the_ID(), 'garmin_account_password_field', true ) ? get_post_meta( get_the_ID(), 'garmin_account_password_field', true ) : null;
	$garmin_account_email = get_post_meta( get_the_ID(), 'garmin_account_email_field', true ) ? get_post_meta( get_the_ID(), 'garmin_account_email_field', true ) : null;

	if ( $garmin_account_password && $garmin_account_email && $new_arr['showStartLocations'] ) {
		require_once MGA_PLUGIN_PATH . 'garmin-connect.php';

		$arr_credentials = array(
			'username' => $garmin_account_email,
			'password' => $garmin_account_password
		 );
				
		 $garmin_activity_array = array(
			'type' => 'FeatureCollection',
			'features' => array(),
		);

		try {
			$obj_garmin_connect = new \Garmin_Connect( $arr_credentials );
			$obj_results = $obj_garmin_connect->getActivityListByDate( $new_arr['dateFrom'], $new_arr['dateTo'], $new_arr['activityType'] );
			if ( $obj_results !== null ) {
				foreach( $obj_results as $obj_activity ) {

					$distance = round( ( $obj_activity->distance / 1000 ), 2 );
					if ( $new_arr['distanceMeasurement'] === 'miles' ) {
						$distance = round( $distance / 1.609, 2 );
					}
					$moving_duration = $obj_activity->movingDuration;
					$moving_hours = floor( $moving_duration / 3600 );
					$moving_minutes = floor(( $moving_duration / 60 ) % 60 );
					$moving_seconds = $moving_duration % 60;
					$duration_string = $moving_minutes . __( ' mins, ', 'map-garmin-activities' ) . $moving_seconds . __( ' seconds.', 'map-garmin-activities' );
					if ($moving_hours > 0 ) {
						if ( $moving_hours > 1 ) {
						$duration_string = $moving_hours . __( ' hrs, ', 'map-garmin-activities' )  . $duration_string;
						}
						else {
							$duration_string = $moving_hours . __( ' hr, ', 'map-garmin-activities' )  . $duration_string;
						}
					}

					$description_title = '<h6 class="map-garmin-activities-popup-title-text">' . $obj_activity->activityName . '</h6>';

					$description_content = sprintf(
						__( '%1$sThis Garmin activity is not publicly viewable.
						Some available key stats: %2$s Local start time and date: %3$s %4$s Total distance: %5$s Total moving time: %6$s', 'map-garmin-activities' ),
						'<p class="map-garmin-activities-popup-text">',
						'</p><ul class="map-garmin-activities-popup-text"><li>',
						date_i18n('d F Y, h:i:s A', strtotime( $obj_activity->startTimeLocal ) ),
						'</li><li>',
						$distance . ' ' . $new_arr['distanceMeasurement'] . '.</li><li>',
						$duration_string . '</li></ul>'
					);

					if ( $obj_activity->privacy->typeKey === 'public' ) {
						$description_content = sprintf(
							__( '%1$s View activity details on %2$s', 'map-garmin-activities' ),
							'<p><a href="https://connect.garmin.com/modern/activity/' .  (string) $obj_activity->activityId . '" target="_blank" rel="noreferrer">',
							'connect.garmin.com </a></p>'
							);
					}

					$garmin_activity_array['features'][] = array(
						'type' => 'Feature',
						'properties'=> array(
						'description' => $description_title . $description_content
						),
						'geometry' => array(
							'type' => 'Point',
							'coordinates' => array(
								$obj_activity->startLongitude,
								$obj_activity->startLatitude
							)
						)
					);
				}
			}
		} catch ( Exception $obj_exception ) {
			$garmin_activity_array[] = array( 'error' => $obj_exception->getMessage() );
			echo 'Oops: ' . $obj_exception->getMessage();
		 }
		return $garmin_activity_array;
	} 
	else {
		return;
	}

}

add_action( 'enqueue_block_assets', 'map_garmin_activities_block_assets' );

/**
 * Ensures that only GPX files can be uploaded via the media library when using this block.
 *
 */
function add_gpx_mime() {
	$mime_types['geojson'] = 'text/plain'; // Adding .geojson extension
	 $mime_types['gpx']    = 'text/xml';
	 return $mime_types;
 }
 
 add_filter( 'upload_mimes', 'add_gpx_mime', 1, 1 );