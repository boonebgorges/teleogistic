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
//$plugins = teleogistic_get_plugins();
//$group_ids = array();

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
		'name'		=> 'last_updated',
		'title'		=> 'Last Updated',
		'default_order'	=> 'desc'
	),
	array(
		'name'		=> 'description',
		'title'		=> 'Description',
		'is_sortable' 	=> false
	),
	array(
		'name'		=> 'rating',
		'title'		=> 'Rating',
		'default_order'	=> 'desc'
	),
	array(
		'name'		=> 'num_ratings',
		'title'		=> '# Ratings',
		'default_order' => 'desc'
	),
	array(
		'name'		=> 'downloaded',
		'title'		=> 'Downloads',
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
		
		<?php add_filter( 'bp_has_groups', 'teleogistic_sort_plugin_groups' ) ?>
		<?php if ( bp_has_groups() ) : ?>
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
						<?php echo $groups_template->group->plugin_data['version'] ?>
					</td>
					
					<td class="last_updated">
						<?php echo $groups_template->group->plugin_data['last_updated'] ?>
					</td>
				
					<td class="description">
						<?php bp_group_description() ?>
					</td>
					
					<td class="rating">
						<?php echo $groups_template->group->plugin_data['rating'] ?>
					</td>
					
					<td class="num_ratings">
						<?php echo $groups_template->group->plugin_data['num_ratings'] ?>
					</td>
					
					<td class="downloaded">
						<?php echo number_format( $groups_template->group->plugin_data['downloaded'] ) ?>
					</td>
				
				</tr>
			<?php endwhile ?>
			</tbody>
			
			</table>
		<?php endif ?>
		
		<?php remove_filter( 'bp_has_groups', 'teleogistic_sort_plugin_groups' ) ?>
		
		<?php comments_template( '', true ); ?>

	</div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
