<?php 
/*
 * Plugin Name: Stream
 * Plugin URI: 
 * Description: a simple intergration for multisite and node 
 * Version: 0.1
 * Author: Enej Bajgoric | CTLT DEV
*/
// an attemtmp at a node - socket.io and wordpress intergration

if( !class_exists('CTLT_Stream') ):
class CTLT_Stream {
	static $add_script;
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
		self::$blog_key = 'ABC1212312312';
		// add_shortcode('myshortcode', array(__CLASS__, 'handle_shortcode'));
 		self::$add_script = true;
		add_action( 'init', array(__CLASS__, 'register_script' ) );
		add_action( 'wp_footer', array(__CLASS__, 'print_script' ) );
		
		// add the menu to the settings
		add_action( 'admin_menu', array(__CLASS__,  'add_menu' ) );
		add_action( 'admin_init', array(__CLASS__,  'admin_init' ) );
		
		add_action('publish_post', array(__CLASS__,'node_live_update_send' ) );
	}
	
	/**
	 * admin_init function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function admin_init(){
		self::$add_script = false;
		register_setting( 'stream_options', 'stream_options', array(__CLASS__, 'validate_options' ) );
		add_settings_section( 'stream_main', 'Main Settings', array(__CLASS__, 'section_text' ), 'stream' );
		add_settings_field( 'plugin_text_string', 'Node Server URL', array(__CLASS__, 'setting_string' ), 'stream', 'stream_main' );

	}
	
	static function setting_string() {
	$options = get_option('stream_options');
	echo "<input id='node-url' name='stream_options[url]' size='40' type='text' value='{$options['url']}' />";
	} 
	
	/**
	 * validate_options function.
	 * 
	 * @access public
	 * @param mixed $option
	 * @return void
	 */
	function validate_options( $options ) {
	
		return $options;
	}
	
	/**
	 * section_text function.
	 * 
	 * @access public
	 * @return void
	 */
	function section_text( ) {
		
	
	}
	
 	/**
 	 * add_menu function.
 	 * 
 	 * @access public
 	 * @static
 	 * @return void
 	 */
 	static function add_menu() {
		add_options_page('Stream', 'Stream', 'manage_options', 'stream', array(__CLASS__,  'setting_page' ));
	}
	
	/**
	 * handle_shortcode function.
	 * 
	 * @access public
	 * @static
	 * @param mixed $atts
	 * @return void
	 */
	static function handle_shortcode($atts) {
		self::$add_script = true;
 
		// actual shortcode handling here
	}
 	
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
			wp_register_script( 'socket-main', plugins_url('/js/main.js', __FILE__), array('jquery','socket') );
			
			wp_localize_script( 'socket', 'STREAM', self::$option );
		
		endif;
	}
 	
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
 		
		wp_print_scripts( 'socket' );
		wp_print_scripts( 'socket-main' );
	}
	
	/**
	 * setting_page function.
	 * 
	 * @access public
	 * @static
	 * @return void
	 */
	static function setting_page(){
	
		?>
		<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div><h2>Stream Settings</h2>
		<form action="options.php" method="post">
		<?php settings_fields('stream_options'); ?>
		<?php do_settings_sections( 'stream' ); ?>
 
		<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</form>
		</div>
		<?
	
	}
	
	function node_live_update_send( $post_id ){
	if ( !wp_is_post_revision( $post_id ) ) {
		$post = get_post( $post_id );
		
		//Send the post to the node.js server
		$response = wp_remote_post( self::$option['url'].'/blog/'.self::$blog_key, 
			array(
				'method' => 'POST',
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array(
					'id'=>$post_id,
					'title'=>$post->post_title,
					'content'=>apply_filters('the_content', $post->post_content),
					'author'=>get_the_author_meta( 'display_name' , $post->post_author),
				),
			)
		);
		
	}
	
	}
	
	
}
CTLT_Stream::init();
endif;
 
