<?php 
	add_filter( 'cron_schedules', 'cron_schedules'  );
	function cron_schedules( $schedules ) {
	    $schedules['monthly_email'] = array(
	        'interval' => 2635200,
	        'display'  => __( 'Once a month' , 'text-domain'),
	    );
	    return $schedules;
	}

	function custom_cron_job_mail() {
	    if ( ! wp_next_scheduled( 'send_email' ) ) {
	        wp_schedule_event( strtotime('third day of every month at 4:55:00PM'), 'monthly_email', 'send_email' );
	    }
	}
	add_action( 'wp', 'custom_cron_job_mail' );
	add_action( 'send_mail', 'mail_function');
	function mail_function(){
	    // $mail = wp_mail( 'aashitadarda@gmail.com', 'Testing Scheduler', 'Next Mail will come next month' );
	    // if($mail){
	    //     echo 'Mail gone';
	    // }
	    // else{
	    //     echo 'Mail not gone';
	    // }

	    $args = array(
	    	'post_type' => 'post',
	    	'post_title' => 'Testing Purpose',
	    	'post_content' => 'Testing the post gone in schedule timing or not',
	    	'post_status' => 'publish'
	    );

	    $wp_post = wp_insert_post($args);
	    if($wp_post){
	    	echo 'Post Published';
	    }else{
	    	echo 'Post not Published';
	    }
	}

?>