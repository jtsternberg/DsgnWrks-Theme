<?php
/** Start the engine */
require_once( get_template_directory() . '/lib/init.php' );

unregister_sidebar( 'header-right' );
unregister_sidebar( 'sidebar-alt' );

set_post_thumbnail_size( 50, 50, true );
add_image_size( 'featured-thumbnail', 152, 110, true ); // featured item thumbnail size
add_image_size( 'slides', 940, 365, true ); // featured item thumbnail size

/**
 * Add post format support
 */
add_theme_support( 'post-formats', array( 'aside', 'link', 'quote', 'status', 'gallery', 'image', 'video', 'audio', 'chat' ) );
add_theme_support( 'genesis-post-format-images' );

add_action('wp_enqueue_scripts', 'dsgnwrks_scripts_and_styles');
function dsgnwrks_scripts_and_styles() {
	if ( is_admin() )
	return;

	wp_enqueue_script( 'popup', get_stylesheet_directory_uri(). '/lib/js/popup.js', false, '1.0' );

	wp_enqueue_script( 'typekit', 'http://use.typekit.net/usu1lsd.js', false, '1.0' );

	// wp_enqueue_script( 'ss-legacy', get_stylesheet_directory_uri(). '/lib/webfonts/ss-legacy.js', false, '1.0', true );
	// wp_enqueue_style( 'ss-standard', get_stylesheet_directory_uri() . '/lib/webfonts/ss-standard.css' );

	if ( is_front_page() ) {
		wp_enqueue_script( 'flexslider', get_stylesheet_directory_uri(). '/lib/js/jquery.flexslider.js', 'jquery', '1.0' );
		wp_enqueue_style( 'flexslider', get_stylesheet_directory_uri() . '/lib/css/flexslider.css', array(), '1.0' );
	}

	// global $wp_styles;
	// wp_enqueue_style( 'atg-ie7', get_stylesheet_directory_uri() . '/lib/css/style-ie7.css', array(), '1.0' );
	// wp_enqueue_style( 'atg-bbpress', get_stylesheet_directory_uri() . '/lib/css/bbpress.css');
	// $wp_styles->add_data( 'atg-ie7', 'conditional', 'lte IE 7' );
}

add_action( 'genesis_meta', 'add_viewport_meta_tag' );
function add_viewport_meta_tag() {
    ?>
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"/> -->
    <link rel="openid.server" href="http://www.myopenid.com/server" />
    <link rel="openid.delegate" href="http://jtsternberg.myopenid.com/" />
    <meta name="author" content="Justin Sternberg"/>
    <?php
}

add_action( 'wp_head', 'dsgnwrks_typekit_script' );
function dsgnwrks_typekit_script() {
    ?>
    <script type="text/javascript">try{Typekit.load();}catch(e){}</script>
    <?php
    if ( is_front_page() ) {
    	?>
    	<script type="text/javascript">
    		jQuery(window).load(function() {
    			jQuery('#featuredcontent').flexslider({
    				directionNav: true,
    				controlNav: true,
    				pauseOnAction: true,
    				pauseOnHover: true,
    				manualControls: ".flex-control-nav li",
    		    });
    		});
    	</script>
    	<?php
    }
}

remove_action( 'genesis_after_header', 'genesis_do_nav' );
add_action( 'genesis_before_header', 'dsgnwrks_do_nav' );
function dsgnwrks_do_nav() {
	echo '<div class="top-half"></div>';
	genesis_do_nav();
}

add_action( 'genesis_after_header', 'dsgnwrks_do_slider' );
function dsgnwrks_do_slider() {
	if ( !is_front_page() ) return;
	?>
	<div id="featuredcontent">
		<?php
		$slides = new WP_Query( array(
			'post_type' => 'wds_featured_gallery',
			'orderby' => 'menu_order',
			'order' => 'ASC',
		) );
		if ( $slides && $slides->have_posts() ) {
			echo '<ul class="slides">';
			$count = 0;

			while ( $slides->have_posts() ) : $slides->the_post();

				$id = get_the_ID();
				$class = '';
				if ( $position = get_post_meta ( $id, '_wds_feat_gallery_caption_position',true ) ) {
					if ( $position != 'none' )
					$class = ' '. $position;
				}
				if ( $bg = get_post_meta ( $id, '_wds_feat_gallery_caption_bg',true ) ) {
					$class .= ' '. $bg;
				}
				$link = get_post_meta( $id, 'link', true );
				$linktext = get_post_meta( $id, 'linktext',true );

				$linktext = ( !empty( $linktext ) && !empty( $link ) ) ? '<h4><a class="more" href="'. $link .'">'. $linktext .'</a></h4>' : '';

				?>

				<li>
					<?php

					echo !empty( $link ) ? '<a href="'. $link .'">' : '';
					echo get_the_post_thumbnail( $id, 'slides' );
					echo !empty( $link ) ? '</a>' : '';

					if ( $position != 'none' ) {
						?>
						<div class="flex-caption<?php echo $class; ?>">
							<?php
							the_title( '<h3>', '</h3>' );
							the_content();
							echo $linktext;
							?>
						</div>
						<?php
					}
					?>

				</li>

				<?php
				$count++;
			endwhile;
			// Reset Post Data
			wp_reset_postdata();
			echo '
			</ul>
			<ul class="flex-control-nav">
			';
			for ( $i = 0; $i < $count; $i++ ) {
				echo '<li></li>';
			}
			echo '
			</ul>
			';
		}
		?>
		<img class="embellish" src="<?php echo get_stylesheet_directory_uri(); ?>/images/embelishfeatured.gif" />
	</div>
	<?php
}

add_action( 'genesis_before_footer', 'dsgnwrks_footer_wrap', 5 );
function dsgnwrks_footer_wrap() {
	echo '<div id="footer-wrap">';
}

add_action( 'genesis_after_footer', 'dsgnwrks_footer_wrap_bottom' );
function dsgnwrks_footer_wrap_bottom() {
	echo '</div><!-- #footer-wrap -->';
}

/** Add support for 3-column footer widgets */
add_theme_support( 'genesis-footer-widgets', 3 );

add_filter( 'genesis_breadcrumb_args', 'dw_breadcrumb_args' );
function dw_breadcrumb_args( $args ) {

	$args['labels']['prefix'] = '';
	return $args;
}

add_filter( 'avatar_defaults', 'dw_newgravatar' );
function dw_newgravatar( $avatars ) {
	$myavatar = get_stylesheet_directory_uri() . '/images/WPAdminLogo.gif';
	$avatars[$myavatar] = 'DsgnWrks';
	return $avatars;
}

//Custom Login Page
add_action( 'login_head', 'dsgrnwrks_custom_login' );
function dsgrnwrks_custom_login() {
	echo '<link rel="stylesheet" type="text/css" href="'. get_stylesheet_directory_uri() .'/lib/css/custom-login.css" />';
}

//hook the administrative header output
add_action( 'admin_head', 'dsgrnwrks_custom_logo' );
function dsgrnwrks_custom_logo() {
	echo '<style type="text/css">
		#header-logo { background-image: url('. get_stylesheet_directory_uri() .'/images/WPAdminLogo.gif) !important; }
	</style>';
}

add_filter( 'genesis_post_info', 'dsgnwrks_show_shortlink' );
function dsgnwrks_show_shortlink( $post_info ) {
	$shortlink = wp_get_shortlink( get_the_ID(), 'post');
	if ( !empty($shortlink) )
		$post_info .= '<span class="post-comments"><input id="shortlink" type="hidden" value="' . esc_attr($shortlink) . '" /><a href="#" class="button button-small" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val()); return false;">' . __('Get Shortlink') . '</a></span>';

	return $post_info;
}

add_filter( 'genesis_post_edit_shortcode', 'dsgnwrks_add_space' );
function dsgnwrks_add_space( $edit ) {

	return $edit .' ';
}
