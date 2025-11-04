<? include('application.php') ?>
<? $admin_page = 1 ?>
<? $use_bootstrap = true; ?>
<?
//Added by Michael...Sherrod Computers
$usetitle = "Special Equipment Notices";
$use_title = "Special Equipment Notices";

if(isset($_GET['did']))
{
     $sql = "
			update equipment_special_notices set 
			 deleted = 1
			where id = '".sql_friendly($_GET['did'])."'
		";
     simple_query($sql);
}     
     
$cur_id=0;
if(isset($_GET['id']))   $cur_id=(int) $_GET['id'];

if(isset($_POST['save_me']))
{
     $cur_id=(int) $_POST['id'];
     if($cur_id==0)
     {
          $sql="
               insert into equipment_special_notices
                (id,linedate_added,deleted)
                values 
                (NULL,NOW(),0)
          ";
          $data=simple_query($sql);
          $cur_id = mysqli_insert_id($datasource);
     }
         
     $sql="
            update equipment_special_notices set                
                active='".sql_friendly($_POST['active'])."',
                truck_id='".sql_friendly($_POST['truck_id'])."',
                trailer_id='".sql_friendly($_POST['trailer_id'])."',
                special_notice='".sql_friendly($_POST['special_notice'])."'
            where id='".$cur_id."'
          ";
     simple_query($sql);    
}

//get the truck list 
$sql = "
	select *
	from trucks
	where deleted = 0
	order by active desc,name_truck asc
";
$data_trucks = simple_query($sql);

// get the trailer list	
$sql = "
	select trailers.*
	from trailers
     where trailers.deleted = 0				
	order by active desc, trailers.trailer_name
";
$data_trailers = simple_query($sql);

$_POST['active']=0;
$_POST['truck_id']=0;
$_POST['trailer_id']=0;
$_POST['special_notice']="";

?>
<? include('header.php') ?>
<form action="<?=$SCRIPT_NAME?>" method="post">
     <div class='container col-md-12'>
          <div class='col-md-6'>
               <div class="panel panel-primary">
                    <div class="panel-heading"><?=$usetitle ?></div>
                    <div class="panel-body">
                         <table class='table table-bordered well'>
                              <tr>
                                   <td valign='top'>ID</td>
                                   <td valign='top'>Added</td>
                                   <td valign='top'>Active</td>
                                   <td valign='top'>Truck</td>
                                   <td valign='top'>Trailer</td>
                                   <td valign='top'>Notice</td>
                                   <td valign='top'>Remove</td>
                              </tr>
                              <?
                              $sql="
                                   select equipment_special_notices.*,
                                        (select trucks.name_truck from trucks where trucks.id=equipment_special_notices.truck_id) as truck_name,
                                        (select trailers.trailer_name from trailers where trailers.id=equipment_special_notices.trailer_id) as tname
                                   from equipment_special_notices
                                   where equipment_special_notices.deleted=0
                                   order by equipment_special_notices.active desc,
                                        equipment_special_notices.truck_id asc, 
                                        equipment_special_notices.trailer_id asc, 
                                        equipment_special_notices.id asc
                               ";
                              $data = simple_query($sql);
                              while($row = mysqli_fetch_array($data))
                              {
                                   if($cur_id == $row['id']) 
                                   {
                                        $_POST['active'] = $row['active'];
                                        $_POST['truck_id'] = $row['truck_id'];
                                        $_POST['trailer_id'] = $row['trailer_id'];
                                        $_POST['special_notice'] = trim($row['special_notice']);
                                   }
                                   ?>
                                   <tr>
                                        <td valign='top'><a href="<?=$SCRIPT_NAME?>?id=<?= $row['id'] ?>"><?= $row['id'] ?></a></td>
                                        <td valign='top'><?= $row['linedate_added']; ?></td>
                                        <td valign='top'><?= $row['active'] ?></td>
                                        <td valign='top'><?= $row['truck_name'] ?></td>
                                        <td valign='top'><?= $row['tname'] ?></td>
                                        <td valign='top'><?= trim($row['special_notice']) ?></td>
                                        <td valign='top'> <a href="javascript:confirm_delete(<?=$row['id']?>)" class='mrr_delete_access'><img src="images/delete_sm.gif" border="0"></a> </td>
                                   </tr>
                              <? }?>
                         </table>
                         <p>
                              <a href="<?=$SCRIPT_NAME?>?id=0">Add New Notice</a>
                         </p>
                    </div>
               </div>
          </div>
          <div class='col-md-6'>
               <div class="panel panel-info">
                    <div class="panel-heading">Edit Special Notice</div>
                    <div class="panel-body">
                         <table class='table table-bordered well'>
                              <tr>
                                   <td valign="top"><b>Editing Notice:</b></td>
                                   <td valign="top"><?=$cur_id ?> <input type="hidden" name="id" value="<?=$cur_id ?>"></td>
                              </tr>
                              <tr>
                                   <td valign="top"><b>Select Truck:</b></td>
                                   <td valign="top">
                                        <select name="truck_id">
                                             <option value="0">N/A</option>
                                             <? while($row_truck = mysqli_fetch_array($data_trucks)) { ?>
                                                  <option value="<?=$row_truck['id']?>" <? if($_POST['truck_id'] == $row_truck['id']) echo "selected"?> >
                                                       <?=$row_truck['name_truck']?> 
                                                  </option>
                                             <? } ?>
                                        </select>
                                   </td>
                              </tr>
                              <tr>
                                   <td valign="top"><b>Or Trailer:</b></td>
                                   <td valign="top">     
                                        <select name="trailer_id" id="id_trailer_id" class='standard12 payroll_lock_down'>
                                             <option value="0">N/A</option>
                                             <? while($row_trailers = mysqli_fetch_array($data_trailers)) { ?>
                                                  <?
                                                  if(trim($row_trailers['nick_name'])=="")		$row_trailers['nick_name']=trim($row_trailers['trailer_name']);
                                                  ?>
                                                  <option value="<?=$row_trailers['id']?>" <? if($_POST['trailer_id'] == $row_trailers['id']) echo "selected"?> >
                                                       <?=$row_trailers['nick_name']?>
                                                  </option>
                                             <? } ?>
                                        </select>                                        
                                   </td>
                              </tr>
                              <tr>
                                   <td valign="top"><b>Special Notice:</b></td>
                                   <td valign="top"><input type="text" name="special_notice" value="<?=trim($_POST['special_notice']) ?>" style="width:500px;"></td>
                              </tr>
                              <tr>
                                   <td valign="top"><b>Status:</b></td>
                                   <td valign="top">
                                        <select name='active'>
                                             <option value='0'<?=($_POST['active'] == 0 ? " selected" : "") ?>> OFF </option>
                                             <option value='1'<?=($_POST['active'] == 1 ? " selected" : "") ?>> ON </option>
                                        </select>
                                   </td>
                              </tr>
                              <tr>
                                   <td valign="top">&nbsp;</td>
                                   <td valign="top"><button type='submit' name="save_me" class='btn btn-primary'><span class="glyphicon glyphicon-floppy-disk"></span> Update</button></td>
                              </tr>
                              
                         </table>
                                                  
                    </div>
               </div>
          </div>
     </div>
<script type='text/javascript'>
    
    function confirm_delete(id) 
    {
        if(confirm("Are you sure you want to delete this Special Notice")) 
        {
            window.location = '<?=$SCRIPT_NAME?>?did=' + id;
        }
    }
</script>
<? include('footer.php') ?>
