<?php
/**
 * Initialization and wp-admin integration for the Gutenberg editor plugin.
 *
 * @package gutenberg
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Silence is golden.' );
}

/**
 * Renders a partial page of metaboxes.
 */
function gutenberg_metabox_partial_page() {
	/**
	 * The metabox param as long as it is set on the wp-admin/post.php request
	 * will trigger this API.
	 *
	 * Essentially all that happens is we try to load in the scripts from admin_head
	 * and admin_footer to mimic the assets for a typical post.php.
	 *
	 * @in_the_future Hopefully the metabox param can be changed to a location,
	 * or contenxt, so that we can use this API to render metaboxes that appear,
	 * in the sidebar vs. regular content, or core metaboxes vs others. For now
	 * a request like http://local.wordpress.dev/wp-admin/post.php?post=40007&action=edit&metabox=taco
	 * works just fine! Will only work on existing posts so far. Need to handle
	 * this differently for new posts.
	 */
	if ( isset( $_REQUEST['metabox'] ) && 'post.php' === $GLOBALS['pagenow'] ) {
		global $post, $wp_meta_boxes, $hook_suffix, $current_screen, $wp_locale;

		/* Scripts and styles that metaboxes can potentially be using */
		wp_enqueue_style( 'common' );
		wp_enqueue_style( 'buttons' );
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'ie' );

		// Loads edit.css from admin, which is not registered anywhere from what I can tell.
		wp_enqueue_style( 'edit-stuff', get_admin_url( null, 'css/edit.css' ) );

		wp_enqueue_script( 'utils' );
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'svg-painter' );

		wp_enqueue_style(
			'metabox-gutenberg',
			gutenberg_url( 'assets/css/metabox.css' ),
			array(),
			filemtime( gutenberg_dir_path() . 'assets/css/metabox.css' )
		);

		wp_enqueue_script(
			'metabox-resize-gutenberg',
			gutenberg_url( 'assets/js/iframeResizer.contentWindow.js' ),
			array(),
			filemtime( gutenberg_dir_path() . 'assets/js/iframeResizer.contentWindow.js' ),
			true
		);

		// Grab the admin body class.
		$admin_body_class = preg_replace( '/[^a-z0-9_-]+/i', '-', $hook_suffix );

		?>
		<!-- Add an html class so that scroll bars can be removed in css and make it appear as though the iframe is one with Gutenberg. -->
		<html id="gutenberg-metabox-html" class="gutenberg-metabox-html sidebar-open">
		<head>
		<!-- Add in JavaScript variables that some meta box plugins make use of. -->
		<script type="text/javascript">
		addLoadEvent = function( func ){ if( typeof jQuery!="undefined" )jQuery( document ).ready( func );else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
			pagenow = '<?php echo $current_screen->id; ?>',
			typenow = '<?php echo $current_screen->post_type; ?>',
			adminpage = '<?php echo $admin_body_class; ?>',
			thousandsSeparator = '<?php echo addslashes( $wp_locale->number_format['thousands_sep'] ); ?>',
			decimalPoint = '<?php echo addslashes( $wp_locale->number_format['decimal_point'] ); ?>',
			isRtl = <?php echo (int) is_rtl(); ?>;
		</script>
		<script>
			function resizeIframe( obj ) {
				obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
			}
		</script>
		<meta name="viewport" content="width=device-width,initial-scale=1.0">
		<?php

		/**
		 * Enqueue scripts for all admin pages.
		 *
		 * @since 2.8.0
		 *
		 * @param string $hook_suffix The current admin page.
		 */
		do_action( 'admin_enqueue_scripts', $hook_suffix );

		/**
		 * Fires when styles are printed for a specific admin page based on $hook_suffix.
		 *
		 * @since 2.6.0
		 */
		do_action( "admin_print_styles-{$hook_suffix}" );

		/**
		 * Fires when styles are printed for all admin pages.
		 *
		 * @since 2.6.0
		 */
		do_action( 'admin_print_styles' );

		/**
		 * Fires when scripts are printed for a specific admin page based on $hook_suffix.
		 *
		 * @since 2.1.0
		 */
		do_action( "admin_print_scripts-{$hook_suffix}" );

		/**
		 * Fires when scripts are printed for all admin pages.
		 *
		 * @since 2.1.0
		 */
		do_action( 'admin_print_scripts' );

		/**
		 * Fires in head section for a specific admin page.
		 *
		 * The dynamic portion of the hook, `$hook_suffix`, refers to the hook suffix
		 * for the admin page.
		 *
		 * @since 2.1.0
		 */
		do_action( "admin_head-{$hook_suffix}" );

		/**
		 * Fires in head section for all admin pages.
		 *
		 * @since 2.1.0
		 */
		do_action( 'admin_head' );

		/**
		 * The main way post.php sets body class.
		 */
		if ( get_user_setting( 'mfold' ) == 'f' ) {
			$admin_body_class .= ' folded';
		}

		if ( ! get_user_setting( 'unfold' ) ) {
			$admin_body_class .= ' auto-fold';
		}

		if ( is_admin_bar_showing() ) {
			$admin_body_class .= ' admin-bar';
		}

		if ( is_rtl() ) {
			$admin_body_class .= ' rtl';
		}

		if ( $current_screen->post_type ) {
			$admin_body_class .= ' post-type-' . $current_screen->post_type;
		}

		if ( $current_screen->taxonomy ) {
			$admin_body_class .= ' taxonomy-' . $current_screen->taxonomy;
		}

		$admin_body_class .= ' branch-' . str_replace( array( '.', ',' ), '-', floatval( get_bloginfo( 'version' ) ) );
		$admin_body_class .= ' version-' . str_replace( '.', '-', preg_replace( '/^([.0-9]+).*/', '$1', get_bloginfo( 'version' ) ) );
		$admin_body_class .= ' admin-color-' . sanitize_html_class( get_user_option( 'admin_color' ), 'fresh' );
		$admin_body_class .= ' locale-' . sanitize_html_class( strtolower( str_replace( '_', '-', get_user_locale() ) ) );

		if ( wp_is_mobile() ) {
			$admin_body_class .= ' mobile';
		}

		if ( is_multisite() ) {
			$admin_body_class .= ' multisite';
		}

		if ( is_network_admin() ) {
			$admin_body_class .= ' network-admin';
		}

		$admin_body_class .= ' no-customize-support no-svg';

		?>
		</head>

		<?php
		/**
		 * Filters the CSS classes for the body tag in the admin.
		 *
		 * This filter differs from the {@see 'post_class'} and {@see 'body_class'} filters
		 * in two important ways:
		 *
		 * 1. `$classes` is a space-separated string of class names instead of an array.
		 * 2. Not all core admin classes are filterable, notably: wp-admin, wp-core-ui,
		 *    and no-js cannot be removed.
		 *
		 * @since 2.3.0
		 *
		 * @param string $classes Space-separated list of CSS classes.
		 */
		$admin_body_classes = apply_filters( 'admin_body_class', '' );

		// This page should always match up with the edit action.
		$action = 'edit';

		?>
		<body class="wp-admin wp-core-ui no-js <?php echo $admin_body_classes . ' ' . $admin_body_class; ?>">
		<script type="text/javascript">
			document.body.className = document.body.className.replace('no-js','js');
		</script>
		<?php
		$notice = false;
		$form_extra = '';
		if ( 'auto-draft' === $post->post_status ) {
			if ( 'edit' === $action ) {
				$post->post_title = '';
			}
			$autosave = false;
			$form_extra .= "<input type='hidden' id='auto_draft' name='auto_draft' value='1' />";
		} else {
			$autosave = wp_get_post_autosave( $post->id );
		}

		$form_action = 'editpost';
		$nonce_action = 'update-post_' . $post->ID;
		$form_extra .= "<input type='hidden' id='post_ID' name='post_ID' value='" . esc_attr( $post->ID ) . "' />";
		?>
		<form name="post" action="post.php" method="post" id="post"
		<?php
		/**
		 * Fires inside the post editor form tag.
		 *
		 * @since 3.0.0
		 *
		 * @param WP_Post $post Post object.
		 */
		do_action( 'post_edit_form_tag', $post );

		$referer = wp_get_referer();
		?>
		><!-- End of Post Form Tag. -->
		<?php wp_nonce_field( $nonce_action ); ?>
		<?php
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
		?>
		<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_id; ?>" />
		<input type="hidden" id="hiddenaction" name="action" value="<?php echo esc_attr( $form_action ); ?>" />
		<input type="hidden" id="originalaction" name="originalaction" value="<?php echo esc_attr( $form_action ); ?>" />
		<input type="hidden" id="post_author" name="post_author" value="<?php echo esc_attr( $post->post_author ); ?>" />
		<input type="hidden" id="post_type" name="post_type" value="<?php echo esc_attr( $post->post_type ); ?>" />
		<input type="hidden" id="original_post_status" name="original_post_status" value="<?php echo esc_attr( $post->post_status ); ?>" />
		<input type="hidden" id="referredby" name="referredby" value="<?php echo $referer ? esc_url( $referer ) : ''; ?>" />
		<!-- This field is not part of the standard post form and is used to signify this is a gutenberg metabox. -->
		<input type="hidden" name="gutenberg_metaboxes" value="gutenberg_metaboxes" />
		<?php if ( ! empty( $active_post_lock ) ) : ?>
		<input type="hidden" id="active_post_lock" value="<?php echo esc_attr( implode( ':', $active_post_lock ) ); ?>" />
		<?php endif; ?>

		<?php
		if ( 'draft' !== get_post_status( $post ) ) {
			wp_original_referer_field( true, 'previous' );
		}

		echo $form_extra;

		wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
		wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

		// Permalink title nonce.
		wp_nonce_field( 'samplepermalink', 'samplepermalinknonce', false );

		/**
		 * Fires at the beginning of the edit form.
		 *
		 * At this point, the required hidden fields and nonces have already been output.
		 *
		 * @since 3.7.0
		 *
		 * @param WP_Post $post Post object.
		 */
		do_action( 'edit_form_top', $post );

		/**
		 * Rendering the metaboxes.
		 */

		// Styles.
		$heading_style = 'display:flex;justify-content:space-between;align-items:center;font-size:14px;margin:0;line-height: 50px;height: 50px;padding: 0 15px;background-color: #eee;border-top: 1px solid #e4e2e7;border-bottom: 1px solid #e4e2e7;box-sizing: border-box;';

		/**
		 * The #poststuff id selector is import for styles and scripts.
		 *
		 * It would be interesting to do a svn blame for that one. #blamenacin.
		 * The sidebar-open class is used to work in tandem with the sidebar
		 * opening and closing in the block editor. By default it is open.
		 */
		?>
		<header class="gutenberg-metaboxes__header" style="<?php echo $heading_style; ?>">
			<h2 style="font-size: 14px;">Extended Settings</h2>
			<!-- @TODO leaving this commented out as it may need to be used. -->
			<!--<input name="save" type="submit" class="button button-primary button-large" id="publish" value="Update Settings">-->
		</header>
		<div id="poststuff" class="sidebar-open">
			<div class="gutenberg-metaboxes">
				<div id="postbox-container-2" class="postbox-container">
		<?php
		$locations = array( 'normal', 'advanced', 'side' );
		foreach ( $locations as $location ) {
			do_meta_boxes(
				null,
				$location,
				$post
			);
		}
		?>
		<!-- Don't ask why this works, but for some reason do_meta_boxes() will output closing div tags, but still needs this one. -->
		</div>
		<?php

		/**
		 * Prints scripts or data before the default footer scripts.
		 *
		 * @since 1.2.0
		 *
		 * @param string $data The data to print.
		 */
		do_action( 'admin_footer', '' );

		/**
		 * Prints scripts and data queued for the footer.
		 *
		 * The dynamic portion of the hook name, `$hook_suffix`,
		 * refers to the global hook suffix of the current page.
		 *
		 * @since 4.6.0
		 */
		do_action( "admin_print_footer_scripts-{$hook_suffix}" );

		/**
		 * Prints any scripts and data queued for the footer.
		 *
		 * @since 2.8.0
		 *
		 * @note This seems to be where most styles etc are hooked into.
		 */
		do_action( 'admin_print_footer_scripts' );

		/**
		 * Prints scripts or data after the default footer scripts.
		 *
		 * The dynamic portion of the hook name, `$hook_suffix`,
		 * refers to the global hook suffix of the current page.
		 *
		 * @since 2.8.0
		 */
		do_action( "admin_footer-{$hook_suffix}" );

		// get_site_option() won't exist when auto upgrading from <= 2.7.
		if ( function_exists( 'get_site_option' ) ) {
			if ( false === get_site_option( 'can_compress_scripts' ) ) {
				compression_test();
			}
		}

		?>
			<div class="clear"></div></div><!-- wpwrap -->
			<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
		</body>
		</html>

		<?php
		remove_all_actions( 'shutdown' );
		exit();

		/**
		 * Shutdown hooks potentially firing.
		 *
		 * Try Query Monitor plugin to make sure the output isn't janky.
		 */
	}
}

add_action( 'do_meta_boxes', 'gutenberg_metabox_partial_page' );

/**
 * Allows the metabox endpoint to correctly redirect to the metabox endpoint
 * when a post is saved.
 *
 * @param string $location The location of the metabox, 'side', 'normal'.
 * @param int    $post_id  Post ID.
 *
 * @hooked redirect_post_location priority 10
 */
function gutenberg_metabox_save_redirect( $location, $post_id ) {
	if ( isset( $_REQUEST['gutenberg_metaboxes'] ) && 'gutenberg_metaboxes' === $_REQUEST['gutenberg_metaboxes'] ) {
		$location = add_query_arg( 'metabox', 'taco', $location );
	}

	return $location;
}

add_filter( 'redirect_post_location', 'gutenberg_metabox_save_redirect', 10, 2 );

?>
