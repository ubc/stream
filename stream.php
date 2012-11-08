<?php
/*
 * Plugin Name: Stream
 * Plugin URI:
 * Description: a simple intergration for multisite and node
 * Version: 0.2
 * Author: Enej Bajgoric | CTLT DEV
*/
// an attemtmp at a node - socket.io and wordpress intergration

// this is the sample plugin
require('carry_update_posts.php');

if( !class_exists('CTLT_Stream') ):


define('CTLT_STREAM', true);

class CTLT_Stream {

	public static $add_script;

	static $option;
	static $node_url;
	static $blog_key;

	/**
	 * init function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function init() {

		self::$blog_key = sha1 ( site_url() );

		add_action( 'init', array(__CLASS__, 'register_script' ) );
		add_action( 'wp_footer', array(__CLASS__, 'print_script' ) );

		// add the menu to the settings
		add_action( 'admin_menu', array(__CLASS__,  'add_menu' ) );
		add_action( 'admin_init', array(__CLASS__,  'admin_init' ) );


	} // end of init

	/**
	 * admin_init function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function admin_init() {

		self::$add_script = false; // don't add the script on the

		register_setting( 'stream_options', 'stream_options', array(__CLASS__, 'validate_options' ) );
		add_settings_section( 'stream_main', 'Main Settings', array(__CLASS__, 'section_text' ), 'stream' );
		add_settings_field( 'plugin_text_string', 'Node Server URL', array(__CLASS__, 'setting_string' ), 'stream', 'stream_main' );

	} // end of admin_init

	static function setting_string() {
		$options = get_option('stream_options');
		echo "<input id='node-url' name='stream_options[url]' size='40' type='text' value='{$options['url']}' />";
	} // end of settings string

	/**
	 * validate_options function.
	 *
	 * @access public
	 * @param mixed $option
	 * @return void
	 */
	function validate_options( $options ) {

		return $options;
	} // end of validate options

	/**
	 * section_text function.
	 *
	 * @access public
	 * @return void
	 */
	function section_text( ) {
		// do we even need this

	} // end of section_text

 	/**
 	 * add_menu function.
 	 *
 	 * @access public
 	 * @static
 	 * @return void
 	 */
 	static function add_menu() {
		add_options_page('Stream', 'Stream', 'manage_options', 'stream', array(__CLASS__,  'setting_page' ));
	} // end of add_menu


	/**
	 * register_script function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function register_script() {

		self::$option = get_option('stream_options');

		if( isset( self::$option['url'] ) ):

			self::$option['blog_key'] = self::$blog_key;
			
			wp_register_script( 'socket', self::$option['url'].'/socket.io/socket.io.js', array('jquery') );
			wp_register_script( 'socket-main', plugins_url('/js/main.js', __FILE__), array('jquery','socket' ) );
			wp_localize_script( 'socket', 'STREAM', self::$option );
			

		endif;
	} // end of register script

	/**
	 * print_script function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function print_script() {
		if ( ! self::$add_script )
			return;

		wp_print_scripts( 'socket-main' );
	} // end of print_script

	/**
	 * setting_page function.
	 *
	 * @access public
	 * @static
	 * @return void
	 */
	static function setting_page() { ?>
		<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div><h2>Stream Settings</h2>
		<form action="options.php" method="post">
			<?php settings_fields('stream_options'); ?>
			<?php do_settings_sections( 'stream' ); ?>

			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</form>
		</div>
	<?php
	} // end of settings_page

	/**
	 * send function.
	 *
	 * @access public
	 * @param mixed $type
	 * @param mixed $data
	 * @param mixed $action
	 * @return void
	 */
	function send( $type, $data, $action ) {

		$response = wp_remote_post( self::$option['url'].'/blog/'.md5( self::$blog_key ),
				array(
					'method' => 'POST',
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array('content-type' => 'application/json'),
					'body' => json_encode( array( 'type' => $type, 'data' => $data, 'action' => $action )),
					'cookies' => array()
    			)// end of array
			); // end of remote post
	} // end of send

} // end of CTLT_Steam class

CTLT_Stream::init();

endif;