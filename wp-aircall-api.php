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
		protected $api_id;

		/**
		 * API Token.
		 *
		 * @var string
		 */
		protected $api_token;

		protected $is_debug;

		/**
		 * BaseAPI Endpoint
		 *
		 * @var string
		 * @access protected
		 */
		protected $base_uri = 'https://api.aircall.io/v1/';

		/**
		 * Class constructor.
		 *
		 * @param string $api_id API ID.
		 * @param string $api_token API Token.
		 */
		public function __construct( $api_id, $api_token, $is_debug = false ) {
			$this->api_id    = $api_id;
			$this->api_token = $api_token;
			$this->is_debug  = $is_debug;
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
			$this->set_headers();
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
		 * Check if HTTP status code is a success.
		 *
		 * @param  int $code HTTP status code.
		 * @return boolean       True if status is within valid range.
		 */
		protected function is_status_ok( $code ) {
			return ( 200 <= $code && 300 > $code );
		}


		protected function set_headers(){
			$this->args['headers'] = array(
				'Authorization' => 'Basic ' . base64_encode( $this->api_id . ':' . $this->api_token )
			);
		}

		/**
		 * Private wrapper function (for simpler coding), prepares the request then fetches it.
		 *
		 * @param  [type] $route  [description]
		 * @param  array  $body   [description]
		 * @param  bool   $method The method of the request.
		 * @return [type]         [description]
		 */
		private function run( $route, $body = array(), $method = 'GET' ){
			return $this->build_request( $route, $body, $method )->fetch();
		}

		/**
		 * Clear query data.
		 */
		protected function clear() {
			$this->args = array();
		}

		/* AUTH. */

		/**
		 * An ancient tradition in the world of establishing communications.
		 *
		 * @return [type] [description]
		 */
		public function ping() {
			return $this->run( 'ping' );
		}

		/* COMPANY. */

		public function get_company() {
			return $this->run( 'company' );
		}

		/* USERS. */

		/**
		 * Get users.
		 *
		 * A list of users
		 *
		 * @return [type] [description]
		 */
		public function get_users() {
			return $this->run( 'users' );
		}

		/**
		 * Get a specific user.
		 *
		 * @param  [type] $user_id [description]
		 * @return [type]          [description]
		 */
		public function get_user( $user_id ) {
			return $this->run( "users/$user_id" );
		}

		/**
		 * Update data for a specific user.
		 *
		 * @param  [type] $user_id [description]
		 * @param  [type] $data    [description]
		 * @return [type]          [description]
		 */
		public function update_user( $user_id, $data ) {
			return $this->run( "users/$user_id", $data, 'PUT' );
		}

		/* TEAMS. */

		/**
		 * Get a list of teams.
		 *
		 * @return [type] [description]
		 */
		public function get_teams() {
			return $this->run( 'teams' );
		}

		/**
		 * Get a specific team.
		 *
		 * @param  [type] $team_id [description]
		 * @return [type]          [description]
		 */
		public function get_team( $team_id ) {
			return $this->run( "teams/$team_id" );
		}

		/**
		 * Add a user to a team.
		 *
		 * @param [type] $user_id  [description]
		 * @param array  $user_obj The User object.
		 * @return object          The results.
		 */
		public function add_user_to_team( $team_id, $user_obj ) {
			return $this->run( "teams/$team_id/users", $user_obj, 'POST' );
		}

		/**
		 * Remove a user from a team.
		 *
		 * Huh, not 100% sure how this is used, their API is a little vague on whether
		 * you pass an object or reference it by ID.
		 *
		 * @param  [type] $user_id [description]
		 * @return [type]          [description]
		 */
		public function remove_user_from_team( $user_id ) {

		}

		/* NUMBERS. */

		public function get_numbers() {
			return $this->run( 'numbers' );
		}

		public function get_number( $number_id ) {
			return $this->run( "numbers/$number_id" );
		}

		public function update_number( $number_id, $number_obj ) {
			return $this->run( "numbers/$number_id", $number_obj, 'PUT' );
		}


		/* CALL ROUTES. */

		public function get_calls() {
			return $this->run( 'calls' );
		}

		public function search_calls( $params = array() ) {
			return $this->run( 'calls/search', $params );
		}

		public function get_call( $call_id ) {
			return $this->run( "calls/$call_id" );
		}

		/**
		 * Transfer a call to a user.
		 *
		 * @param  [type] $call_id [description]
		 * @param  [type] $user_id [description]
		 * @return [type]          [description]
		 */
		public function transfer_call( $call_id, $user_id ) {
			return $this->run( "calls/$call_id/transfers", array( 'user_id' => $user_id ), 'POST' );
		}

		/**
		 * See a link in the requests body.
		 *
		 * @param  [type] $call_id [description]
		 * @return [type]          [description]
		 */
		public function display_call_link( $call_id ) {
			return $this->run( "calls/$call_id/link" );
		}

		public function set_custom_call_data( $call_id, $args ) {
			return $this->run( "calls/$call_id/metadata", $args, 'POST' );
		}

		public function delete_recording( $call_id ) {
			return $this->run( "calls/$call_id/recording", array(), 'DELETE' );
		}

		public function delete_voicemail( $call_id ) {
			return $this->run( "calls/$call_id/voicemail", array(), 'DELETE' );
		}

		/* CONTACTS. */

		public function get_contacts( $page = 1, $per_page = 50 ) {
			$args = array(
				'page' => $page,
				'per_page' => $per_page,
			);

			return $this->run( 'contacts', $args );
		}

		public function get_contact( $contact_id ){
			return $this->run( "contacts/$contact_id" );
		}

		public function search_contacts( $params = array() ) {
			return $this->run( 'contacts/search', $params );
		}

		public function add_contact( $contact ) {
			return $this->run( 'contacts', $contact, 'POST' );
		}

		/**
		 * Update a contact.
		 *
		 * @param  [type] $contact_id [description]
		 * @param  array  $contact    [description]
		 * @return [type]             [description]
		 */
		public function update_contact( $contact_id, $contact = array() ) {
			return $this->run( "contacts/$contact_id", $contact, 'POST' );
		}

		public function delete_contact( $contact_id ) {
			return $this->run( "contacts/$contact_id", array(), 'DELETE' );
		}

	}
}
