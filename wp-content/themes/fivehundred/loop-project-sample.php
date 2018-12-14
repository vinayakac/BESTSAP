<?php
/*
Template Name: Fullwidth Project page
*/
?>

<?php get_header(); ?>
<?php
global $post;
$post = get_post( 3357 );
?>
<?php idcf_get_project(); ?>
<div id="container">
	<div id="site-description">
		<h1><?php $project_loop->idcf_project_title(); ?></h1>
	</div>
	
	<article id="content" class="fullwidth">
		<div id="project-<?php $project_loop->the_ID(); ?>">
		
		<div class="entry-content">
		<?php 
		if ( has_post_thumbnail() ) {
		the_post_thumbnail();
		} 
		?>
		<?php $project_loop->idcf_the_content(); ?>
		<?php wp_link_pages('before=<div class="page-link">' . __( 'Pages:', 'fivehundred' ) . '&after=</div>') ?>
		</div>
		</div>
		<?php comments_template( '', true ); ?>
	</article>
<div class="clear"></div>
</div>
<?php get_footer(); ?>