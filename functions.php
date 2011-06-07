<?php
/**
 * @package WordPress
 * @subpackage Toolbox
 */

/**
 * Make theme available for translation
 * Translations can be filed in the /languages/ directory
 * If you're building a theme based on toolbox, use a find and replace
 * to change 'toolbox' to the name of your theme in all the template files
 */
load_theme_textdomain( 'toolbox', TEMPLATEPATH . '/languages' );

$locale = get_locale();
$locale_file = TEMPLATEPATH . "/languages/$locale.php";
if ( is_readable( $locale_file ) )
	require_once( $locale_file );

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 640; /* pixels */

/**
 * This theme uses wp_nav_menu() in one location.
 */
register_nav_menus( array(
	'primary' => __( 'Primary Menu', 'toolbox' ),
) );

/**
 * Add default posts and comments RSS feed links to head
 */
add_theme_support( 'automatic-feed-links' );

/**
 * Add support for the Aside and Gallery Post Formats
 */
add_theme_support( 'post-formats', array( 'aside', 'gallery' ) );

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 */
function toolbox_page_menu_args($args) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'toolbox_page_menu_args' );

/**
 * Register widgetized area and update sidebar with default widgets
 */
function toolbox_widgets_init() {
	register_sidebar( array (
		'name' => __( 'Sidebar 1', 'toolbox' ),
		'id' => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	) );

	register_sidebar( array (
		'name' => __( 'Sidebar 2', 'toolbox' ),
		'id' => 'sidebar-2',
		'description' => __( 'An optional second sidebar area', 'toolbox' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	) );	
}
add_action( 'init', 'toolbox_widgets_init' );

/**
 * Borrowed from bp.org
 */
function teleogistic_get_plugins() {
	require_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );

	$args = array( 'author' => 'boonebgorges' );

	$plugins = plugins_api('query_plugins', $args);
	
	foreach( $plugins->plugins as $plugin_key => $plugin_value ) {
		if ( $plugins->plugins[$plugin_key]->slug == 'mingle' ) {
			unset( $plugins->plugins[$plugin_key] );
			$plugins->info['results']--;
		}
	}
	
	foreach( $plugins->plugins as $plugin_array_key => $plugin ) {
		$group_id = groups_check_group_exists( $plugin->slug );
		
		// Check to make sure this plugin has a group
		if ( !empty( $plugin->name ) && !$group_id ) {
			
			/* Get the plugin contribs. Just me on this site */
			$admin_ids = array( bp_core_get_userid( 'boonebgorges' ) );
			
			$args = array(
				'name'		=> $plugin->name,
				'creator_id'	=> $admin_ids[0],
				'description'	=> $plugin->short_description,
				'slug'		=> $plugin->slug,
				'status'	=> 'public',
				'enable_forum'	=> 1,
				'date_created'	=> gmdate( "Y-m-d H:i:s" )
			);
				
			if ( $group_id = groups_create_group( $args ) ) {
				groups_update_groupmeta( $group_id, 'type', 'plugin' );
				groups_update_groupmeta( $group_id, 'total_member_count', 1 );
			}
			
			unset( $admin_ids, $group_id, $user_id );
		} 
		
		if ( $group_id ) {
			// Get some relevant data from the WP plugins API and store in BP metadata
			// to save some hits
			
			$api = plugins_api( 'plugin_information', array( 'slug' => stripslashes( $plugin->slug ) ) );
			
			groups_update_groupmeta( $group_id, 'plugin_data', (array)$api );
		}
	}
}

function teleogistic_sort_plugin_groups( $has_groups ) {
	global $groups_template, $wpdb, $bp;
	
	$gtgroups = $groups_template->groups;
	
	$gtgroups = array_values( $gtgroups );
	
	$group_ids = array();
	foreach( $gtgroups as $group ) {
		$group_ids[] = $group->id;
	}
	$group_ids = implode( ',', $group_ids );
	
	// Now I'll get the plugin_data from groupmeta in one fell swoop
	$sql = $wpdb->prepare( "SELECT group_id, meta_value FROM {$bp->groups->table_name_groupmeta} WHERE meta_key = 'plugin_data' AND group_id IN ({$group_ids})" );
	$metas = $wpdb->get_results( $sql );
	
	// Key by group_id
	$plugin_data = array();
	foreach( $metas as $meta ) {
		$plugin_data[$meta->group_id] = maybe_unserialize( $meta->meta_value );
	}
	
	// Add the metadata to the list of groups
	foreach( $gtgroups as $gtgroup_key => $group ) {
		if ( isset( $plugin_data[$group->id] ) )
			$gtgroups[$gtgroup_key]->plugin_data = $plugin_data[$group->id];
	}
	
	uasort( $gtgroups, 'teleogistic_plugin_sorter' );
	
	$groups_template->groups = array_values( $gtgroups );
	
	return $has_groups;
}

function teleogistic_plugin_sorter( $a, $b ) {
	global $sortable;
	
	$orderby = $sortable->get_orderby;
	$order = $sortable->get_order;
	
	if ( $a == $b ) {
		return 0;
	}
	
	$avalue = isset( $a->{$orderby} ) ? $a->{$orderby} : $a->plugin_data[$orderby];
	$bvalue = isset( $b->{$orderby} ) ? $b->{$orderby} : $b->plugin_data[$orderby];
	
	if ( $order == 'asc' )
		return ( $avalue > $bvalue ) ? 1 : -1;
	else
		return ( $avalue > $bvalue ) ? -1 : 1;
}

/**
 * Borrowed from bp.org
 */
function bporg_get_plugin_info( $slug ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );

	$api = plugins_api('plugin_information', array('slug' => stripslashes( $slug ) ));

	if ( is_wp_error($api) )
		wp_die($api);

	$plugins_allowedtags = array('a' => array('href' => array(), 'title' => array(), 'target' => array()),
								'abbr' => array('title' => array()), 'acronym' => array('title' => array()),
								'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
								'div' => array(), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
								'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
								'img' => array('src' => array(), 'class' => array(), 'alt' => array()));
	//Sanitize HTML
	foreach ( (array)$api->sections as $section_name => $content )
		$api->sections[$section_name] = wp_kses($content, $plugins_allowedtags);
	foreach ( array('version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug') as $key )
		$api->$key = wp_kses($api->$key, $plugins_allowedtags);

	if ( ! empty($api->last_updated ) )
		$api->last_updated = human_time_diff( strtotime($api->last_updated) );

	$api->downloaded = number_format( $api->downloaded );

	return $api;
}

?>