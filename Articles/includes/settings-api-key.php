<h3 class="container text-center my-3">API Key Setting</h3>
<form class="container text-center" id="api-form" style="font-size: 20px;padding: 100px;border: 2px solid;width: fit-content;margin-top: 2rem;" method="POST">

	<div class="d-flex justify-content-center">
		<label class="mx-3">
			 API Key: 
		</label>
		<input 
			type="tel" //this can be any HTML input type: date, number, text etc.
			name="api_key"
			placeholder="Enter your API KEY" 
			value="<?php echo esc_attr(get_option('api_key')); ?>" 
		/>
	</div>
	
	<br>
	<!-- Default switch -->
	<div class="d-flex justify-content-between">
		<div class="custom-control custom-switch text-left">

			<input type="hidden" name="status_api" id="status_api" value="">
			<?php  
			$checked_status=esc_attr(get_option('status_api'));
			if ($checked_status == "deactivated") { ?>
				<input type="checkbox" class="custom-control-input" id="customSwitches" style="width:50px;height: 20px;">	
			<?php
			} 
			else { 
			?>
				<input type="checkbox" class="custom-control-input" id="customSwitches" style="width:50px;height: 20px;" checked>	
			<?php
			}
			?>
			<label class="custom-control-label" for="customSwitches"></label>
			<br>			
		</div> 
		<div class="text-right">
			<input type="submit" name="submit" value="Save" class="btn btn-success text-left">
		</div>
	</div>
</form>
<script>
	$(document).ready(function(){
		$("#customSwitches").click(function(){
			api_status=$(this).prop('checked');
			if (api_status == false) {
				$('#status_api').val('deactivated');
				// $('.status_show').html('Deactivated')..show();
			} else {
				$('#status_api').val('activated');
				// $('.status_show').html('Activated').show();
			}

		})
	})
</script>
<?php
if (isset($_POST['api_key'])) {
	$api_key = get_option('api_key');
	$api_key = sanitize_text_field( $_POST['api_key'] );
	$api_key_status = sanitize_text_field( $_POST['status_api'] );
	update_option( 'api_key', $api_key);
	update_option( 'status_api', $api_key_status);
	echo "<script>location.replace('admin.php?page=keywords-list');</script>";
}
?>