<? include('application.php') ?>
<? $admin_page = 1; ?>
<?
	if(!isset($_POST['id']))				$_POST['id']=0;
	if(!isset($_POST['budget_name']))		$_POST['budget_name']="";
	
	if(!isset($_POST['item']))			$_POST['item']=0;
	if(!isset($_POST['acct_code']))		$_POST['acct_code']="";
	if(!isset($_POST['diff_acct_code']))	$_POST['diff_acct_code']="";
	if(!isset($_POST['activator']))		$_POST['activator']=0;
	if(!isset($_POST['deactivator']))		$_POST['deactivator']=0;	
	
	if(isset($_GET['id']))				$_POST['id']=$_GET['id'];
	if(isset($_GET['item']))				$_POST['item']=$_GET['item'];
	
	if(isset($_GET['go']))				$_POST['build_report']=1;
	
	if(isset($_GET['date_from']))	
	{
		$_GET['date_from']=str_replace("_","/",$_GET['date_from']);	
		$_POST['date_from']=$_GET['date_from'];
		
		//$_POST['build_report']=1;
	}
	if(isset($_GET['date_to']))		
	{
		$_GET['date_to']=str_replace("_","/",$_GET['date_to']);
		$_POST['date_to']=$_GET['date_to'];
				
		//$_POST['build_report']=1;
	}
	if(!isset($_POST['date_from'])) 		$_POST['date_from'] = date("n/j/Y", strtotime("-1 month", time()));
	if(!isset($_POST['date_to'])) 		$_POST['date_to'] = date("n/j/Y", time());
	
	$date_from=$_POST['date_from'];		$date_to=$_POST['date_to'];
	
	if(isset($_POST['budget_items_saver']))	$_POST['build_report']=0;
	if(isset($_POST['budget_acct_saver']))	$_POST['build_report']=0;
	if(isset($_POST['budget_acct_adder']))	$_POST['build_report']=0;
	
	//update
	if(isset($_POST['budget_items_saver']))
	{
		//first update budget section name
		$sql="
			update comparison_sections set 
				budget_name='".sql_friendly($_POST['budget_name'])."',
				linedate_added=NOW()
			where id='".sql_friendly($_POST['id'])."'
		";		
		simple_query($sql);	
	}
	
	if(isset($_POST['budget_acct_saver']))
	{
		if($_POST['id'] > 0 && $_POST['item'] > 0)
		{
			$sql="
				update comparison_section_items set 
					account_code='".sql_friendly($_POST['diff_acct_code'])."',
					active='".sql_friendly($_POST['activator'])."',
					deleted='".sql_friendly($_POST['deactivator'])."',
					linedate_added=NOW()
				where id='".sql_friendly($_POST['item'])."'
			";		
			simple_query($sql);	
		}	
	}
	
	if(isset($_POST['budget_acct_adder']))
	{
		if($_POST['id'] > 0)
		{
			$sql="
				insert into comparison_section_items
					(id,
					section_id,
					linedate_added,
					account_code,
					active,
					deleted)
				values
					(NULL,
					'".sql_friendly($_POST['id'])."',
					NOW(),
					'".sql_friendly($_POST['acct_code'])."',
					1,
					0)
			";		
			simple_query($sql);	
		}
	}
	
	//get complete list of charts...store in array and check off those used.		
	$mrr_coa_list=mrr_get_coa_list(0,"");
	$all_coa_cntr=0;
	$all_coa_arr[0]=0;
	$all_coa_grp[0]="";
	$all_coa_type[0]="";
	$all_coa_name[0]="";
	$all_coa_numb[0]="";
	$all_coa_used[0]=0;
	
	$accts_unused2="";
	foreach($mrr_coa_list as $key2 => $value2 )
	{		
		if($key2=="ChartEntry")
		{
     		foreach($value2 as $key => $value )
			{         		
          		$prt=trim($key);		$tmp=trim($value);
          		if($prt=="ID")			$chart_id=$tmp;
          		if($prt=="Name")		$chart_name=$tmp;
          		if($prt=="Number")		$chart_acct=$tmp;
          		if($prt=="Type")		$chart_type=$tmp;
          		
          		if($chart_id > 0 && $chart_acct!="" && $chart_name!="" && $chart_type!="")
          		{
          			$group=$chart_acct;
          			if(strlen($chart_acct) > 5)	$group=substr($chart_acct,0,5);
          			
          			$foundzz=0;
               		for($zz=0;$zz < $all_coa_cntr;$zz++)
               		{
               			if($all_coa_grp[$zz] == $group)	$foundzz=1;	//group already listed here 
               		}	
               		if($foundzz==0)
               		{					
               			$all_coa_arr[$all_coa_cntr]=$chart_id;
               			$all_coa_grp[$all_coa_cntr]="".$group."";
               			$all_coa_name[$all_coa_cntr]="".$chart_name."";
     					$all_coa_numb[$all_coa_cntr]="".$chart_acct."";	
     					$all_coa_type[$all_coa_cntr]="".$chart_type."";
     					$all_coa_used[$all_coa_cntr]=0;
     					
     					$chart_type2=$chart_type;
                    		$chart_type2=str_replace("Cost of Goods Sold","COGS",$chart_type2);
                    		$chart_type2=str_replace("Cost of Sales","COS",$chart_type2);
                    				
     					$accts_unused2.="
          					<tr>
          						<td valign='top'>".($all_coa_cntr+1)."</td>
          						<td valign='top'>".$all_coa_grp[$all_coa_cntr]."</td>
          						<td valign='top'>".$all_coa_name[$all_coa_cntr]."</td>
          						<td valign='top'>".$all_coa_numb[$all_coa_cntr]."</td>
          						<td valign='top'>".$chart_type2."</td>
          					</tr>
     					";
     					
     					$all_coa_cntr++;  
               		}
					$chart_id=0;			$chart_acct="";		$chart_name="";   		$group="";		
          		}          		
          	}//end for loop per entry	
          }//end if	
	}//end for loop for each result returned
	
	
	//now load this budget section.
	$notice="";
	$active=0;
	$remove=0;
	
	$mrr_table2="";
	if($_POST['id'] > 0)
	{
		if($_POST['item'] > 0)
		{
			$accts=mrr_get_budget_comparison_section_item($_POST['item']);
			$active=$accts['active'];
			$_POST['diff_acct_code']=trim($accts['account_code']);
		}
		
		
		$_POST['budget_name']=mrr_get_budget_comparison_sections($_POST['id']);	//get name
		$notice=mrr_get_budget_comparison_sections($_POST['id'],1);				//get note
		
		$res=mrr_get_budget_all_comparison_section_items($_POST['id']);
		$arr=$res['arr'];
		$list=$res['list'];
		
		for($i=0;$i < $res['num'];$i++)
		{
			$myid=$arr[ $i ]; 
			$accts=mrr_get_budget_comparison_section_item($myid);
			$act=$accts['active'];
			$actor="No";
			if($act>0)		$actor="Yes";
			
			$acct_namer="";
			$results=mrr_get_coa_list('',trim($list[ $i ]));		//first arg is $chart_id, second arg is $chart_number	
			
			$acct_name_codes="
						<table border='0' width='100%' class='tablesorter'>				
               			<thead>
                         	<tr>          		
                         		<th valign='top'>#</th>          					
          					<th valign='top'>Name</th>
          					<th valign='top'>Number</th>
          					<th valign='top'>Group</th>
          					<th valign='top'>Type</th>
                         	</tr>
                         	</thead>
               			<tbody>
			";
			
			$coa_cntr=0;
          	$coa_name[0]="";
          	$coa_numb[0]="";	
          	$coa_group[0]="";	
          	$coa_type[0]="";
          	
          	foreach($results as $key2 => $value2 )
          	{
          		$acct_name="";
          		//$all_coa_name[$all_coa_cntr]
          		if($key2=="ChartEntry")
          		{
               		foreach($value2 as $key => $value )
          			{         		
                    		$prt=trim($key);		$tmp=trim($value);
                    		if($prt=="ID")			$chart_id=$tmp;
                    		if($prt=="Name")	{	$chart_name=$tmp;			}		//	$acct_name=$chart_name;
                    		if($prt=="Number")		$chart_acct=$tmp;
                    		if($prt=="Type")		$chart_type=$tmp;
                    		
                    		if($chart_id > 0 && $chart_acct!="" && $chart_name!="" && $chart_type!="")
                    		{
                    			$group=$chart_acct;
                    			if(strlen($chart_acct) > 5)	$group=substr($chart_acct,0,5);                   			
                    			
                    			$chart_name=str_replace("&"," and ",$chart_name);
                    			
                    			
                    			
                    			if(trim($group)==trim($list[ $i ]))
                    			{
                    				$chart_type2=$chart_type;
                    				$chart_type2=str_replace("Cost of Goods Sold","COGS",$chart_type2);
                    				$chart_type2=str_replace("Cost of Sales","COS",$chart_type2);
                    				
                    				$acct_name_codes.="
                    					<tr>
                    						<td valign='top'>".($coa_cntr+1)."</td>
                    						<td valign='top'>".$chart_name."</td>
                    						<td valign='top'>".$chart_acct."</td>
                    						<td valign='top'>".$group."</td>
                    						<td valign='top'>".$chart_type2."</td>
                    					</tr>
                    				";
                    				
                    				if($coa_cntr==0)
                    				{
                    					$acctpos=strpos($chart_name,"#",0);
                    					if($acctpos>0)
                    					{
                    						$acct_name=substr($chart_name,0,$acctpos);
                    						$acct_name=str_replace("-","",$acct_name);
                    						$acct_name=trim($acct_name);
                    					}  
                    					else
                    					{
                    						$acctpos=strpos($chart_name,"-",0);
                    						if($acctpos>0)
                    						{
                    							$acct_name=substr($chart_name,0,$acctpos);
	                    						$acct_name=str_replace("-","",$acct_name);
     	               						$acct_name=trim($acct_name);	
                    						}
                    						else
                    						{
                    							$acct_name=trim($chart_names);	
                    						}
                    							
                    					} 
                    				} 
                    				$acct_name=$chart_name;                				
                    			}                    			
                    			$coa_name[$coa_cntr]="".$chart_name."";
          					$coa_numb[$coa_cntr]="".$chart_acct."";	
          					$coa_group[$coa_cntr]="".$group."";
          					$coa_type[$coa_cntr]="".$chart_type."";
          					$coa_cntr++;   
          					
          					$chart_id=0;			$chart_acct="";		$chart_name="";   		$group="";			
                    		}
               		}//end for loop for each chart entry
          		}//end if
          		if($acct_namer=="")		$acct_namer=$acct_name;
          	}//end for loop for each result returned
			
			$acct_name_codes.="</tbody>
						</table>";		//   
               $mrr_table2.="
     				<tr>
     					<td valign='top'><a href='admin_budget_sections.php?id=".$_POST['id']."&item=".$arr[ $i ]."'>".$list[ $i ]."</a></td>
     					<td valign='top'>".$acct_namer."</td>
     					<td valign='top'>".date("m/d/Y",strtotime($accts['linedate_added']))."</td>
     					<td valign='top'>".$actor."</td>
     					<td valign='top'><span class='mrr_link_like_on' onClick='show_this_acct_subs(\"".$list[ $i ]."\");'>Show ".$list[ $i ]."</span></td>
     				</tr>
     				<tr class='acct_".$list[ $i ]." all_accts'>
     					<td valign='top' colspan='5'>
     						".$acct_name_codes."<br>
     						<center><span class='mrr_link_like_on' onClick='hide_this_acct_subs(\"".$list[ $i ]."\");'>Hide ".$list[ $i ]."</span></center>
     					</td>
     				</tr>
     		";	
     		    		
     		$accts=mrr_get_budget_comparison_section_item($myid);
			$active=$accts['active'];	
		}//end for loop
			
	}
	
	//check if accounts are in any section of the budget.
	$res=mrr_get_budget_all_comparison_section_items(0,1);
	//$marr=$res['arr'];
	$mlist=$res['list'];
	$mcnt=$res['num'];
	$msect=$res['sector'];
	$ranger1=0;
	$ranger2=0;
	for($y=0; $y < $mcnt; $y++)
	{     	
     	if($msect[ $y ]==11)
     	{	//the only one ranged at this point...mark those between once both range markers are greater than zero
     		if($ranger1==0)	$ranger1=(int) trim($mlist[ $y ]);
     		elseif($ranger2==0) $ranger2=(int) trim($mlist[ $y ]);
     			
     		if($ranger1>0 && $ranger2>0)
     		{
     			for($z=0; $z < $all_coa_cntr; $z++)
     			{
     				if( (int)$all_coa_grp[$z] >= $ranger1 && (int)$all_coa_grp[$z] <= $ranger2 ) 	$all_coa_used[$z]=1;		
     			}		
     		}
     	}     	
     	
     	for($z=0; $z < $all_coa_cntr; $z++)
     	{
     		if($all_coa_grp[$z]==$mlist[ $y ])     	$all_coa_used[$z]=1;
     		
     	}
	}
	
	//now build list of accounts unused
	$accts_unused="";
	$already_grped=0;
	$already_named[0]="";
     for($z=0;$z < $all_coa_cntr;$z++)
     {
     	if($all_coa_used[$z] == 0)
     	{	//only if not used in accounts for comparison reports
     		$foundzz=0;
     		for($zz=0;$zz < $already_grped;$zz++)
     		{
     			if($already_named[$zz] == $all_coa_grp[$z])	$foundzz=1;	//group already listed here 
     		}	
     		if($foundzz==0)
     		{					
     			$mytype=$all_coa_type[$z];
     			$acct_valuer=0;
     			
     			if($_POST['build_report']==1)
     			{
          			if($mytype=="Expenses")
          			{
          				$mreser=mrr_fetch_comparison_data_alt(10,$date_from,$date_to,$all_coa_numb[$z],'');	
          			}
          			elseif($mytype=="Income")
          			{
          				$mreser=mrr_fetch_comparison_data_alt(99,$date_from,$date_to,$all_coa_numb[$z],'');		//$all_coa_numb[$z]		//,'0','99999'
          			}
          			elseif($mytype=="Cost of Goods Sold")
          			{
          				$mreser=mrr_fetch_comparison_data_alt(98,$date_from,$date_to,$all_coa_numb[$z],'');
          			}
          			else
          			{
          				$mreser=mrr_fetch_comparison_data_alt(0,$date_from,$date_to,$all_coa_numb[$z],'');	
          			}
          			
          			foreach($mreser as $key => $value )
          			{
          				$prt=trim($key);		$tmp=trim($value);
          				if($prt=="Comparison")	$acct_valuer=(float)$tmp;
          			}
     			}
     			$mytype=str_replace("Cost of Goods Sold","COGS",$mytype);
                    $mytype=str_replace("Cost of Sales","COS",$mytype);
     			
     			$accts_unused.="
					<tr>
						<td valign='top'>".($already_grped+1)."</td>
						<td valign='top'>".$all_coa_grp[$z]."</td>
						<td valign='top'>".$all_coa_name[$z]."</td>
						<td valign='top'>".$all_coa_numb[$z]."</td>
						<td valign='top'>".$mytype."</td>
						<td valign='top' align='right'>$".number_format($acct_valuer,2)."</td>
					</tr>
				";
				$already_named[$already_grped]=$all_coa_grp[$z];
     			$already_grped++;
     		}
     	}
     }
     
$use_bootstrap = true; 
$usetitle = "Admin Budget Sections";	
?>
<? include('header.php') ?>
<?
	
	$mrr_table="";
	$sql="
		select * 
		from comparison_sections
		order by budget_name asc,id asc
	";		
	$data=simple_query($sql);
	if(mysqli_num_rows($data)==0)
	{
		mrr_populate_budget_sections();		
		
		$sql="
			select * 
			from comparison_sections
			order by budget_name asc,id asc
		";		
		$data=simple_query($sql);	
	}
	while($row=mysqli_fetch_array($data))
	{
		$id=$row['id'];	
		$dater=date("m/d/Y",strtotime($row['linedate_added']));
		$name=$row['budget_name'];
		$del=$row['deleted'];
		if($del==0)
		{
			$mrr_table.="
				<tr>
					<td valign='top'><a href='admin_budget_sections.php?id=".$id."&date_from=".$date_from."&date_to=".$date_to."'>".$name."</a></td>
					<td valign='top'>".$dater."</td>
					<td valign='top'>Yes</td>
				</tr>
			";
		}
	}


function mrr_populate_budget_sections()
{
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Fuel',0,1,'',0)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Insurance',0,1,'',1)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Labor(Drivers)',0,1,'',2)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Truck Maintenance',0,1,'',3)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Truck Repairs',1,1,'',4)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Tires',0,1,'',5)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Trailer Repairs',1,1,'',6)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Trailer Maintenance',0,1,'',7)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Truck Lease Fixed',0,1,'',8)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Mileage Expense',0,1,'',9)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Admin Expense',0,1,'',10)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Tolls',1,1,'',11)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Miscellaneous Expense',0,1,'',12)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Weigh Ticket Expense',1,1,'',13)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Trailer Rental Expense',0,1,'',14)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes,comparison_code) values (NULL,NOW(),'Accidents',0,1,'',15)";		
		simple_query($sql);
		$sql="insert into comparison_sections (id,linedate_added,budget_name,deleted,active,notes) values (NULL,NOW(),'Trailer Accidents',1,1,'',16)";		
		simple_query($sql);	
}

//echo $accts_unused3."<br>";
?>
<form action="<?=$SCRIPT_NAME?>?id=<?=$_POST['id']?>" method="post">
<div class='container col-md-12'>
	<div class='col-md-3'>
		<div class="panel panel-info">
			<div class="panel-heading">Admin Budget Sections</div>
			<div class="panel-body">
				
				<table class='table table-bordered well'>
          		<thead>
          		<tr>
          			<th valign='top'>Budget</th>
          			<th valign='top'>Updated</th>
          			<th valign='top'>Active</th>
          		</tr>
          		</thead>
          		<tbody id='mrr_body'>
          			<?= $mrr_table ?>
          		</tbody>
          		</table>				
				
			</div>
		</div>
	</div>
	<div class='col-md-4'>
		<div class="panel panel-primary">
			<div class="panel-heading">Admin Budget Section Update</div>
			<div class="panel-body">
				
				<table class='table table-bordered well'>
				<tr>
					<td valign='top'>Budget Section Label</td>
					<td valign='top'><input name='budget_name' id='budget_name' class='input_normal' value='<?= $_POST['budget_name'] ?>'></td>					
				</tr>		
				</table>	
				<p>
					<button type='submit' name='budget_items_saver' id='budget_items_saver' class='btn btn-primary'><span class="glyphicon glyphicon-floppy-disk"></span> Save Section</button>	
					<!-- <input type='submit' name='budget_items_saver' id='budget_items_saver' value='Save Section'> -->
				</p>
				<input type='hidden' id='id' name='id' value='<?= $_POST['id'] ?>'>
				<input type='hidden' id='item' name='item' value='<?= $_POST['item'] ?>'>
				<br>
				<p><b><?= $notice ?></b></p>
						
				<?			
     			//budget_items_saver
     			//budget_acct_saver
     			//budget_acct_adder
     			if($_POST['id'] > 0)
     			{
     				?>     
     				<br>
     				<p><b>Admin Budget Section Accounts</b></p>			
          			<table class='table table-bordered well'>				
          			<thead>
                    	<tr>          		
                    		<th valign='top'>Code</th>
                    		<th valign='top'>Account Desc</th>
                    		<th valign='top'>Updated</th>
                    		<th valign='top'>Active</th>
                    		<th valign='top'>Sub-Accounts</th>
                    	</tr>
                    	</thead>
          			<tbody id='mrr_body2'>
          				<?= $mrr_table2 ?>
          			</tbody>		
          			</table>	
          			
          			<br>
          			<p><b>Update Selected Account</b></p>	
          			<table class='table table-bordered well'>
          			<tr>
          				<td valign='top'>Change Code</td>
          				<td valign='top'><input name='diff_acct_code' id='diff_acct_code' class='input_normal' value='<?= $_POST['diff_acct_code'] ?>'></td>
          			</tr>
          			<tr>
          				<td valign='top'><label for='activator'>Active</a></td>
          				<td valign='top'><input type='checkbox' name='activator' id='activator' value='1'<?= ( $active>0 ? " checked" : "") ?>> Turn this code off for now...</td>
          			</tr>	
          			<tr>
          				<td valign='top'><label for='deactivator'>Remove</a></td>
          				<td valign='top'><input type='checkbox' name='deactivator' id='deactivator' value='1'> Remove this code from the list permanently.</td>
          			</tr>	
          			</table>	
          			<p>
          				<button type='submit' name='budget_acct_saver' id='budget_acct_saver' class='btn btn-primary'><span class="glyphicon glyphicon-floppy-disk"></span> Update Account</button>
          				<!-- <input type='submit' name='budget_acct_saver' id='budget_acct_saver' value='Update Account'> -->
          			</p>
          			<br>
          			<p><b>Add New Account</b></p>	
          			<table class='table table-bordered well'>
          			<tr>
          				<td valign='top'>Add Account Code</td>
          				<td valign='top'><input name='acct_code' id='acct_code' class='input_normal' value='<?= $_POST['acct_code'] ?>'></td>
          			</tr>		
          			</table>	
          			<p>
          				<button type='submit' name='budget_acct_adder' id='budget_acct_adder' class='btn btn-primary'><span class="glyphicon glyphicon-plus"></span> Add Account</button>
          				<!-- <input type='submit' name='budget_acct_adder' id='budget_acct_adder' value='Add Account'> -->
          			</p>
     				<?
     			}
     			?>	
				
			</div>
		</div>
	</div>
	<div class='col-md-5'>
		<div class="panel panel-info">
			<div class="panel-heading">Excluded Chart of Accounts</div>
			<div class="panel-body">
				
				<table class='table table-bordered well'>
				<tr>
          			<td valign='top'>
     				<?
               		$rfilter = new report_filter();
               		//$rfilter->show_driver 			= true;
               		//$rfilter->show_employers 		= true;
               		//$rfilter->summary_only	 		= true;
               		//$rfilter->team_choice	 		= true;
               		//$rfilter->show_font_size		= true;
               		$rfilter->mrr_no_form_enclosed	= true;
               		$rfilter->mrr_special_print_button	= false;
               		$rfilter->show_filter();
               		
               		if($_POST['build_report']==1)
               		{
               			echo "<div class='section_heading'>Values have been captured from date range.</div>";
               		}
               		else
               		{
               			echo "<div class='section_heading'>Press SUBMIT button to capture accounting values.</div>";	
               		}
               		?>
          			</td>
          		</tr>
          		</table>
				
				
				<table class='table table-bordered well'>
     			<thead>
               	<tr>          		
               		<th valign='top'>#</th>
               		<th valign='top'>Group</th>
               		<th valign='top'>Name</th>
               		<th valign='top'>Number</th>
               		<th valign='top'>Type</th>
               		<th valign='top' align='right'>Value</th>
               	</tr>
               	</thead>
     			<tbody id='mrr_nobody'>
     				<?= $accts_unused ?>
     			</tbody>				
     			</table>
								
			</div>
		</div>
	</div>
</div>

</form>
<script type='text/javascript'>
	$('.tablesorter').tablesorter();
	$('#date_from').datepicker();
	$('#date_to').datepicker();
	
	$().ready(function() {
		hide_all_sub_accounts();
	});
	
	function show_all_sub_accounts()
	{
		$('.all_accts').show();	
	}
	function hide_all_sub_accounts()
	{
		$('.all_accts').hide();	
	}
	function show_this_acct_subs(acctnum)
	{
		$('.acct_'+acctnum+'').show();
	}
	function hide_this_acct_subs(acctnum)
	{		
		$('.acct_'+acctnum+'').hide();
	}
</script>
<? include('footer.php') ?>