<? include('application.php') ?>
<? $admin_page = 1; ?>
<? $use_bootstrap = true; ?>
<? $usetitle = "Admin Menu Controls - List"; ?>
<? include('header.php') ?>
<?
//Added by Michael...Sherrod Computers

$main_menus[0]="Logout";
$main_menus[1]="Home";
$main_menus[2]="Admin";
$main_menus[3]="Trucks";
$main_menus[4]="Trailers";
$main_menus[5]="Drivers";
$main_menus[6]="Customers";
$main_menus[7]="Maintenance";
$main_menus[8]="Reports";
//$main_menus[]="";
//Mini-Menu

$menu_count=count($main_menus);

$menu_msg="";

$trigger_sector=0;

if(isset($_POST['id']) && isset($_POST['page_label'])) 
{
     $sqlu = "
        update user_menu_pages set
        
            display_live='".sql_friendly($_POST['display_live'])."',
            top_level='".sql_friendly($_POST['top_level'])."',
            page_label='".sql_friendly($_POST['page_label'])."',
            display_order='".sql_friendly($_POST['display_order'])."',
            menu_separator='".sql_friendly($_POST['menu_separator'])."',
            access_level='".sql_friendly($_POST['access_level'])."'
            
        where id = '".sql_friendly($_POST['id'])."'
        
     ";
     simple_query($sqlu);
     $menu_msg="<span style='color:#00CC00;'><b>Menu Item ".$_POST['id']." has been Updated.</b></span><br>";
     $_GET['id']=$_POST['id'];
}

$sql = "
     select *		
     from user_menu_pages
     where deleted = 0
     order by top_level asc,display_order asc,page_label asc, id asc
";
$data = simple_query($sql);

if(!isset($_GET['id']))       $_GET['id']=0;
$_POST['id']=(int) $_GET['id'];

$_POST['display_live']=0;
$_POST['top_level']=0;
$_POST['page_label']="";
$_POST['page_name']="";
$_POST['display_order']=0;
$_POST['access_level']=0;
$_POST['linedate_added']="";
$_POST['special_viewer']=0;
$_POST['menu_separator']=0;

$last_menu=$main_menus[0];
?>
<form name='mrr-form' action="<?=$SCRIPT_NAME?>" method="post">
     <div class='container col-md-12'>
          <div class='col-md-6'>
               <div class="panel panel-primary">
                    <div class="panel-heading"><?=$usetitle ?></div>
                    <div class="panel-body">
                         <?php
                         echo "MENU: <span style='color:#0000cc; cursor:pointer;' onClick='mrr_toggle_menu_sectors(-1)'><b>Show All</b></span> &nbsp; &nbsp; &nbsp;";
                         for($i=1;$i < $menu_count; $i++)
                         {                             
                             echo "<span style='color:#0000cc; cursor:pointer;' onClick='mrr_toggle_menu_sectors(".$i.")'><b>".$main_menus[$i]."</b></span> &nbsp; &nbsp; &nbsp;";
                         }
                         ?>                       
                         <table class='table table-bordered well'>
                         <tr style='font-weight:bold;'>
                              <td>&nbsp;</td>
                              <td>Menu Section</td>
                              <td>Menu Item</td>
                              <td>Order</td>
                         </tr>
                         <?
                         $current_section = '';
                         while($row = mysqli_fetch_array($data))
                         {
                              if($last_menu!=$main_menus[ $row['top_level'] ])  echo "<tr class='sector_".$row['top_level']." all_sectors'> <td colspan='4'><hr></td> </tr>";                               
                              
                              if($row['menu_separator']==1)                          echo "<tr class='sector_".$row['top_level']." all_sectors'> <td colspan='4'>---<i>separator before</i>---</td> </tr>";
                                                            
                              echo "
                                    <tr style='".($row['display_live']==0 ? "text-decoration: line-through;" : "text-decoration: none;")."".($row['special_viewer'] > 0 ? "color: purple;" : "")."' class='sector_".$row['top_level']." all_sectors'>
                                        <td><a href='admin_menu_controls.php?id=".$row['id']."'>Edit</a></td>
                                        <td>".$main_menus[ $row['top_level'] ]."</td>
                                        <td>".$row['page_label']."</td>
                                        <td>".$row['display_order']."</td>  
                                    </tr>
                              ";
                              
                              if($row['menu_separator']==2)                          echo "<tr class='sector_".$row['top_level']." all_sectors'> <td colspan='4'>---<i>separator after</i>---</td> </tr>";
                              
                              if($_POST['id']==$row['id'])
                              {    //selected, so use it for the form filling
                                   $_POST['display_live']=$row['display_live'];
                                   $_POST['top_level']=$row['top_level'];
                                   $_POST['page_label']=$row['page_label'];
                                   $_POST['page_name']=$row['page_name'];
                                   $_POST['display_order']=$row['display_order'];
                                   $_POST['access_level']=$row['access_level'];
                                   $_POST['linedate_added']=date("m/d/Y H:i:s",strtotime($row['linedate_added']));
                                   $_POST['special_viewer']=$row['special_viewer'];
                                   $_POST['menu_separator']=$row['menu_separator'];
                              }
                              $last_menu=trim($main_menus[ $row['top_level'] ]);
                         }
                         ?>
                         </table>
                    </div>
               </div>
          </div>
          <div class='col-md-6'>
               <div class="panel panel-info">
                    <div class="panel-heading">Menu Item Control Settings</div>
                    <div class="panel-body">
                         <?
                         echo $menu_msg;
                         
                         if($_POST['id'] > 0)
                         {
                              $trigger_sector=$_POST['top_level'];
                              ?>
                              <input type="hidden" name="id" value="<?=$_POST['id'] ?>"> 
                              <table class='table table-bordered well'>
                                   <tr>
                                        <td valign="top">Item <?=$_POST['id'] ?></td>
                                        <td valign="top">File: <b><?=$_POST['page_name'] ?></b> created <b><?=$_POST['linedate_added'] ?></b></td>
                                   </tr>
                                   <tr>
                                        <td valign="top">Menu Item Label:</td>
                                        <td valign="top"><input type="text" name="page_label" id="page_label" value="<?=$_POST['page_label'] ?>" style="text-align:left; width:500px;"></td>
                                   </tr>
                                   <tr>
                                        <td valign="top">Access Level:</td>
                                        <td valign="top"><input type="text" name="access_level" id="access_level" value="<?=$_POST['access_level'] ?>" style="text-align:right; width:50px;"></td>
                                   </tr>
                                   <tr>
                                        <td valign="top">Menu Section:</td>
                                        <td valign="top">
                                             <select name="top_level" id="top_level">
                                                  <? for($i=0; $i< $menu_count; $i++) { ?>
                                                       <option value="<?= $i ?>"<?=($_POST['top_level']==$i ? " selected": "") ?>><?=$main_menus[ $i ] ?></option>
                                                  <? } ?>
                                             </select>
                                        </td>
                                   </tr>                                   
                                   <tr>
                                        <td valign="top">Display Order:</td>
                                        <td valign="top"><input type="text" name="display_order" id="display_order" value="<?=$_POST['display_order'] ?>" style="text-align:right; width:50px;"></td>
                                   </tr>                                   
                                   <tr>
                                        <td valign="top">Active:</td>
                                        <td valign="top">
                                             <select name="display_live" id="display_live">
                                                  <option value="0"<?=($_POST['display_live']==0 ? " selected": "") ?>>OFF</option>
                                                  <option value="1"<?=($_POST['display_live']==1 ? " selected": "") ?>>ON</option>
                                             </select>
     
                                             <?=($_POST['special_viewer'] > 0 ? "<span style='color: purple;'><b>FOR SELECT VIEWERS ONLY.</b></span>" : "") ?>
                                        </td>
                                   </tr>
                                   <tr>
                                        <td valign="top">Separation:</td>
                                        <td valign="top">
                                            <select name="menu_separator" id="menu_separator">
                                                <option value="0"<?=($_POST['menu_separator']==0 ? " selected": "") ?>>None</option>
                                                <option value="1"<?=($_POST['menu_separator']==1 ? " selected": "") ?>>Before</option>
                                                <option value="2"<?=($_POST['menu_separator']==2 ? " selected": "") ?>>After</option>
                                            </select>
                                        </td>
                                   </tr>
                              </table>
                              <p>
                                   <button type='submit' class='btn btn-primary'><span class="glyphicon glyphicon-floppy-disk"></span> Update</button>
                              </p>
                              <?
                         }
                         else
                         {
                              echo "Select Menu Item on Left side to edit.";
                         }
                         ?>                         
                    </div>
               </div>
          </div>
     </div>
</form>
                             
<script type='text/javascript'>

    //function mrr_use_this_library_image(namer)
    //{
        //var txt=namer;
        //$('#default_202').val(txt);
    //}
    
    function mrr_toggle_menu_sectors(sector)
    {
        $('.all_sectors').hide();
        if(parseInt(sector) < 0)   {   $('.all_sectors').show();      return;  }

        if(parseInt(sector) > 0)        $('.sector_'+sector+'').show();
    }

    mrr_toggle_menu_sectors(<?=$trigger_sector ?>);
</script>
</form>
<? include('footer.php') ?>