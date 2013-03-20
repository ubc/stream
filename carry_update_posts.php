<?php 

// A Sample plugin that helps update the post on the front end of carry, carry is a wordpress theme

/**
 * Carry_Update_Post class.
 */
class Carry_Update_Post {
	
	
	/**
	 * init function.
	 * 
	 * @access public
	 * @return void
	 */
	function init() {
		
		add_action( 'init', array(__CLASS__, 'register_script' ), 10 );
		add_action( 'save_post', array(__CLASS__,'push_updated_post' ) , 99, 2 );
		add_action( 'wp_footer',   array( __CLASS__, 'post_template_js'), 1 ); // templates should be generated before calling the js
		add_action( 'wp_footer', array(__CLASS__, 'print_script' ) );

	}
	
	function register_script() {
			// don't do anything unless CLTL STEAM is set
			if( defined( 'CTLT_STREAM' ) && !CTLT_STREAM )
				return true;
			
			wp_register_script( 'doT', plugins_url('/js-carry/doT.min.js', __FILE__)  ); // templating library 
			wp_register_script( 'carry-update-posts', plugins_url('/js-carry/carry-update-posts.js', __FILE__), array( 'jquery','socket-main','doT', 'jquery-color') );
	
	}
	
	function print_script() {
		
		wp_print_scripts( 'carry-update-posts' );
	
	}
	
	function push_updated_post( $post_id, $post ) {
	
		// don't do anything unless CLTL STEAM is set
		if( defined( 'CTLT_STREAM' ) && !CTLT_STREAM )
			return $post_id;
	
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      		return $post_id;
      	      	
		if ( wp_is_post_revision( $post_id ) )
			return;
			
		// we don't updat the password protected posts or if it is not a post
		if( 'publish' != $post->post_status || !empty( $post->post_password ) || 'post' != $post->post_type ) 
			return $post_id;
			
		$categories_data = get_the_category();
		
		if( is_array( $categories_data) ):
			foreach($categories_data as $category)
				$categories[] = array( 'url' => get_category_link( $category->term_id ), 'name' => $category->cat_name );
		
		endif;
		$tags_data = get_the_tags();
		if( is_array( $tags_data ) ):
			foreach($tags_data as $tag)
				$tags[] = array( 'url' => get_tag_link($tag->term_id), 'name' => $tag->name );
		endif;
		
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
	
	
	
	
	/**
	 * post_template_js function.
	 * include the post template 
	 * @access public
	 * @return void
	 */
	function post_template_js() {
		// don't do anything unless CLTL STEAM is set
		if( defined( 'CTLT_STREAM' ) && !CTLT_STREAM )
			return true;
		?>
  		<script id="post-cpt-single" type="text/x-dot-template"><?php echo self::post_template(); ?></script>
  		<?php 
	}
	
	/**
	 * post_template function.
	 * this function is very specific for carry
	 * @access public
	 * @return void
	 */
	function post_template() { ?>
<article id="post-{{=it.id}}" class="post-{{=it.id}} post type-post status-publish hentry ">
	<header class="entry-header">
		<h1 class="entry-title"><a href="{{=it.permalink}}" title="Permalink to {{=it.title}}" rel="bookmark">{{=it.title}}</a></h1>
				<div class="entry-meta">
			Posted on <a href="{{=it.permalink}}" title="{{=it.permalink}}" rel="bookmark"><time class="entry-date" datetime="{{=it.gmt_date}}" pubdate>{{=it.date}}</time></a><span class="byline"> by <span class="author vcard"><a class="url fn n" href="{{=it.author_url}}" title="View all posts by {{=it.author}}" rel="author">{{=it.author}}</a></span></span>		</div><!-- .entry-meta -->
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
		{{?}}
		<i class="icon-globe"></i>  <a href="{{=it.permalink}}" title="Permalink to {{=it.title}}" rel="bookmark">permalink</a>.	
		<span class="sep"> | </span> 
		<span class="comments-link"><a href="{{=it.permalink}}#respond" title="Comment on hey there">Leave a comment</a></span>
		
	</footer>
</article>
	<?php
	}

}

Carry_Update_Post::init();

