<?php
require_once plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';


add_menu_page( '', 'OpenAI Articles', '', 'menu-article', '', 'dashicons-welcome-write-blog', 10 );

add_submenu_page('menu-article','Generate All Articles','Generate All Articles','manage_options','add-data-article','add_data_articles');

add_submenu_page('menu-article','All keyword List','All keyword List','manage_options','keywords-list','all_keyword');

add_submenu_page('menu-article','Settings','Settings','manage_options','settings_api_key','setting');

add_submenu_page('menu-article','Publish Daily Post','Publish Daily Post','manage_options','publish-post','publish_post');

add_submenu_page('','Add Keyword','Add Keyword','manage_options','add-keyword','add_keyword');
add_submenu_page('','','Delete Keyword','manage_options','delete-keyword','delete_keyword');

add_submenu_page('','Publish Manually Post','Keyword Based Article','manage_options','keyword-article','keyword_article');
add_submenu_page('menu-article','monthly schedule','monthly schedule','manage_options','monthly-schedule','monthly_schedule');


function monthly_schedule(){
	do_action("send_mail");
}
function setting(){
	require( plugin_dir_path( __FILE__ ).'settings-api-key.php' );
}
//POST PUBLISHED
function publish_post(){
	do_action("publish_post_one_min");
}

//ARTICLE IS GENERATING THROUGH AI
function add_data_articles(){
	do_action("generate_article_one_min");
}

//LIST SHOWING OF ALL KEYWORDS ON WHICH ARTICLES WILL BE GENERATED

function all_keyword(){
	echo '<h2>All Keywords List is here</h2>';
	global $wpdb;
	$articles_table = $wpdb->prefix.'openAi_articles';
	$keywords_table	 = $wpdb->prefix.'openAi_keywords';	
?>
	<div class="file mx-5">
	    <a href="<?php echo 'admin.php?page=add-keyword'; ?>">
	        <button id="btn" class="btn btn-success mb-3">
	            Add New Keyword
	        </button>
	    </a>
    	<table class="table table-bordered">
    		<thead>
    			<tr class="file">
            <th>S.No.</th>
            <th>Keyword Name</th>
            <th>Total Articles</th>
            <th>Generate Articles (Manually)</th> 
            <th>Action</th>
					</tr>
    		</thead>
		    <tbody>
		    <?php 
	        	$sql_show = $wpdb->get_results("SELECT * FROM `$keywords_table` ORDER BY id DESC");
	        	$i=1;
		        foreach($sql_show as $display){ 
		        	$keywordName = $display->topics;
		        	$keyword_id = $display->id;
		        	$sql_show_count = $wpdb->get_results("SELECT COUNT(a.keyword_id) as size FROM `$keywords_table` AS k INNER JOIN `$articles_table` AS a  on k.id=a.keyword_id WHERE topics='$keywordName';");
		        	$size_article = $sql_show_count[0]->size;
		        	if($size_article == 0){

		        		$current_page_generate = admin_url("admin.php?page=add-data-article&b_id=" . $display->id); 
		        		$delete_page_generate = admin_url("admin.php?page=delete-keyword&key_id_del=" . $display->id); 
		    ?>
			        <tr>
								<td class="file-id">
								    <?php echo $i; ?>
								</td>
								<td class="decoration-keyword">
									<a href="<?php echo 'admin.php?page=keyword-article&k_id=' . $display->id; ?>">
								  		<h5 class="keyword"><?php echo $display->topics; ?></h5>
									</a>
								</td>
								<td class="file-description">
									<?php echo $size_article; ?>
								</td>
								<td>
								    <a href="<?php echo $current_page_generate; ?>" class='mx-1'>
								        <button class="btn btn-primary btn-sm">Generate Article</button>  
								    </a>
								</td>
								<td>
								    <a href="<?php echo $delete_page_generate ; ?>" class='mx-1'>
								        <button onclick='DeleteFunction()' class="btn btn-secondary btn-sm">Delete Keyword</button>  
								    </a>
								</td>
						</tr>
			<?php 		$i++; 
					} else {
						$sql_display = $wpdb->get_results("SELECT *,COUNT(a.keyword_id) as size FROM `$keywords_table` AS k INNER JOIN `$articles_table` AS a  on k.id=a.keyword_id WHERE k.id = '$keyword_id' ;");
		    			foreach($sql_display as $result){
		    				$current_page_generate = admin_url("admin.php?page=add-data-article&b_id=" . $result->keyword_id);
		    				$delete_page_generate = admin_url("admin.php?page=delete-keyword&key_id_del=" . $result->keyword_id); 
    		?>					
			        		<tr>
										<td class="file-id">
										    <?php
										    	echo $i;
										    ?>
										</td>
										<td class="decoration-keyword">
										<a href="<?php echo 'admin.php?page=keyword-article&k_id=' . $result->keyword_id; ?>">
									  		<h5 class="keyword"><?php echo $result->topics; ?></h5>
										</a>
										</td>
										<td class="file-description">
											<?php echo $result->size; ?>
										</td>
										<td>
										    <a href="<?php echo $current_page_generate;?>" class='mx-1'>
										        <button class="btn btn-primary btn-sm">Generate Article</button>  
										    </a>
										</td>
										<td>
									    <a href="<?php echo $delete_page_generate ; ?>" class='mx-1'>
									        <button onclick='DeleteFunction()' class="btn btn-secondary btn-sm">Delete Keyword</button>  
									    </a>
										</td>
									</tr>
			<?php 			$i++; 
						} 
					}
				}
			?>
		    </tbody>
	    </table> 		      
	</div>
	<script>
			function DeleteFunction(){

				if (confirm("Are you sure you want to DELETE this Post!!!") == true) {
				} 
				else {
					event.preventDefault();
				}
			}
	</script>
<?php
}
//ADDING NEW KEYWORD

function add_keyword(){
	global $wpdb;
	$keywords_table	 = $wpdb->prefix.'openAi_keywords';
	$sql = $wpdb->get_results("SELECT * FROM $keywords_table");	
	echo '<h1> Add Keywords </h1><hr>';
?>
	<form method="post" class="my-5" id="keyword_add_list" name="keyword_add_list" enctype="multipart/form-data" novalidate style="border: black solid 2px; margin-left: 200px; margin-right: 200px;">
		<div class="d-flex justify-content-center">
			<div class="text-center my-3 pt-3">
				<input class="text-center " type="text" name="topics" id="topics" placeholder="Add Keyword " style="border-right: none; border-left: none; border-top: none; width: 100%;">
			</div>
		</div>
		<div class="text-center">
			<input class="btn btn-primary" type="submit" id="add_keywords" name="add_keywords" value="Submit">
		</div>
		<br>
	</form>
	<script src="https://code.jquery.com/jquery-3.6.1.js"></script>	
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>	
	<script>
		jQuery("#keyword_add_list").validate({
			rules:{
				topics:{
					required:true,
					minlength:3
				}
			},
			messages:{
				topics:{
					required:'Please Fill Keyword.Do not keep it blank!!',
					minlength:'Keyword name must be 3 char long'
				}
			}
		});
	</script>
<?php
	global $wpdb;
	$keywords_table	 = $wpdb->prefix.'openAi_keywords';	
	if(isset($_POST['add_keywords'])){
		$topics = $_POST['topics'];
		$sql_check = "SELECT * FROM $keywords_table WHERE topics = '$topics'";
		$sql_result = $wpdb->get_results($sql_check);
		if(empty($sql_result)){
			$sql_add = "INSERT INTO `$keywords_table`( `topics`) VALUES ('$topics')";
			$result = $wpdb->query($sql_add);
			
			if($result){
				echo "<h5> Your keyword is successfully submitted</h5>";
				echo "<script>location.replace('admin.php?page=keywords-list');</script>";
			}
			else{
				echo "<h5 class='not_submit'> Your keyword is not submitted</h5>";
				echo "<script>$('.not_submit').fadeOut(7000)</script>";
			}
		}
		else{
			echo "<h5 class='info'> This keyword is already in database.Create new keyword!!</h5>";
			echo "<script>$('.info').fadeOut(7000)</script>";
		}
	}
}

//DELETING THE KEYWORD
function delete_keyword(){
	global $wpdb;
	$keyword_delete_id = $_REQUEST['key_id_del'];
	$keywords_table	 = $wpdb->prefix.'openAi_keywords';
	$sql_results = $wpdb->query("DELETE FROM $keywords_table WHERE id='$keyword_delete_id'");
	echo "<h3>Your keyword is successfully deleted</h3>";
	echo "<script>location.replace('admin.php?page=keywords-list');</script>";
}



//SHOWING ARTICLES ON BASIS OF KEYWORDS AND ALSO PUBLISH MANUALLY\
function keyword_article(){
	echo '<h3>All Articles List based on keywords is here</h3>';
	global $wpdb;
	$key_id = $_REQUEST['k_id'];
	$articles_table = $wpdb->prefix.'openAi_articles';
	$keywords_table	 = $wpdb->prefix.'openAi_keywords';	
	$per_page_record = 25;          
    if (isset($_GET["page_id"])) {    
        $page  = $_GET["page_id"];   
    }    
    else {    
      $page=1;   
    }    
	$page_count = (int)$page-1;
    $start_from = $page_count * $per_page_record; 
    $myrows = $wpdb->get_results("SELECT * FROM $articles_table WHERE keyword_id = '$key_id' limit $start_from, $per_page_record");
?>
	<div class="container">
		<form method="post" name="article_list">
			<table class="table table-bordered">
	    		<thead>
	    			<tr class="file">
			            <th>S.No.</th>
			            <th>Title</th>
			            <th>Description</th>
			            <th>Actions</th>  
					</tr>
	    		</thead>
			    <tbody>
			    	<?php
			    	$i = 1;
			    	if(isset($_GET['page_id'])){
			    		if ($_GET['page_id'] == 1) {
			    			$i = 1;
			    		} else {
			    			$dynamic_page=$per_page_record-1;
			    			$i=$per_page_record*$_GET['page_id']-$dynamic_page;
			    		}
			    	}
			    	foreach ($myrows as $myarticleresults){
			    		// print_r($myarticleresults);
			    		// die();
			    	?>
			    		<tr>
				    		<td>
				    			<?php echo $i;?>
				    		</td>
				    		<td class="title-sib">
				    			<h6 class="title"><?php echo $myarticleresults->title; ?></h6>
				    		</td>
				    		<td class="description">
				    			<input type="hidden" class="description-val" value="
				    				<?php
				    				echo $myarticleresults->article;
				    		
				    			?>
				    			">
				    			<?php
				    				$article_len=$myarticleresults->article;
				    				$length=substr($article_len, 0,200);
				    				$concate=$length."...";
				    			 	echo $concate; 
				    			?>
				    		</td>
				    		<td>	
				    			<?php  
				    			
									$result = $wpdb->get_results ( "SELECT `status` FROM `$articles_table` WHERE `id` =".$myarticleresults->id);
									if($result[0]->status== '0'){ ?>
											 <a href="javascript:void(0);" article_id="<?php echo $myarticleresults->id; ?>" class='article_id my-3 mx-1 btn btn-info'>Publish
											</a>
									<?php }else{ ?>
											 <h5 class='mx-1 my-3'>Published</h5>
								<?php	}
									?>
				    		</td>
				    	</tr>
			    	<?php
			    		$i++;	
			    	}
			    	?>
			    </tbody>
		    </table> 
		</form>
		<nav aria-label="...">
			<ul class="pagination">
				<?php
				$query_table = $wpdb->get_results("SELECT COUNT(*) AS count FROM $articles_table WHERE keyword_id = '$key_id'");
				$total_records = $query_table[0]->count;
				$total_pages = ceil($total_records / $per_page_record);
				$pagLink = "";
				if($page>=2){   
		            echo '<li class="page-item">
						  	<a class="page-link" href="admin.php?page=keyword-article&k_id='.$_GET["k_id"].'&page_id='.($page-1).'">Previous</a>
						</li>';   
		        }          
		        for ($i=1; $i<=$total_pages; $i++) {   
					if ($i == $page) {   
						$pagLink .= '<li class="page-item active" aria-current="page">
								  <a class="page-link" href="admin.php?page=keyword-article&k_id='.$_GET["k_id"].'&page_id='.$i.'">'.$i.'</a>
								</li>' ;  
					}               
					else  {   
					  	$pagLink .= '<li class="page-item">
					  				<a class="page-link" href="admin.php?page=keyword-article&k_id='.$_GET["k_id"].'&page_id='.$i.'">'.$i.'
					  				</a>
					  			 </li>';     
					}   
		        };     
		        echo $pagLink; 
		        if($page<$total_pages){   
		            echo '<li class="page-item">
						  	<a class="page-link" href="admin.php?page=keyword-article&k_id='.$_GET["k_id"].'&page_id='.($page+1).'">Next</a>
						</li>';   
		        }   
				?>
			</ul>
		</nav>
	</div>
<?php
}

