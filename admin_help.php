<? include('header.php') ?>
<? $admin_page = 1 ?>
<?
error_reporting(E_ALL);
ini_set('display_errors', '1');

	$id=0;	
	if(isset($_POST['help_id']))		$id=$_POST['help_id'];
	
	$active="";
	$page_name="";
	$field_name="";
	$quick_text="";
	$help_text="";
	$stamp= "00/00/0000";
	$btntxt="Add Help Content";
	
	if(isset($_GET['id']))
	{
		$sql = "
				select *										
				from help_desk
				where id='".sql_friendly( $_GET['id'] )."'
					and deleted=0
			";
		$data = simple_query($sql);
		while($row = mysqli_fetch_array($data))
		{		
			$id=$row['id'];
			$active=$row['active'];
			$page_name=trim($row['page_name']);
			$field_name=trim($row['field_name']);
			$quick_text=trim($row['quick_text']);
			$help_text=trim($row['help_text']);
			$stamp= date("n/j/Y", strtotime($row['linedate_added']));
			$btntxt="Update Help Content";
		}
	}
	
	$pg_nm_sel="";		if(isset($_POST['page_name_selector']))		$pg_nm_sel=$_POST['page_name_selector'];
	$page_sel=mrr_help_selector_box('page_name',$pg_nm_sel);
	
	$fld_nm_sel="";	if(isset($_POST['field_name_selector']))	$fld_nm_sel=$_POST['field_name_selector'];
	$field_sel=mrr_help_selector_box('field_name',$fld_nm_sel);
	
	$search_txt="";	if(isset($_POST['search_text']))			$search_txt=$_POST['search_text'];
	
?>


<form action="<?=$SCRIPT_NAME?>?id=<?= $id ?>" method="post">
<table border="0">
<tr>
<td valign="top" width="710">	
     <input type="hidden" id="help_id" name="help_id" value="<?= $id ?>">	
     <table class='admin_menu1' style='width:700px;'>
     <tr>
     	<td colspan='2'><font class="standard18"><b>Help Desk Administration</b></font><br><br></td>
     </tr>
     <!--
     <tr>
     	<td colspan='2'><div class="mrr_link_like_on" onClick="mrr_load_help_info(0);"><b>Add New Help Topic</b></div></td>
     </tr>
     -->    
     <tr>
     	<td valign="top" width="150"><b>File Name:</b><?= show_help('admin_help.php','page_name') ?></td>
     	<td valign="top"><input type="hidden" id="page_name" name="page_name" value="<?= $page_name ?>"><span id='page_name_lab' name='page_name_lab'><?= $page_name ?></span></td>
     </tr>
     <tr>
     	<td valign="top"><b>Field Name:</b><?= show_help('admin_help.php','field_name') ?></td>
     	<td valign="top"><input type="hidden" id="field_name" name="field_name" value="<?= $field_name ?>"><span id='field_name_lab' name='field_name_lab'><?= $field_name ?></span></td>
     </tr>
     <tr>
     	<td valign="top"><b>Help Tip (Quick Text):</b><?= show_help('admin_help.php','quick_text') ?></td>
     	<td valign="top"><input id="quick_text" name="quick_text" value="<?= $quick_text ?>" size="80"></td>
     </tr>
     <tr>
     	<td valign="top"><b>Help Text:</b><?= show_help('admin_help.php','help_text') ?></td>
     	<td valign="top"><textarea id="help_text" name="help_text" wrap="virtual" rows="6" cols="60"><?= $help_text ?></textarea></td>
     </tr>
     <tr>
     	<td valign="top"><b>First Entered:</b></td>
     	<td valign="top"><span id='help_date' name='help_date'><?= $stamp ?></span></td>
     </tr>
     <tr>
     	<td colspan='2'><center><input type="button" id="help_saver" name="help_saver" value="<?= $btntxt ?>" onClick="mrr_save_help_info();"></center><br></td>
     </tr>
     </table>
     <br>
     <table class='admin_menu1' style='width:700px;'>
     <tr>
     	<td valign="top"><font class="standard18"><b>Search Topics</b></font><br><br></td>
     	<td valign="top"align='right'><input type="button" id="help_search" name="help_search" value="Search Help" onClick="mrr_search_list();"></td>
     </tr>
     <tr>
     	<td valign="top" width="160"><b>Search File Name:</b></td>
     	<td valign="top"><?= $page_sel ?></td>
     </tr>
     <tr>
     	<td valign="top"><b>Search Field Name:</b></td>
     	<td valign="top"><?= $field_sel ?></td>
     </tr>
     <tr>
     	<td valign="top"><b>Search for Text:</b></td>
     	<td valign="top"><input id="search_text" name="search_text" value="<?= $search_txt ?>" size="80"></td>
     </tr>
     </table>     
</td>
<td valign="top" width="700">Use "Search Topics" form on the left to find or filter Help Desk topics.<br>
	<div id='help_desk_section'></div>
</td>
</tr>
</table>
</form>

<? include('footer.php') ?>

<script type='text/javascript'>
	var my_id=<?= $id ?>;
		
	//help desk listing for first load.
	$().ready(function() {
		
		create_help_section('#help_desk_section', 0, my_id);
		$('#quick_text').focus();
	});
	
	function mrr_search_list()
	{
		display_help_section(0, 0);
	}
	function mrr_save_help_info()
	{
		$.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=save_help_desk",
		   data: {
		   		"help_id": $('#help_id').val(),
		   		"help_page": $('#page_name').val(),
		   		"help_field": $('#field_name').val(),
		   		"help_quick": $('#quick_text').val(),
		   		"help_text": $('#help_text').val(),
		   		},
		   success: function(data) {
		   		new_help_id=$(this).find('HelpID').text();
				new_help_name=$(this).find('HelpPage').text();
				$.noticeAdd({text: "Help Topic "+new_help_id+" Info for "+new_help_name+" has been saved."});	
				
				$('#help_saver').val( 'Update Help Content' );
						
		   		//create_help_section('#help_desk_section', 0, new_help_id);
		   		display_help_section(0, new_help_id);
		   		$('#quick_text').focus();
		   }		   
		 });		
	}
	
	function mrr_load_help_info(id)
	{
		var myid_val=id;
		   	
     	$.ajax({
		   type: "POST",
     		   url: "ajax.php?cmd=load_help_desk",
     		   dataType: "xml",
     		   data: {
     		   		"help_id": myid_val
     		   		},
		   success: function(xml) {
		   		$(xml).find('Help').each(function() {
     		   		$('#help_id').val( $(this).find('HelpID').text() );
     		   		$('#page_name').val( $(this).find('HelpPage').text() );
     		   		$('#field_name').val( $(this).find('HelpField').text() );
     		   		$('#quick_text').val( $(this).find('HelpQuick').text() );
     		   		$('#help_text').val( $(this).find('HelpText').text() );
     		   		$('#help_date').html( $(this).find('HelpAdded').text()  );
     		   		
     		   		$('#page_name_lab').html( $(this).find('HelpPage').text() );
     		   		$('#field_name_lab').html( $(this).find('HelpField').text() );
     		   		
     		   		id_val=$(this).find('HelpID').text();
     		   		if(id_val > 0)
     		   		{
     		   			$('#help_saver').val( 'Update Help Content' );
     		   		}
     		   		else
     		   		{
     		   			$('#help_saver').val( 'Add Help Content' );
     		   		}
     		   		$('#quick_text').focus();
     		   	});
		   }
		 });
	}
	function confirm_del_help(id) {
		var myid_val=id;
		if(confirm("Are you sure you want to delete this help desk info?")) {
			
			$.ajax({
     		   type: "POST",
     		   url: "ajax.php?cmd=kill_help_desk",
     		   dataType: "xml",
     		   data: {
     		   		"help_id": myid_val
     		   		},
     		   success: function(xml) {
     		   		kill_help_id=$(xml).find('HelpID').text();
     				$.noticeAdd({text: "Help Topic "+kill_help_id+" has been removed."});	
     		   		$('.helper_'+kill_help_id+'').remove();
     		   		
     		   		display_help_section(0, 0);
     		   		//create_help_section('#help_desk_section', 0, 0);
     		   }		   
     		 });	
			
		}
	}
	
	function create_help_section(element_holder, section_id, xref_id) {
		
		uc_tmp = "<div id='help_desk_container'>";
			uc_tmp += "<div class='inside_container'>";
				uc_tmp += "<div class='header'>Help Desk Topics</div>";

			uc_tmp += "</div>";
			uc_tmp += "<div id='help_desk_holder'>";
			uc_tmp += "</div>";
		uc_tmp += "</div>";
			
		$(element_holder).append(uc_tmp);
		
		display_help_section(0, xref_id);		
	}
	function display_help_section(section_id, xref_id) {
		 $.ajax({
		   type: "POST",
		   url: "ajax.php?cmd=display_help_desk",
		   data: {"section_id":section_id,
		   		"help_id":xref_id,
		   		"pgname": $('#page_name_selector').val(),
		   		"fldname": $('#field_name_selector').val(),
		   		"stext": $('#search_text').val(),
		   		},
		   success: function(data) {
		   		$('#help_desk_holder').html(data);
		   }
		 });
	}	
</script>