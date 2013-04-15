<?php
/**
 * Plugin Name: Stream
 * Plugin URI: https://github.com/ubc/stream
 * Version: 1.0
 * Description: A simple integration for multisite and nodejs
 * Author: Enej Bajgoric Devindra Payment, CTLT, UBC
 */
require( 'carry_update_posts.php' ); // This is the sample plugin

if ( ! class_exists( 'CTLT_Stream' ) ):
	define( 'CTLT_STREAM', true );

	class CTLT_Stream {
		static $add_script = false;
		static $node_url;
		static $blog_key;
		static $url_override;
		
		/**
		 * init function.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		static function init() {
			self::$blog_key = sha1( site_url() );
			self::$url_override = get_site_option( 'stream_url_override', "on" );
			self::$node_url = self::get_node_url();
			
			add_action( 'init',      array(__CLASS__, 'register_script' ) );
			add_action( 'wp_footer', array(__CLASS__, 'print_script' ) );
			
			add_action( 'admin_init', array(__CLASS__, 'load' ) );
			add_action( 'network_admin_menu', array(__CLASS__, 'network_admin_menu' ) );
			
			if ( self::$url_override ):
				add_action( 'admin_menu', array(__CLASS__, 'admin_menu' ) );
			endif;
		}
		
		/**
		 * admin_menu function.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		static function admin_menu() {
			add_options_page( 'Stream', 'Stream', 'manage_options', 'stream_settings', array( __CLASS__,  'admin_page' ) );
		}
		
		/**
		 * network_admin_menu function.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		static function network_admin_menu() {
			add_submenu_page( 'settings.php', 'Stream', 'Stream', 'manage_options', 'stream_settings', array( __CLASS__, 'admin_page' ) );
		}
		
		/**
		 * admin_init function.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		static function load() {
			register_setting( 'stream_options', 'stream_node_url');
			
			add_settings_section( 'stream_main', 'Main Settings',     array( __CLASS__, 'setting_section_main' ), 'stream_settings' );
			add_settings_field( 'node_server_url', 'Node Server URL', array( __CLASS__, 'setting_server_url' ), 'stream_settings', 'stream_main' );
			
			if ( is_network_admin() ):
				add_settings_field( 'stream_url_override', 'Allow URL Override', array( __CLASS__, 'setting_server_override' ), 'stream_settings', 'stream_main' );
			endif;
			
			add_settings_field( 'node_server_status', 'Node Server',  array( __CLASS__, 'setting_server_status' ), 'stream_settings', 'stream_main' );
		}
		
		static function setting_section_main() {
			?>
			Stream Settings and NodeJS Server Status
			<?php
		}
		
		static function setting_server_url() {
			$disabled = self::$url_override != "on" && ! is_network_admin();
			$disabled = ( $disabled ? 'disabled="disabled" readonly="readonly" title="This url has been set by the network administrator."' : '' );
			?>
			<input id="node-url" name="stream_node_url" size="40" type="text" value="<?php echo self::$node_url; ?>" <?php echo $disabled; ?> />
			<?php
		}
		
		static function setting_server_override() {
			?>
			<input id="url-overide" name="stream_url_override" type="checkbox" <?php echo checked( self::$url_override == "on" ); ?>/>
			Allow site admins to choose their own node server url.
			<?php
		}
		
		static function setting_server_status() {
			$status = self::get_node_status();
			?>
			<?php if ( CTLT_Stream::is_node_active() ): ?>
				<div style="color: green">Connected</div>
			<?php else: ?>
				<div style="color: red">
					Not Found
					<?php
					if ( $status->errors ):
						?>
						<br />
						<div style="color: gray; margin-left: 25px;">
							<?php
							foreach ( $status->errors as $error => $messages ):
								echo $error.": ".implode(', ', $messages);
							endforeach;
							?>
						</div>
						<?php
					endif;
					?>
				</div>
			<?php endif;
		}
		
		/**
		 * register_script function.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		static function register_script() {
			if ( isset( self::$node_url ) ):
				wp_register_script( 'socket', self::$node_url.'/socket.io/socket.io.js', array( 'jquery' ) );
				wp_register_script( 'socket-main', plugins_url('/js/main.js', __FILE__), array( 'jquery', 'socket' ) );
				wp_localize_script( 'socket', 'stream_plugin', array(
					'blog_key' => self::$blog_key,
					'url' => self::$node_url,
				) );
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
			if ( self::$add_script ):
				wp_print_scripts( 'socket-main' );
			endif;
		}
		
		/**
		 * admin_page function.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		static function admin_page() {
			if ( is_network_admin() ):
				if ( ! empty( $_POST ) ):
					self::$node_url = ( isset( $_POST['stream_node_url'] ) ? $_POST['stream_node_url'] : '' );
					self::$url_override = ( isset( $_POST['stream_url_override'] ) ? $_POST['stream_url_override'] : false );
					
					update_site_option( 'stream_node_url', self::$node_url );
					update_site_option( 'stream_url_override', self::$url_override );
				endif;
			else:
				$action = 'action="options.php"';
			endif;
			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"><br /></div>
				<h2>Stream Settings</h2>
				<form id="stream-options" method="post" <?php echo $action; ?>>
					<?php settings_fields( 'stream_options' ); ?>
					<?php do_settings_sections( 'stream_settings' ); ?>
					<br />
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
				</form>
			</div>
			<?php
		}
		
		/**
		 * send function.
		 *
		 * @access public
		 * @param mixed $type
		 * @param mixed $data
		 * @param mixed $action
		 * @return void
		 */
		static function send( $type, $data, $action ) {
			$response = wp_remote_post( self::$node_url.'/blog/'.md5( self::$blog_key ), array(
				'method'      => 'POST',
				'timeout'     => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array( 'content-type' => 'application/json' ),
				'body'        => json_encode( array( 'type' => $type, 'data' => $data, 'action' => $action ) ),
				'cookies'     => array(),
			) );
		}
		
		static function get_node_url() {
			if ( self::$url_override ):
				$result = get_option( 'stream_node_url', '' );
			endif;
			
			if ( empty( $result ) ):
				$result = get_site_option( 'stream_node_url', '' );
			endif;
			
			$result = rtrim( $result, '/' ); // Remove trailing slash.
			return $result;
		}
		
		/**
		 * Check to see node server is alive.
		 */
		static function get_node_status() {
			// Send a server_status request to nodejs server
			return wp_remote_post( self::$node_url.'/server_status', array( 'method' => 'POST' ) );
		}
		
		/**
		 * Check to see node server is alive.
		 */
		static function is_node_active( $response = null ) {
			if ( empty( $response ) ):
				$response = self::get_node_status();
			endif;
			
			if ( is_object( $response ) && get_class( $response ) == 'WP_Error' ):
				return false;
			elseif ( isset( $response['response'] ) && isset( $response['response']['code'] ) && $response['response']['code'] == 200 ):
				return true;
			else:
				return $response;
			endif;
		}
	}
	
	CTLT_Stream::init();
endif;