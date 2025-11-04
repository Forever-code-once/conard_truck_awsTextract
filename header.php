<? include_once("application.php") ?>
<?
//Added by Michael...Sherrod Computers
if(!isset($mrr_bypass_login))       $mrr_bypass_login=0;
if(substr_count($_SERVER['PHP_SELF'],"customer_loads.php")==0 && substr_count($_SERVER['PHP_SELF'],"logistics_trucking_email_form.php")==0 && $mrr_bypass_login==0 &&
     substr_count($query_string,"auto_save_trigger")==0 && substr_count($_SERVER['PHP_SELF'],"mrr_load_auto_saver.php")==0 && substr_count($_SERVER['PHP_SELF'],"report_maint_requests.php")==0)
{
     if((isset($admin_page) && !isset($_SESSION['admin'])) || (!isset($_SESSION['conard_trucking_logged_in']) && strpos(strtolower($SCRIPT_NAME), "login.php") == 0))
     {
          if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "iemobile") !== false || strpos(strtolower($_SERVER['HTTP_USER_AGENT']), "mobi/") !== false)
          {
               header("Location: m_login.php");
          }
          else
          {
               $id=0;
               if(isset($_COOKIE['uuid']))
               {
                    $id=mrr_update_session_cookie(0, '',$_COOKIE['uuid']);		//attempt to log in with cookie...  ID and session values are set by function...
                    //header("Location: index.php");
               }
               if($id==0)
               {
                    header("Location: login.php");
                    die();
               }
          }
     }
}

//Check if user is forced to log in again by "Forced Logout" flag.
if(isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0 && !isset($admin_page) || (isset($admin_page) && $admin_page==0) && substr_count($_SERVER['PHP_SELF'],"ajax.php")==0)
{
     $sqlx="
				select force_logout	
				from users				
				where id='".sql_friendly($_SESSION['user_id'])."'
			";
     $datax=simple_query($sqlx);
     $rowx=mysqli_fetch_array($datax);
     if($rowx['force_logout'] > 0)		$_SESSION['force_logout']=1;
}

if($_SESSION['force_logout'] > 0 && substr_count($_SERVER['PHP_SELF'],"login.php")==0)
{
     //user is about to be logged out, so clear the "Forced Logout" flag to prevent an infinite loop.  Session variable created in Appication file just in case.
     $sqlx = "
				update users set
					force_logout='0'
				where id='".sql_friendly($_SESSION['user_id'])."'
			";
     simple_query($sqlx);
     
     $_SESSION['force_logout']=0;
     
     header("Location: login.php?out=1");
     die();
}




$mrr_micro_seconds_start=time();

if(!isset($usetitle)) $usetitle = $defaultsarray['company_name'];

if(!isset($body_tag))
{
     $body_tag = "<body style='background-color:#F8F3E4'>";
}

if(isset($_GET['print']) || isset($_POST['print'])) 							$no_header = 1;
if(substr_count($_SERVER['PHP_SELF'],"customer_loads.php") > 0)					$no_header = 1;
if(substr_count($_SERVER['PHP_SELF'],"logistics_trucking_email_form.php") > 0)		$no_header = 1;

$new_style_path="images/2012/";
if(!isset($new_design))
{
     $new_design=2;
}

$mrr_special_view=1;
if(!isset($_SESSION['user_id']))		                $_SESSION['user_id']=0;
//if(mrr_special_admin_users($_SESSION['user_id'])==1)	$mrr_special_view=1;


                                                        //http://www.w3.org/TR/html4/strict.dtd
?>
<? if(!isset($no_html)) { ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"   "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
        <title><?=$usetitle?></title>
        <script type='text/javascript'>
            global_user_id = 0;
            global_ftype_array = "<?=$defaultsarray['valid_file_types']?>";
            <?
            if(isset($_SESSION['user_id'])) echo " global_user_id = $_SESSION[user_id]; ";
            ?>
        </script>
         <?
         //?dummytoken=<?=time()
         ?>
        <link rel="stylesheet" href="style.css" type="text/css" media="all">
        <link rel="stylesheet" href="includes/jquery.timeentry.css" type="text/css" media="all">
        <link rel="stylesheet" href="includes/jquery.notice.css" type="text/css" media="all">
        <link rel="stylesheet" href="includes/tablesort_theme/style.css" type="text/css">
        <link rel="stylesheet" href="colorpicker/colorPicker.css" type="text/css" />
        <link rel="stylesheet" href="includes/uploadify/uploadify.css" type="text/css" />
        <link rel="stylesheet" href="includes/jquery.tools.css" type="text/css" />
        <link rel="stylesheet" href="includes/jquery-autocomplete-ajax.css" type="text/css"></script>
        <link rel="icon"  type="image/png" href="/favicon.png">
         <?
         if($new_design>0)
         {
         ?>
         <link rel="stylesheet" href="images/2012/style.css" type="text/css">
             <link rel="stylesheet" href="images/2012/css3.css" type="text/css">
              <?
              }
              ?>
             <link rel="stylesheet" href="includes/css/ui-lightness/jquery-ui-1.8.21.custom.css" type="text/css">
             <script src="includes/jquery-1.3.2.min.js" language="JavaScript" type="text/javascript"></script>
         
         <? if(substr_count($SCRIPT_NAME,"admin_users.php") > 0 && $_SERVER['REMOTE_ADDR'] == '70.90.229.29' && 1==2) { ?>
             <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
         <? } else { ?>
         
         <? } ?>
         
         <? if(substr_count($SCRIPT_NAME,"admin_drivers.php") > 0 && $_SERVER['REMOTE_ADDR'] == '70.90.229.29' && 1==2) { ?>
             <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
         <? } else { ?>
         
         <? } ?>

        <script src="includes/jquery-ui-1.8.21.custom.min.js" language="JavaScript" type="text/javascript"></script>
        <script src="includes/jquery.tools.min.js" language="JavaScript" type="text/javascript"></script>
        <script type="text/javascript" src="includes/jquery.tablesorter.min.js"></script>
        <script src="includes/jquery-impromptu.3.1.js" language='JavaScript' type='text/javascript'></script>
        <script type="text/javascript" src="colorpicker/jquery.colorPicker.js"/></script>
         <?
         //?nocache=<?=time()
         ?>
         <script type="text/javascript" src="includes/functions.js"/></script>
        <script type="text/javascript" src="includes/functions_sicap.js?nocache=<?=time()?>"/></script>
        <script type="text/javascript" src="includes/uploadify/swfobject.js"/></script>
        <script type="text/javascript" src="includes/uploadify/jquery.uploadify.v2.1.0.min.js"/></script>
        <script type="text/javascript" src="includes/jquery.timeentry.min.js"/></script>
        <script src="includes/jquery-autocomplete-ajax.js" type="text/javascript"></script>
        <script src="includes/jquery.notice.js" type="text/javascript"></script>

        <script src="includes/jquery.qtip-1.0.0-rc3.min.js" type="text/javascript"></script>
         
         <? if(isset($use_bootstrap) && $use_bootstrap) { ?>
             <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" type="text/css">
             <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>

             <style>

             </style>
         <? } ?>
        <!-- uploader code -->
        <link href="includes/mini_upload/assets/css/style.css" rel="stylesheet" />
         <? if(substr_count($SCRIPT_NAME,"admin_drivers.php") > 0 && $_SERVER['REMOTE_ADDR'] == '70.90.229.29' && 1==2) { ?>

             <script src="includes/mini_upload/assets/js/jquery.knob.js"></script>
             <script src="includes/mini_upload/assets/js/jquery.ui.widget.js"></script>
             <script src="includes/mini_upload/assets/js/jquery.iframe-transport.js"></script>
             <script src="includes/mini_upload/assets/js/jquery.fileupload.js"></script>
             <script src="includes/mini_upload/assets/js/script.js"></script>
         <? } else { ?>
         
         <? } ?>
         <?
         if( substr_count($SCRIPT_NAME,"index.php") > 0 || substr_count($SCRIPT_NAME,"quote.php") > 0 || substr_count($SCRIPT_NAME,"prices_loves.php") > 0 || substr_count($SCRIPT_NAME,"peoplenet") > 0 || substr_count($SCRIPT_NAME,"report_unit_proximaty") > 0)
         {
              echo "<script type='text/javascript' src='//maps.googleapis.com/maps/api/js?key=".$defaultsarray['google_map_api_key']."&sensor=false'></script>";	//
              // or 
              //echo "<script type='text/javascript' src='https://www.google.com/jsapi'></script>";
              // or 
              //echo "<script src='https://maps.googleapis.com/maps/api/js?v=3&client=gme-".$defaultsarray['google_map_api_key']."&sensor=false&channel=ConardLogisticsTrucking' type='text/javascript'></script>";
              
              //echo "<script src='includes/jquery.marquee.js'></script>";
         }
         ?>
    </head>
<?=$body_tag ?>
    <script type='text/javascript'>

        //pre defined in "includes/functions.js" file...defaults set based on table instead.
        mrr_default_window_size_load_width=<?= $defaultsarray['window_size_load_width'] ?>;
        mrr_default_window_size_load_height=<?= $defaultsarray['window_size_load_height'] ?>;
        mrr_default_window_size_dispatch_width=<?= $defaultsarray['window_size_dispatch_width'] ?>;
        mrr_default_window_size_dispatch_height=<?= $defaultsarray['window_size_dispatch_height'] ?>;
        mrr_default_window_size_trailer_drop_width=<?= $defaultsarray['window_size_trailer_drop_width'] ?>;
        mrr_default_window_size_trailer_drop_height=<?= $defaultsarray['window_size_trailer_drop_height'] ?>;
        mrr_default_window_size_misc_width=<?= $defaultsarray['window_size_misc_width'] ?>;
        mrr_default_window_size_misc_height=<?= $defaultsarray['window_size_misc_height'] ?>;

        //used for lines like this below:
        //windowname = window.open('manage_load.php','edit_note_date','height='+mrr_default_window_size_load_height+',width='+mrr_default_window_size_load_width+',menubar=no,location=no,resizable=yes,status=no,scrollbars=yes');

    </script>

<?
$comp_logo = "/images/2012/logo2b.png";
?>

<? if(!isset($no_header))
{
if($new_design>0)
{
if(!isset($_SESSION['admin']))	$_SESSION['admin']=0;
if(!isset($_SESSION['user_id']))	$use_admin_level=$_SESSION['admin'];	else		$use_admin_level=mrr_get_user_access_level($_SESSION['user_id']);


$menu_items=0;
$menu_level[0]="";
$menu_arr[0]="";
$menu_pgs[0]="";
$menu_sep[0]=0;

$sqlm="
        select *
        from user_menu_pages				
        where deleted=0 
            and display_live>0
            and access_level<='".sql_friendly($use_admin_level)."'
        order by top_level asc,
            display_order asc,
            page_label asc        
";
$datam=simple_query($sqlm);
while($rowm=mysqli_fetch_array($datam))
{
     //capture all the menu items at once so they can be looped through as needed
     if($rowm['special_viewer'] ==0 || ($rowm['special_viewer'] > 0 && isset($_SESSION['user_id']) && $mrr_special_view > 0))
     {
          $menu_arr[$menu_items] = trim($rowm['page_label']);
          $menu_pgs[$menu_items] = trim($rowm['page_name']);
          $menu_level[$menu_items]=$rowm['top_level'];
          $menu_sep[$menu_items]=$rowm['menu_separator'];
          $menu_items++;
     }
}

$sep_bar="<li class='mrr_li'><hr style='clear:both;margin:5px; padding:0px;'></li>";
?>
    <div id="outer_header" style='position:fixed;top:0px;left:0px;margin-bottom:10px;z-index:10000;'>
        <div class="wrapper">
            <div class="header">
                <a href="index.php" class="logo"><img src="<?=$comp_logo ?>" alt="logo"></a>
                 <?
                 if($new_design!=3 && $use_admin_level > 0)
                 {
                      ?>
                     <div class="search_bar">
                         <form action="search_site.php" method='post'>
                             <input type="text" id='search_term' name='search_term' value="Search..." onClick='mrr_clear_search_box();'>
                             <input type='hidden' name='build_report' value='1'>
                             <!--<input type="submit" class="sub" value="search">-->
                             <input type="button" class="sub" value="search" onClick='mrr_run_ajax_search();'>
                         </form>
                     </div>
                      <?
                 }
                 ?>
                <div class="menu">
                    <ul>
                        <li><a href="login.php?out=1">Logout</a></li>
                        <li id='header_home'><?= ($use_admin_level >= mrr_get_user_menu_group_level('Home') ? "<a href='index.php'>Home </a>" : "<div style='width:72px;'>&nbsp;</div>") ?></li>
                        <li id='header_admin'><?= ($use_admin_level >= mrr_get_user_menu_group_level('Admin') ? "<a href='admin_defaults.php'>Admin </a>" : "<div style='width:74px;'>&nbsp;</div>") ?> </li>
                        <li id='header_trucks'><?= ($use_admin_level >= mrr_get_user_menu_group_level('Trucks') ? "<a href='admin_trucks.php'>Trucks</a>" : "<div style='width:78px;'>&nbsp;</div>") ?></li>
                        <li id='header_trailers'><?= ($use_admin_level >= mrr_get_user_menu_group_level('Trailers') ? "<a href='admin_trailers.php'> Trailers</a>" : "<div style='width:82px;'>&nbsp;</div>") ?> </li>
                        <li id='header_drivers'><?= ($use_admin_level >= mrr_get_user_menu_group_level('Drivers') ? "<a href='admin_drivers.php'>Drivers</a>" : "<div style='width:84px;'>&nbsp;</div>") ?></li>
                        <li id='header_customers'><?= ($use_admin_level >= mrr_get_user_menu_group_level('Customers') ? "<a href='admin_customers.php'>Customers </a>" : "<div style='width:108px;'>&nbsp;</div>") ?></li>
                        <li id='header_maint'><?= ($use_admin_level >= mrr_get_user_menu_group_level('Maintenance') ? "<a href='maint.php'>Maintenance</a>" : "<div style='width:120px;'>&nbsp;</div>") ?>  </li>
                        <li id='header_reports'><?= ($use_admin_level >= mrr_get_user_menu_group_level('Reports') ? "<a href='javascript:void(0)'> Reports</a>" : "<div style='width:90px;'>&nbsp;</div>") ?></li>
                        <li id='header_custom'><a href='admin_mini_menu.php'>Mini-Menu</a></li>
                    </ul>
                </div>

            </div>
        </div>
    </div><br>
    <div id='maint_holder'>
        <div class='maint_menu header_sub'>
            <ul class='popup_nav_ul' style='background-image:none;width:225px'>
                 <?
                 $cur_top_level=7;                       //Maintenance...
                 for($i=0;$i < $menu_items; $i++)
                 {
                      if($menu_level[$i]==$cur_top_level && $use_admin_level >= mrr_get_user_menu_access_level('/'.$menu_pgs[$i].''))
                      {
                           $use_blank="";
                           if(substr_count($menu_pgs[$i], "://") > 0)         $use_blank=" target='_blank'";
                           
                           if($menu_sep[$i]==1)           echo $sep_bar;
                           
                           echo "<li class='mrr_li'><a class='nav_popup_link' href='".$menu_pgs[$i]."'".$use_blank.">".$menu_arr[$i]."</a></li>";
                           
                           if($menu_sep[$i]==2)           echo $sep_bar;
                      }
                 }
                 ?>
                <li class='mrr_li'>&nbsp;</li>
            </ul>
        </div>
    </div>

    <div id='truck_holder'>
        <div class='truck_menu header_sub'>
            <ul class='popup_nav_ul' style='background-image:none;width:225px'>
                 <?
                 $cur_top_level=3;                       //Truck
                 for($i=0;$i < $menu_items; $i++)
                 {
                      if($menu_level[$i]==$cur_top_level && $use_admin_level >= mrr_get_user_menu_access_level('/'.$menu_pgs[$i].''))
                      {
                           $use_blank="";
                           if(substr_count($menu_pgs[$i], "://") > 0)         $use_blank=" target='_blank'";
                           
                           if($menu_sep[$i]==1)           echo $sep_bar;
                           
                           echo "<li class='mrr_li'><a class='nav_popup_link' href='".$menu_pgs[$i]."'".$use_blank.">".$menu_arr[$i]."</a></li>";
                           
                           if($menu_sep[$i]==2)           echo $sep_bar;
                      }
                 }
                 ?>
                <li class='mrr_li'>&nbsp;</li>
            </ul>
        </div>
    </div>

    <div id='trailer_holder'>
        <div class='trailer_menu header_sub'>
            <ul class='popup_nav_ul' style='background-image:none;width:225px'>
                 <?
                 $cur_top_level=4;                       //Trailers
                 for($i=0;$i < $menu_items; $i++)
                 {
                      if($menu_level[$i]==$cur_top_level && $use_admin_level >= mrr_get_user_menu_access_level('/'.$menu_pgs[$i].''))
                      {
                           $use_blank="";
                           if(substr_count($menu_pgs[$i], "://") > 0)         $use_blank=" target='_blank'";
                           
                           if($menu_sep[$i]==1)           echo $sep_bar;
                           
                           echo "<li class='mrr_li'><a class='nav_popup_link' href='".$menu_pgs[$i]."'".$use_blank.">".$menu_arr[$i]."</a></li>";
                           
                           if($menu_sep[$i]==2)           echo $sep_bar;
                      }
                 }
                 ?>
                <li class='mrr_li'>&nbsp;</li>
            </ul>
        </div>
    </div>

    <div id='report_holder'>
        <div class='report_menu header_sub'>
            <ul class='popup_nav_ul' style='background-image:none;width:225px'>
                 <?
                 $cur_top_level=8;                       //REPORTS...
                 for($i=0;$i < $menu_items; $i++)
                 {
                      if($menu_level[$i]==$cur_top_level && $use_admin_level >= mrr_get_user_menu_access_level('/'.$menu_pgs[$i].''))
                      {
                           $use_blank="";
                           if(substr_count($menu_pgs[$i], "://") > 0)         $use_blank=" target='_blank'";
                           
                           if($menu_sep[$i]==1)           echo $sep_bar;
                           
                           echo "<li class='mrr_li'><a class='nav_popup_link' href='".$menu_pgs[$i]."'".$use_blank.">".$menu_arr[$i]."</a></li>";
                           
                           if($menu_sep[$i]==2)           echo $sep_bar;
                      }
                 }
                 ?>
                <li class='mrr_li'>&nbsp;</li>
            </ul>
        </div>
    </div>

    <div id='drivers_holder'>
        <div class='drivers_menu header_sub'>
            <ul class='popup_nav_ul' style='background-image:none;width:225px'>
                 <?
                 $cur_top_level=5;                       //Driver...
                 for($i=0;$i < $menu_items; $i++)
                 {
                      if($menu_level[$i]==$cur_top_level && $use_admin_level >= mrr_get_user_menu_access_level('/'.$menu_pgs[$i].''))
                      {
                           $use_blank="";
                           if(substr_count($menu_pgs[$i], "://") > 0)         $use_blank=" target='_blank'";
                           
                           if($menu_sep[$i]==1)           echo $sep_bar;
                           
                           echo "<li class='mrr_li'><a class='nav_popup_link' href='".$menu_pgs[$i]."'".$use_blank.">".$menu_arr[$i]."</a></li>";
                           
                           if($menu_sep[$i]==2)           echo $sep_bar;
                      }
                 }
                 ?>
                <li class='mrr_li'>&nbsp;</li>
            </ul>
        </div>
    </div>
    <div id='admin_holder'>
        <div class='admin_menu header_sub'>
            <ul class='popup_nav_ul' style='background-image:none;width:225px'>
                 <?
                 $cur_top_level=2;                       //Admin...
                 for($i=0;$i < $menu_items; $i++)
                 {
                      if($menu_level[$i]==$cur_top_level && $use_admin_level >= mrr_get_user_menu_access_level('/'.$menu_pgs[$i].''))
                      {
                           $use_blank="";
                           if(substr_count($menu_pgs[$i], "://") > 0)         $use_blank=" target='_blank'";
                           
                           if($menu_sep[$i]==1)           echo $sep_bar;
                           
                           echo "<li class='mrr_li'><a class='nav_popup_link' href='".$menu_pgs[$i]."'".$use_blank.">".$menu_arr[$i]."</a></li>";
                           
                           if($menu_sep[$i]==2)           echo $sep_bar;
                      }
                 }
                 ?>
                <li>&nbsp;</li>
            </ul>
        </div>
    </div>

    <div id='home_holder'>
        <div class='home_menu header_sub'>
            <ul class='popup_nav_ul' style='background-image:none;width:225px'>
                 <?
                 $cur_top_level=1;                       //Home...
                 for($i=0;$i < $menu_items; $i++)
                 {
                      if($menu_level[$i]==$cur_top_level && $use_admin_level >= mrr_get_user_menu_access_level('/'.$menu_pgs[$i].''))
                      {
                           $use_blank="";
                           if(substr_count($menu_pgs[$i], "://") > 0)         $use_blank=" target='_blank'";
                           
                           if($menu_sep[$i]==1)           echo $sep_bar;
                           
                           echo "<li class='mrr_li'><a class='nav_popup_link' href='".$menu_pgs[$i]."'".$use_blank.">".$menu_arr[$i]."</a></li>";
                           
                           if($menu_sep[$i]==2)           echo $sep_bar;
                      }
                 }
                 ?>
                <li class='mrr_li'>&nbsp;</li>
            </ul>
        </div>
    </div>


    <div id='customers_holder' class='mrr_li'>
        <div class='customers_menu header_sub mrr_li'>
            <ul class='popup_nav_ul mrr_li' style='background-image:none;width:225px'>
                 <?
                 $cur_top_level=6;                       //Customer...
                 for($i=0;$i < $menu_items; $i++)
                 {
                      if($menu_level[$i]==$cur_top_level && $use_admin_level >= mrr_get_user_menu_access_level('/'.$menu_pgs[$i].''))
                      {
                           $use_blank="";
                           if(substr_count($menu_pgs[$i], "://") > 0)         $use_blank=" target='_blank'";
                           
                           if($menu_sep[$i]==1)           echo $sep_bar;
                           
                           echo "<li class='mrr_li'><a class='nav_popup_link' href='".$menu_pgs[$i]."'".$use_blank.">".$menu_arr[$i]."</a></li>";
                           
                           if($menu_sep[$i]==2)           echo $sep_bar;
                      }
                 }
                 ?>
                <li class='mrr_li'>&nbsp;</li>
            </ul>
        </div>
    </div>

    <div id='custom_holder'>
        <div class='custom_menu header_sub'>
            <ul class='popup_nav_ul' style='background-image:none;width:225px'>
                 <?
                 echo mrr_get_mini_menu_display(1);		//1=stripped down reults that are formatted for this menu bar.
                 ?>
                <li class='mrr_li'>&nbsp;</li>
            </ul>
        </div>
    </div>

    <div style='margin-bottom:30px'>&nbsp;</div>
    <script type='text/javascript'>
        function mrr_clear_search_box()
        {
            $('#search_term').val('');
        }

        function mrr_run_ajax_search()
        {
            st1=$('#search_term').val();
            txt="";	//"<center><a href='search_site.php?search_term="+st1+"' target='_blank'>Advanced Search</a></center><br>";

            $.ajax({
                type: "POST",
                url: "ajax.php?cmd=mrr_full_search",
                data: {
                    "search_term":st1,
                    "search_term2":'',
                    "search_term3":'',
                    "search_term4":'',
                    "search_term5":'',
                    "search_term6":'',
                    "search_term7":'',
                    "search_term8":''
                },
                dataType: "xml",
                cache:false,
                error: function() {
                    alert('general error running search feature.');
                },
                success: function(xml) {
                    newtab=$(xml).find('mrrTab').text();
                    if(newtab !="")
                    {
                        //$('#search_results').html(newtab);	                    					
                        full_form=""+txt+""+newtab+"";
                        display_nice_dialog_search(1300,700,'Search Results for '+st1,full_form,st1);
                        $('.tablesorter').tablesorter();
                    }
                    else
                    {
                        display_nice_dialog_search(800,700,'Search Results for '+st1,''+txt+'Sorry, no matches found for your term "<b>'+st1+'</b>".',st1);
                    }
                }
            });
        }

        var toolbar_offset=185;
        $().ready(function() {

            $('#header_trucks').append($('#truck_holder').html());
            $('#truck_holder').html('');

            $('#header_trailers').append($('#trailer_holder').html());
            $('#trailer_holder').html('');

            $('#header_reports').append($('#report_holder').html());
            $('#report_holder').html('');

            $('#header_maint').append($('#maint_holder').html());
            $('#maint_holder').html('');

            $('#header_admin').append($('#admin_holder').html());
            $('#admin_holder').html('');

            $('#header_home').append($('#home_holder').html());
            $('#home_holder').html('');

            $('#header_drivers').append($('#drivers_holder').html());
            $('#drivers_holder').html('');

            $('#header_customers').append($('#customers_holder').html());
            $('#customers_holder').html('');

            $('#header_custom').append($('#custom_holder').html());
            $('#custom_holder').html('');

            $('.nav_popup_link').hover(
                function() {
                    $(this).css('color','red');
                },
                function() {
                    $(this).css('color','white');
                }
            );

            $('.nav_popup_link').css('color','white');
            $('.nav_popup_link').css('font-size','12px');
            //$('.nav_popup_link').css('line-height','14px');
            //$('.nav_popup_link').css('height','12px');
            $('.nav_popup_link').css('margin-top','0');
            $('.nav_popup_link').css('margin-bottom','0');
            $('.nav_popup_link').css('padding-top','0');
            $('.nav_popup_link').css('padding-bottom','0');
            $('.nav_popup_link').css('font-weight','normal');

            $('#header_maint').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.maint_menu').css('left',parseInt(780 + toolbar_offset));
                    $('.maint_menu').css('top',40);
                    $('.maint_menu').css('background-color','black');
                    $('.maint_menu').show();
                },
                function() {
                    $('.maint_menu').hide();
                }
            );

            $('#header_trucks').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.truck_menu').css('left',parseInt(430 + toolbar_offset));
                    $('.truck_menu').css('top',40);
                    $('.truck_menu').css('background-color','black');
                    $('.truck_menu').show();
                },
                function() {
                    $('.truck_menu').hide();
                }
            );

            $('#header_trailers').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.trailer_menu').css('left',parseInt(508 + toolbar_offset));
                    $('.trailer_menu').css('top',40);
                    $('.trailer_menu').css('background-color','black');
                    $('.trailer_menu').show();
                },
                function() {
                    $('.trailer_menu').hide();
                }
            );

            $('#header_reports').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.report_menu').css('left',parseInt(900 + toolbar_offset));
                    $('.report_menu').css('top',40);
                    $('.report_menu').css('background-color','black');
                    $('.report_menu').show();
                },
                function() {
                    $('.report_menu').hide();
                }
            );

            $('#header_admin').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.admin_menu').css('left',parseInt(335 + toolbar_offset));
                    $('.admin_menu').css('top',40);
                    $('.admin_menu').css('background-color','black');
                    $('.admin_menu').show();
                },
                function() {
                    $('.admin_menu').hide();
                }
            );

            $('#header_home').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.home_menu').css('left',parseInt(285 + toolbar_offset));
                    $('.home_menu').css('top',40);
                    $('.home_menu').css('background-color','black');
                    $('.home_menu').show();
                },
                function() {
                    $('.home_menu').hide();
                }
            );

            $('#header_drivers').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.drivers_menu').css('left',parseInt(590 + toolbar_offset));
                    $('.drivers_menu').css('top',40);
                    $('.drivers_menu').css('background-color','black');
                    $('.drivers_menu').show();
                },
                function() {
                    $('.drivers_menu').hide();
                }
            );

            $('#header_customers').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.customers_menu').css('left',parseInt(675 + toolbar_offset));
                    $('.customers_menu').css('top',40);
                    $('.customers_menu').css('background-color','black');
                    $('.customers_menu').show();
                },
                function() {
                    $('.customers_menu').hide();
                }
            );


            $('#header_custom').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.custom_menu').css('left',parseInt(990 + toolbar_offset));
                    $('.custom_menu').css('top',40);
                    $('.custom_menu').css('background-color','black');
                    $('.custom_menu').show();
                },
                function() {
                    $('.custom_menu').hide();
                }
            );
        });

    </script>
<?
}
else
{
?>
    <table width='100%' cellspacing='0' cellpadding='0' style='position:fixed;top:0px;left:0px'>
        <tr>
            <td>
                <div id="vista_toolbar">
                    <ul>
                        <li><a href="login.php?out=1"><span><img style="margin:13px 0px 0 2px;" align="left" src="images/menu_logoff.png" alt="Log Out" title="Log Out"/>&nbsp;Log Out</span></a></li>
                        <li id='header_home'><a href="index.php"><span><img style="margin:13px 0px 0 2px;" align="left" src="images/menu_home.png" alt="Home" title="Home"/>&nbsp;Home</span></a></li>
                        <li id='header_admin'><a href="admin_defaults.php"><span><img style="margin:13px 0px 0 2px;" align="left" src="images/menu_system.png" alt="Defaults" title="Defaults"/>&nbsp;Admin</span></a></li>
                        <li><a href="admin_trucks.php"><span><img style="margin:13px 0px 0 2px;" align="left" src="images/menu_truck.png" alt="Trucks" title="Trucks"/>&nbsp;Trucks</span></a></li>
                        <li><a href="admin_trailers.php"><span><img style="margin:13px 0px 0 2px;" align="left" src="images/menu_departments.png" alt="Trailers" title="Trailers"/>&nbsp;Trailers</span></a></li>
                        <li id='header_drivers'><a href="admin_drivers.php"><span><img style="margin:13px 0px 0 2px;" align="left" src="images/menu_drivers.png" title="Admin Drivers" alt="Admin Drivers"/>&nbsp;Drivers</span></a></li>
                        <li id='header_customers'><a href="admin_customers.php"><span><img style="margin:13px 0px 0 2px;" align="left" src="images/menu_cust1.png" alt="Customers" title="Customers" />&nbsp;Customers</span></a></li>
                        <li id='header_maint'>
                            <a href="maint.php"><span><img style="margin:13px 0px 0 2px;height:24px;width:24px" align="left" src="images/maint1.png" alt="Maintenance" title="Maintenance" />&nbsp;Maintenance</span></a>
                        </li>
                        <li id='header_reports'>
                            <a href="javascript:void(0)">
                                <span><img style="margin:13px 0px 0 2px;" align="left" src="images/menu_reports.png" title="Reports" alt="Reports"/>&nbsp;Reports</span>
                            </a>
                        </li>
                        <div style='float:right;margin-right:10px;color:white'>Version <?=$defaultsarray['version']?></div>
                    </ul>

                </div>
            </td>
        </tr>
    </table>
    <div id='maint_holder'>
        <div class='maint_menu header_sub'>
            <ul class='popup_nav_ul' style='clear:both;background-image:none;line-height:20px;width:225px'>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='maint.php'>Maintenance Requests</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='maint_recur.php'>Recurring Requests</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='maint_recur_notices.php'>Maintenance Alerts</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_maint_requests.php'>Maintenance Reports</a></li>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='accident_trucks.php'>Accident Trucks</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_accident_trucks.php'>Accident Truck Reports</a></li>
            </ul>
        </div>
    </div>

    <div id='report_holder'>
        <div class='report_menu header_sub'>
            <ul class='popup_nav_ul' style='clear:both;background-image:none;line-height:20px;width:225px'>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_dispatch.php'>Dispatch history report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_dispatch_open.php'>Open Dispatch report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_available_loads.php'>Available loads report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_driver_expenses.php'>Driver Expense report</a></li>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_dropped_trailers.php'>Dropped Trailer Report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_trailer_location.php'>Trailer Location Report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_truck_location.php'>Truck Location Report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_canceled_loads.php'>Canceled Loads</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_credit_hold.php'>Credit Hold</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_customers_by_location.php'>Customers by Location</a></li>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_not_invoiced.php'>Not Invoiced</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_invoiced.php'>Invoiced Report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_edi_invoiced.php'>EDI Invoiced Report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_accounting_discrepancy.php'>Accounting Discrepancy Report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_payroll.php'>Payroll report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_payroll_by_truck.php'>Payroll report - By Truck</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_payroll_for_bills.php'>Payroll report - For Bills</a></li>
                 <?
                 if($defaultsarray['sicap_integration'] > 0) {
                      echo "
									<li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_comparison.php'>Comparison Report</a></li>
									<li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_comparison_scenarios.php'>Comparison - What If</a></li>
									<li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_comparison_archive.php'>Comparison - Archive</a></li>
									<li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_view_ar_details.php'>AR Details</a></li>
								";
                 }
                 ?>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_punch_clock_users.php'>Punch Clock Users</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_punch_clock_full.php'>Punch Clock Detailed - By User</a></li>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_missing_trip_packs.php'>Dispatches POD Report</a></li>
                <!---
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_trip_packs.php'>Trip Pack Report</a></li>
                --->
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_sales_by_load.php'>Sales Report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_sales.php'>Sales Report - By Dispatch</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_sales_by_truck.php'>Sales Report - by Truck</a></li>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_insurance.php'>Insurance Report</a></li>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_truck_odometer.php'>Truck Odometer Report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_truck_odometer_month.php'>Truck Month Odometer Report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_days_available.php'>Truck days report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_days_available_per_truck.php'>Truck days report - by Truck</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_truck_mileage_variance.php'>Truck Mileage Variance Report</a></li>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_quotes.php'>Quotes report</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_miles_not_match_pcm.php'>Manual Mileage Variance Report</a></li>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_scanned_loads.php'>Scanned Loads Report</a></li>
                <li><hr style='clear:both'></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_peoplenet_activity.php'>Hot Load Activity</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='report_graded_loads.php'>Graded Loads</a></li>
            </ul>
        </div>
    </div>

    <div id='admin_holder'>
        <div class='admin_menu header_sub'>
            <ul class='popup_nav_ul' style='clear:both;background-image:none;line-height:20px;width:225px'>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='admin_users.php'>Admin Users</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='admin_defaults.php'>Settings</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='admin_options.php'>Manage Option Lists</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='admin_punch_clock.php'>Punch Clock</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='admin_log_activity.php'>Log Activity</a></li>
                 <?
                 if(isset($_SESSION['admin']) && $_SESSION['admin'] > 95)
                 {
                      ?>
                     <!--	<li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='admin_budget.php'>Budget Settings</a></li> -->
                     <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='admin_budget_sections.php'>Budget Sections</a></li>
                     <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='payroll_employer_bridge.php'>Employer/Vendor Bridge</a></li>
                      <?
                 }
                 ?>
            </ul>
        </div>
    </div>

    <div id='home_holder'>
        <div class='home_menu header_sub'>
            <ul class='popup_nav_ul' style='clear:both;background-image:none;line-height:20px;width:225px'>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='index.php'>Load Board</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='javascript:new_load()'>New Load</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='quote.php'>New Quote</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='javascript:edit_dropped_trailer(0)'>New Drop Trailer</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='miler.php'>Estimate Miles</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='snap_shot.php'>Company Snapshot</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='search_form.php'>Search Notes/Files</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='search_site.php'>Search Site</a></li>
            </ul>
        </div>
    </div>

    <div id='drivers_holder'>
        <div class='drivers_menu header_sub'>
            <ul class='popup_nav_ul' style='clear:both;background-image:none;line-height:20px;width:225px'>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='admin_drivers.php'>Admin Drivers</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='driver_expense.php'>Add Driver Expense</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='driver_hourly_payroll.php'>Driver Hourly Payroll</a></li>
            </ul>
        </div>
    </div>

    <div id='customers_holder'>
        <div class='customers_menu header_sub'>
            <ul class='popup_nav_ul' style='clear:both;background-image:none;line-height:20px;width:225px'>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='admin_customers.php'>Admin Customers</a></li>
                <li><a style='clear:both;background:none;color:black' class='nav_popup_link' href='import_customers.php'>Import Customers</a></li>
            </ul>
        </div>
    </div>

    <div style='margin-bottom:30px'>&nbsp;</div>
    <script type='text/javascript'>

        $().ready(function() {

            $('#header_reports').append($('#report_holder').html());
            $('#report_holder').html('');

            $('#header_maint').append($('#maint_holder').html());
            $('#maint_holder').html('');

            $('#header_admin').append($('#admin_holder').html());
            $('#admin_holder').html('');

            $('#header_home').append($('#home_holder').html());
            $('#home_holder').html('');

            $('#header_drivers').append($('#drivers_holder').html());
            $('#drivers_holder').html('');

            $('#header_customers').append($('#customers_holder').html());
            $('#customers_holder').html('');

            $('.nav_popup_link').hover(
                function() {
                    $(this).css('color','red');
                },
                function() {
                    $(this).css('color','black');
                }
            );

            $('#header_maint').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.maint_menu').css('left',700);
                    $('.maint_menu').css('top',40);
                    $('.maint_menu').show();
                },
                function() {
                    $('.maint_menu').hide();
                }
            );

            $('#header_reports').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.report_menu').css('left',835);
                    $('.report_menu').css('top',40);
                    $('.report_menu').show();
                },
                function() {
                    $('.report_menu').hide();
                }
            );

            $('#header_admin').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.admin_menu').css('left',200);
                    $('.admin_menu').css('top',40);
                    $('.admin_menu').show();
                },
                function() {
                    $('.admin_menu').hide();
                }
            );

            $('#header_home').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.home_menu').css('left',100);
                    $('.home_menu').css('top',40);
                    $('.home_menu').show();
                },
                function() {
                    $('.home_menu').hide();
                }
            );

            $('#header_drivers').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.drivers_menu').css('left',480);
                    $('.drivers_menu').css('top',40);
                    $('.drivers_menu').show();
                },
                function() {
                    $('.drivers_menu').hide();
                }
            );

            $('#header_customers').hover(
                function() {
                    var position = $('#vista_toolbar').offset(); // for some reason, the offset is not returning the correct 'left' position
                    $('.customers_menu').css('left',580);
                    $('.customers_menu').css('top',40);
                    $('.customers_menu').show();
                },
                function() {
                    $('.customers_menu').hide();
                }
            );

        });
    </script>
     <?
}	//end else for new design.  All sub menus are going to be the same.		
     ?>
     
     <?
}
     ?>
<? } ?>

<? if(isset($_POST['print']) || isset($_GET['print'])) { ?>
    <script type='text/javascript'>
        $().ready(function() {
            $('.no_print').hide();
            window.print();
        });
    </script>
<? } ?>
<?
include_once("includes/fusioncharts/FC_Colors.php");
include_once("includes/fusioncharts/FusionCharts.php");
?>