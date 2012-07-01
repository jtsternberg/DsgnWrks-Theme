<?php
/** Start the engine */
require_once( get_template_directory() . '/lib/init.php' );

unregister_sidebar( 'header-right' );
unregister_sidebar( 'sidebar-alt' );

set_post_thumbnail_size( 50, 50, true );
add_image_size( 'featured-thumbnail', 152, 110, true ); // featured item thumbnail size
add_image_size( 'slides', 940, 365, true ); // featured item thumbnail size

add_action('wp_enqueue_scripts', 'dsgnwrks_scripts_and_styles');
function dsgnwrks_scripts_and_styles() {
	if ( is_admin() )
	return;

	wp_enqueue_script( 'popup', get_stylesheet_directory_uri(). '/lib/js/popup.js', false, '1.0' );

	wp_enqueue_script( 'typekit', 'http://use.typekit.com/usu1lsd.js', false, '1.0' );

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
		$slides = new WP_Query(
			array(
				'post_type' => 'wds_featured_gallery',
				'orderby' => 'menu_order',
				'order' => 'ASC',
				)
			);
		$posts = $slides->posts;
		echo '<ul class="slides">';
		foreach ( $posts as $post ) {

			$class = '';
			if ( $position = get_post_meta ( $post->ID, '_wds_feat_gallery_caption_position',true ) ) {
				if ( $position != 'none' )
				$class = ' '. $position;
			}
			if ( $bg = get_post_meta ( $post->ID, '_wds_feat_gallery_caption_bg',true ) ) {
				$class .= ' '. $bg;
			}
			$link = get_post_meta( $post->ID, 'link', true );
			$linktext = get_post_meta( $post->ID, 'linktext',true );

			$linktext = ( !empty( $linktext ) && !empty( $link ) ) ? '<h4><a class="more" href="'. $link .'">'. $linktext .'</a></h4>' : '';

			?>

			<li>
				<?php

				echo !empty( $link ) ? '<a href="'. $link .'">' : '';
				echo get_the_post_thumbnail( $post->ID, 'slides' );
				echo !empty( $link ) ? '</a>' : '';

				if ( $position != 'none' ) {
					?>
					<div class="flex-caption<?php echo $class; ?>">
						<?php
						echo '<h3>'. get_the_title( $post->ID ) .'</h3>';
						echo apply_filters( 'the_content', $post->post_content );
						echo $linktext;
						?>
					</div>
					<?php
				}
				?>

			</li>


			<?php
		}
		echo '</ul>';
		?>
		<ul class="flex-control-nav"><li></li><li></li><li></li></ul>

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

add_action('wp_footer', 'powered_by_wpengine');
function powered_by_wpengine() {
	echo '<a class="powered_by" href="http://wpengine.com/?a_aid=4ec5c177f0064&a_bid=88380a39" target="_blank">Fast, secure WordPress hosting is provided by WP Engine.</a>';
}

// Move Admin Bar to bottom
add_action( 'wp_head', 'dsgnwrks_stick_admin_bar_to_bottom' );
function dsgnwrks_stick_admin_bar_to_bottom() {
	if ( is_user_logged_in() ) {
		echo '
		<style type="text/css">
		html {
		padding-bottom: 28px !important;
		}

		body {
		margin-top: -28px;
		}

		#wpadminbar {
		top: auto !important;
		bottom: 0;
		}

		#wpadminbar .quicklinks .menupop ul {
		bottom: 28px;
		}
		</style>
		';
	}
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

//hook change default gravator
add_filter( 'avatar_defaults', 'dsgnwrks_newgravatar' );
function dsgnwrks_newgravatar( $avatar_defaults ) {
	$avatar_defaults[get_stylesheet_directory_uri() .'/images/WPAdminLogo.gif'] = 'DsgnWrks';
	return $avatar_defaults;
}

add_filter( 'user_contactmethods','dsgnwrks_hide_profile_fields', 10, 1 );
function dsgnwrks_hide_profile_fields( $contactmethods ) {
	unset($contactmethods['aim']);
	unset($contactmethods['jabber']);
	unset($contactmethods['yim']);
	return $contactmethods;
}

add_filter( 'upload_mimes', 'dsgnwrks_upload_mimes' );
function dsgnwrks_upload_mimes( $existing_mimes=array() ) {

	$existing_mimes['xml'] = 'text';
	return $existing_mimes;

}