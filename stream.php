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
		self::$blog_key = sha1 ( site_url() );
		// add_shortcode('myshortcode', array(__CLASS__, 'handle_shortcode'));
 		self::$add_script = true;
		add_action( 'init', array(__CLASS__, 'register_script' ) );
		add_action( 'wp_footer', array(__CLASS__, 'print_script' ) );
		
		// add the menu to the settings
		add_action( 'admin_menu', array(__CLASS__,  'add_menu' ) );
		add_action( 'admin_init', array(__CLASS__,  'admin_init' ) );
		
		add_action( 'save_post', array(__CLASS__,'node_live_update_send' ) , 99, 2);
		
		add_action( 'wp_footer',   array( __CLASS__, 'post_template_js'), 1 ); // templates should be generated before calling the js
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
			wp_register_script( 'doT', plugins_url('/js/doT.js', __FILE__)  );
			wp_register_script( 'socket', self::$option['url'].'/socket.io/socket.io.js', array('jquery') );
			wp_register_script( 'socket-main', plugins_url('/js/main.js', __FILE__), array('jquery','socket','doT', 'jquery-color') );
			
			
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
 		
		// wp_print_scripts( 'socket' );
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
	
	function node_live_update_send( $post_id, $post ){
		
		// var_dump($post,$post_id);
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      		return;
      		
		if ( wp_is_post_revision( $post_id ) )
			return;
		// we don't updat the password protected posts or if it is not a post
		if( 'publish' != $post->post_status && !empty( $post->post_password ) && 'post' == $post->post_type ) 
			return;
			
		$categories_data = get_the_category();
		
		foreach($categories_data as $category)
			$categories[] = array( 'url' => get_category_link( $category->term_id ), 'name' => $category->cat_name );
		
		$tags_data = get_the_tags();
		foreach($tags_data as $tag)
			$tags[] = array( 'url' => get_tag_link($tag->term_id), 'name' => $tag->name );
		
		$excerpt = ( empty( $post->post_excerpt )? $post->post_content : $post->post_excerpt );
		
		$data = array(
			'id' => $post_id,
			'title' 	=> apply_filters( 'the_title',$post->post_title) ,
			'author'	=> apply_filters( 'the_author', get_the_author_meta('display_name', $post->post_author) ),
			'author_url'=> get_author_posts_url( $post->post_author ),
			'permalink'	=> get_permalink(),
			'content'  	=> apply_filters( 'the_content', $post->post_content ),
			'gmt_date' 	=> get_the_date( 'c' ),
			'date' 	   	=> get_the_date(),
			'time' 	   	=> get_the_time(),
			'excerpt'  	=> apply_filters( 'the_excerpt', apply_filters('get_the_excerpt',$excerpt ) ),
			'categories'=> $categories,
			'tags'=> $tags,
			'comment_status' => $post->comment_status,
			'comment_count'  => $post->comment_count
		);
		
		$action = ( get_post_meta($post->ID, '_stream_update', true)? 'update' : 'new' );
		
		if( 'new' == $action )
			add_post_meta($post->ID, '_stream_update', '1', true );
		
		//Send the post to the node.js server
		CTLT_Stream::send( 'post', $data, $action );
			
			
		
	
	}
	
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
    			)
			);
	}
	
	function post_template_js() {
		
		?>
  		<script id="post-cpt-single" type="text/x-dot-template"><?php echo self::post_template(); ?></script>
  		<?php 
	}
	
	function post_template() { 
	?>
<article id="post-{{=it.id}}" class="post-{{=it.id}} post type-post status-publish hentry ">
	<header class="entry-header">
		<h1 class="entry-title"><a href="{{=it.permalink}}" title="Permalink to {{=it.title}}" rel="bookmark">{{=it.title}}</a></h1>
				<div class="entry-meta">
			Posted on <a href="{{=it.url}}" title="{{=it.permalink}}" rel="bookmark"><time class="entry-date" datetime="{{=it.gmt_date}}" pubdate>{{=it.date}}</time></a><span class="byline"> by <span class="author vcard"><a class="url fn n" href="{{=it.author_url}}" title="View all posts by {{=it.author}}" rel="author">{{=it.author}}</a></span></span>		</div><!-- .entry-meta -->
			</header><!-- .entry-header -->

		<div class="entry-content">
		{{=it.excerpt}}
			</div><!-- .entry-content -->
	
	<footer class="entry-meta">
		{{? it.categories }}
		<i class="icon-folder-open"></i> <span class="meta-shell">
		{{~it.categories :value:index}}
			<a rel="tag" href="{{=value.url}}">{{=value.name}}</a>,
		{{~}}</span>
		{{?}}
		{{? it.tags }}
		<i class="icon-tags"></i><span class="meta-shell">
		{{~it.tags :value:index}}
			<a rel="tag" href="{{=value.url}}">{{=value.name}}</a>,
		{{~}}</span>
		<i class="icon-globe"></i>  <a href="{{=it.permalink}}" title="Permalink to {{=it.title}}" rel="bookmark">permalink</a>.	
		{{?}}
		<span class="sep"> | </span>
		<span class="comments-link"><a href="{{=it.permalink}}#respond" title="Comment on hey there">Leave a comment</a></span>
		
	</footer><!-- #entry-meta -->
</article><!-- #post-721 -->
	<?php
	}
	
	
}
CTLT_Stream::init();
endif;
 
