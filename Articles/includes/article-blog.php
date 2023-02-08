<?php
get_header();
$args = array(
	'posts_per_page'=>25,
	'post_type'=>'post',
	'status'=>'publish',
	'paged'=>get_query_var('paged') ? get_query_var('paged') : 1
);

$the_blog_query = new WP_Query($args);
?>
<div class="container article-blog">
	<?php 
	while ($the_blog_query -> have_posts()) : $the_blog_query -> the_post(); 
	?>
		<div class="blog-class">
			<a href="<?php the_permalink(); ?>" class="article_title" target="_blank">
	         <h4 class="mx-5"><?php echo get_the_title(); ?></h4>
	     </a>
	     <p>
	     	<?php 
	     	the_excerpt(); 
	     	the_author();
	     	?>
	     </p>
		</div>
	<?php
	endwhile;
	?> 
</div>
<?php
$big = 999999999; // need an unlikely integer
 echo paginate_links( array(
    'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
    'format' => '?paged=%#%',
    'current' => max( 1, get_query_var('paged') ),
    'total' => $the_blog_query->max_num_pages
) );
wp_reset_postdata();
?>
<!-- <style type="text/css">
	.container.article-blog {
	    padding-top: 2rem;
	}
	.article_title{
		display: inline-block;
		color: blue;
	}
	.blog-class{
		background: linear-gradient(87.83deg,#02044e,#4e00b1 100.96%);
		width: 100%;
		max-width: 75%;
		margin: auto;
		border-radius: 8px;
		padding: 1rem;
		margin-bottom: 1rem;
	}
	.blog-class a.article_title h4 {

	    margin: 0;
	    padding: 0;
	    display: block;
	    color: #fff;
	    text-decoration: none;
	    font-weight: 600;
	}

	.blog-class p ,.blog-class span {
	    color: #fff;
	    font-size: 16px;
	    font-weight: 400;
	}
	.blog-class p.mx-2 {
	    display: none;
	}
	.article_title{
		text-decoration: none;
	}
</style> -->