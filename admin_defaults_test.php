<? include('application.php') ?>
<? $admin_page = 1; ?>
<? $use_bootstrap = true; ?>
<? $usetitle = "Admin Default Settings"; ?>
<? include('header_menus.php') ?>

<?
$rep_val="";
if(!isset($_POST['mrr_special_op_menu']))	$_POST['mrr_special_op_menu']=0;
if(isset($_POST['mrr_special_operation']))
{
     if($_POST['mrr_special_op_menu']==1)
     {
          $rep_val=mrr_super_repair_pc_miler_app();
     }
     if($_POST['mrr_special_op_menu']==2)
     {
          $rep_val=mrr_super_repair_scan_process();
     }
     
     //header("Location: admin_defaults.php#mrr_spec_ops");
     //die;			
}
else
{
     if(isset($_POST['idlist']))
     {
          foreach($_POST['idlist'] as $id)
          {
               if(isset($_POST['default_'.$id]))
               {
                    $save_value=trim($_POST['default_'.$id]);
                    
                    if((int) $id==202)
                    {
                         //if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br>Uploading File: Fun Box Image.";
                         
                         $target_dir = $defaultsarray['base_path']."www/images/";
                         if(isset($_FILES["fileToUpload"]["name"]))
                         {
                              //if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br>Uploading File ".basename($_FILES["fileToUpload"]["name"])."...";
                              
                              $target_file = $target_dir.str_replace(" ","_",basename($_FILES["fileToUpload"]["name"]));
                              $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
                              
                              $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                              
                              //if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br>New File will be '".$target_file."'... of type ".$imageFileType.".  Real Type = ".$check["mime"].".";
                              
                              if($check !== false)
                              {
                                   //$check["mime"]
                                   //if(!file_exists($target_file))
                                   //{
                                   //	
                                   //}
                                   //if($_FILES["fileToUpload"]["size"] > 500000)
                                   //{
                                   //	
                                   //}
                                   
                                   //if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br>Attempting to move '".$_FILES["fileToUpload"]["tmp_name"]."' to '".$target_file."'.";
                                   
                                   if(move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file))
                                   {
                                        $save_value=str_replace(" ","_",basename($_FILES["fileToUpload"]["name"]));
                                        
                                        //if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br>MOVED file '".$_FILES["fileToUpload"]["tmp_name"]."' to '".$target_file."'.  Simple Name is ".$save_value."";
                                        
                                        
                                        $sqla = "
          								insert into image_library 
          									(id,
          									linedate_added,
          									section_id,
          									image_name,
          									image_type,
          									deleted,
          									user_id)
          								values
          									(NULL,
          									NOW(),
          									4,
          									'".sql_friendly(trim($save_value))."',
          									'".sql_friendly(trim($imageFileType))."',
          									0,
          									'".sql_friendly($_SESSION['user_id'])."')
          							";
                                        simple_query($sqla);
                                        
                                        //if($_SERVER['REMOTE_ADDR'] == '70.90.229.29')		echo "<br>Library Insert Query: '".$sqla."'.";						
                                   }
                              }
                         }
                    }
                    
                    
                    $sql = "
     					update defaults set
     						xvalue_string = '".sql_friendly($save_value)."'     						
     					where id = '$id'
     				";
                    $data_update = mysql_query($sql,$datasource) or die("Database Query Failed! <br>". mysql_error() . "<pre>". $sql );
               }
          }
     }
     
     if(isset($_POST['fixed_expense_array']))
     {
          foreach($_POST['fixed_expense_array'] as $id)
          {
               $sql = "
     				update option_values set
     					fvalue = '".sql_friendly(money_strip($_POST['fixed_expense_'.$id]))."'
     				where id = '".sql_friendly($id)."'
     			";
               simple_query($sql);
          }
     }
}

//load our library images
$sqlx = "
		select *
		
		from image_library
		where deleted=0 and section_id=4
		order by image_name asc
	";
$datax = mysql_query($sqlx,$datasource) or die("Database Query Failed! <br>". mysql_error() . "<pre>". $sqlx );


// load our defaults
$sql = "
		select *
		
		from defaults
		where load_default = 1
		order by section, xname, xvalue_string
	";
$data = mysql_query($sql,$datasource) or die("Database Query Failed! <br>". mysql_error() . "<pre>". $sql );

// load our fixed expenses
$sql = "
		select option_values.*
		
		from option_values, option_cat
		where option_cat.id = option_values.cat_id
			and option_cat.cat_name = 'fixed_expenses'
			and option_values.deleted = 0
		order by option_values.fname
	";
$data_expenses = simple_query($sql);

?>
     
     <!---
     <a href='download_fuel_price.php' target='_blank'>[Click here to Update Fuel Surcharge]</a>
     --->
     <form action="<?=$SCRIPT_NAME?>" method="post" enctype="multipart/form-data">
          <div class='container col-md-12'>
               <div class='col-md-6'>
                    <div class="panel panel-primary">
                         <div class="panel-heading"><?=$usetitle ?></div>
                         <div class="panel-body">
                              <table class='table table-bordered well'>
                                   <?
                                   $current_section = '';
                                   while($row = mysqli_fetch_array($data))
                                   {
                                        if($current_section != $row['section'])
                                        {
                                             $current_section = $row['section'];
                                             
                                             echo "
               				<tr>
               					<td colspan='2'>
               					<h3 style='font-family:arial;padding:0;margin:0;text-decoration:underline;margin-top:10px'>$current_section</h3>
               					</td>
               				</tr>
               			";
                                        }
                                        ?>
                                        <tr>
                                             <td valign='top'>
                                                  <?
                                                  if($row['id']==202)
                                                  {
                                                       echo $row['display_name']." ". show_help('admin_defaults.php',$row['display_name']) ;
                                                       echo "
               				<br><b>Library:</b>
               				<div style='width:300px; height:200px; overflow:auto;'>
               			";
                                                       while($rowx = mysqli_fetch_array($datax))
                                                       {
                                                            echo "<br><span class='mrr_link_like_on' title='Click to reuse this image...' onClick='mrr_use_this_library_image(\"".$rowx['image_name']."\");'>".$rowx['image_name']."</span>";
                                                       }
                                                       echo "</div>";
                                                  }
                                                  elseif($row['display_name'] != '')
                                                  {
                                                       echo $row['display_name']." ". show_help('admin_defaults.php',$row['display_name']) ;
                                                  }
                                                  else
                                                  {
                                                       echo $row['xname']." ". show_help('admin_defaults.php',$row['display_name']) ;
                                                  }
                                                  
                                                  ?>
                                             </td>
                                             <td valign='top'>
                                                  <?
                                                  if($row['id']==202)
                                                  {	// || trim($row['xname'])=="fun_box_img"
                                                       echo "
               					<input name='default_$row[id]' id='default_$row[id]' value=\"$row[xvalue_string]\" size='80'>
               					<br>or Upload new file to Library: 
               					<input type='file' name='fileToUpload' id='fileToUpload'>
               				";
                                                  }
                                                  elseif($row['locked'])
                                                  {
                                                       echo $row['xvalue_string'];
                                                  }
                                                  else
                                                  {
                                                       echo "<input name='default_$row[id]' id='default_$row[id]' value=\"$row[xvalue_string]\" size='80'>";
                                                  }
                                                  ?>
                                                  <input type='hidden' name="idlist[]" value="<?=$row['id']?>">
                                             </td>
                                        </tr>
                                        <?
                                        if($row['title_notes']!='')
                                        {
                                             echo "
               				<tr>
               					<td valign='top' colspan='2'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; * ".$row['title_notes']."<br>&nbsp;</td>
               				</tr>			
               			";
                                        }
                                        ?>
                                   <? }?>
                              </table>
                              <p>
                                   <button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-floppy-disk"></span> Update</button>
                              </p>
                         </div>
                    </div>
               </div>
               <div class='col-md-6'>
                    <div class="panel panel-info">
                         <div class="panel-heading">Fixed Expenses</div>
                         <div class="panel-body">
                              <table class='table table-bordered well'>
                                   <tr>
                                        <td valign='top'><u>Fixed Expenses</u></td>
                                        <td valign='top'><u>Daily Cost Factors</u></td>
                                        <td valign='top'>
                                             Active Trucks: <?= show_help('admin_defaults.php','Current Active Trucks') ?> <b><?= get_active_truck_count() ?></b><br>
                                             
                                             Active Trailers: <?= show_help('admin_defaults.php','Current Active Trailers') ?> <b><?= get_active_trailer_count() ?></b><br>
                                             Daily Cost: <?= show_help('admin_defaults.php','Current Daily Cost') ?> <b>$<?= money_format('',get_daily_cost()) ?></b><br>
                                        </td>
                                   </tr>
                                   <?
                                   /*
                                   Excluded Trucks: <?= show_help('admin_defaults.php','Excluded Active Trucks') ?> <b><?= get_active_truck_count_excluded() ?></b><br>
                                   */
                                   $mrr_tot_expenses=0;
                                   while($row_expense = mysqli_fetch_array($data_expenses))
                                   {
                                        echo "
          			<tr>
          				<td>$row_expense[fname] ".show_help('admin_defaults.php',$row_expense['fname'])."</td>
          				<td>
          					<input name='fixed_expense_$row_expense[id]' value='$".money_format('',$row_expense['fvalue'])."' size='15' style='text-align:right'>
          					<input name='fixed_expense_array[]' value='$row_expense[id]' type='hidden'>
          				</td>
          			</tr>
          			";
                                        $mrr_tot_expenses+=$row_expense['fvalue'];
                                        //<input type="submit" value="Update">
                                   }
                                   ?>
                                   <tr>
                                        <td valign='top'>Total Expenses</td>
                                        <td valign='top' align='right'><b>$<?= money_format('',$mrr_tot_expenses) ?></b></td>
                                        <td valign='top'><button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-floppy-disk"></span> Update</button></td>
                                   </tr>
                                   <tr>
                                        <td><a name='mrr_spec_ops'><b>Special Operations</b></a><br><?=$rep_val ?><br> </td>
                                        <td valign='top'>
                                             <select name='mrr_special_op_menu'>
                                                  <option value='0' selected>Choose Operation</option>
                                                  <option value='1'>PC Miler Repair</option>
                                                  <option value='2'>Scan Processing Repair</option>
                                             </select>
                                        </td>
                                        <td valign='top'>
                                             <!--<input type="submit" name='mrr_special_operation' value="Run">-->
                                             <button type='submit' name='mrr_special_operation' class='btn btn-primary'><span class="glyphicon glyphicon-play"></span> Run</button>
                                        </td>
                                   </tr>
                              </table>
                         </div>
                    </div>
               </div>
          </div>
          <script type='text/javascript'>

              function mrr_use_this_library_image(namer)
              {
                  var txt=namer;
                  $('#default_202').val(txt);
              }
          </script>
     </form>
<? include('footer.php') ?>
<?
if($_POST['mrr_special_op_menu']==2)
{
     include('scan_process.php');
}
?>