<?php
/**
 * Custom functions for the IHS Geo Location Plugin.
 * Contains definition of constants, file includes and enqueuing stylesheets and scripts.
 *
 * @package IHS Geo Location Plugin
 */

/* Define Constants */
define( 'IHS_GEO_PLUGIN_URI', plugins_url( 'ihs-geo-location' ) );
define( 'IHS_GEO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'IHS_GEO_JS_URI', plugins_url( 'ihs-geo-location' ) . '/vendor/js' );


if ( ! function_exists( 'ihs_geo_enqueue_scripts' ) ) {
	/**
	 * Enqueue Styles and Scripts
	 */
	function ihs_geo_enqueue_scripts() {
		wp_enqueue_style( 'ihs_geo_styles', IHS_GEO_PLUGIN_URI . '/style.css' );
		if ( is_front_page() || is_single() || is_page() ) {
			wp_enqueue_script( 'ihs_geo_main_js', IHS_GEO_JS_URI . '/main.js', array( 'jquery' ), '', true );
		}
	}
}

add_action( 'wp_enqueue_scripts', 'ihs_geo_enqueue_scripts' );


if ( ! function_exists( 'ihs_location_enqueue_scripts' ) ) {
	/**
	 * Create geo data object and send to js file for geo location
	 */
	function ihs_location_enqueue_scripts() {
		wp_localize_script(
			'ihs_geo_main_js', 'geodata', array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( 'ihs_nonce_action_name' ),
			)
		);
	}

	add_action( 'wp_enqueue_scripts', 'ihs_location_enqueue_scripts' );
}

if ( ! function_exists( 'ihs_get_user_location' ) ) {
	/**
	 * Finds city of the user using geo location.
	 */
	function ihs_get_user_location() {
		$nonce_val = wp_unslash( $_POST['security'] );
		$nonce_val = sanitize_text_field( $nonce_val );

		if ( ! wp_verify_nonce( $nonce_val, 'ihs_nonce_action_name' ) ) {
			wp_die();
		}

		// If latitude and longitude are submitted.
		if ( ! empty( $_POST['latitude'] ) && ! empty( $_POST['longitude'] ) ) {
			$latitude  = wp_unslash( $_POST['latitude'] );
			$latitude  = sanitize_text_field( $latitude );
			$longitude = wp_unslash( $_POST['longitude'] );
			$longitude = sanitize_text_field( $longitude );

			// Send request and receive json data by latitude and longitude.
			$url    = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $latitude . ',' . $longitude . '&sensor=false';
			$json   = file_get_contents( $url );
			$data   = json_decode( $json );
			$status = $data->status;

			// If request status is successful.
			if ( 'OK' === $status ) {

				// Get address from json data.
				$address       = $data->results[0]->formatted_address;
				$address_array = $data->results[0]->address_components;

			} else {
				$address       = '';
				$address_array = '';
			}
		}

		wp_send_json_success(
			array(
				'location_data_sent_to_js' => [ $address, $address_array ],
				'data_recieved_from_js'    => $_POST,
			)
		);
	}

	add_action( 'wp_ajax_geo_ajax_hook', 'ihs_get_user_location' );
	add_action( 'wp_ajax_nopriv_geo_ajax_hook', 'ihs_get_user_location' );
}

/**
 * Register the [ihs_geo_locality] shortcode
 *
 * @return {string} div element that will contain the locality
 */
function ihs_geo_locality_shortcode() {
	return '<div class="ihs-locality"></div>';
}
add_shortcode( 'ihs_geo_locality', 'ihs_geo_locality_shortcode' );

/**
 * Register the [ihs_geo_city] shortcode
 *
 * @return {string} div element that will contain the city
 */
function ihs_geo_city_shortcode() {
	return '<div class="ihs-city"></div>';
}
add_shortcode( 'ihs_geo_city', 'ihs_geo_city_shortcode' );

/**
 * Register the [ihs_geo_state] shortcode
 *
 * @return {string} div element that will contain the state
 */
function ihs_geo_state_shortcode() {
	return '<div class="ihs-state"></div>';
}
add_shortcode( 'ihs_geo_state', 'ihs_geo_state_shortcode' );

/**
 * Register the [ihs_geo_country] shortcode
 *
 * @return {string} div element that will contain the country
 */
function ihs_geo_country_shortcode() {
	return '<div class="ihs-country"></div>';
}
add_shortcode( 'ihs_geo_country', 'ihs_geo_country_shortcode' );

/**
 * Register the [ihs_geo_address] shortcode
 *
 * @return {string} div element that will contain the address
 */
function ihs_geo_address_shortcode() {
	return '<div class="ihs-address"></div>';
}
add_shortcode( 'ihs_geo_address', 'ihs_geo_address_shortcode' );
