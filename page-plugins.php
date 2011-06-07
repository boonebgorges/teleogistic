<?php

/*
Template Name: Plugins
*/

/**
 * @package WordPress
 * @subpackage Toolbox
 */

global $groups_template;

// Get the plugins from the repo
$plugins = teleogistic_get_plugins();
$group_ids = array();

foreach( $plugins->plugins as $plugin_array_key => $plugin ) {
	// Check to make sure this plugin has a group
	if ( !empty( $plugin->name ) && !$group_id = groups_check_group_exists( $plugin->slug ) ) {
		
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
		$plugins->plugins[$plugin_array_key]->group_id = $group_id;
		$group_ids[] = $group_id;
	}
}

// Load the sortable and pagination helpers
require_once( get_stylesheet_directory() . '/lib/boones-sortable-columns.php' );
$cols = array(
	array(
		'name'		=> 'name',
		'title'		=> 'Plugin Name',
		'is_default' 	=> true
	),
	array(
		'name'		=> 'version',
		'title'		=> 'Version',
		'is_sortable' 	=> false
	),
	array(
		'name'		=> 'description',
		'title'		=> 'Description',
		'is_sortable' 	=> false
	),
	array(
		'name'		=> 'rating',
		'title'		=> 'Average Rating',
		'default_order'	=> 'desc'
	),
	array(
		'name'		=> 'num_ratings',
		'title'		=> 'Number of Ratings',
		'default_order' => 'desc'
	)	
);
$sortable = new BBG_CPT_Sort( $cols );

require_once( get_stylesheet_directory() . '/lib/boones-pagination.php' );
$pagination = new BBG_CPT_Pag();

get_header(); ?>
		<div id="primary">
			<div id="content" role="main">
				<?php the_post(); ?>

				<?php get_template_part( 'content', 'page' ); ?>
				
				<?php if ( bp_has_groups( array( 'include' => $group_ids ) ) ) : ?>
					<?php teleogistic_sort_plugin_groups( $plugins ) ?>
				
					<table class="widefat">
					
					<thead>
					<?php if ( $sortable->have_columns() ) : ?>
						<?php while ( $sortable->have_columns() ) : $sortable->the_column() ?>
						
						<?php $sortable->the_column_th() ?>
						
						<?php endwhile ?>
					<?php endif ?>
					</thead>
					
					<tbody>
					<?php while ( bp_groups() ) : bp_the_group() ?>
						<tr>
							<td class="name">
								<a href="<?php bp_group_permalink() ?>"><?php bp_group_name() ?></a>
							</td>
						
							<td class="version">
								<?php echo $groups_template->group->plugin_data->version ?>
							</td>
						
							<td class="description">
								<?php bp_group_description() ?>
							</td>
							
							<td class="rating">
								<?php echo $groups_template->group->plugin_data->rating ?>
							</td>
							
							<td class="num_ratings">
								<?php echo $groups_template->group->plugin_data->num_ratings ?>
							</td>
						
						</tr>
					<?php endwhile ?>
					</tbody>
					
					</table>
				<?php endif ?>
				
				<?php comments_template( '', true ); ?>

			</div><!-- #content -->
		</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
