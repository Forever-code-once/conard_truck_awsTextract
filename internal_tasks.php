<? include('application.php') ?>
<? $admin_page = 1; ?>
<? $use_bootstrap = true; ?>
<? $usetitle = "Internal Tasks"; ?>
<?
if(isset($_GET['connect_key']) && $_GET['connect_key'] == 'bas82bad98fqhbnwga8shq34908asdhbn')
{
     $mrr_bypass_login=1;   //If coming from the cronjob called, skip the login check.  Otherwise, require the login and do not bypass this.
     
     //get the next task to automatically run...
     $sql = "
		select id	
		from internal_tasks
		where deleted <= 0 and active>0
		order by last_run asc,id asc
	 ";
     $data = mysqli_query($datasource, $sql) or die("0. Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );
     if($row = mysqli_fetch_array($data))
     {
          $_GET['id']=$row['id'];
          //update this one so it cycles through the active ones.
          $sqlu = "
			    update internal_tasks set		
			        last_run=NOW()
			    where id = '".sql_friendly($_GET['id'])."'
		  ";
          simple_query($sqlu);
     }
}

if(isset($_GET['del_id']))
{
     $sql = "
			update internal_tasks set		
			    deleted = 1
			where id = '".sql_friendly($_GET['del_id'])."'
		";
     simple_query($sql);
     
     mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,0,0,0,"Deleted Internal Task ".$_GET['del_id'].".");	//change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
     
     header("Location: internal_tasks.php?edit_id=0");
     die;
}

$linked_task_id=0;
$linked_task=0;
if(isset($_GET['task']))            $linked_task=(int) $_GET['task'];

if(!isset($_GET['edit_id']))        $_GET['edit_id']=0;
if(!isset($_POST['edit_id']))       $_POST['edit_id']=0;

if((int) $_GET['edit_id'] > 0)      $_POST['edit_id']=(int) $_GET['edit_id'];
$linked_task_id=(int) $_GET['edit_id'];

if(isset($_POST['mrr_saver']))
{
     $edit_name=trim($_POST['task_name']);
     $edit_type=(int) $_POST['task_type'];
     $edit_active=(int) $_POST['active'];
     $edit_days=(int) $_POST['interval_days'];
     $days_before=(int) $_POST['show_days_before'];
     $edit_start="0000-00-00 00:00:00";
     $edit_due="0000-00-00 00:00:00";
     
     $edit_task_cntr=(int) $_POST['task_active_cntr'];
     
     if($_POST['linedate_due']=="" && $_POST['linedate_start']!="" && $edit_days > 0)
     {  //calculate the first due date here... if days are set and start date is also set.
          $_POST['linedate_due']=date("m/d/Y",strtotime("+".$edit_days." days",strtotime($_POST['linedate_start'])));    //." 00:00:00"
     }
     
     if($_POST['linedate_start']=="" && $_POST['linedate_due']!="")       $_POST['linedate_start']=$_POST['linedate_due'];
     if($_POST['linedate_due']=="" && $_POST['linedate_start']!="")       $_POST['linedate_due']=$_POST['linedate_start'];
     
     if($_POST['linedate_start']!="0000-00-00 00:00:00")      $edit_start=date("Y-m-d",strtotime($_POST['linedate_start']))." 00:00:00";
     if($_POST['linedate_due']!="0000-00-00 00:00:00")        $edit_due=date("Y-m-d",strtotime($_POST['linedate_due']))." 00:00:00";
               
     //$_POST['edit_id']=(int) $_GET['edit_id'];
     
     $is_new=0;
     if($_POST['edit_id']==0)
     {
          $sql = "
			insert into internal_tasks
			    (id,
			    linedate_added,
			    deleted,
			    created_by)
			values 
			    (NULL, 
			    NOW(),
			    0,
			    '".sql_friendly((int) $_SESSION['user_id'])."')
		";
        simple_query($sql);
        $_POST['edit_id']=mysqli_insert_id($datasource);
     
        $_GET['edit_id']=$_POST['edit_id'];
        $is_new=1;
        mrr_add_user_change_log($_SESSION['user_id'],0,0,0,0,0,0,0,"Added Internal Task ".$_POST['edit_id'].".");
     }
     
     //if($edit_type==0)      $edit_type=1;
     
     $sqlu = "
			update internal_tasks set			    
			    active='".sql_friendly($edit_active)."',
			    task_name='".sql_friendly($edit_name)."',
			    task_type='".sql_friendly($edit_type)."',
			    interval_days='".sql_friendly($edit_days)."',
			    show_days_before='".sql_friendly($days_before)."',
			    linedate_start='".$edit_start."',
			    linedate_due='".$edit_due."',
			    last_active_cntr='".$edit_task_cntr."'
			where id = '".sql_friendly($_POST['edit_id'])."'
		";
     simple_query($sqlu);
     
     if($is_new==0) 
     {
          mrr_add_user_change_log($_SESSION['user_id'], 0, 0, 0, 0, 0, 0, 0, "Updated Internal Task " . $_POST['edit_id'] . ".");    //change log tracking... $_SESSION['user_id'],$cust,$driver,$truck,$trailer,$load,$disp,$stop,$notes
     }
     
     header("Location: internal_tasks.php?edit_id=".$_POST['edit_id']."");
     die;
}
?>
<? include('header.php') ?>
<?
$task_types[0]="Other/Misc.";
$task_types[1]="Truck";
$task_types[2]="Trailer";
$task_types[3]="Driver";
$task_types[4]="Customer";
$task_types[5]="User";

$cur_user_name=trim($_SESSION['username']);
$cur_user_id=(int) $_SESSION['user_id'];
$cur_completed="".date("m/d/Y",time())."";

// load our main tasks
$sql = "
		select internal_tasks.*,
		    users.username	
		from internal_tasks
		    left join users on users.id=internal_tasks.created_by
		where internal_tasks.deleted <= 0
		order by internal_tasks.active desc, internal_tasks.task_name asc,internal_tasks.id asc
	";
$data = mysqli_query($datasource, $sql) or die("1. Database Query Failed! <br>". mysqli_error() . "<pre>". $sql );


$summary_display="";
// create the active entity records (active trucks, trailers, and drivers) 
$sql2 = "
		select internal_tasks.*
		from internal_tasks
		where internal_tasks.deleted <= 0
		    and internal_tasks.active > 0
		    and (internal_tasks.linedate_start <= NOW() or internal_tasks.linedate_due > NOW())
		order by internal_tasks.active desc, internal_tasks.task_name asc,internal_tasks.id asc
	";
$data2 = mysqli_query($datasource, $sql2) or die("2. Database Query Failed! <br>". mysqli_error() . "<pre>". $sql2 );
while($row2 = mysqli_fetch_array($data2))
{
     $id=$row2['id'];
     $name=trim($row2['task_name']);
     $type=$row2['task_type'];
     //$active=$row2['active'];
     $days=$row2['interval_days'];
     $days_before=$row2['show_days_before'];
     $first_due=date("Y-m-d",strtotime($row2['linedate_due'])). " 00:00:00";
     $start=date("Y-m-d",strtotime($row2['linedate_start']))." 00:00:00";
     $next=$start;
     if($days > 0)      $next=date("Y-m-d",strtotime("+".$days." days",strtotime($row2['linedate_start'])))." 00:00:00";     
          
     if($first_due!="0000-00-00 00:00:00" && $first_due > date("Y-m-d",time()). " 00:00:00")
     {    //the first due date has not yet happened, so use it to evaluate the show alert days...
          $show_now = $first_due;
          $next = $first_due;
          if($days_before > 0 && $next >= date("Y-m-d", time()) . " 00:00:00")
          {
               $show_now = date("Y-m-d", strtotime("-" . ($days_before) . " days", strtotime($row2['linedate_due']))) . " 00:00:00";
          }
          elseif($days_before > 0 && $next < date("Y-m-d", time()) . " 00:00:00")
          {
               $show_now = date("Y-m-d", strtotime($row2['linedate_due'])) . " 00:00:00";
          }
     }
     elseif($days > 0) 
     {
          $runs = 1;
          while($next < date("Y-m-d", time()) . " 00:00:00") 
          {
               $runs++;
               $next = date("Y-m-d", strtotime("+" . ($days * $runs) . " days", strtotime($row2['linedate_start']))) . " 00:00:00";
          }
          $show_now = $start;
          if($days_before > 0 && $next >= date("Y-m-d", time()) . " 00:00:00") 
          {
               $show_now = date("Y-m-d", strtotime("-" . ($days_before) . " days", strtotime($next))) . " 00:00:00";
          }
          elseif($days_before > 0 && $next < date("Y-m-d", time()) . " 00:00:00")
          {
               $show_now = date("Y-m-d", strtotime($next)) . " 00:00:00";
          }
     }
              
     $this_cntr=0;
     $summary_display.="
        <div style='color:purple; display:inline-block; margin:15px;'>
            <b><span onClick='mrr_toggle_task(".$id.");' style='color:#0000CC; cursor:pointer;'>+/-</span> ".$name." -- 
            <span onClick='mrr_toggle_task_completed(".$id.");' style='color:#0000CC; cursor:pointer;' title='Toggle the completed entries to show if hidden.'>Comp</span></b>
     ";
     
     $show_arr[0]=0;
     $show_cntr=0;
     
     
     if($type >= 0)
     {
         $table="";
         if($type==1)       $table="trucks";
         if($type==2)       $table="trailers";
         if($type==3)       $table="drivers";
         if($type==4)       $table="customers";
         if($type==5)       $table="users";
         
         $entities=1;
     
         if($type > 0) 
         {
              $sql3 = "
                   select id 
                   from " . $table . "
                   where deleted<=0 and active>0
                   order by id asc            
              ";
              $data3 = mysqli_query($datasource, $sql3) or die("3. Database Query Failed! <br>" . mysqli_error() . "<pre>" . $sql3);
              while($row3 = mysqli_fetch_array($data3)) 
              {
                   //echo "<br>".$entities.". Truck ID ".$row3['id']."... ";
                   $entities++;
          
                   $sql4 = "
                        select * 
                        from internal_tasks_checked 
                        where task_id='" . $id . "' and entity_id='" . $row3['id'] . "' 
                            and deleted=0
                        order by cur_date desc, id desc
                   ";     // and cur_date < NOW()
                   $data4 = mysqli_query($datasource, $sql4) or die("4. Database Query Failed! <br>" . mysqli_error() . "<pre>" . $sql4);
                   if($row4 = mysqli_fetch_array($data4)) 
                   {    //this entity (truck, trailer, or driver) already has a row here... so just check to see if a new line needs to be entered,
                        //but use the last date it had to make the new one with the entry's date from the entity itself, not the overall task.
               
                        //echo " Found it. ";       // ".$show_now."<br>
               
                        if($row4['user_id'] > 0 && date("Y-m-d", time() . " 00:00:00") >= $show_now && $days > 0) 
                        {  //this one has been completed, so make the next one.
                             $last_run = date("Y-m-d", strtotime($row4['cur_date'])) . " 00:00:00";
                             $next_run = date("Y-m-d", strtotime("+" . $days . " days", strtotime($row4['cur_date']))) . " 00:00:00";
                    
                             $sql5 = "
                                    select * 
                                    from internal_tasks_checked 
                                    where task_id='" . $id . "' and entity_id='" . $row3['id'] . "' 
                                        and deleted=0 and cur_date='" . $next_run . "' and done_date='0000-00-00 00:00:00'
                            ";     // and cur_date < NOW()
                             $data5 = mysqli_query($datasource, $sql5) or die("5a. Database Query Failed! <br>" . mysqli_error() . "<pre>" . $sql5);
                             if($row5 = mysqli_fetch_array($data5)) 
                             {
                                  //skip it, it is already there.
                                  
                             } 
                             else 
                             {   //not there, so add it.
                                  $sqli = "
                                    insert into internal_tasks_checked
                                        (id,task_id,linedate_added,deleted,user_id,entity_id,cur_date,done_date)
                                    values 
                                        (NULL,'" . sql_friendly($id) . "',NOW(),0,0,'" . sql_friendly($row3['id']) . "','" . $next_run . "','0000-00-00 00:00:00')    
                                  ";
                                  mysqli_query($datasource, $sqli);
                             }
                    
                             //echo " --ADDED. <br>---".$sqli."";                             
                        } 
                        elseif($row4['user_id'] == 0 && date("Y-m-d", time() . " 00:00:00") < $show_now) 
                        {
                             //skip... do not make one.  May have to remove all non-current ones that are also not completed.
                             $sqlu = "
                                    update internal_tasks_checked set 
                                        deleted=1                           
                                    where id='" . $row4['id'] . "' and user_id='0' 
                                        and entity_id='" . sql_friendly($row3['id']) . "' and task_id='" . sql_friendly($id) . "'
                                        and cur_date > '".$next_run."'
                             ";
                             //mysql_query($sqlu, $datasource);
                             $this_cntr++;                              //  It is still active and should be marked as one.
                        } 
                        else 
                        {
                             $this_cntr++;
                             //also, clear out any later ones... since this one isn't done yet.
                             $sqlu = "
                                    update internal_tasks_checked set 
                                        deleted=1                           
                                    where id!='" . $row4['id'] . "' and user_id='0' and entity_id='" . sql_friendly($row3['id']) . "' and task_id='" . sql_friendly($id) . "'
                             ";
                             mysqli_query($datasource, $sqlu);
                        }
                   } 
                   elseif(date("Y-m-d", time()) . " 00:00:00" >= $show_now) 
                   {   //no entry yet, so add one here.
                        $sqli = "
                            insert into internal_tasks_checked
                                (id,task_id,linedate_added,deleted,user_id,entity_id,cur_date,done_date)
                            values 
                                (NULL,'" . sql_friendly($id) . "',NOW(),0,0,'" . sql_friendly($row3['id']) . "','" . $next . "','0000-00-00 00:00:00')    
                        ";
                        mysqli_query($datasource, $sqli);
               
                        //echo " Not Found. Added. <br>---".$sqli."";
                        $this_cntr++;
                   }
              }
              $sqlu="update internal_tasks set last_active_cntr='".(int) $this_cntr."' where id='" . sql_friendly($id) . "'";
              mysqli_query($datasource, $sqlu);
         }
         else
         {    //this is the generic one-and-done task that is NOT linked to any entity.              
              $sql4 = "
                    select * 
                    from internal_tasks_checked 
                    where task_id='" . $id . "' and entity_id='1' and deleted=0
              ";     // and cur_date < NOW()
              $data4 = mysqli_query($datasource, $sql4) or die("4b. Database Query Failed! <br>" . mysqli_error() . "<pre>" . $sql4);
              //echo " MISC. <br>---".$sql4."";
              if($row4 = mysqli_fetch_array($data4)) 
              {
                   if($row4['user_id'] > 0 && date("Y-m-d", time() . " 00:00:00") >= $show_now && $days > 0)
                   {  //this one has been completed, so make the next one.
                        $last_run = date("Y-m-d", strtotime($row4['cur_date'])) . " 00:00:00";
                        $next_run = date("Y-m-d", strtotime("+" . $days . " days", strtotime($row4['cur_date']))) . " 00:00:00";
     
                        //echo "<br>---Found Misc. DONE.";
          
                        $sql5 = "
                                    select * 
                                    from internal_tasks_checked 
                                    where task_id='" . $id . "' and entity_id='1' and deleted=0 and cur_date='" . $next_run . "' and done_date='0000-00-00 00:00:00'
                            ";     // and cur_date < NOW()
                        $data5 = mysqli_query($datasource, $sql5) or die("5b. Database Query Failed! <br>" . mysqli_error() . "<pre>" . $sql5);
                        if($row5 = mysqli_fetch_array($data5))
                        {
                             //skip it, it is already there.
                             //echo "<br>---N/A";
                             
                             //let's see if it is too early... so we cna hide it.
                             
                             if($days_before > 0)
                             {
                                 $only_show_on=date("Y-m-d", strtotime("-" . $days_before . " days", strtotime($next_run))) . " 00:00:00";
                                 
                                 if(date("Y-m-d", time() . " 00:00:00") < $only_show_on)
                                 {    //not time to show it yet, so hide it form the active count.
                                      $sqlu="update internal_tasks set last_active_cntr='0' where id='".sql_friendly($id)."'";
                                      mysqli_query($datasource, $sqlu);
     
                                      $summary_display.="...0 Active ".$task_types[ $type ]."";
                                 }
                                 else
                                 {    //show it, so make sure the count is 1  
                                      $sqlu="update internal_tasks set last_active_cntr='1' where id='".sql_friendly($id)."'";
                                      mysqli_query($datasource, $sqlu);
     
                                      $summary_display.="...1 Active ".$task_types[ $type ]."";
                                 }                                 
                             }
                        }
                        else
                        {   //not there, so add it.
                             $sqli = "
                                    insert into internal_tasks_checked
                                        (id,task_id,linedate_added,deleted,user_id,entity_id,cur_date,done_date)
                                    values 
                                        (NULL,'" . sql_friendly($id) . "',NOW(),0,0,'1','" . $next_run . "','0000-00-00 00:00:00')    
                                  ";
                             mysqli_query($datasource, $sqli);
                             //echo "<br>---Found Misc. Added 1.";
     
                             $sqlu="update internal_tasks set last_active_cntr='1' where id='".sql_friendly($id)."'";
                             mysqli_query($datasource, $sqli);
     
                             $summary_display.="...1 Active ".$task_types[ $type ]."";
                        }
                   }
                   elseif($row4['user_id'] == 0 && date("Y-m-d", time() . " 00:00:00") < $show_now)
                   {
                        //skip... do not make one.  May have to remove the non-current one that are also not completed.
                        $sqlu = "
                                    update internal_tasks_checked set 
                                        deleted=1                           
                                    where id='" . $row4['id'] . "' and user_id='0' and entity_id='1' and task_id='" . sql_friendly($id) . "'
                             ";                        
                        //mysql_query($sqlu, $datasource);
                        //echo "<br>---Found Misc. Not Done...remove too early.";   $days_before
     
                        $summary_display.="...1 Active ".$task_types[ $type ]."";
                   }
                   else
                   {
                        //$this_cntr++;
                        //also, clear out the later one... since this one isn't done yet.
                        $sqlu = "
                                    update internal_tasks_checked set 
                                        deleted=1                           
                                    where id!='" . $row4['id'] . "' and user_id='0' and entity_id='1' and task_id='" . sql_friendly($id) . "'
                             ";
                        //mysql_query($sqlu, $datasource);
                        //echo "<br>---Found Misc. Removed sicne should be there either.";
     
                        //$sqlu="update internal_tasks set last_active_cntr='0' where id='".sql_friendly($id)."'";
                        //mysql_query($sqlu,$datasource);
     
                        $summary_display.="...0 Active ".$task_types[ $type ]."";
                   }
              }
              elseif(date("Y-m-d", time()) . " 00:00:00" >= $show_now)
              {   //no entry yet, so add one here.
                   $sqli = "
                        insert into internal_tasks_checked
                            (id,task_id,linedate_added,deleted,user_id,entity_id,cur_date,done_date)
                        values 
                            (NULL,'" . sql_friendly($id) . "',NOW(),0,0,'1','" . $next . "','0000-00-00 00:00:00')    
                   ";
                   mysqli_query($datasource, $sqli);
                   $this_cntr++;
                   //echo "<br>---NOT Found Misc. Added 1.";
     
                   $sqlu="update internal_tasks set last_active_cntr='".(int) $this_cntr."' where id='".sql_friendly($id)."'";
                   mysqli_query($datasource, $sqlu);
     
                   $summary_display.="...1 Active ".$task_types[ $type ]."";
              }
              
         }
     
         $mrr_show_active_cntr=0;
         $active_cntr=0;
         $sql5="
                select id,entity_id,cur_date
                from internal_tasks_checked 
                where task_id='".$id."' and user_id=0
                    and deleted=0
                order by cur_date asc,entity_id asc,id asc    
         ";     // and cur_date < NOW()
         $data5 = mysqli_query($datasource, $sql5) or die("5. Database Query Failed! <br>". mysqli_error() . "<pre>". $sql5 );
         while($row5 = mysqli_fetch_array($data5))
         {
             $founder=0;
             for($z=0; $z < $show_cntr; $z++)
             {
                 if($show_arr[$z] == $row5['entity_id'])    $founder=1;
             }
             
             if($founder==0)
             {
                  $show_arr[$show_cntr]=$row5['entity_id'];
                  $show_cntr++;
     
                  $active_cntr++;       //only count each unit's active line once... not each time.
                  
                  if(date("Y-m-d",strtotime("-".$days." days",strtotime($row5['cur_date']))) < date("Y-m-d",time()) || $days==0)
                  {    //$days_before
                       $mrr_show_active_cntr++;                       
                  }                  
             }
             
             //$active_cntr++;
             //$mrr_show_active_cntr++;       
         }
     
         //$mrr_show_active_cntr=$active_cntr;
     
          if($type >= 0)      $summary_display.="...".$mrr_show_active_cntr." Active ".$task_types[ $type ]."s";
         //
         $sqlu="update internal_tasks set last_active_cntr='".(int) $mrr_show_active_cntr."' where id='".sql_friendly($id)."'";
         if($type >= 0)      mysqli_query($datasource, $sqlu); 
     }          
     $summary_display.="</div>";
}

//get to display each record that does (not) have an active task... for each main task.
$sqlx = "
		select internal_tasks_checked.*,
		    internal_tasks.task_name,
		    internal_tasks.task_type,
		    internal_tasks.interval_days,
		    (select name_truck from trucks where trucks.id=internal_tasks_checked.entity_id) as truck_name,
		    (select trailer_name from trailers where trailers.id=internal_tasks_checked.entity_id) as trailer_name,
		    (select CONCAT(name_driver_first,' ',name_driver_last) from drivers where drivers.id=internal_tasks_checked.entity_id) as driver_name,
		    (select customers.name_company from customers where customers.id=internal_tasks_checked.entity_id) as cust_name,
		    (select CONCAT(us.name_first,' ',us.name_last) from users us where us.id=internal_tasks_checked.entity_id) as user_name,
		    users.username	
		from internal_tasks_checked
		    left join internal_tasks on internal_tasks.id=internal_tasks_checked.task_id
		    left join users on users.id=internal_tasks_checked.user_id
		where internal_tasks.deleted <= 0 and internal_tasks_checked.deleted<=0
		    and internal_tasks.active > 0
		    and (internal_tasks.linedate_start <= NOW() or internal_tasks.linedate_due > NOW())
		order by internal_tasks.task_name asc,
		    internal_tasks_checked.entity_id asc,
		    internal_tasks_checked.cur_date asc,
		    
		    internal_tasks_checked.id asc
	";
$datax = mysqli_query($datasource, $sqlx) or die("B. Database Query Failed! <br>". mysqli_error() . "<pre>". $sqlx );

// enctype="multipart/form-data"
?>
    <form action="<?=$SCRIPT_NAME?>" method="post">
        <div class='container col-md-12'>
            <div class='col-md-6'>
                <div class="panel panel-primary">
                    <div class="panel-heading"><?=$usetitle ?> Check</div>
                    <div class="panel-body">
                        <p><?=$summary_display ?></p>
                        <table class='table table-bordered well'>
                            <tr>
                                <td valign='top'><b>Task</b></td>
                                <td valign='top'><b>Type</b></td>
                                <td valign='top'><b>Entity</b></td>
                                <td valign='top'><b>Due Date</b></td>                                
                                <td valign='top'><b>Checked by</b></td>
                                <td valign='top'><b>Completed</b></td>
                                <td valign='top'><b>&nbsp;</b></td>
                                <td valign='top'><b>&nbsp;</b></td>
                            </tr>
                             <?
                             $cur_task_id=0;
                             $show_arr[0]=0;
                             $show_cntr=0;
                             $not_done_yet=0;
                             
                             $cntr=0;
                             while($rowx = mysqli_fetch_array($datax))
                             {
                                 if($rowx['task_id'] != $cur_task_id || ($not_done_yet>0 && $rowx['user_id']==0))
                                 {
                                      if($_GET['edit_id'] ==  $cur_task_id)
                                      {
                                           $sqlu="update internal_tasks set last_active_cntr='".(int) $show_cntr."' where id='".sql_friendly($cur_task_id)."'";
                                           //if($cur_task_id > 0)   mysql_query($sqlu,$datasource);
                                      }                                     
     
                                      $show_arr[0]=0;
                                      $show_cntr=0;
                                 }
                                 $cur_task_id=$rowx['task_id'];
                                 $not_done_yet=$rowx['user_id'];
                                 
                                 
                                 $done_by="";
                                 $done_on="";
                                 if($rowx['user_id'] > 0) 
                                 {
                                     $done_by=trim($rowx['username']); 
                                     $done_on="".date("m/d/Y",strtotime($rowx['done_date']))."";
                                 }
                                 $cur_date_col="#000000;";
                                 if(date("Y-m-d",strtotime("-".$rowx['interval_days']." days",strtotime($rowx['cur_date']))) > date("Y-m-d",time()))
                                 {
                                      if($rowx['interval_days'] > 0)        $cur_date_col="#00CC00;";
                                 }
                                 
                                 $entity_name="<b>N/A</b>";
                                 if($rowx['task_type']==1)  $entity_name="<a href='admin_trucks.php?id=".$rowx['entity_id']."' target='_blank'>".trim($rowx['truck_name'])."</a>";
                                 if($rowx['task_type']==2)  $entity_name="<a href='admin_trailers.php?id=".$rowx['entity_id']."' target='_blank'>".trim($rowx['trailer_name'])."</a>";
                                 if($rowx['task_type']==3)  $entity_name="<a href='admin_drivers.php?id=".$rowx['entity_id']."' target='_blank'>".trim($rowx['driver_name'])."</a>";
                                 if($rowx['task_type']==4)  $entity_name="<a href='admin_customers.php?eid=".$rowx['entity_id']."' target='_blank'>".trim($rowx['cust_name'])."</a>";
                                 if($rowx['task_type']==5)  $entity_name="<b>".trim($rowx['user_name'])."</b>";   //<a href='admin_users.php?eid=".$rowx['entity_id']."' target='_blank'>".trim($rowx['user_name'])."</a>
     
     
                                  $founder=0;
                                  for($z=0; $z < $show_cntr; $z++)
                                  {
                                       if($show_arr[$z] == $rowx['entity_id'])    $founder=1;
                                  }
     
                                  if($founder==0)
                                  {
                                       $show_arr[$show_cntr]=$rowx['entity_id'];
                                       $show_cntr++;
          
                                       $active_cntr++;       //only count each unit's active line once... not each time.
                                  }
                                  
                                  $too_early=0;
                                  if($cur_date_col=="#00CC00;")
                                  {
                                       $too_early=1;
                                  }
     
                                  if(($too_early==0 && $founder==0) && $rowx['user_id']==0) 
                                  {     
                                       echo "
                                            <tr style='background-color:" . ($cntr % 2 == 0 ? "eeeeee" : "dddddd") . ";' class='task_" . $rowx['task_id'] . " task_" . $rowx['task_type'] . "_entity_" . $rowx['entity_id'] . " ".(($rowx['user_id']>0 && date("Y-m-d",strtotime($rowx['done_date']))!=date("Y-m-d",time())) ? "completed_" . $rowx['task_type'] . "" : "")." all_tasks'>
                                                <td valign='top'>" . $rowx['task_name'] . "</td>
                                                <td valign='top'>" . $task_types[$rowx['task_type']] . "</td>
                                                <td valign='top'>" . $entity_name . "</td>
                                                <td valign='top'><span style='color:" . $cur_date_col . ";'>" . date("m/d/Y", strtotime($rowx['cur_date'])) . "</span></td>                                        
                                                <td valign='top'><span id='done_by_" . $rowx['id'] . "'>" . $done_by . "</span></td>
                                                <td valign='top'><span id='done_on_" . $rowx['id'] . "'>" . $done_on . "</span></td> 
                                                <td valign='top'>
                                                    <span onClick='mrr_mark_it(" . $rowx['id'] . ");' style='color:#00CC00;cursor:pointer;'>
                                                        <span class='glyphicon glyphicon-ok'></span>
                                                    </span>
                                                </td>
                                                <td valign='top'>
                                                    <span onClick='mrr_clear_it(" . $rowx['id'] . ");' style='color:#CC0000;cursor:pointer;'>
                                                        <span class='glyphicon glyphicon-remove'></span>
                                                    </span>
                                                </td>                                      
                                            </tr>
                                         ";     //".."
                                  }
                                 $cntr++;
                             }

                             if($_GET['edit_id'] ==  $cur_task_id)
                             {
                                  $sqlu="update internal_tasks set last_active_cntr='".(int) $show_cntr."' where id='".sql_friendly($cur_task_id)."'";
                                  //if($cur_task_id > 0)   mysql_query($sqlu,$datasource);
                             }
                             ?>
                        </table>
                    </div>
                </div>
            </div>
            <div class='col-md-6'>
                <div class="panel panel-info">
                    <div class="panel-heading"><?=$usetitle ?> Setup</div>
                    <div class="panel-body">
                        <table class='table table-bordered well'>
                            <tr>
                                <td valign='top'><b>ID</b></td>
                                <td valign='top'><b>Task</b></td>
                                <td valign='top'><b>Type</b></td>
                                <td valign='top'><b>Status</b></td>
                                <td valign='top' align='right'><b>1st Due Date</b></td>
                                <td valign='top' align='right'><b>Start Date</b></td>
                                <td valign='top' align='right'><b>Days</b></td>
                                <td valign='top' align='right'><b>Alert</b></td>
                                <td valign='top' align='right'><b>&nbsp;</b></td>
                            </tr>
                            <?
                            $edit_id=0;
                            $edit_name="";
                            $edit_type=0;
                            $edit_active=0;
                            $edit_days=0;
                            $days_before=0;
                            $edit_start="";
                            $edit_due="";
                            
                            $created_by="";
                            $created_on="";
                            
                            if(isset($_GET['edit_id']))     $edit_id=(int) $_GET['edit_id'];
                            //if(isset($_POST['edit_id']))    $edit_id=(int) $_POST['edit_id']; 
                            
                            while($row = mysqli_fetch_array($data))
                            {      
                                  $use_start_date="";
                                  $use_due_date="";
                                  if($row['linedate_due']!="0000-00-00 00:00:00")       $use_due_date="".date("m/d/Y",strtotime($row['linedate_due']))."";
                                  if($row['linedate_start']!="0000-00-00 00:00:00")     $use_start_date="".date("m/d/Y",strtotime($row['linedate_start']))."";
                                  
                                  $mrr_styler1="CC0000";
                                  $mrr_styler2="CC0000";
                                  $mrr_styler3="CC0000";
                                  
                                  if($use_due_date!="" && date("Y-m-d",strtotime($row['linedate_due'])) > date("Y-m-d",time())) 
                                  {
                                      $mrr_styler1="00CC00";
                                  }   
                                  else
                                  {
                                      $mrr_styler2="00CC00";
                                      $mrr_styler3="00CC00";
                                  }
                                  
                                  echo "
                                    <tr>
                                        <td valign='top'><a href='internal_tasks.php?edit_id=".$row['id']."'>".$row['id']."</a></td>
                                        <td valign='top'>".$row['task_name']."</td>
                                        <td valign='top'>".$task_types[ $row['task_type'] ]."</td>
                                        <td valign='top'>".($row['active'] > 0 ? "Active" : "Inactive")."</td>
                                        <td valign='top' align='right'><span style='color:#".$mrr_styler1.";'>".$use_due_date."</span></td>
                                        <td valign='top' align='right'><span style='color:#".$mrr_styler2.";'>".$use_start_date."</span></td>                                        
                                        <td valign='top' align='right'><span style='color:#".$mrr_styler3.";'>".$row['interval_days']."</span></td>
                                        <td valign='top' align='right'>".$row['show_days_before']."</td> 
                                        <td valign='top'>
                                            <a href='javascript:confirm_delete(".$row['id'].");' class='mrr_delete_access'>
                                                <img src='images/delete_sm.gif' border='0'>
                                            </a>
                                        </td>
                                    </tr>
                                  ";
                                  
                                  if($row['id']==$edit_id)
                                  {
                                       $edit_id=$row['id'];
                                       $edit_name=trim($row['task_name']);
                                       $edit_type=$row['task_type'];
                                       $edit_active=$row['active'];
                                       $edit_days=$row['interval_days'];
                                       $days_before=$row['show_days_before'];
                                       $edit_start="";
                                       $edit_due="";
                                       if($row['linedate_start']!="0000-00-00 00:00:00")    $edit_start=date("m/d/Y",strtotime($row['linedate_start']));
                                       if($row['linedate_due']!="0000-00-00 00:00:00")      $edit_due=date("m/d/Y",strtotime($row['linedate_due']));
     
                                       $created_by=trim($row['username']);
                                       $created_on="".date("m/d/Y H:i",strtotime($row['linedate_added']))."";
     
                                       $edit_task_cntr = (int) $row['last_active_cntr'];
                                  }
                            }
                            ?>
                        </table>
                        <br>
                        <b>*** The color coding represents the due date that is <span style='color:#00CC00;'>USED</span> or <span style='color:#CC0000;'>NOT USED</span> 
                            above to factor the next due date.</b>
                        <br><br>
                        <h3>Edit Task Settings:</h3>
                        <table class='table table-bordered well'>
                            <tr>
                                <td><b>Task Name</b></td>
                                <td valign='top'>
                                    <input name='task_name' value='<?=$edit_name ?>' size='50'>
					<?= show_help('internal_tasks.php','Task Name') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Task Type</b></td>
                                <td valign='top'>
                                    <select name='task_type'>
                                        <?
                                        //echo "<option value='0'".($edit_type==0 ? " selected" : "").">Select Type</option>";
                                        for($i=0;$i < count($task_types) ; $i++)
                                        {
                                            echo "<option value='".$i."'".($edit_type==$i ? " selected" : "").">".$task_types[ $i ]."</option>";        
                                        }
                                        ?>
                                    </select>
					<?= show_help('internal_tasks.php','Task Type') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Status</b></td>
                                <td valign='top'>
                                    <select name='active'>
                                        <option value='0'<?=($edit_active==0 ? " selected" : "")?>>Inactive</option>
                                        <option value='1'<?=($edit_active==1 ? " selected" : "")?>>Active</option>
                                    </select>
					<?= show_help('internal_tasks.php','Status') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Interval</b></td>
                                <td valign='top'>
                                    <input name='interval_days' value='<?=$edit_days ?>' size='5' style='text-align:right;'> Days
					<?= show_help('internal_tasks.php','Days') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Start Alerts</b></td>
                                <td valign='top'>
                                    <input name='show_days_before' value='<?=$days_before ?>' size='5' style='text-align:right;'> Days Before Due Date to Show Alerts
					<?= show_help('internal_tasks.php','Start Alert Days') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>First Due Date</b></td>
                                <td valign='top'>
                                    <input name='linedate_due' id='linedate_due' value='<?=$edit_due ?>' size='15' class="mrr_datepicker">
					<?= show_help('internal_tasks.php','First Due Date') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Or, Starting Date</b></td>
                                <td valign='top'>
                                    <input name='linedate_start' id='linedate_start' value='<?=$edit_start ?>' size='15' class="mrr_datepicker">
					<?= show_help('internal_tasks.php','Starting Date') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Task Counter</b></td>
                                <td valign='top'>
                                    <input name='task_active_cntr' value='<?=$edit_task_cntr ?>' size='5' style='text-align:right;'> Units for active counter... 
                                    Bypass to set active (Drivers, Trucks, Trailers) if the task does not show up... or 1 for single tasks <i>if needed</i>.
					<?= show_help('internal_tasks.php','Task Counter') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Created By</b></td>
                                <td valign='top'><?=$created_by ?> on <?=$created_on ?></td>
                            </tr>
                            <tr>
                                <td><a href='internal_tasks.php?edit_id=0'><b>ADD NEW TASK</b></a></td>
                                <td valign='top'>
                                    <button type='submit' name='mrr_saver' class='btn btn-primary'>
                                        <span class="glyphicon glyphicon-play"></span> Save Task</button>

                                    <input type='hidden' name='edit_id' value='<?=$edit_id ?>'>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script type='text/javascript'>
            function confirm_delete(id)
            {
                if(confirm("Are you sure you want to delete this task?")) 
                {
                    window.location = '<?=$SCRIPT_NAME?>?del_id=' + id;
                }
            }

            $('.mrr_datepicker').datepicker();
            
            var this_user="<?=$cur_user_name ?>";      //$cur_user_id
            var this_date="<?=$cur_completed ?>";
            
            function mrr_mark_it(id)
            {
                $.ajax({
                    type: "POST",
                    url: "ajax.php?cmd=mrr_mark_off_internal_task",
                    data: {"id":id },
                    dataType: "xml",
                    cache:false,
                    success: function(xml) {
                        $('#done_by_'+id+'').html(this_user);
                        $('#done_on_'+id+'').html(this_date);
                    }
                });
            }
            function mrr_clear_it(id)
            {
                $.ajax({
                    type: "POST",
                    url: "ajax.php?cmd=mrr_clear_internal_task",
                    data: {"id":id },
                    dataType: "xml",
                    cache:false,
                    success: function(xml) {
                        $('#done_by_'+id+'').html("");
                        $('#done_on_'+id+'').html("");
                    }
                });
            }
            function mrr_toggle_task(id)
            {
                $('.task_'+id+'').toggle();
                $('.completed_'+id+'').hide();
            }
            function mrr_toggle_task_completed(id)
            {
                $('.completed_'+id+'').toggle();
            }
            <?php
                if($linked_task > 0)
                {
                     echo "
                        $('.all_tasks').hide();
                        $('.task_".$linked_task."').show();
                        $('.completed_".$linked_task."').hide();
                     ";
                }
                if($linked_task_id > 0)
                {
                     echo "
                        $('.all_tasks').hide();
                        $('.task_".$linked_task_id."').show();
                        $('.completed_".$linked_task_id."').hide();
                     ";                    
                }
            ?>
        </script>
    </form>
<? include('footer.php') ?>