<? include('header.php') ?>
<?
	$map_storage="";
	if(isset($_GET['quote_id'])) {
		//get quote details
		$sql = "
			select map_storage
			
			from quotes
			where id = '".sql_friendly($_GET['quote_id'])."'
		";
		$data_quote = simple_query($sql);
		$row = mysqli_fetch_array($data_quote);
		$map_storage = $row['map_storage'];
	}

?>

<form name='quote_form' action='' method='post'>		
		<div style='margin-top:10px;'><input type='hidden' id='mrr_url_value' name='mrr_url_value' value='<?=$map_storage ?>'>
			<iframe width="900" height="700" id="map_frame" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="" style="color:#0000FF;text-align:left;border:1px black solid"></iframe>
		</div>
</form>

<script type='text/javascript'>
	var map_link = generate_map_url(true);
	$('#map_frame').attr('src',map_link);
	
	function generate_map_url(embed_flag) {
		map_link = "https://maps.google.com/maps?"+$('#mrr_url_value').val();
		
		if(embed_flag) map_link += "&output=embed";
		
		return map_link;
	}
</script>

<? include('footer.php') ?>
