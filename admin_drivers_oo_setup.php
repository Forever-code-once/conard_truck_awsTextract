<? include('application.php') ?>
<? $admin_page = 1 ?>
<?	
	$use_bootstrap = true;
	
	if(isset($_GET['did'])) 
	{
		$sql = "
			update owner_operator_setup set
				deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
		$data_delete = simple_query($sql);
		$mrr_activity_log_notes.="Deleted Owner Operator Setup ".$_GET['did'].". ";			
		mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,0,0,0,"Deleted driver Owner Operator Setup ".$_GET['did']." info.");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes	
		
		javascript_redirect("admin_drivers_oo_setup.php");
		//$_POST['id']=0;
	}
	
	if(isset($_POST['id'])) 
	{
		if($_POST['id']==0)
		{
			$sql = "
     			insert into owner_operator_setup
     				(id,
     				user_id,
     				linedate_added,
     				deleted)     				
     			values (NULL,
     				'".sql_friendly($_SESSION['user_id'])."',
     				NOW(),
     				0)
     		";
     		simple_query($sql);
			$_POST['id']=mysqli_insert_id($datasource);
		}
		if($_POST['id'] > 0)
		{
			if($_POST['cat_id']==0 && $_POST['driver_id'] > 0)
			{	//new cat...so attempt to make it from the driver name.
				$sql = "
					select name_driver_first,name_driver_last,employer_id		
					from drivers
					where id = '".(int) $_POST['driver_id']."'
				";
				$data_drivers = simple_query($sql);
				if($row = mysqli_fetch_array($data_drivers)) 
				{
					$fname=trim($row['name_driver_first']);
					$lname=trim($row['name_driver_last']);
					
					$old_employer_id=$row['employer_id'];
										
					if($old_employer_id != $_POST['employer_id'])
					{	//change employer...
						$sqlu = "
               				insert into drivers_employer_change 
               					(id,
               					linedate_added,
               					linedate,
               					driver_id,
               					old_employer_id,
               					new_employer_id,
               					deleted)
               				values
               					(NULL,
               					NOW(),
               					NOW(),
               					'".sql_friendly($_POST['driver_id'])."',
               					'".sql_friendly($old_employer_id)."',
               					'".sql_friendly($_POST['employer_id'])."',
               					0)
               				";
               			simple_query($sqlu);
					}
					
					
					$cat_name="";
					$cat_label="";
					
					//trim the names down to just the first part.
					$poser=0;
					$poser=strpos($fname," ");
					if($poser > 0)		$fname=substr($fname,0,$poser);
					$poser=0;
					$poser=strpos($lname," ");
					if($poser > 0)		$lname=substr($lname,0,$poser);
					$lname2=$lname;
					
					//get the cat name first...
					$lname=trim(strtolower($lname));
					$lname=str_replace(" ","-",$lname);
					$cat_name.=$lname."_rates";
					
					//now get the label
					$cat_label="".$fname." ".$lname2." O.O.";
					
					$_POST['cat_name']=trim($cat_name);
					$_POST['cat_label']=trim($cat_label);
					
					//now add the cat...and allow it to be linked to this driver as O.O. Rates.
					$sql = "
     					insert into option_cat
          					(id,
          					cat_name,
          					cat_desc,
          					locked,
          					blank_text,
          					deleted)     				
          				values (NULL,
          					'".sql_friendly($_POST['cat_name'])."',
          					'".sql_friendly($_POST['cat_label'])."',
          					0,
          					'Select O.O. Rate',
          					0)
					";
					simple_query($sql);
					$_POST['cat_id']=mysqli_insert_id($datasource);
				}
			}
			elseif($_POST['cat_id'] > 0 && $_POST['driver_id'] > 0)
			{	//if cat is selected, just grab the settings from there.
				$sql = "
					select name_driver_first,name_driver_last,employer_id		
					from drivers
					where id = '".(int) $_POST['driver_id']."'
				";
				$data_drivers = simple_query($sql);
				if($row = mysqli_fetch_array($data_drivers)) 
				{
					//$fname=trim($row['name_driver_first']);
					//$lname=trim($row['name_driver_last']);
					
					$old_employer_id=$row['employer_id'];
					
					if($old_employer_id != $_POST['employer_id'])
					{	//change employer...
						$sqlu = "
               				insert into drivers_employer_change 
               					(id,
               					linedate_added,
               					linedate,
               					driver_id,
               					old_employer_id,
               					new_employer_id,
               					deleted)
               				values
               					(NULL,
               					NOW(),
               					NOW(),
               					'".sql_friendly($_POST['driver_id'])."',
               					'".sql_friendly($old_employer_id)."',
               					'".sql_friendly($_POST['employer_id'])."',
               					0)
               				";
               			simple_query($sqlu);
					}
				}
				
				$sql = "
					select cat_name,cat_desc		
					from option_cat
					where id = '".(int) $_POST['cat_id']."'
				";
				$data_drivers = simple_query($sql);
				if($row = mysqli_fetch_array($data_drivers)) 
				{
					$_POST['cat_name']=trim($row['cat_name']);
					$_POST['cat_label']=trim($row['cat_desc']);
				}
			}			
			
     		$sql = "
     			update owner_operator_setup set
     			     
     			     driver_id = '".sql_friendly($_POST['driver_id'])."',
     				cat_id = '".sql_friendly($_POST['cat_id'])."',
     				cat_label = '".sql_friendly(trim($_POST['cat_label']))."',
     				cat_name = '".sql_friendly(trim($_POST['cat_name']))."',
     				
     				deleted=0
     			where id = '".sql_friendly($_POST['id'])."'
     		";     		
     		simple_query($sql);
     		
     		$sql = "
				update drivers set
					owner_operator=1,
					employer_id='".(int) $_POST['employer_id']."'
				where id = '".(int) $_POST['driver_id']."'
			";
			simple_query($sql);
			     		
     		javascript_redirect("admin_drivers_oo_setup.php?id=".$_POST['id']);
		}
	}
	
	if(isset($_GET['id']))			$_POST['id']=$_GET['id'];
	
	if(!isset($_POST['id']))			$_POST['id']=0;
	if(!isset($_POST['driver_id']))	$_POST['driver_id']=0;
	if(!isset($_POST['cat_id']))		$_POST['cat_id']=0;

	$usetitle = "Driver Owner Operator Setup";
	$use_title = "Driver Owner Operator Setup";
?>
<? include('header.php') ?>
<form action='' method='post'>

	<input type='hidden' name='id' id='id' value='<?=$_POST['id'] ?>'>
	
<div class='container col-md-12'>
	<div class='col-md-6'>
		<div class="panel panel-info">
			<div class="panel-heading"><?=$use_title ?></div>
			  <div class="panel-body">
			  	<h4>Use this form to set up an active driver as an Owner Operator.  Will create category and link to rates on Dispatch setup page.</h4>
			  	<p>
			  		The Driver O.O. Rates can be set up by going to the appropriate category.  The O.O. Insurance info can be assigned on the drivers page.
			  		<br>
			  		Don't forget to assign the truck and flag it if that is the owner-operator's normal unit for payroll to separate work done with it.
			  	</p>
				<table class='table well table-bordered' width='100%'>
				<thead>
				<tr>
					<th>ID</th>
					<th>Driver Name</th>
					<th>Category Name</th>
					<th>Category Desc</th>
					<th>Employer</th>
					<th>&nbsp;</th>
				</tr>
				</thead>
				<tbody>				
				<?
				$i = 0;	
				$sql="
					select owner_operator_setup.*,
						option_cat.cat_name,
						option_cat.cat_desc,
						drivers.name_driver_first,
						drivers.name_driver_last,
						drivers.owner_operator,
						drivers.employer_id
						
					from owner_operator_setup
						left join option_cat on option_cat.id=owner_operator_setup.cat_id
						left join drivers on drivers.id=owner_operator_setup.driver_id
					where owner_operator_setup.deleted=0
					order by owner_operator_setup.id asc
				";	
				$data = simple_query($sql);	
				while($row = mysqli_fetch_array($data)) 
				{
					$mrr_emp_name=mrr_get_employer_by_id($row['employer_id']);
					
					echo "
						<tr bgcolor='#".($i % 2==0 ? "eeeeee" : "dddddd")."'>
							<td><a href='admin_drivers_oo_setup.php?id=".$row['id']."'>".$row['id']."</a></td>
							<td><a href='admin_drivers.php?id=".$row['driver_id']."' target='_blank'>".$row['name_driver_last'].", ".$row['name_driver_first']."</a></td>
							<td><a href='admin_options.php?eid=".$row['cat_id']."' target='_blank'>".$row['cat_name']."</a></td>
							<td>".$row['cat_desc']."</td>
							<td>".$mrr_emp_name."</td>
							<td><a href='javascript:confirm_del(".$row['id'].");'><img src='images/delete_small.png' border='0'></a></td>
						</tr>
					";
					$i++;
				}
				
				echo "
						<tr bgcolor='#ffffff'>
							<td><a href='admin_drivers_oo_setup.php?id=0'>New</a></td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
				";
				?>
				</tbody>
				</table>
			  </div>
			</div>
		</div>
		
		<? if(isset($_POST['id'])) { ?>
			<?
			$row_detail['id']=0;
			$row_detail['driver_id']=0;
			$row_detail['linedate_added']="";
			//$row_detail['deleted']=0;
			$row_detail['user_id']=0;
			$row_detail['username']="";
			$row_detail['cat_id']=0;
			$row_detail['cat_label']=" O.O.";
			$row_detail['cat_name']="_rates";
			$row_detail['employer_id']=0;
						
			if($_POST['id'] > 0)
			{
				$sql = "
					select owner_operator_setup.*,
						users.username,
						drivers.employer_id
					from owner_operator_setup
						left join users on users.id=owner_operator_setup.user_id
						left join drivers on drivers.id=owner_operator_setup.driver_id
					where owner_operator_setup.id = '".(int) $_POST['id']."'
					";
				$data_detail = simple_query($sql);
				$row_detail = mysqli_fetch_array($data_detail);
			}
			// get the driver list 
			$sql = "
				select *		
				from drivers
				where deleted = 0 and active > 0
				order by active desc, name_driver_last, name_driver_first
			";
			$data_drivers = simple_query($sql);
			
			$sql = "
				select *		
				from option_cat
				where deleted = 0 and id > 25
				order by cat_name asc, cat_desc asc, id asc
			";
			$data_cats = simple_query($sql);
			
			$sql="
                    	select option_values.id as use_val,
                    		option_values.fvalue as use_disp
                    	from option_values,option_cat
                    	where option_values.deleted=0
                    		and option_cat.id=option_values.cat_id
                    		and option_cat.cat_name='employer_list'
                    	order by option_values.fvalue asc
               ";
               $mrr_emp_id=$row_detail['employer_id'];
			$emp_box=mrr_select_box_disp($sql,'employer_id',$mrr_emp_id,'Choose Employer',''); 		// style="width:300px;"
			?>
			<div class='col-md-6'>
							
				<div class="panel panel-primary">
					<div class="panel-heading">Editing O.O. Entry</div>
					  <div class="panel-body">
					  	<p>
					  		If new, cat name and cat label will be created from the dirver name, if possible. 
					  		<br>
					  		If existing category is chosen, will use the option list info.					  		
					  	</p>
						<table class='table table-bordered well'>
						<tr>
							<td>O.O. Driver:</td>
							<td>
								<select name="driver_id" id="driver_id">
									<option value="0">Choose Active Driver</option>
									<? 
									while($row_driver = mysqli_fetch_array($data_drivers)) 
									{ 
										?>					
										<option value="<?=$row_driver['id']?>" <? if($row_detail['driver_id'] == $row_driver['id']) echo "selected"?>>
											<?=$row_driver['name_driver_last']?>, <?=$row_driver['name_driver_first']?>
										</option>
									<? } ?>
								</select>	
							</td>
						</tr>
						<tr>
							<td>O.O. Rates Category:</td>
							<td>
								<select name="cat_id" id="cat_id">
									<option value="0">Add New Category</option>
									<?								
									while($row_cats = mysqli_fetch_array($data_cats)) 
									{ 
										$sel="";
										if($row_detail['cat_id'] == $row_cats['id'])
										{
											$sel=" selected";	
											$row_detail['cat_label']=trim($row_cats['cat_desc']);
											$row_detail['cat_name']=trim($row_cats['cat_name']);
										}										
										?>					
										<option value="<?=$row_cats['id']?>" <?=$sel ?>>(<?=$row_cats['id']?>) <?=$row_cats['cat_desc']?></option>
									<? } ?>
								</select>	
							</td>
						</tr>
						<tr>
							<td>Current Employer:</td>
							<td><?=$emp_box ?></td>	
						</tr>
						<tr>
							<td>New Cat Name:</td>
							<td><input name='cat_name' id='cat_name' value="<?=$row_detail['cat_name'] ?>" style='width:200px' readonly> Auto-Created</td>
						</tr>
						<tr>
							<td>New Cat Desc:</td>
							<td><input name='cat_label' id='cat_label' value="<?=$row_detail['cat_label']?>" style='width:200px' readonly> Auto-Created</td>
						</tr>
						</table>								
						<p>
							<button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-ok"></span> Save O.O. Settings</button>
						</p>
					</div>
				</div>
			</div>				
		<? } ?>		
</div>
</form>
<script type='text/javascript'>
	function confirm_del(id) {
		if(confirm("Are you sure you want to delete this driver O.O. setting?")) {
			window.location = '<?=$SCRIPT_NAME?>?did=' + id;
		}
	}
</script>
<? include('footer.php') ?>