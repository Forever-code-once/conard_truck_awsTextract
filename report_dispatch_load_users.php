<?php
//Added by Michael...Sherrod Computers
$usetitle = "Page User Link";
$use_title = "Page User Link";
?>
<? include('header.php') ?>
<?
     $user_id=$_SESSION['user_id'];
     $pg_type=0;
     $pg_id=0;   
     
     if(isset($_GET['clear']))
     {
          $sqlu="update user_page_editing set deleted=2 where deleted=0"; 
          simple_query($sqlu);           
     }
          
     $sql="
          select user_page_editing.*,
                users.name_first,
                users.name_last
          from user_page_editing 
                left join users on users.id=user_page_editing.user_id
          where user_page_editing.deleted=0
                ".($pg_type > 0 ? " and user_page_editing.page_type='".(int) $pg_type."'" : "")."
                ".($pg_id > 0 ? " and user_page_editing.page_id='".(int) $pg_id."'" : "")."                
          order by user_page_editing.page_type asc,user_page_editing.page_id asc,user_page_editing.user_id asc,user_page_editing.linedate_added asc,user_page_editing.id
     ";   //".($not_user > 0 ? "and user_page_editing.user_id!='".(int) $not_user."'" : "")."
          //".($user > 0 ? "and user_page_editing.user_id='".(int) $user."'" : "")."
     $data = simple_query($sql);
	?>
	<center><span class='section_heading'><?=$use_title ?></span></center>		
	<div style=color:purple;margin:10px;'><b>Use the links to close the user out if they are no longer using the page. ---<a href='report_dispatch_load_users.php'>Reload/Refresh Page</a></b></div>
	<table class='admin_menu2 font_display_section tablesorter' style='margin:0 10px;width:1300px;text-align:left'>
	<thead>
     <tr>
          <th valign='top'>Use#</th>
          <th valign='top'>Date</th>
          <th valign='top'>First Name</th>
          <th valign='top'>Last Name</th>
          <th valign='top'>Section</th>
          <th valign='top'>ID</th>
          <th valign='top'>URL</th>
          <th valign='top'>Action</th>
     </tr>			
	</thead>
	<tbody>
	<?
     $cntr = 0;
               
     while($row = mysqli_fetch_array($data)) 
     {	
          $styler="";  
          if($row['user_id']==$user_id)      $styler=" style='color:purple; font-weight:bold;'";
     
          echo "
               <tr class='".($cntr % 2==0 ? "even" : "odd")." row_".$row['id']."'>
                    <td valign='top'".$styler.">".($cntr+1)."</td>
                    <td valign='top'".$styler.">".date("m/d/Y H:i",strtotime($row['linedate_added']))."</td>
                    <td valign='top'".$styler.">".$row['name_first']."</td>
                    <td valign='top'".$styler.">".$row['name_last']."</td>
                    <td valign='top'".$styler.">".mrr_user_page_editing_decoder($row['page_type'])."</td>
                    <td valign='top'".$styler.">".$row['page_id']."</td>
                    <td valign='top'".$styler.">".$row['page_url']."</td>
                    <td valign='top'><span style='color:#0000CC; cursor:pointer;' onClick='mrr_manual_close(".$row['id'].",".$row['page_type'].",".$row['page_id'].",".$row['user_id'].");'>Close</span></td>
               </tr>
          ";
          $cntr++;
     }			
	?>
	</tbody>
	</table>
<br><center><a href='report_dispatch_load_users.php?clear=1'>Reset Page User Linkage</a></center>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	
	function mrr_manual_close(id,pg_type,pg_id,user_id)
	{	
		$.ajax({
			   type: "POST",
			   url: "ajax.php?cmd=mrr_change_user_pg_usage",
			   data: {
                         "pg_type":pg_type,
			   		"pg_id":pg_id,
			   		"user_id":user_id
			   		},		   
			   dataType: "xml",
			   cache:false,
			   async:false,
			   success: function(xml) {
			   		$.noticeAdd({text: "Line Entry "+id+" has closed successfully."});
			   		$('.row_'+id+'').hide();
			   }	
		});
	}
</script>
<? include('footer.php') ?>