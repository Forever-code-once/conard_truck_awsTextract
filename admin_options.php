<? $inventory_page = 1 ?>
<? $admin_page = 1 ?>
<? 
$usetitle = "Admin Lists";
$use_title = 'Admin Lists';
$use_bootstrap = true;
?>
<? include('header.php') ?>
<?

	$query_string =  query_string_remove($query_string, "start_row");
	$query_string =  query_string_remove($query_string, "sort_field");
	$records_per_page = 20;
	$record_start = 0;

	sort_fields();

	if(isset($_GET['search'])) $_POST['sbox'] = $_GET['search'];
	if(isset($_GET['start_row'])) $record_start = $_GET['start_row'];

	if(isset($_POST['sbox'])) 
	{
		$search_filter = "
			and (
				cat_desc like '%".sql_friendly($_POST['sbox'])."%'
			)
		";
		$extra_search = "&search=$_POST[sbox]";
	} 
	else 
	{
		$search_filter = "";
		$extra_search = "";
	}

    //Stop Relay Section
    if(isset($_GET['del_relay_id']))
    {
         $sql = "
                    update relay_stops set
                        deleted = 1
                    where id = '".sql_friendly($_GET['del_relay_id'])."'
                ";
         simple_query($sql);
         javascript_redirect("admin_options.php?relay_id=0#relays");
    }
    
    if(!isset($_GET['relay_id']))       $_GET['relay_id']=0;
    
    if(isset($_POST['relay_id']) && (int) $_POST['relay_id'] > 0)       $_GET['relay_id']=(int) $_POST['relay_id'];
    
    $_GET['relay_id']=(int)$_GET['relay_id'];
    
    if($_GET['relay_id'] > 0)       $_POST['relay_id']=$_GET['relay_id'];
    
    //$messager="";
    if(isset($_POST['save_relay']))
    {
         $relay_id=(int) $_POST['relay_id'];
         $namer=trim($_POST['relay_name']);
         $addr1=trim($_POST['relay_addr1']);
         $addr2=trim($_POST['relay_addr2']);
         $city=trim($_POST['relay_city']);
         $state=trim($_POST['relay_state']);
         $zip=trim($_POST['relay_zip']);
         $phone=trim($_POST['relay_phone']);
         
         $sqlu="
            update relay_stops set 
                  relay_name='".sql_friendly($namer)."',
                  relay_address1='".sql_friendly($addr1)."',
                  relay_address2='".sql_friendly($addr2)."',
                  relay_city='".sql_friendly($city)."',
                  relay_state='".sql_friendly($state)."',
                  relay_zip='".sql_friendly($zip)."',
                  relay_phone='".sql_friendly($phone)."'
            where id='".sql_friendly($relay_id)."'
         ";
         simple_query($sqlu);
         
         //die("1. Saving the Stop Relay... Query is ".$sqlu.".");
         
         //$messager="<br><b>Updated Relay Stop '".$namer."'.</b>   ...".$sqlu.".<br>";
         //javascript_redirect("admin_options.php?relay_id=".$relay_id."#relays");
    }

    // create a new stop relay entry if specified
    if(isset($_GET['new_relay']))
    {
         $query_string =  query_string_remove($query_string, "new_relay");
         
         $sql = "
                    insert into relay_stops
                        (id,relay_name,linedate_added,priority,deleted)                    
                    values (NULL, 'New Relay Stop', NOW(),0,0)
                ";
         simple_query($sql);
         
         javascript_redirect("admin_options.php?relay_id=".mysqli_insert_id($datasource)."#relays");
    }
    
    
    // create a new entry if specified
	if(isset($_GET['new'])) 
	{
		$query_string =  query_string_remove($query_string, "new");
		
		$sql = "
			insert into option_values
				(cat_id,
				fname,
				fvalue)
				
			values ('".sql_friendly($_GET['eid'])."',
				'New Entry',
				'New Entry')
		";
		simple_query($sql);
		
		javascript_redirect($SCRIPT_NAME."?".$query_string."&edid=".mysqli_insert_id($datasource));
	}
	
	if(isset($_GET['delid'])) 
	{
		$sql = "
			update option_values set
			    deleted = 1
			where id = '".sql_friendly($_GET['delid'])."'
		";
		simple_query($sql);
	}
	if(isset($_GET['deleid'])) 
	{
		$sql = "
			update option_cat set
			    deleted = 1
			where id = '".sql_friendly($_GET['deleid'])."'
		";
		simple_query($sql);
	}
    	
	if(isset($_POST['list_value'])) {
		
		$use_val=$_POST['list_value'];
		
		if($_GET['eid']==17)
		{
			$use_val=date("Y-m-d", strtotime($_POST['list_value']));	
		}		
		
		$sql = "
			update option_values set
			     fvalue = '".sql_friendly($use_val)."',
				fname = '".sql_friendly($_POST['list_name'])."',
				dummy_val = '".sql_friendly($_POST['list_value2'])."'
			where id = '".sql_friendly($_GET['edid'])."'
		";
		
		simple_query($sql);
		
		$query_string =  query_string_remove($query_string, "edid");
		javascript_redirect($SCRIPT_NAME."?".$query_string);
	}
	
	$sql = "
		select *		
		from option_cat
		where deleted = 0 and locked = 0
	";
	$data = mysqli_query($datasource, $sql) or die("Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
?>
<div class='container col-md-12'>
	<div class='col-md-4'>
		<div class="panel panel-info">
			<div class="panel-heading">Admin Lists</div>
			  <div class="panel-body">
				<table class='table well table-bordered' width='100%'>
				<thead>
				<tr>
					<th>ID</th>
					<th>List</th>
					<th>&nbsp;</th>
				</tr>
				</thead>
				<tbody>
				<?
					$i = 0;
				?>	
				<? while($row = mysqli_fetch_array($data)) { ?>
					<? 
						if(fmod($i,2) == 0) {
							$use_bgcolor = "eeeeee";
						} else {
							$use_bgcolor = "ffffff";
						}						
						
						if($row['xlink'] != '') {
							$use_link = $row['xlink'];
						} else {
							$use_link = "$SCRIPT_NAME?eid=$row[id]";
						}						
					?>					
					<tr bgcolor="<?=$use_bgcolor?>">
						<td><?=$row['id']?></td>
						<td><a href="<?=$use_link?>"><?=$row['cat_desc']?></a></td>
						<td>
							<? if($row['id'] >=26 ) { ?>
								<a href="javascript:confirm_cat_del(<?=$row['id']?>)"><img src='images/delete_small.png' border='0'></a>
							<? } else { ?>
								&nbsp;
							<? } ?>
						</td>
					</tr>
				<? } ?>
				</tbody>
				</table>
			  </div>
		</div>
	</div>
	
	<? if(isset($_GET['eid'])) { ?>
		<?
			// get the category name we're editing
			$sql = "
				select *				
				from option_cat
				where id = '$_GET[eid]'
			";
			$data_cat_name = simple_query($sql);
			$row_cat_name = mysqli_fetch_array($data_cat_name);
		
			// get the full collection of this list category
			$sql = "
				select *				
				from option_values
				where cat_id = '$_GET[eid]'
					and deleted = 0
				order by fname asc
			";
			$data_values = simple_query($sql);
		?>

				<? if(isset($_GET['edid'])) { ?>
					<?
						$sql = "
							select *							
							from option_values
							where id = '$_GET[edid]'
								and deleted = 0
						";
						$data_detail = simple_query($sql);
						$row_detail = mysqli_fetch_array($data_detail);
						
						$use_val=$row_detail['fvalue'];
						if($_GET['eid']==17)
						{
							$use_val=date("m/d/Y", strtotime($row_detail['fvalue']));	
						}
					?>
					<div class='col-md-8'>
							
						<div class="panel panel-primary">
							<div class="panel-heading">Editing Entry</div>
							  <div class="panel-body">
								<form method="post" name="edit_opt" id="edit_opt">	
								<table class='table table-bordered well'>
								<tr>
									<td>Name:</td>
									<td><input name='list_name' value="<?=$row_detail['fname']?>" style='width:400px'></td>
								</tr>
								<tr>
									<td>Value:</td>
									<td><input name='list_value' value="<?=$use_val ?>" style='width:400px'></td>
								</tr>
								<tr>
									<td>Value 2:</td>
									<td><input name='list_value2' value="<?=$row_detail['dummy_val']?>" style='width:400px'></td>
								</tr>
								</table>								
								<p>
									<button type='submit' name="save_opt" id="save_opt" class='btn btn-primary'>
                                        <span class="glyphicon glyphicon-ok"></span> Save Changes
                                    </button>
								</p>
								</form>
							</div>
						</div>
					</div>
				<? } ?>

					<div class='col-md-8'>
						<div class="panel panel-primary" id='print_list_entries'>
							<div class="panel-heading">
								<? if($_GET['eid'] >=26 ) { ?>
									<?=$row_cat_name['cat_desc']?> - <?=mysqli_num_rows($data_values)?> Owner Operator Route(s)
								<? } else { ?>	
									Editing List - <?=$row_cat_name['cat_desc']?> - <?=mysqli_num_rows($data_values)?> entrie(s)
								<? } ?>
							</div>
							  <div class="panel-body">
					
								<p>
									<? if($_GET['eid'] >=26 ) { ?>
										<div style='float:right; margin-left:10px; margin-right:10px;'>
											<button onclick="mrr_print_report('<?=$row_cat_name['cat_desc']?> - <?=mysqli_num_rows($data_values)?> Owner Operator Route(s)');" class='btn btn-info kill_bot'>
												<span class='glyphicon glyphicon-print'></span> Print Owner Operator Routes
											</button>	
										</div>
									<? } ?>							
									
									<button onclick="window.location='<?=$SCRIPT_NAME?>?<?=$query_string?>&new=1'" class='btn btn-success kill_bot'><span class='glyphicon glyphicon-plus'></span> Add new entry</button>
								</p>
					
									<table class='table table-bordered'>
									<? if($_GET['eid'] >=26 ) { ?>
										<tr>
     										<td><b>ID</b></td>
     										<td><b>Route</b></td>
     										<td><b>Rate</b></td>
     										<td><b>Note</b></td>
     										<td class='kill_bot'>&nbsp;</td>
     									</tr>
									<? } else { ?>	
     									<tr>
     										<td><b>ID</b></td>
     										<td><b>Name</b></td>
     										<td><b>Value</b></td>
     										<td><b>Value 2</b></td>
     										<td class='kill_bot'>&nbsp;</td>
     									</tr>
									<? } ?>	
									<? while($row_value = mysqli_fetch_array($data_values)) 
                                    { 										
											$use_val=$row_value['fvalue'];
											if($_GET['eid']==17)
											{
												$use_val=date("m/d/Y", strtotime($row_value['fvalue']));	
												if($use_val=="12/31/1969")		$use_val="";
											}
										?>
										<tr>
											<td><a href='<?=$SCRIPT_NAME?>?<?=$query_string?>&edid=<?=$row_value['id']?>'><?=$row_value['id']?></a></td>
											<td><a href='<?=$SCRIPT_NAME?>?<?=$query_string?>&edid=<?=$row_value['id']?>'><?=$row_value['fname']?></a></td>
											<td><a href='<?=$SCRIPT_NAME?>?<?=$query_string?>&edid=<?=$row_value['id']?>'><?=($_GET['eid'] >=26 ? "$" : "") ?><?=$use_val ?></a></td>
											<td><a href='<?=$SCRIPT_NAME?>?<?=$query_string?>&edid=<?=$row_value['id']?>'><?=$row_value['dummy_val']?></a></td>
											<td class='kill_bot'>
												<a href="javascript:confirm_del(<?=$_GET['eid']?>,<?=$row_value['id']?>)"><img src='images/delete_small.png' border='0'></a>
											</td>
										</tr>
									<? } ?>
									</table>
								</div>
							</div>
				</div>
		</div>
	<? } ?>

</div>
<?
$landscape=1;
$form_mode=1;
?>
<script type='text/javascript'>
	function confirm_del(eid, id) 
	{
		if(confirm("Are you sure you want to delete this entry?")) 
		{
			window.location = "<?=$SCRIPT_NAME?>?eid="+eid+"&delid=" + id;
		}
	}
	function confirm_cat_del(id) 
	{
		if(confirm("Are you sure you want to delete this Category (and all the entries with it)?")) 
		{			
			window.location = "<?=$SCRIPT_NAME?>?deleid=" + id;
		}
	}
	$().ready(function() 
	{				
   		//printing like the accounting side....
   		print_block='print_list_entries';
   			
   		if(print_block!='')
   		{	
   			obj_holder = $('#'+print_block+'');
			obj_wrapper_holder = "";
			
			$(obj_holder).wrap("<div id='"+print_block+"_print_wrapper' />");
			
			obj_wrapper_holder = $('#'+print_block+'_print_wrapper');
   		}		
	});	
	function mrr_print_report(mrr_title) 
	{ 
		$('.kill_bot').hide();
		
		$.ajax({
			url: "print_report.php",
			dataType: "xml",
			type: "post",
			data: {
				script_name: "<?=$_SERVER['SCRIPT_NAME']?>",
				report_title: mrr_title,
				'display_mode':"<?=$landscape?>",
				'form_mode':"<?=$form_mode?>",
				report_contents: encodeURIComponent(html_entity_decode($(obj_wrapper_holder).html()))
			},
			error: function() {
				$.prompt("General error printing report");
				//$('#'+print_icon_holder).attr('src','images/printer.png');
				$('.kill_bot').show();
			},
			success: function(xml) {
				//$('#'+print_icon_holder).attr('src','images/printer.png');
				if($(xml).find('PDFName').text() == '') {
					$.prompt("Error reading filename");
				} else {
					window.open($(xml).find('PDFName').text());
					$('.kill_bot').show();
				}
			}
		});
	}

    function confirm_cat_del_relay(id)
    {
        if(confirm("Are you sure you want to delete this Relay Stop?"))
        {
            window.location = "admin_options.php?del_relay_id="+ id +"#relays";
        }
    }
</script>
<br><hr>  <a name="relays">&nbsp;</a><br>
<div class='container col-md-12'>
    
    <div class='col-md-6'>        
        <div class="panel panel-info">
            <div class="panel-heading">Relay Stop Manager</div>
            <div class="panel-body">
                <table class='table well table-bordered' width='100%'>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Relay Name</th>
                        <th>Address 1</th>
                        <th>Address 2</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Zip</th>
                        <th>Phone</th>
                        <th>&nbsp</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?                    
                    $i = 0;
                    $sqlr="
                        select * 
                        from relay_stops 
                        where deleted=0 
                        order by priority desc,relay_name asc,relay_state asc,relay_city asc,relay_zip asc,relay_address1 asc
                     ";
                    $datar = simple_query($sqlr);
                    while($rowr = mysqli_fetch_array($datar))
                    {     
                         $i++;
                         $use_link = "admin_options.php?relay_id=".$rowr['id']."#relays";
                         $use_bgcolor = "ffffff";
                         if(fmod($i,2) == 0)    $use_bgcolor = "eeeeee";
                         ?>
                        <tr bgcolor="<?=$use_bgcolor?>">
                            <td><?=$rowr['id']?></td>
                            <td><a href="<?=$use_link?>"><?=$rowr['relay_name'] ?></a></td>
                            <td><?=$rowr['relay_address1'] ?></td>
                            <td><?=$rowr['relay_address2'] ?></td>
                            <td><?=$rowr['relay_city'] ?></td>
                            <td><?=$rowr['relay_state'] ?></td>
                            <td><?=$rowr['relay_zip'] ?></td>
                            <td><?=$rowr['relay_phone'] ?></td>
                            <td>
                                 <? if($rowr['id'] >=2 ) { ?>
                                     <a href="javascript:confirm_cat_del_relay(<?=$rowr['id']?>)"><img src='images/delete_small.png' border='0'></a>
                                 <? } else { ?>
                                     &nbsp;
                                 <? } ?>
                            </td>
                        </tr>
                    <? } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class='col-md-6'>
        <div class="panel panel-info">
                <div class="panel-heading">Relay Stop Editor</div>
                <div class="panel-body">
                    <form method="post" action="admin_options.php?relay_id=<?=$_GET['relay_id'] ?>" name="relay_form" id="relay_form">
                    
                    <input type="hidden" name="relay_id" value="<?=$_GET['relay_id'] ?>">
                        
                    <?=$messager ?>  
                    <table class='well table-bordered' width='100%'>
                        <?
                        $relay_id="NEW RELAY";
                        $namer="";
                        $addr1="";
                        $addr2="";
                        $city="";
                        $state="";
                        $zip="";
                        $phone="";

                        $added="N/A";
                        
                        $sqlr="
                            select * 
                            from relay_stops 
                            where id='".sql_friendly($_GET['relay_id'])."'
                        ";
                        $datar = simple_query($sqlr);
                        if($rowr = mysqli_fetch_array($datar))
                        {
                             $relay_id="Editing Relay ".$rowr['id'];
                             $namer=trim($rowr['relay_name']);
                             $addr1=trim($rowr['relay_address1']);
                             $addr2=trim($rowr['relay_address2']);
                             $city=trim($rowr['relay_city']);
                             $state=trim($rowr['relay_state']);
                             $zip=trim($rowr['relay_zip']);
                             $phone=trim($rowr['relay_phone']);
     
                             $added=date("m/d/Y H:i:s",strtotime($rowr['linedate_added']));
                        } 
                        ?>    
                        <tr>
                            <td valign="top"><?=$relay_id ?></td>
                            <td valign="top" align="right"><a href='admin_options.php?new_relay=1'>Add New Relay Stop</a></td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Relay Stop Name</b></td>
                            <td valign="top"><input type='text' name='relay_name' value="<?=$namer ?>" style='width:400px'></td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Address 1</b></td>
                            <td valign="top"><input type='text' name='relay_addr1' value="<?=$addr1 ?>" style='width:400px'></td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Address 2</b></td>
                            <td valign="top"><input type='text' name='relay_addr2' value="<?=$addr2 ?>" style='width:400px'></td>
                        </tr>
                        <tr>
                            <td valign="top"><b>City</b></td>
                            <td valign="top"><input type='text' name='relay_city' value="<?=$city ?>" style='width:200px'></td>
                        </tr>
                        <tr>
                            <td valign="top"><b>State</b></td>
                            <td valign="top"><input type='text' name='relay_state' value="<?=$state ?>" style='width:50px'></td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Zip</b></td>
                            <td valign="top"><input type='text' name='relay_zip' value="<?=$zip ?>" style='width:100px'></td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Phone</b></td>
                            <td valign="top"><input type='text' name='relay_phone' value="<?=$phone ?>" style='width:200px'></td>
                        </tr>
                        <tr>
                            <td valign="top"><i><?=$added ?></i></td>
                            <td valign="top" align="right">
                                <button type='submit' name="save_relay" id="save_relay" class='btn btn-primary'>
                                    <span class="glyphicon glyphicon-ok"></span> Save Stop Relay
                                </button>
                            </td>
                        </tr>
                    </table>
                    </form>
                </div>
        </div>    
    </div>

</div>