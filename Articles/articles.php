<?php
/**
 * The Plugin Statred
 * 
 * @wordpress-plugin
 * 
 * Plugin Name: Open AI Articles
 * Description: It generate articles through api key of openAI and publish it on a post.
 * Version: 1.0.0
 * Author: Vkaps IT Solutions
 * Author URI: https://vkaps.com/
*/

/* if this file is called directly, abort. */

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
*/

if( ! defined( 'ABSPATH' ) ) exit;

require_once plugin_dir_path(__FILE__).'includes/scheduler.php';
require_once plugin_dir_path(__FILE__).'includes/post-scheduler.php';
require_once plugin_dir_path(__FILE__).'includes/test_file.php';

add_action( 'admin_enqueue_scripts', 'enqueue_scripts_and_styles' );
function enqueue_scripts_and_styles(){
    wp_enqueue_style('boot-css',"https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css");

    wp_enqueue_style('jquery-css',"http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css");
    wp_enqueue_style('article-css',plugin_dir_url( __FILE__ ) . 'asset/css/article.css');

    wp_enqueue_script('ajax-js',"https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js");
    wp_enqueue_script('custom-js',plugin_dir_url( __FILE__ ) . 'asset/js/custom.js');

    wp_enqueue_script( 'ajaxHandle' );
    wp_localize_script( 
        'ajaxHandle', 
        'ajax_object', 
        array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) 
    );
}

// activate hook
register_activation_hook( __FILE__, 'activate_articles' );
function activate_articles(){
	
	require_once plugin_dir_path(__FILE__).'includes/class-articles-activator.php';
    Article_Activator::activate();
	
}

// deactivate hook
register_deactivation_hook( __FILE__, 'deactivate_articles' );
function deactivate_articles(){
	   require_once plugin_dir_path(__FILE__).'includes/class-articles-deactivator.php';
	Article_Deactivator::deactivate();
}

add_action( 'admin_menu', 'all_articles' );
function all_articles(){
    require( plugin_dir_path( __FILE__ ).'includes/class-all-articles.php' );
}


add_action( "wp_ajax_publishPost", "publishPost" );
add_action( "wp_ajax_nopriv_publishPost", "publishPost" );
function publishPost(){
    global $wpdb;
    $articles_table = $wpdb->prefix.'openAi_articles';
    $keywords_table   = $wpdb->prefix.'openAi_keywords';
    $sql_query = $wpdb->get_results("SELECT * FROM $articles_table WHERE `id`=".$_POST['article_id']);



    $category_id = $sql_query[0]->keyword_id; 
    $sql_query_categories = $wpdb->get_results("SELECT `id`, `topics` FROM $keywords_table where id= ".$category_id);
   $cate_name = $sql_query_categories[0]->topics;
   if (category_exists($cate_name) ){
        $category_ids = get_cat_ID($cate_name);
   }else{
         $my_cat_id  =  wp_insert_term( $cate_name, 'category' );
         $category_ids =  $my_cat_id['term_id'];
   } 


    $users = array();
    $args  = array(
     'role' => 'author',
     'orderby' => 'display_name'
    );
    $wp_user_query = new WP_User_Query($args);
    $authors = $wp_user_query->get_results();

    foreach($authors as $currentUser){
        if(!in_array( 'author ', $currentUser->roles )){
          $users[] = $currentUser->ID;
        }
    }
    $user = array_flip($users);
    $post_author_id  = array_rand($user);
    $data = array(
        'status' => "1",
    );
    $where = array(
        'id' => $_POST['article_id']
    );
    $res_update = $wpdb->update( $articles_table, $data, $where );
    $title = $_POST['title'];
    $content = $_POST['Content'];
    $id = wp_insert_post(array(
          'post_title'=>$title, 
          'post_type'=>'post', 
          'post_content'=>$content,
          'post_author' => $post_author_id,
          'post_status'=>'publish',
          'post_category' => array($category_ids)
    ));
    if($id){
        $result['status']   = 200;
        $result['message']  = 'Publish Successfully';

    }else{
        $result['status']   = 404;
        $result['message']  = 'Something Went Wrong!! Please try again';
    }
    
    echo json_encode($result);
    exit();
}


add_shortcode('article-blog','short_code');
function short_code(){
    require( plugin_dir_path( __FILE__ ).'includes/article-blog.php' );
}
