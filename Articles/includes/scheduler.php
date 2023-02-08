<?php
use Orhanerday\OpenAi\OpenAi;
require_once plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';
add_filter( 'cron_schedules', 'check_every_1_min' );
 function check_every_1_min( $schedules ) {
    $schedules['every_one_min'] = array(
        'interval' => 60,
        'display'  => __( 'Every 1 min' ),
    );
    return $schedules;
}

// Unless an event is already scheduled, create one.
 
add_action( 'wp', 'generate_article_cron_job' );
 
function generate_article_cron_job() {
   if ( ! wp_next_scheduled( 'generate_article_one_min' ) ) {
      wp_schedule_event( time(), 'every_one_min', 'generate_article_one_min' );
   }
}

// Trigger email when hook runs
add_action( 'generate_article_one_min', 'openAI_generate_article_hook' );
function openAI_generate_article_hook(){
      
   $message = "Article Generated";
   $file = fopen("../custom_logs.log","a"); 
   fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: " . $message);
   
   $checked_status=get_option('status_api');
   if($checked_status == 'activated'){
      $open_ai_key = get_option('api_key');
      if($open_ai_key !== ''){
         $open_ai = new OpenAi($open_ai_key);
         global $wpdb;
         $articles_table = $wpdb->prefix.'openAi_articles';
         $keywords_table    = $wpdb->prefix.'openAi_keywords';
         $sql_keyword="SELECT * from $keywords_table";
         $arr_keyword=$wpdb->get_results($sql_keyword);
         if (isset($_REQUEST['b_id'])) {
            $a_key_id = $_REQUEST['b_id'];
            $sql_keyword2="SELECT * from $keywords_table WHERE id=$a_key_id";
            $arr_keyword2=$wpdb->get_results($sql_keyword2);
            $key_id=$arr_keyword2[0]->id;
            $key_name=$arr_keyword2[0]->topics;
            $sql_count="SELECT COUNT(keyword_id) as count FROM $articles_table WHERE keyword_id=$key_id";
            $arr_count=$wpdb->get_results($sql_count);
            $count=$arr_count[0]->count;
            if ($count <= 249) {
               $count_row=0;
               for($j=$count;$j <= 249;$j++){ 
                  $res = $open_ai->completion([
                  'model' => 'text-davinci-003',
                  'prompt' =>'Generate a title related to '.$key_name,
                  'temperature' => 0.3,
                  'max_tokens' => 150,
                  'frequency_penalty' => 0,
                  'presence_penalty' => 0,
                  'best_of'=>1
                  ]);
                  $res_title=json_decode($res,true);
                  $arr_title=$res_title["choices"][0]["text"];
                  $title_str=str_replace('"','',$arr_title);
                  $sql1="SELECT * from $articles_table where title ='$title_str'";
                  $title=$wpdb->get_results($sql1);
                  if (empty($title)) {
                     $complete = $open_ai->completion([
                     'model' => 'text-davinci-003',
                     'prompt' =>'Generate an article related to '.$title_str,
                     'temperature' => 0.7,
                     'max_tokens' => 4000,
                     'frequency_penalty' => 0.5,
                     'presence_penalty' => 0.6,
                     'best_of'=>1
                     ]);
                     $output=json_decode($complete,true);
                     $article_genrating=$output["choices"][0]["text"];
                     $artical=$wpdb->_real_escape($article_genrating);
                     $title_string=$wpdb->_real_escape($title_str);
                     
                     if ( $artical != '' && $title_string != '' ) {
                        
                        $sql2="INSERT into $articles_table (title,article,keyword_id) VALUES ('$title_string','$key_id')";
                        if($wpdb->query($sql2)) {
                           $count_row++;
                           echo  $count_row. " Row inserted successfully"."<br>";
                        }
                        else {
                           echo "No record inserted";
                        }   
                     }           
                  }    
               }
            }
            else{
               echo 'Your 250 article is generated for this topics.';
            }   
         } 
         else {
            foreach ($arr_keyword as $key => $data) {
               $keyword_id=$data->id;
               $keyword_name=$data->topics;
               $sql_count="SELECT COUNT(keyword_id) as count FROM $articles_table WHERE keyword_id=$keyword_id";
               $arr_count=$wpdb->get_results($sql_count);
               // print_r($arr_count);die('end');
               $count=$arr_count[0]->count;
               if ($count <= 249) {
                  $count_row=0;
                  for($j=$count;$j <= 249;$j++){ 
                     $res = $open_ai->completion([
                     'model' => 'text-davinci-003',
                     'prompt' =>'Generate a title related to '.$keyword_name,
                     'temperature' => 0.3,
                     'max_tokens' => 150,
                     'frequency_penalty' => 0,
                     'presence_penalty' => 0,
                     'best_of'=>1
                     ]);
                     $res_title=json_decode($res,true);
                     $arr_title=$res_title["choices"][0]["text"];
                     $title_str=str_replace('"','',$arr_title);
                     $sql1="SELECT * from $articles_table where title ='$title_str'";             
                     $title=$wpdb->get_results($sql1);
                     if (empty($title)) {
                        $complete = $open_ai->completion([
                        'model' => 'text-davinci-003',
                        'prompt' =>'Generate an article related to '.$title_str,
                        'temperature' => 0.7,
                        'max_tokens' => 4000,
                        'frequency_penalty' => 0.5,
                        'presence_penalty' => 0.6,
                        'best_of'=>1
                        ]);
                        $output=json_decode($complete,true);                   
                        $article_genrating=$output["choices"][0]["text"];
                        $artical=$wpdb->_real_escape($article_genrating);
                        $title_string=$wpdb->_real_escape($title_str);
                        if ( $artical != '' && $title_string != '' ) {
                           $sql2="INSERT into $articles_table (title,article,keyword_id) VALUES ('$title_string','$artical','$keyword_id')";
                           if($wpdb->query($sql2)) {
                              $count_row++;
                              echo  $count_row. " Row inserted successfully"."<br>";
                           } 
                           else {
                              echo "No record inserted";
                           } 
                        }     
                     }    
                  }
               }
            }
         }
      }
      else{
         echo '<h2 class="text-center my-5 py-5">Generate your API Key at openAI Articles submenu->Settings->API Key Setting</h2>';
         echo '<h3>Then, Please Activate your toggle button for generating articles</h3>';
      }
   }
   else{
      echo '<h3>Please Activate your toggle button for generating articles</h3>';
   }
}
