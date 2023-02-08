<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


add_filter( 'cron_schedules', 'check_daily' );
function check_daily( $schedules ) {
    $schedules['daily'] = array(
        'interval' => 86400,
        'display'  => __( 'Daily' ),
    );
    return $schedules;
}

// Unless an event is already scheduled, create one.
 
add_action( 'wp', 'openAi_custom_cron_job' );
 
function openAi_custom_cron_job() {
   if ( ! wp_next_scheduled( 'publish_post_daily' ) ) {
      wp_schedule_event( strtotime('6:00:00'), 'daily', 'publish_post_daily' );
     
   }
}

// Trigger publish post when hook runs
add_action( 'publish_post_daily', 'openAI_publish_article_hook' );
function openAI_publish_article_hook(){

    // if(isset($_GET['test'])){
   $message = "Post Published";
    $file = fopen("../custom_logs.log","a"); 
    fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: " . $message);
    
    global $wpdb;
    $articles_table = $wpdb->prefix.'openAi_articles';
    $keywords_table   = $wpdb->prefix.'openAi_keywords';
    $number_of_post = rand(50,100);
    $sql_query = $wpdb->get_results("SELECT * FROM $articles_table  ORDER BY rand() LIMIT $number_of_post");
    foreach ($sql_query as $result){
        $id = $result->id;
        $title = $result->title;
        $content = $result->article;
        $status = $result->status;
        $category_id = $result->keyword_id;
        
        $sql_query_categories = $wpdb->get_results("SELECT `id`, `topics` FROM $keywords_table where id= ".$category_id);
        $cate_name = $sql_query_categories[0]->topics;
        
        //If it doesn't exist create new category
        if (get_cat_ID($cate_name)){
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

        if($status == 0){  
            
            $my_post = wp_insert_post(array(
                'post_type' => 'post',
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_author' => $post_author_id,
                'post_category' => array($category_ids)
            ));  

            if($my_post){
                $sql_update = $wpdb->query("UPDATE $articles_table SET status='1' WHERE id = '$id'");
                echo '<div class="alert alert-success publish">Post Publish successfully</div>';
                echo '<script>$(".publish").fadeOut(10000)</script>';
            }

        }else{

            echo '<div class="alert alert-danger not-publish">Post already published</div>';
            echo '<script>$(".not-publish").fadeOut(10000)</script>';
        }
        
    }    
}

