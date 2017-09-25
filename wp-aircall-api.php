<?php
/**
 * WP-Aircall-API
 *
 * @package WP-API-Libraries\WP-Aircall-API
 */
/*
* Plugin Name: WP Aircall API
* Plugin URI: https://github.com/wp-api-libraries/wp-aircall-api
* Description: Perform API requests to Aircall in WordPress.
* Author: imFORZA
* Version: 1.0.0
* Author URI: https://www.imforza.com
* GitHub Plugin URI: https://github.com/wp-api-libraries/wp-aircall-api
* GitHub Branch: master
*/
/* Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Check if class exists. */
if ( ! class_exists( 'AircallAPI' ) ) {
	
	/**
	 * Aircall API Class.
	 *
	 */
	class AircallAPI {
		
		/**
		 * API ID.
		 *
		 * @var string
		 */
		static protected $api_id;
		
		/**
		 * API Token.
		 *
		 * @var string
		 */
		static protected $api_token;
		
		/**
		 * BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://api.aircall.io/v1';
		
		/**
		 * Class constructor.
		 *
		 * @param string $api_id API ID.
		 * @param string $api_token API Token.
		 */
		public function __construct( $api_id, $api_token ) {
			static::$api_id = $api_id;
			static::$api_token = $api_token;
		}
		
		/**
		 * Prepares API request.
		 *
		 * @param  string $route   API route to make the call to.
		 * @param  array  $args    Arguments to pass into the API call.
		 * @param  array  $method  HTTP Method to use for request.
		 * @return self            Returns an instance of itself so it can be chained to the fetch method.
		 */
		protected function build_request( $route, $args = array(), $method = 'GET' ) {
			// Start building query.
			$this->args['method'] = $method;
			$this->route = $route;
			// Generate query string for GET requests.
			if ( 'GET' === $method ) {
				$this->route = add_query_arg( array_filter( $args ), $route );
			} elseif ( 'application/json' === $this->args['headers']['Content-Type'] ) {
				$this->args['body'] = wp_json_encode( $args );
			} else {
				$this->args['body'] = $args;
			}
			return $this;
		}
		
		/**
		 * Fetch the request from the API.
		 *
		 * @access private
		 * @return array|WP_Error Request results or WP_Error on request failure.
		 */
		protected function fetch() {
			// Make the request.
			$response = wp_remote_request( $this->base_uri . $this->route, $this->args );
			// Retrieve Status code & body.
			$code = wp_remote_retrieve_response_code( $response );
			$body = json_decode( wp_remote_retrieve_body( $response ) );
			$this->clear();
			// Return WP_Error if request is not successful.
			if ( ! $this->is_status_ok( $code ) ) {
				return new WP_Error( 'response-error', sprintf( __( 'Status: %d', 'wp-aircall-api' ), $code ), $body );
			}
			return $body;
		}
		
		/**
		 * Clear query data.
		 */
		protected function clear() {
			$this->args = array();
		}
		
		/**
		 * Check if HTTP status code is a success.
		 *
		 * @param  int     $code HTTP status code.
		 * @return boolean       True if status is within valid range.
		 */
		protected function is_status_ok( $code ) {
			return ( 200 <= $code && 300 > $code );
		}
		
		/* AUTH. */
		
		public function get_ping() {
			
		}
		
		/* COMPANY. */
		
		public function get_company() {
			
		}
		
		/* USERS. */
		
		public function get_users() {
			
		}
		
		public function get_user( $user_id ) {
			
		}
		
		public function update_user( $user_id ) {
			
		}
		
		/* TEAMS. */
		
		public function get_teams() {
			
		}
		
		public function get_team( $team_id ) {
			
		}
		
		public function add_user_to_team( $user_id ) {
			
		}
		
		public function remove_user_from_team( $user_id ) {
			
		}
		
		/* NUMBERS. */
		
		public function get_numbers() {
			
		}
		
		public function get_number( $number_id ) {
			
		}
		
		public function update_number( $number_id ) {
			
		}
		
		
		/* CALL ROUTES. */
		
		public function get_calls() {
			
		}
		
		public function search_calls() {
			
		}
		
		public function get_call() {
			
		}
		
		public function transfer_call() {
			
		}
		
		public function display_call_link() {
			
		}
		
		public function display_custom_call_data() {
			
		}
		
		public function delete_recording() {
			
		}
		
		public function delete_voicemail() {
			
		}
		
		/* CONTACTS. */
		
		public function get_contacts() {
			
		}
		
		public function search_contacts() {
			
		}
		
		public function add_contact() {
			
		}
		
		public function update_contact() {
			
		}
		
		public function delete_contact() {
			
		}

	}
}