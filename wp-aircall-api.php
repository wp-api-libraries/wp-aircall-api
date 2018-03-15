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

		/**
		 * is_debug
		 *
		 * @var mixed
		 * @access protected
		 */
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
			// pp( $this->base_uri . $this->route, $this->args );
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


		/**
		 * set_headers function.
		 *
		 * @access protected
		 * @return void
		 */
		protected function set_headers(){
			$this->args['headers'] = array(
				'Authorization' => 'Basic ' . base64_encode( $this->api_id . ':' . $this->api_token ),
				'Content-Type' => 'application/json',
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

		/* RATE LIMITING. */

		/**
		 * get_rate_limit function.
		 *
		 * @access public
		 * @return void
		 */
		public function get_api_limit_header() {
			$rate_limit = $_SERVER['X-AircallApi-Limit'];
			return $rate_limit;
		}

		/**
		 * get_api_remaining_header function.
		 *
		 * @access public
		 * @return void
		 */
		public function get_api_remaining_header() {
			$remaining = $_SERVER['X-AircallApi-Remaining'];
			return $remaining;
		}

		/**
		 * get_api_reset_header function.
		 *
		 * @access public
		 * @return void
		 */
		public function get_api_reset_header() {
			$reset_header = $_SERVER['X-AircallApi-Reset'];
			return $reset_header;
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

		/**
		 * Get a list of numbers.
		 *
		 * @return object A pagination object (with results).
		 */
		public function get_numbers() {
			return $this->run( 'numbers' );
		}

		/**
		 * Get a specific number.
		 *
		 * @param  int/string $number_id The ID of the number.
		 * @return object                The number.
		 */
		public function get_number( $number_id ) {
			return $this->run( "numbers/$number_id" );
		}

		/**
		 * Update a number.
		 *
		 * @param  int/string $number_id  The ID of the number.
		 * @param  array      $number_obj The number object.
		 * @return object                 The response.
		 */
		public function update_number( $number_id, $number_obj ) {
			return $this->run( "numbers/$number_id", $number_obj, 'PUT' );
		}


		/* CALL ROUTES. */

		/**
		 * get_calls function.
		 *
		 * @access public
		 * @param array $params (default: array())
		 * @return void
		 */
		public function get_calls( $params = array() ) {
			return $this->run( 'calls', $params );
		}

		/**
		 * search_calls function.
		 *
		 * @access public
		 * @param array $params (default: array())
		 * @return void
		 */
		public function search_calls( $params = array() ) {
			return $this->run( 'calls/search', $params );
		}

		/**
		 * get_call function.
		 *
		 * @access public
		 * @param mixed $call_id
		 * @return void
		 */
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

		/**
		 * Get the custom data for a call.
		 *
		 * @param int   $call_id The ID of the call.
		 * @param array $args    Additional arguments to pass.
		 */
		public function set_custom_call_data( $call_id, $args ) {
			return $this->run( "calls/$call_id/metadata", $args, 'POST' );
		}

		/**
		 * Delete a recording.
		 *
		 * @param  int    $call_id The ID of the call.
		 * @return object          Hopefully an empty 200 OK response.
		 */
		public function delete_recording( $call_id ) {
			return $this->run( "calls/$call_id/recording", array(), 'DELETE' );
		}

		/**
		 * Delete a voicemail.
		 *
		 * @param  int    $call_id The ID of the call.
		 * @return object          Hopefully an empty 200 OK response.
		 */
		public function delete_voicemail( $call_id ) {
			return $this->run( "calls/$call_id/voicemail", array(), 'DELETE' );
		}

		/* CONTACTS. */

		/**
		 * Get a list of contacts.
		 *
		 * @param  int    $page     (Default: 1) The first page to start at.
		 * @param  int    $per_page (Default: 50) The number of results to display per page.
		 * @return object           Hopefully a pagination object with results.
		 */
		public function get_contacts( $page = 1, $per_page = 50 ) {
			$args = array(
				'page' => $page,
				'per_page' => $per_page,
			);

			return $this->run( 'contacts', $args );
		}

		/**
		 * Get a specific contact.
		 *
		 * @param  int    $contact_id The ID of the contat.
		 * @return object             The contact.
		 */
		public function get_contact( $contact_id ){
			return $this->run( "contacts/$contact_id" );
		}

		/**
		 * Search through contacts.
		 *
		 * $params accepts these (all optional) key => vals.
		 *  page
		 *  	Pagination for list of objects	1
		 *  per_page
		 *    Number of objects fetched per request	20
		 *  order
		 *    Reorder entries per order_by value, asc or desc	asc
		 *  order_by
		 *    Set the order field (only for contacts), created_at or updated_at	created_at
		 *  from
		 *    Set a minimal creation date for objects (UNIX timestamp)	(none)
		 *  to
		 *    Set a maximal creation date for objects (UNIX timestamp)	(none)
		 *
		 * @param  array  $params [desciption]
		 * @return [type]         [description]
		 */
		public function search_contacts( $params = array() ) {
			return $this->run( 'contacts/search', $params );
		}

		/**
		 * Create a contact.
		 *
		 * @param  array  $contact The contact.
		 * @return object          The hopefully created contact.
		 */
		public function add_contact( $contact ) {
			return $this->run( 'contacts', $contact, 'POST' );
		}

		/**
		 * Get a list of webhooks.
		 *
		 * @return array
		 */
		public function get_webhooks() {
			return $this->run( 'webhooks' );
		}

		/**
		 * get_webhook function.
		 *
		 * @access public
		 * @param mixed $webhook_id
		 * @return void
		 */
		public function get_webhook( $webhook_id ){
			return $this->run( "webhooks/$webhook_id" );
		}

		/**
		 * create_webhook function.
		 *
		 * @access public
		 * @param mixed $webhook
		 * @return void
		 */
		public function create_webhook( $webhook ){
			return $this->run( 'webhooks', $webhook, 'POST' );
		}

		/**
		 * update_webhook function.
		 *
		 * @access public
		 * @param mixed $webhook
		 * @param mixed $webhook_id
		 * @return void
		 */
		public function update_webhook( $webhook, $webhook_id ){
			return $this->run( "webhooks/$webhook_id", $webhook, 'PUT' );
		}

		/**
		 * delete_webhook function.
		 *
		 * @access public
		 * @param mixed $webhook_id
		 * @return void
		 */
		public function delete_webhook( $webhook_id ){
			return $this->run( "webooks/$webhook_id", array(), 'DELETE' );
		}

		/**
		 * Update a contact.
		 *
		 * $obj = array(
		 * 	'emails' => array(
		 * 		array(
		 *	 		'label' => 'Work',
		 * 			'value' => 'abcd@gmail.com'
		 * 		)
		 * 	)
		 * );
		 *
		 *
		 * @param  [type] $contact_id [description]
		 * @param  array  $contact    [description]
		 * @return [type]             [description]
		 */
		public function update_contact( $contact_id, $contact = array() ) {
			return $this->run( "contacts/$contact_id", $contact, 'POST' );
		}

		/**
		 * delete_contact function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @return void
		 */
		public function delete_contact( $contact_id ) {
			return $this->run( "contacts/$contact_id", array(), 'DELETE' );
		}

		/**
		 * add_contact_number function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @param mixed $value
		 * @param string $label (default: 'Alternate')
		 * @return void
		 */
		public function add_contact_number( $contact_id, $value, $label = 'Alternate' ){
			$number = array(
				'label' => $label,
				'value' => $value
			);

			return $this->run( "contacts/$contact_id/phone_details/", $number, 'POST' );
		}

		/**
		 * update_contact_number function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @param mixed $phone_id
		 * @param mixed $label (default: null)
		 * @param mixed $value (default: null)
		 * @return void
		 */
		public function update_contact_number( $contact_id, $phone_id, $label = null, $value = null ){
			if( $label === $value && $value === null ){
				return new WP_Error( 'invalid-data', __( 'You must submit either $label or $value.', 'wp-aircall-api' ) );
			}

			$args = array();

			if( $value !== null ){
				$args['value'] = $value;
			}

			if( $label !== null ){
				$args['label'] = $label;
			}

			return $this->run( "contacts/$contact_id/phone_details/$phone_id", $args, 'PUT' );
		}

		/**
		 * delete_contact_number function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @param mixed $phone_id
		 * @return void
		 */
		public function delete_contact_number( $contact_id, $phone_id ){
			return $this->run( "contacts/$contact_id/phone_details/$phone_id", array(), 'DELETE' );
		}

		/**
		 * add_contact_email function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @param mixed $value
		 * @param string $label (default: 'Alternate')
		 * @return void
		 */
		public function add_contact_email( $contact_id, $value, $label = 'Alternate' ){
			$email = array(
				'label' => $label,
				'value' => $value
			);

			return $this->run( "contacts/$contact_id/email_details/", $email, 'POST' );
		}

		/**
		 * update_contact_email function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @param mixed $email_id
		 * @param mixed $label (default: null)
		 * @param mixed $value (default: null)
		 * @return void
		 */
		public function update_contact_email( $contact_id, $email_id, $label = null, $value = null ){
			if( $label === $value && $value === null ){
				return new WP_Error( 'invalid-data', __( 'You must submit either $label or $value.', 'wp-aircall-api' ) );
			}

			$args = array();

			if( $value !== null ){
				$args['value'] = $value;
			}

			if( $label !== null ){
				$args['label'] = $label;
			}

			return $this->run( "contacts/$contact_id/email_details/$email_id", $args, 'PUT' );
		}

		/**
		 * delete_contact_email function.
		 *
		 * @access public
		 * @param mixed $contact_id
		 * @param mixed $email_id
		 * @return void
		 */
		public function delete_contact_email( $contact_id, $email_id ){
			return $this->run( "contacts/$contact_id/email_details/$email_id", array(), 'DELETE' );
		}

	}
}
