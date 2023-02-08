<?php
class Article_Activator {
	public static function activate(){
		global $wpdb;
		$articles_table = $wpdb->prefix.'openAi_articles';
		$keywords_table	 = $wpdb->prefix.'openAi_keywords';
		if($wpdb->get_var( "show tables like '$keywords_table'" )!= $keywords_table){
			$sql_keyword = "CREATE TABLE $keywords_table (
					`id` INT(11) NOT NULL AUTO_INCREMENT , 
					`topics` VARCHAR(255) NOT NULL , 
					PRIMARY KEY (`id`)
			) ENGINE = InnoDB;";
			$wpdb->query($sql_keyword);

        	$keyword_Array = ['online slots','casino','gambling games','bingo','casino industry growth','bitcoin and crypto casinos','casino bonuses and free offers','online poker','sport betting','horse racing','gambling history','the future of online betting','the future of land based casinos','online roulette','blackjack','lotteries','pachinko'];

        	foreach ($keyword_Array as $key => $value) {
        		$wpdb->insert( $keywords_table , array( 'id' => $key+1, 'topics' => $value ), array( '%d', '%s' ) );
        	}
		}

		
		if($wpdb->get_var( "show tables like '$articles_table'" )!= $articles_table){
			$sql_article = "CREATE TABLE $articles_table (
					`id` INT(11) NOT NULL AUTO_INCREMENT , 
					`title` VARCHAR(255) NOT NULL , 
					`article` TEXT(5000) NOT NULL ,
					`keyword_id` INT(11) NOT NULL,
					`status` BOOLEAN NOT NULL,
					PRIMARY KEY (`id`),
					FOREIGN KEY (`keyword_id`) REFERENCES $keywords_table(`id`)
					-- FOREIGN KEY(`keyword`) REFERENCE `$keywords`(`id`)
			) ENGINE = InnoDB;";
			$wpdb->query($sql_article);
		}
	}
}