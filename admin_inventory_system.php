<? include('application.php') ?>
<? $admin_page = 1 ?>
<? $usetitle = "Admin Inventory System" ?>
<?
    if(!isset($_GET['pid']))        $_GET['pid']=0;
    if(!isset($_GET['vid']))        $_GET['vid']=0;
    if(!isset($_GET['cid']))        $_GET['cid']=0;
    if(!isset($_GET['mid']))        $_GET['mid']=0;
    

    //Inventory Part
    if(isset($_GET['delp']))
    {
         $sql = "
                update inventory_parts set
                    deleted = 1			
                where id = '".$_GET['delp']."'
            ";
         simple_query($sql);
         
         header("Location: admin_inventory_system.php?pid=0");
         die();
    }
    
    if(isset($_GET['newp']))
    {
         $sql="update inventory_parts set deleted=1 where inv_part_name='New Part'";
         mysqli_query($datasource, $sql);
         
         $sql = "
                insert into inventory_parts	
                    (inv_part_name,
                    inv_part_code,
                    active,
                    linedate_added)
                values 
                    ('New Part',
                    '',
                    1,
                    NOW())
            ";
         $data = simple_query($sql);
         $new_id=mysqli_insert_id($datasource);
         
         header("Location: admin_inventory_system.php?pid=".$new_id."");
         die();
    }
    
    if(isset($_POST['save_part']))
    {
         $useactive = 0;
         if(isset($_POST['active']))		$useactive = 1;
         
         $sql = "
                update inventory_parts set
                    inv_part_name = '".sql_friendly(trim($_POST['inv_part_name']))."',
                    inv_part_code = '".sql_friendly(trim($_POST['inv_part_code']))."',
                    
                    inv_part_desc= '".sql_friendly(trim($_POST['inv_part_desc']))."',
                    vendor_id= '".sql_friendly($_POST['vendor_id'])."',
                    manu_id= '".sql_friendly($_POST['manu_id'])."',
                    cat_id= '".sql_friendly($_POST['cat_id'])."',
                    
                    cost= '".sql_friendly(money_strip($_POST['cost']))."',
                    
                    active ='".sql_friendly($useactive)."'
                where id = '".$_GET['pid']."'
            ";
         simple_query($sql);
         
         header("Location: admin_inventory_system.php?pid=".$_GET['pid']."");
         die();
    }

    //vendor
    if(isset($_GET['delv'])) 
	{
		$sql = "
			update inventory_vendors set
				deleted = 1			
			where id = '".$_GET['delv']."'
		";
		simple_query($sql);
		
		header("Location: admin_inventory_system.php?vid=0");
		die();
	}

	if(isset($_GET['newv'])) 
	{
		$sql="update inventory_vendors set deleted=1 where vendor_name='New Vendor'";
		mysqli_query($datasource, $sql);
		
		$sql = "
			insert into inventory_vendors	
				(vendor_name,
				vendor_code,
				active,
				linedate_added)
			values 
				('New Vendor',
				'',
				1,
				NOW())
		";
		$data = simple_query($sql);
		$new_id=mysqli_insert_id($datasource);
		
		header("Location: admin_inventory_system.php?vid=".$new_id."");
		die();
	}
	
	if(isset($_POST['save_vendor'])) 
	{
		$useactive = 0;
		if(isset($_POST['active']))		$useactive = 1;
				
		$sql = "
			update inventory_vendors set
				vendor_name = '".sql_friendly(trim($_POST['vendor_name']))."',
				vendor_code = '".sql_friendly(trim($_POST['vendor_code']))."',
				active ='".sql_friendly($useactive)."'
			where id = '".$_GET['vid']."'
		";
		simple_query($sql);
		
		header("Location: admin_inventory_system.php?vid=".$_GET['vid']."");
		die();		
	}
	
	//category
    if(isset($_GET['delc']))
    {
         $sql = "
                update inventory_cats set
                    deleted = 1			
                where id = '".$_GET['delc']."'
            ";
         simple_query($sql);
         
         header("Location: admin_inventory_system.php?cid=0");
         die();
    }
    
    if(isset($_GET['newc']))
    {
         $sql="update inventory_cats set deleted=1 where inv_cat_name='New Category'";
         mysqli_query($datasource, $sql);
         
         $sql = "
                insert into inventory_cats	
                    (inv_cat_name,
                    inv_cat_code,
                    active,
                    linedate_added)
                values 
                    ('New Category',
                    '',
                    1,
                    NOW())
            ";
         $data = simple_query($sql);
         $new_id=mysqli_insert_id($datasource);
         
         header("Location: admin_inventory_system.php?cid=".$new_id."");
         die();
    }
    
    if(isset($_POST['save_cat']))
    {
         $useactive = 0;
         if(isset($_POST['active']))		$useactive = 1;
         
         $sql = "
                update inventory_cats set
                    inv_cat_name = '".sql_friendly(trim($_POST['inv_cat_name']))."',
                    inv_cat_code = '".sql_friendly(trim($_POST['inv_cat_code']))."',
                    active ='".sql_friendly($useactive)."'
                where id = '".$_GET['cid']."'
            ";
         simple_query($sql);
         
         header("Location: admin_inventory_system.php?cid=".$_GET['cid']."");
         die();
    }
    
    //manufacturers
    if(isset($_GET['delm']))
    {
         $sql = "
                update inventory_manu set
                    deleted = 1			
                where id = '".$_GET['delm']."'
            ";
         simple_query($sql);
         
         header("Location: admin_inventory_system.php?mid=0");
         die();
    }
    
    if(isset($_GET['newm']))
    {
         $sql="update inventory_manu set deleted=1 where manu_name='New Manufacturer'";
         mysqli_query($datasource, $sql);
         
         $sql = "
                insert into inventory_manu	
                    (manu_name,
                    manu_code,
                    active,
                    linedate_added)
                values 
                    ('New Manufacturer',
                    '',
                    1,
                    NOW())
            ";
         $data = simple_query($sql);
         $new_id=mysqli_insert_id($datasource);
         
         header("Location: admin_inventory_system.php?mid=".$new_id."");
         die();
    }
    
    if(isset($_POST['save_manu']))
    {
         $useactive = 0;
         if(isset($_POST['active']))		$useactive = 1;
         
         $sql = "
                update inventory_manu set
                    manu_name = '".sql_friendly(trim($_POST['manu_name']))."',
                    manu_code = '".sql_friendly(trim($_POST['manu_code']))."',
                    active ='".sql_friendly($useactive)."'
                where id = '".$_GET['mid']."'
            ";
         simple_query($sql);
         
         header("Location: admin_inventory_system.php?mid=".$_GET['mid']."");
         die();
    }
	
	

	if(!isset($_POST['sbox'])) 
	{
		$_POST['sbox'] = "";
		$sql_extra = "";
        $sql_extrav = "";
        $sql_extrac = "";
        $sql_extram = "";
	} 
	else 
	{
		$sql_extra = "and (inv_part_name like '%".$_POST['sbox']."%' or inv_part_code like '%".$_POST['sbox']."%' or inv_part_desc like '%".$_POST['sbox']."%')";
        $sql_extrav = "and (vendor_name like '%".$_POST['sbox']."%' or vendor_code like '%".$_POST['sbox']."%')";
        $sql_extrac = "and (inv_cat_name like '%".$_POST['sbox']."%' or inv_cat_code like '%".$_POST['sbox']."%')";
        $sql_extram = "and (manu_name like '%".$_POST['sbox']."%' or manu_code like '%".$_POST['sbox']."%')";
	}
	
	$mrr_activity_log_notes.="Viewed list of Inventory. ";


    //Category
    function mrr_inv_cat_box($field,$pre=0,$cd=0,$java="")
    {      
         $tab="<select name='".$field."' id='".$field."'".$java.">";
         
         $sel="";	if($pre==0) 	$sel=" selected";
         $tab.="<option value='0'".$sel.">None</option>";
         
         $sql = "select * from inventory_cats where deleted = 0 order by inv_cat_name asc, id asc";
         $data = simple_query($sql);
         while($row = mysqli_fetch_array($data))
         {
              $sel="";	if($pre==$row['id']) 	$sel=" selected";
              
              $label=trim($row['inv_cat_name']);
              if($cd==1)    $label=trim($row['inv_cat_code']);
              
              $tab.="<option value='".$row['id']."'".$sel.">".$label."</option>";
         }
         $tab.="</select>";
         return $tab;
    }
    function mrr_display_cat_val($id,$cd=0)
    {
         $name="";
         $sql = "select * from inventory_cats where id = '".(int) $id."'";
         $data = simple_query($sql);
         if($row = mysqli_fetch_array($data))
         {
             $name=$row['inv_cat_name'];
             if($cd==1)     $name=$row['inv_cat_code'];
         }
         return trim($name);
    }
    //Manufacturers
    function mrr_inv_manu_box($field,$pre=0,$cd=0,$java="")
    {
         $tab="<select name='".$field."' id='".$field."'".$java.">";
         
         $sel="";	if($pre==0) 	$sel=" selected";
         $tab.="<option value='0'".$sel.">None</option>";
         
         $sql = "select * from inventory_manu where deleted = 0 order by manu_name asc, id asc";
         $data = simple_query($sql);
         while($row = mysqli_fetch_array($data))
         {
              $sel="";	if($pre==$row['id']) 	$sel=" selected";
              
              $label=trim($row['manu_name']);
              if($cd==1)    $label=trim($row['manu_code']);
              
              $tab.="<option value='".$row['id']."'".$sel.">".$label."</option>";
         }
         $tab.="</select>";
         return $tab;
    }
    function mrr_display_manu_val($id,$cd=0)
    {
         $name="";
         $sql = "select * from inventory_manu where id = '".(int) $id."'";
         $data = simple_query($sql);
         if($row = mysqli_fetch_array($data))
         {
              $name=$row['manu_name'];
              if($cd==1)     $name=$row['manu_code'];
         }
         return trim($name);
    }
    //Vendors
    function mrr_inv_vendor_box($field,$pre=0,$cd=0,$java="")
    {
         $tab="<select name='".$field."' id='".$field."'".$java.">";
         
         $sel="";	if($pre==0) 	$sel=" selected";
         $tab.="<option value='0'".$sel.">None</option>";
         
         $sql = "select * from inventory_vendors where deleted = 0 order by vendor_name asc, id asc";
         $data = simple_query($sql);
         while($row = mysqli_fetch_array($data))
         {
              $sel="";	if($pre==$row['id']) 	$sel=" selected";
              
              $label=trim($row['vendor_name']);
              if($cd==1)    $label=trim($row['vendor_code']);
              
              $tab.="<option value='".$row['id']."'".$sel.">".$label."</option>";
         }
         $tab.="</select>";
         return $tab;
    }
    function mrr_display_vendor_val($id,$cd=0)
    {
         $name="";
         $sql = "select * from inventory_vendors where id = '".(int) $id."'";
         $data = simple_query($sql);
         if($row = mysqli_fetch_array($data))
         {
              $name=$row['vendor_name'];
              if($cd==1)     $name=$row['vendor_code'];
         }
         return trim($name);
    }
    //Parts
    function mrr_inv_parts_box($field,$pre=0,$cd=0,$java="")
    {
         $tab="<select name='".$field."' id='".$field."'".$java.">";
         
         $sel="";	if($pre==0) 	$sel=" selected";
         $tab.="<option value='0'".$sel.">None</option>";
         
         $sql = "select * from inventory_parts where deleted = 0 order by inv_part_name asc, id asc";
         $data = simple_query($sql);
         while($row = mysqli_fetch_array($data))
         {
              $sel="";	if($pre==$row['id']) 	$sel=" selected";
              
              $label=trim($row['inv_part_name']);
              if($cd==1)    $label=trim($row['inv_part_code']);
              
              $tab.="<option value='".$row['id']."'".$sel.">".$label."</option>";
         }
         $tab.="</select>";
         return $tab;
    }
    function mrr_display_parts_val($id,$cd=0)
    {
         $name="";
         $sql = "select * from inventory_parts where id = '".(int) $id."'";
         $data = simple_query($sql);
         if($row = mysqli_fetch_array($data))
         {
              $name=$row['inv_part_name'];
              if($cd==1)     $name=$row['inv_part_code'];
         }
         return trim($name);
    }

    //general list section....
    $sqlp = "select * from inventory_parts where deleted = 0 ".$sql_extra." order by inv_part_name asc, id asc";
    $datap = simple_query($sqlp);
    
    $sqlv = "select * from inventory_vendors where deleted = 0 ".$sql_extrav." order by vendor_name asc, id asc";
    $datav = simple_query($sqlv);
    $sqlc = "select * from inventory_cats where deleted = 0 ".$sql_extrac." order by inv_cat_name asc, id asc";
    $datac = simple_query($sqlc);
    $sqlmx = "select * from inventory_manu where deleted = 0 ".$sql_extram." order by manu_name asc, id asc";
    $datamx = simple_query($sqlmx);   
    
    
    //individual items selected queries...
    $sqlp2 = "select * from inventory_parts where id='".(int) $_GET['pid']."'";
    $datap2 = simple_query($sqlp2);
    $rowp2 = mysqli_fetch_array($datap2);
    
    $sqlv2 = "select * from inventory_vendors where id='".(int) $_GET['vid']."'";
    $datav2 = simple_query($sqlv2);
    $rowv2 = mysqli_fetch_array($datav2);
    
    $sqlc2 = "select * from inventory_cats where id='".(int) $_GET['cid']."'";
    $datac2 = simple_query($sqlc2);
    $rowc2 = mysqli_fetch_array($datac2);
    
    $sqlm2 = "select * from inventory_manu where id='".(int) $_GET['mid']."'";
    $datam2 = simple_query($sqlm2);
    $rowm2 = mysqli_fetch_array($datam2);
    
    $use_bootstrap = true;
?>
<? include('header.php') ?>
<div class='container col-md-12'>
	<div class='col-md-6'>
		<div class="panel panel-info">
			<div class="panel-heading"><?=$usetitle ?></div>
			<div class="panel-body">
					<form action="<?=$SCRIPT_NAME?>" method="post">			
					<table class='table table-bordered well'>
					<tr>				
						<td><input name="sbox" class='form-control' value="<?=$_POST['sbox']?>"></td>
						<td><button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-search"></span> Search</button></td>
						<td valign="top">
                            <a href='admin_inventory_system.php?newv=1'><button type='button' class='btn btn-success'><span class="glyphicon glyphicon-plus"></span> Vendor</button></a>
                            <a href='admin_inventory_system.php?newm=1'><button type='button' class='btn btn-success'><span class="glyphicon glyphicon-plus"></span> Manufacturer</button></a>
                            <a href='admin_inventory_system.php?newc=1'><button type='button' class='btn btn-success'><span class="glyphicon glyphicon-plus"></span> Category</button></a>
                            <a href='admin_inventory_system.php?newp=1'><button type='button' class='btn btn-success'><span class="glyphicon glyphicon-plus"></span> Inventory Part</button></a>
                        </td>
					</tr>
					</table>
					</form>
				

                <br><br>
                <b>Inventory Parts</b><br>
				<table class='table table-striped'>	
				<thead>	
          		<tr>
          			<th><b>ID</b></th>          			
          			<th><b>Part Name</b></th>
          			<th><b>Code</b></th>
                    <th><b>Desc</b></th>
                    <th><b>Vendor</b></th>
                    <th><b>Manu</b></th>
                    <th><b>Cat</b></th>
                    <th align="right"><b>Cost</b></th>
          			<th align="right"><b>Active</b></th>
          			<th>&nbsp;</th>
          		</tr>
          		</thead>
          		<tbody>
          		<? while($rowp = mysqli_fetch_array($datap)) { ?>                     
          			<tr>
          				<td><?=$rowp['id']?></td>
          				<td><a href="<?=$SCRIPT_NAME?>?pid=<?=$rowp['id']?>"><? if($rowp['active']==0) echo "<strike>"?><?=$rowp['inv_part_name']?><? if($rowp['active']==0) echo "</strike>"?></a></td>
          				<td><?=$rowp['inv_part_code']?></td>
          				<td><?=$rowp['inv_part_desc']?></td>
                        <td><?=mrr_display_vendor_val($rowp['vendor_id'],1)?></td>
                        <td><?=mrr_display_manu_val($rowp['manu_id'],1)?></td>
                        <td><?=mrr_display_cat_val($rowp['cat_id'],1)?></td>
                        <td align="right">$<?=number_format($rowp['cost'],2) ?></td>
          				<td align="right"><?= ($rowp['active']==0 ? "Inactive" : "Active")?></a></td>                        
          				<td>
          					<button onclick="confirm_delp(<?=$rowp['id']?>)" class='mrr_delete_access btn btn-danger btn-sm'><span class='glyphicon glyphicon-trash'></span></button>
          				</td>
          			</tr>
          		<? } ?>
          		</tbody>
          		</table>

                <br><br>
                <b>Inventory Vendors</b><br>
                <table class='table table-striped'>
                    <thead>
                    <tr>
                        <th><b>ID</b></th>
                        <th><b>Name</b></th>
                        <th><b>Code</b></th>
                        <th><b>Active</b></th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <? while($rowv = mysqli_fetch_array($datav)) { ?>
                        <tr>
                            <td><?=$rowv['id']?></td>
                            <td><a href="<?=$SCRIPT_NAME?>?vid=<?=$rowv['id']?>"><? if($rowv['active']==0) echo "<strike>"?><?=$rowv['vendor_name']?><? if($rowv['active']==0) echo "</strike>"?></a></td>
                            <td><?=$rowv['vendor_code']?></td>
                            <td><?= ($rowv['active']==0 ? "Inactive" : "Active")?></a></td>
                            <td>
                                <button onclick="confirm_delv(<?=$rowv['id']?>)" class='mrr_delete_access btn btn-danger btn-sm'><span class='glyphicon glyphicon-trash'></span></button>
                            </td>
                        </tr>
                    <? } ?>
                    </tbody>
                </table>

                <br><br>
                <b>Inventory Categories</b><br>
                <table class='table table-striped'>
                    <thead>
                    <tr>
                        <th><b>ID</b></th>
                        <th><b>Name</b></th>
                        <th><b>Code</b></th>
                        <th><b>Active</b></th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <? while($rowc = mysqli_fetch_array($datac)) { ?>
                        <tr>
                            <td><?=$rowc['id']?></td>
                            <td><a href="<?=$SCRIPT_NAME?>?cid=<?=$rowc['id']?>"><? if($rowc['active']==0) echo "<strike>"?><?=$rowc['inv_cat_name']?><? if($rowc['active']==0) echo "</strike>"?></a></td>
                            <td><?=$rowc['inv_cat_code']?></td>
                            <td><?= ($rowc['active']==0 ? "Inactive" : "Active")?></a></td>
                            <td>
                                <button onclick="confirm_delc(<?=$rowc['id']?>)" class='mrr_delete_access btn btn-danger btn-sm'><span class='glyphicon glyphicon-trash'></span></button>
                            </td>
                        </tr>
                    <? } ?>
                    </tbody>
                </table>

                <br><br>
                <b>Inventory Manufacturers</b><br>
                <table class='table table-striped'>
                    <thead>
                    <tr>
                        <th><b>ID</b></th>
                        <th><b>Name</b></th>
                        <th><b>Code</b></th>
                        <th><b>Active</b></th>
                        <th>&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <? while($rowmx = mysqli_fetch_array($datamx)) { ?>
                        <tr>
                            <td><?=$rowmx['id']?></td>
                            <td><a href="<?=$SCRIPT_NAME?>?mid=<?=$rowmx['id']?>"><? if($rowmx['active']==0) echo "<strike>"?><?=$rowmx['manu_name']?><? if($rowmx['active']==0) echo "</strike>"?></a></td>
                            <td><?=$rowmx['manu_code']?></td>
                            <td><?= ($rowmx['active']==0 ? "Inactive" : "Active")?></a></td>
                            <td>
                                <button onclick="confirm_delm(<?=$rowmx['id']?>)" class='mrr_delete_access btn btn-danger btn-sm'><span class='glyphicon glyphicon-trash'></span></button>
                            </td>
                        </tr>
                    <? } ?>
                    </tbody>
                </table>
                
                
                
                
			</div>
		</div>
	</div>
	<div class='col-md-6'>
         
        <? if(isset($_GET['pid']) && $_GET['pid'] > 0) { ?>

             <div class="panel panel-primary">
                 <div class="panel-heading">Edit Inventory Part: <?=$rowp2['inv_part_name']?></div>
                 <div class="panel-body">
                      <?
                      $mrr_activity_log_notes.="View Inventory Part ".$_GET['pid']." info. ";
                      
                      //$selbx=mrr_admin_email_list_box('cat_id',$rowp['cat_id'],"");
                      $selbxv=mrr_inv_vendor_box("vendor_id",$rowp2['vendor_id'],0,"");
                      $selbxm=mrr_inv_manu_box("manu_id",$rowp2['manu_id'],0,"");
                      $selbxc=mrr_inv_cat_box("cat_id",$rowp2['cat_id'],0,"");
                      ?>
                     <form action="<?=$SCRIPT_NAME?>?pid=<?=$_GET['pid']?>" method="post">
                         <table class='table table-bordered well'>
                             <tr>
                                 <td>Part Name:</td>
                                 <td><input name="inv_part_name" value="<?=$rowp2['inv_part_name']?>" class='form-control'></td>
                             </tr>
                             <tr>
                                 <td>Part No./Code:</td>
                                 <td><input name="inv_part_code" value="<?=$rowp2['inv_part_code']?>" class='form-control'></td>
                             </tr>
                             <tr>
                                 <td>Part Desc/Notes:</td>
                                 <td><input name="inv_part_desc" value="<?=$rowp2['inv_part_desc']?>" class='form-control'></td>
                             </tr>
                             <tr>
                                 <td>Vendor:</td>
                                 <td><?=$selbxv?></td>
                             </tr>
                             <tr>
                                 <td>Manufacturer:</td>
                                 <td><?=$selbxm?></td>
                             </tr>
                             <tr>
                                 <td>Category:</td>
                                 <td><?=$selbxc?></td>
                             </tr>
                             <tr>
                                 <td>Part Cost:</td>
                                 <td>$<input name="cost" value="<?=money_strip($rowp2['cost'])?>" style="text-align:right; padding:5px;" size="10"></td>
                             </tr>
                             <tr>
                                 <td><label for='active'>Active:</label></td>
                                 <td><input type="checkbox" name="active" id='active' <? if($rowp2['active']) echo "checked"?>></td>
                             </tr>
                         </table>
                         <p>
                             <button type='submit' class='btn btn-primary' name='save_part'><span class="glyphicon glyphicon-floppy-disk"></span> Update Inventory Part</button>
                         <div class='mrr_button_access_notice'>&nbsp;</div>
                         </p>
                     </form>
                 </div>
             </div>
        <? } ?>
		<? if(isset($_GET['vid']) && $_GET['vid'] > 0) { ?>
			
     		<div class="panel panel-primary">
     			<div class="panel-heading">Edit Vendor: <?=$rowv2['vendor_name']?></div>
     			<div class="panel-body">
     				<?
          			$mrr_activity_log_notes.="View Vendor ".$_GET['vid']." info. ";     			
          			
          			//$selbx=mrr_admin_email_list_box('cat_id',$rowv['cat_id'],"");    			
          			?>			
          			<form action="<?=$SCRIPT_NAME?>?vid=<?=$_GET['vid']?>" method="post">
               			<table class='table table-bordered well'>          				
               				<tr>
               					<td>Vendor Name:</td>
               					<td><input name="vendor_name" value="<?=$rowv2['vendor_name']?>" class='form-control'></td>
               				</tr>
               				<tr>
               					<td>Vendor Code:</td>
               					<td><input name="vendor_code" value="<?=$rowv2['vendor_code']?>" class='form-control'></td>
               				</tr>
               				<tr>
               					<td><label for='active'>Active:</label></td>
               					<td><input type="checkbox" name="active" id='active' <? if($rowv2['active']) echo "checked"?>></td>
               				</tr>               							
               			</table>               			
               			<p>
               				<button type='submit' class='btn btn-primary' name='save_vendor'><span class="glyphicon glyphicon-floppy-disk"></span> Update Vendor</button>
               				<div class='mrr_button_access_notice'>&nbsp;</div>
               			</p>               			
          			</form>			          			
     			</div>
     		</div>		
	    <? } ?>
        <? if(isset($_GET['cid']) && $_GET['cid'] > 0) { ?>

             <div class="panel panel-primary">
                 <div class="panel-heading">Edit Vendor: <?=$rowc2['inv_cat_name']?></div>
                 <div class="panel-body">
                      <?
                      $mrr_activity_log_notes.="View Inventory Cateogry ".$_GET['cid']." info. ";
                      
                      //$selbx=mrr_admin_email_list_box('cat_id',$rowc['cat_id'],"");    			
                      ?>
                     <form action="<?=$SCRIPT_NAME?>?cid=<?=$_GET['cid']?>" method="post">
                         <table class='table table-bordered well'>
                             <tr>
                                 <td>Category Name:</td>
                                 <td><input name="inv_cat_name" value="<?=$rowc2['inv_cat_name']?>" class='form-control'></td>
                             </tr>
                             <tr>
                                 <td>Category Code:</td>
                                 <td><input name="inv_cat_code" value="<?=$rowc2['inv_cat_code']?>" class='form-control'></td>
                             </tr>
                             <tr>
                                 <td><label for='active'>Active:</label></td>
                                 <td><input type="checkbox" name="active" id='active' <? if($rowc2['active']) echo "checked"?>></td>
                             </tr>
                         </table>
                         <p>
                             <button type='submit' class='btn btn-primary' name='save_cat'><span class="glyphicon glyphicon-floppy-disk"></span> Update Category</button>
                         <div class='mrr_button_access_notice'>&nbsp;</div>
                         </p>
                     </form>
                 </div>
             </div>
        <? } ?>
        <? if(isset($_GET['mid']) && $_GET['mid'] > 0) { ?>

             <div class="panel panel-primary">
                 <div class="panel-heading">Edit Manufacturer: <?=$rowm2['manu_name']?></div>
                 <div class="panel-body">
                      <?
                      $mrr_activity_log_notes.="View Inventory Manufacturer ".$_GET['mid']." info. ";
                      
                      //$selbx=mrr_admin_email_list_box('cat_id',$rowm['cat_id'],"");    			
                      ?>
                     <form action="<?=$SCRIPT_NAME?>?mid=<?=$_GET['mid']?>" method="post">
                         <table class='table table-bordered well'>
                             <tr>
                                 <td>Manufacturer Name:</td>
                                 <td><input name="manu_name" value="<?=$rowm2['manu_name']?>" class='form-control'></td>
                             </tr>
                             <tr>
                                 <td>Manufacturer Code:</td>
                                 <td><input name="manu_code" value="<?=$rowm2['manu_code']?>" class='form-control'></td>
                             </tr>
                             <tr>
                                 <td><label for='active'>Active:</label></td>
                                 <td><input type="checkbox" name="active" id='active' <? if($rowm2['active']) echo "checked"?>></td>
                             </tr>
                         </table>
                         <p>
                             <button type='submit' class='btn btn-primary' name='save_manu'><span class="glyphicon glyphicon-floppy-disk"></span> Update Manufacturer</button>
                         <div class='mrr_button_access_notice'>&nbsp;</div>
                         </p>
                     </form>
                 </div>
             </div>
        <? } ?>

    </div>
</div>
<? include('footer.php') ?>
<script type='text/javascript'>
    function confirm_delp(id)
    {
        if(confirm("Are you sure you want to delete this Inventory Part?")) {
            window.location = "<?=$SCRIPT_NAME?>?delp=" + id;
        }
    }
	function confirm_delv(id) 
	{
		if(confirm("Are you sure you want to delete this Vendor?")) {
			window.location = "<?=$SCRIPT_NAME?>?delv=" + id;
		}
	}
    function confirm_delc(id)
    {
        if(confirm("Are you sure you want to delete this Category?")) {
            window.location = "<?=$SCRIPT_NAME?>?delc=" + id;
        }
    }
    function confirm_delm(id)
    {
        if(confirm("Are you sure you want to delete this Manufacturer?")) {
            window.location = "<?=$SCRIPT_NAME?>?delm=" + id;
        }
    }
</script>
