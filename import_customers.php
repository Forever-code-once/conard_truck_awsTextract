<? set_time_limit(6000) ?>
<? include("header.php") ?>
<?
	// get a list of the import columns
	$sql = "
		select *
		
		from option_values
		where cat_id = '".sql_friendly(get_option_cat_id("import_fields_customer"))."'
		order by zorder, fname
	";
	$data_import_fields = simple_query($sql);



	if(isset($_POST['show_complete'])) {
		// do the import
		$import_array = explode(chr(10),$_POST['import_list']);
		$import_col_array = explode(chr(9),$import_array[0]);
		$col_count = count($import_col_array);
		
		$i=0;
		foreach($import_col_array as $header_entry) {
			if($_POST['import_field_'.$i] != '') {
				$import_column_array[$_POST['import_field_'.$i]] = $i;
			}
			$i++;
		}
		
		// loop through our array and validate the number of actual entries
		$item_count = 0;
		$skip_done = 0;
		$entries_updated = 0;
		$entries_added = 0;
		$entries_found = 0;
		$new_entries = array();
		$update_entries = array();
		foreach($import_array as $lineitem) {
			$line_columns = explode(chr(9),$lineitem);
			if(count($line_columns) == $col_count && (trim($line_columns[$import_column_array['name_company']]) != '')) {
				// valid line, import/update it
				if($item_count == 0 && $_POST['first_row_names'] && $skip_done == 0) {
					// skip this first row if the first was was the column names
					$skip_done = 1;
				} else {
					$item_count++;
					
					$modified_item_name = $line_columns[$import_column_array['name_company']];
					if(isset($line_columns[$import_column_array['cts_id']])) {
						// specific cts_id (Conard Trucking System ID)
						$sql = "
							select *
							
							from customers
							where id = '".sql_friendly($line_columns[$import_column_array['cts_id']])."'
						";
						$data_check = simple_query($sql);						
					} else {
						// see if this entry exists by checking against company name
						$sql = "
							select *
							
							from customers
							where name_company = '".sql_friendly($modified_item_name)."'
								and deleted = 0
						";
						$data_check = simple_query($sql);
					}
				

					
					if(!mysqli_num_rows($data_check)) {
						
						if($_POST['test_run_only'] == '1') {
							$new_entries[] = $modified_item_name;
						} else {
							$sql = "
								insert into customers
									(name_company,
									deleted)
								
								values ('".sql_friendly($modified_item_name)."',
									0)
							";
							simple_query($sql);
							$item_id = mysqli_insert_id($datasource);
						}
						$entries_added++;
					} else {
						
						if($_POST['test_run_only'] == '1') {
							$update_entries[] = $modified_item_name;
						}
						
						$row = mysqli_fetch_array($data_check);
						$item_id = $row['id'];
						$entries_updated++;
					}
					
					$entries_found++;
					

					if($_POST['test_run_only'] == '1') {
						// we're in test only - so no inserts/updates need to be completed at this point
					} else {
						// build our SQL update statement
						$sql = "
							update customers
							set 
						";
						foreach($import_column_array as $key => $value) {
							// don't update the item name field, as it's not necessary
							($key == 'credit_limit' && $line_columns[$value] == '' ? $line_columns[$value] = '0' : "");
							if($key != 'cts_id') {
								$sql .= " $key = '".trim(sql_friendly($line_columns[$value]))."', ";
							}
						}
						$sql .= "
							deleted = 0
							
							where id = '".sql_friendly($item_id)."'
								and deleted = 0
							limit 1
						";
						//d($sql);
						simple_query($sql);
					}
						
					
				} // end if - check to see if we're skipping the first row
			}
		}
		
		if($_POST['test_run_only'] == '1') {
			
			echo "
				<div class='import_steps'>
				<div class='is_header'>Test Import - Results</div>
				<table style='text-align:left'>
				<tr><td>New Entries Found: ".count($new_entries)."</td></tr>
				<tr><td>Updated Entries Found: ".count($update_entries)."</td></tr>
				<tr><td><hr></td></tr>
				<tr><td><b>New (Not Found) Entries</b></td></tr>
			";
			foreach($new_entries as $entry_name) echo "<tr style='color:red'><td>$entry_name</td></tr>";
			
			echo "
				<tr><td><hr></td></tr>
				<tr><td>Updated Entries Found: ".count($update_entries)."</td></tr>
			";
			
			foreach($update_entries as $entry_name) echo "<tr style='color:green'><td>$entry_name</td></tr>";
			echo "</table>
				</div>
			";
		} else {
			//die('hit');
			//import has completed, show the 'Done' page
			javascript_redirect($SCRIPT_NAME."?done=1&found=$entries_found&updated=$entries_updated&added=$entries_added");
		}
	}


	
?>
<div class='main_container'>
<? if(isset($_GET['done'])) { ?>

	<div class='import_steps'>
		<div class='is_header'>Import Successful</div>
		<div class='is_desc'>
			<center>
			<table class='standard12'>
			<tr>
				<td colspan='2'><center><b>Summary</b></center></td>
			</tr>
			<tr>
				<td align='right'>Entries Found:</td>
				<td align='right'><b><?=($_GET['found'])?></b></b></td>
			</tr>
			<tr>
				<td align='right'>Entries Added:</td>
				<td align='right'><b><?=$_GET['added']?></b></td>
			</tr>
			<tr>
				<td align='right'>Entries Updated:</td>
				<td align='right'><b><?=$_GET['updated']?></b></td>
			</tr>
			</table>
			<br><br>
			<a href='<?=$SCRIPT_NAME?>'>Click here</a> to import/update more entries
			</center>
		</div>
	</div>

<? } else if(!isset($_POST['import_list'])) { ?>
	<form name='mainform' action='' method='post'>

		<div class='import_steps'>
			<div class='is_header'>Available Columns</div>
			<div class='is_desc' style='margin-left:20px'>
				<? 
				while($row_import_fields = mysqli_fetch_array($data_import_fields)) {
					echo "<div style='float:left;width:160px'>$row_import_fields[fvalue]</div>";
				}
				?>
					
			</div>
		</div>

		<div class='import_steps'>
			<div class='is_header'>Import Data<p>Step 1 - Copy/Paste your data</div>
			
				<div class='is_desc'>
					Copy and paste the data from Microsoft Excel into this box. You can do this by selecting everything in the Excel window, and copying it. Then, right-click inside the box below and 
					select 'Paste'.
				</div>
			<center>
			<textarea id='import_list' name='import_list' style='width:500px;height:250px'></textarea>
			</center>
		</div>
		
		<div class='import_steps'>
			<div class='is_header'>Step 2 - Does the first row contain column headers</div>
			<div class='is_desc'>
				Does the first row of the information you copied/pasted in contain the column header names? For example, if the first row
				contains fields called <b>First Name</b>, <b> Last Name</b>, etc... then the answer to this question is <b>Yes</b>. On the other hand,
				if the first line of information contains the person's actual first and last name, then the answer to this question is <b>No</b>.
			</div>
			<center>
			<label><input type='radio' name='first_row_names' value='1'> Yes</label> / 
			<label><input type='radio' name='first_row_names' value='0'> No</label>
			</center>
		</div>

		<div class='import_steps'>
			<div class='is_header'>Step 3 - Test Run Only</div>
			<div class='is_desc'>
				Select <b>Yes</b> on this field <b>only if</b> this is a test import and you don't want anything to be actually created or updated.
				If this is selected as <b>Yes</b>, you will get a full output of what entries were matched, and which would would be created new on the result page.
			</div>
			<center>
			<label><input type='radio' name='test_run_only' value='1'> Yes</label> / 
			<label><input type='radio' name='test_run_only' value='0' checked> No</label>
			</center>
		</div>

		<div class='import_steps'>
			<div class='is_header'>Step 4 - Proceed to review page</div>
			<div class='is_desc'>
				Press the 'Preview' button below to continue to the next section. This will give you the oportunity to 
				review what you are about to import.
			</div>
			<center><input type='button' value='Review' onclick='verify_form()'></center>
		</div>
	</form>
	
	<script type='text/javascript'>
		
		$('#vendor_name').autocomplete('ajax.php?cmd=search_vendors',{formatItem:formatItem});
		
		function verify_form() {
			if($('#import_list').val() == '') {
				$.prompt('Step 1 has not been completed - Please copy/paste your list to import into the box in Step 2.');
				return;
			}
			
			if($('input[name=first_row_names]:checked').val() == undefined) {
				$.prompt('Step 2 has not been completed - Please specify whether the first row contains the column names.');
				return;
			}
			
			
			$('#category_name').val($('#category_id :selected').text());
			document.mainform.submit();
		}
	</script>
<? } else if(!isset($_POST['show_complete'])) { ?>
	<?
		$use_separator = chr(10);
		if(strpos($_POST['import_list'], chr(10)) === false) {
			$_POST['import_list'] = str_replace(chr(13),chr(10), $_POST['import_list']);
		}
		
		if(strpos($_POST['import_list'], chr(10)) === false) {
			echo "Could not locate line separator";
			die;
		}
		
		$import_array = explode(chr(13),$_POST['import_list']);
		$import_col_array = explode(chr(9),$import_array[0]);
		$col_count = count($import_col_array);
		
		//die("(".$col_count.") | ".substr($_POST['import_list'],0,400));
		
		if($col_count > 200) {
			die("too many import columns");
		}
		
		// loop through our array and validate the number of actual entries
		$item_count = 0;
		foreach($import_array as $lineitem) {
			if(count(explode(chr(9),$lineitem)) == $col_count) $item_count++;
		}
		
		// if the first row is column names, then it doesn't count as an entry found to import, so deduct one from the count
		if($_POST['first_row_names']) $item_count--;
	?>
	
	<form name='import_form' action='' method='post'>
		<input type='hidden' name='import_list' value="<?=htmlentities($_POST['import_list'])?>">
		<input type='hidden' name='first_row_names' value="<?=$_POST['first_row_names']?>">
		<input type='hidden' name='test_run_only' value="<?=$_POST['test_run_only']?>">
		<input type='hidden' name='show_complete' value='1'>
		
		<div class='import_steps'>
			<div class='is_header'>Summary</div>
			<div class='is_desc'>
				<table class='standard12'>
				<tr>
					<td align='right'>Entries Found:</td>
					<td><b><?=$item_count?></b></b></td>
				</tr>
				<tr>
					<td align='right'>First row contains headers:</td>
					<td>
						<b><?=($_POST['first_row_names'] ? "Yes" : "No")?></b>
					</td>
				</tr>
				<tr>
					<td align='right'>Test run only:</td>
					<td>
						<b><?=($_POST['test_run_only'] ? "Yes" : "No")?></b>
					</td>
				</tr>
				</table>
			</div>
		</div>

		<div class='import_steps'>
			<div class='is_header'>Map the columns</div>
			<div class='is_desc'>
				Please select which columns from the drop-down list match the columns from your import list. Any columns that are not matched
				will be skipped on the import.
			</div>
			<table class='standard12'>
			<tr>
				<td><b>System100 Column</b></td>
				<td>&nbsp;&nbsp;&nbsp;</td>
				<td><b>Import Column</b></td>
			<?
				$i=0;
				foreach($import_col_array as $header_entry) {
					echo "<tr>
							<td>
								<select class='import_mappings' name='import_field_$i'>
								<option value='' style='color:#bbbbbb;'>Select One</option>
					";
					mysqli_data_seek($data_import_fields,0);
					while($row_import_fields = mysqli_fetch_array($data_import_fields)) {
						if(strtolower($row_import_fields['fvalue']) == strtolower(trim($header_entry)) || 
									(strtolower($row_import_fields['dummy_val']) == strtolower(trim($header_entry)) && $row_import_fields['dummy_val'] != '')) {
							$use_selected = 'selected';
						} else {
							$use_selected = '';
						}
									echo "<option value='$row_import_fields[fname]' $use_selected>$row_import_fields[fvalue]</option>";
					}

					echo "
								</select>
							</td>
							<td>&nbsp;</td>
							<td>$header_entry</td>
						</tr>
					";
					$i++;
				}
			?>
			</table>
		</div>
		
		<div class='import_steps'>
			<div class='is_header'>Final Step</div>
			<div class='is_desc'>
				Click the <b>Finish Importing</b> button to complete the process and import your updatess.
			</div>
			<center><input type='button' value='Finish Importing' onclick='verify_final_submit()'></center>
		</div>
	</form>
	
	<script type='text/javascript'>
		function verify_final_submit() {
			// there are three mandatory fields (name_first, name_last, name_company)
			// make sure at least those three are mapped
			customer_name = 0;
			$(".import_mappings").each(function(i, n) {
				if($(n).val() == 'name_company') customer_name = 1;
			});
			
			
			if(!customer_name) {
				$.prompt('Missing Field<p>Company Name a required field, please make sure you imported and mapped that field');
				return;
			}

			$.prompt("Final Confirmation - Please verify the columns are mapped the way you want. <br><br>If they are, then click \'Yes\'. If not, then click \'No\' to make changes.", {
					buttons: {Yes: true, No:false},
					submit: function(v, m, f) {
						if(v) {
							document.import_form.submit();
						}
					}
				}
			);

		}
	</script>
<? } else { ?>
	
<? } ?>
</div>

<? include("footer.php") ?>