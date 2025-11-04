<?
ini_set("max_input_vars","40000");

	$use_title = "Report - View AR Details";
	$usetitle = "Report - View AR Details";
?>
<? include('header.php') ?>
<?

		
	if(isset($_GET['date_from']))	
	{
		$_GET['date_from']=str_replace("_","/",$_GET['date_from']);	
		$_POST['date_from']=$_GET['date_from'];
	}
	if(!isset($_POST['date_from'])) 		$_POST['date_from'] = date("m/d/Y");	//"-1 month", 
	if(!isset($_POST['mrr_aging_from'])) 	$_POST['mrr_aging_from'] = 0;
	if(!isset($_POST['mrr_aging_to'])) 	    $_POST['mrr_aging_to'] = 365;


    

function mrr_pull_acct_db()
{
    return "sicap_conard.";    
}

function mrr_avg_days_customer_pays($cust_id)
{	//get average amount of time customer "pays the bills" in days
     $days=0;
     $avg=0;
     $cntr=0;
     
     $sql = "select TO_DAYS(customers_payments.linedate_received) as paid_days,
					TO_DAYS(invoice.linedate_customer) as inv_days
			from ".mrr_pull_acct_db()."customers_payments, ".mrr_pull_acct_db()."customers_payments_details, ".mrr_pull_acct_db()."invoice, ".mrr_pull_acct_db()."customers
			where customers_payments.deleted = 0				
				and customers_payments.id=customers_payments_details.customers_payments_id
				and customers_payments.customer_id=customers.id
				and customers.id='".sql_friendly($cust_id)."'
				and customers_payments_details.invoice_id=invoice.invoice_number
				and invoice.deleted = 0
				and invoice.linedate_customer>='2010-08-01 00:00:00'
				and customers_payments.linedate_received>='2010-08-01 00:00:00'
			order by customers_payments.id asc
		";
     //echo "<br><br>New Query 2: ".$sql."<br><br>";
     $data = simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {
          $pdays=$row['paid_days'];
          $idays=$row['inv_days'];
          $sum=$pdays-$idays;
          
          $days+=$sum;
          
          $cntr++;
     }
     if($cntr>0)
     {
          $avg=$days/$cntr;
     }
     $res['avg']=$avg;
     $res['sql']=$sql;
     return $res;
}
function mrr_get_invoices_still_opened_v2($cust_id,$days_back,$days_to)
{
     $mrr_tab="
			<table class='tablesorter' width='100%'>
			<thead>
				<tr>
					<th valign='top'>Invoice</th>
					<th valign='top'>Memo</th>
					<th valign='top'>Date</th>
					<th valign='top'>Sent</th>
					<th valign='top' align='right'>Amount &nbsp;</th>
				</tr>
			</thead>
			<tbody>
		";
     
     $days_back2=$days_back;
     if($days_back==0)		$days_back2=(date("Y") - 2009) * 365;
     
     $all_tot=0;
     
     $sql = "select invoice.invoice_number,					
					invoice.total,
					invoice.id,
					invoice.notes,
					invoice.linedate,
					invoice.linedate_customer					
			from ".mrr_pull_acct_db()."invoice
				left join ".mrr_pull_acct_db()."customers on customers.id = invoice.customer_id
			where customers.id='".sql_friendly($cust_id)."'
				and invoice.deleted = 0
				and invoice.linedate>='2010-08-01 00:00:00'
				and invoice.linedate>=DATE_ADD(NOW(), INTERVAL -".$days_back2." DAY)
				and invoice.linedate<=DATE_ADD(NOW(), INTERVAL -".$days_to." DAY)
				 AND  invoice.id NOT IN
				  (SELECT
				    cpd.invoice_id
				  FROM
				    ".mrr_pull_acct_db()."customers_payments_details cpd
				    INNER JOIN ".mrr_pull_acct_db()."customers_payments cp
				      ON cp.id = cpd.`customers_payments_id`
				  WHERE cp.`customer_id` = customers.id)
			order by invoice.linedate desc
		";	//and customers_payments.linedate_received='0000-00-00 00:00:00'
     //echo "<br><br>New Query 3: ".$sql."<br><br>";
     $data = simple_query($sql);
     $mn=mysqli_num_rows($data);
     while($row = mysqli_fetch_array($data))
     {
          $number=$row['invoice_number'];
          $total=$row['total'];
          $id=$row['id'];
          $notes=$row['notes'];
          $dater=date("m/d/Y", strtotime($row['linedate']));
          $cdater=date("m/d/Y", strtotime($row['linedate_customer']));
          
          
          $notes=mrr_get_api_invoice_entries($id);
          if(substr_count($notes,"Base Rate - Load ID: ") > 0 && substr_count($notes," - (From:) ") > 0)
          {
               $pose1=strpos($notes,"Base Rate - Load ID: ");
               $pose2=strpos($notes," - (From:) ");
               $temp_id=substr($notes,$pose1,($pose2-$pose1));
               $temp_id=str_replace("Base Rate - Load ID: ","",$temp_id);
               $temp_id=str_replace(" - (From:) ","",$temp_id);
               $temp_id=trim($temp_id);
               $temp_id=(int)$temp_id;
               
               $link="<a href='manage_load.php?load_id=".$temp_id."' target='_blank'>".$temp_id."</a>";
               
               $notes=str_replace($temp_id,$link,$notes);
          }
          $mrr_tab.="
				<tr>
					<td valign='top'>".$number."</td>
					<td valign='top'>".$notes."</td>
					<td valign='top'>".$dater."</td>
					<td valign='top'>".$cdater."</td>
					<td valign='top' align='right'>$".number_format($total,2)." &nbsp;</td>
				</tr>
			";
          $all_tot+=$total;
     }
     
     $mrr_tab.="
				<tr>
					<td valign='top' colspan='4'>Total</td>
					<td valign='top' align='right'>$".number_format($all_tot,2)." &nbsp;</td>
				</tr>
				
				</tbody>
			</table>
		";
     if($mn==0)	$mrr_tab="";
     
     return $mrr_tab;
}
function mrr_get_api_invoice_entries($inv_id)
{
     $pay_items="";
     
     //invoice_entries.*,
     $sql = "select invoice_entries.item_desc,
					inventory.item_name					
			from ".mrr_pull_acct_db()."invoice_entries,".mrr_pull_acct_db()."inventory
			where invoice_entries.item_id=inventory.id
				and invoice_entries.invoice_id='".sql_friendly($inv_id)."'								
			order by invoice_entries.id desc
		";	//and customers_payments.linedate_received='0000-00-00 00:00:00'
     //echo "<br><br>New Query 4: ".$sql."<br><br>";
     $data = simple_query($sql);
     while($row = mysqli_fetch_array($data))
     {
          //$id=$row['item_id'];
          $name=$row['item_name'];
          $desc=$row['item_desc'];
          //$qty=$row['qty'];
          //$price=$row['unit_price'];
          //$shipped=$row['qty_shipped'];
          
          $pay_items.="
				<div width='100%' style='text-align:left;'>".$desc."</div>
			";
     }
     return $pay_items;     
}
?>	
	<table border='0'>
	<tr>
		<td valign='top'>
            <form name='mainform' action='<?=$_SERVER['SCRIPT_NAME']?>' method='post'>
                <table border='0' class='admin_menu1' width='1300'>
                <tr>
                    <td valign='top' colspan='8'><center><b><?=$usetitle ?></b></center></td>
                </tr>
                <tr>
                    <td valign='top' align='left' width='150'><b>Date</b></td>
                    <td valign='top' align='left' width='150'><input type='text' name='date_from' id='date_from' value='<?= $_POST['date_from'] ?>' class='input_medium' onBlur='mrr_load_up_ar_details();'></td>
                    <td valign='top' align='left' width='150'><b>Aging (days)</b></td>
                    <td valign='top' align='left' width='150'><input type='text' name='mrr_aging_from' id='mrr_aging_from' value='<?= $_POST['mrr_aging_from'] ?>' class='input_medium' onBlur='mrr_load_up_ar_details();'></td>
                    <td valign='top' align='left' width='150'>&nbsp; to &nbsp;</td>
                    <td valign='top' align='left' width='150'><input type='text' name='mrr_aging_to' id='mrr_aging_to' value='<?= $_POST['mrr_aging_to'] ?>' class='input_medium' onBlur='mrr_load_up_ar_details();'></td>
                    <td valign='top' align='right'>
                        <input type="submit" name="mrr_reload" value="Run Report">                        
                    </td>
                    <td valign='top' align='right'><span class='mrr_link_like_on' onClick='mrr_toggle_inv_display();'>Toggle Invoice List</span></td>
                </tr>
                </table>
                <br>
                <table border='0' class='admin_menu2' width='1300'>
                <tr>		
                    <td valign='top'>
                         <?php
                             //code copied from the Accounting side.... using direct database connection since on same server....  Goes around data limits for the AJAX process to pass the full table.
                             $cust_id=0;                             //$_POST['cust_id'];
                             $cust_name="";                          //$_POST['cust_name'];
                             // //$date_from=$_POST['date_from'];
                             $date_to=$_POST['date_from'];           //$_POST['date_to'];
    
                             $aging_from=$_POST['mrr_aging_from'];   //$_POST['aging_from'];
                             $aging_to=$_POST['mrr_aging_to'];       //$_POST['aging_to'];
    
                             $use_aging_type = "".mrr_pull_acct_db()."uf_ar_aging_on_date";
                             $mrr_val=0.00;
                             $mncnt=0;
                             $mrr_result="";
    
                             $mrr_adder="";
                             if($cust_id>0)
                             {
                                  $mrr_adder.=" and customers.id='".sql_friendly($cust_id)."'";
                             }
                             if(trim($cust_name)!="")
                             {
                                  $mrr_adder.=" and customers.name_company='".sql_friendly($cust_name)."'";
                             }
                             //get vendor ID first
                             $mrrdays="700";	//365*(date("Y") - 2010)
                             //if($aging_to > 700)	$aging_to=700;
    
                             echo "
                                <table width='100%'>
                                <thead>
                                        <tr>
                                            <th valign='top'>Customer</th>
                                            <th valign='top' nowrap align='right'>0 to 15</th>
                                            <th valign='top' nowrap align='right'>16 to 30</th>
                                            <th valign='top' nowrap align='right'>31 to 45</th>
                                            <th valign='top' nowrap align='right'>46+ Days</th>
                                            <th valign='top' nowrap align='right'>Total</th>
                                            <th valign='top' nowrap align='right'>Avg Age</th>
                                        </tr>
                                </thead>
                                <tbody>   				
                            ";
    
                             $ar_fifteen=0;
                             $ar_thirty=0;
                             $ar_forty_five=0;
                             $ar_old=0;
                             $tline=0;
                             $tot_all=0;
                             $avg_tot=0;
    
                             $sql = "select 
                                    $use_aging_type(id,0,15,'".date("Y-m-d", strtotime($date_to))."') as ar_fifteen,
                                    $use_aging_type(id,16,30,'".date("Y-m-d", strtotime($date_to))."') as ar_thirty,
                                    $use_aging_type(id,31,45,'".date("Y-m-d", strtotime($date_to))."') as ar_forty_five,
                                    $use_aging_type(id,46,".$mrrdays.",'".date("Y-m-d", strtotime($date_to))."') as ar_old,
                                    $use_aging_type(id,".$aging_from.",".$aging_to.",'".date("Y-m-d", strtotime($date_to))."') as ar_all,
                                    customers.*		
                                from ".mrr_pull_acct_db()."customers
                                where customers.deleted = 0
                                    ".$mrr_adder."
                                order by name_company
                            ";
                             //echo "<br><br>New Query: ".$sql."<br><br>";
                             $data = simple_query($sql);
                             while($row = mysqli_fetch_array($data))
                             {
                                  $cname=$row['name_company'];
                                  $cid=$row['id'];
         
                                  $res=mrr_avg_days_customer_pays($cid);
                                  $avg=$res['avg'];
                                  //$mysql=$res['sql'];	
         
                                  $all_of_them=$row['ar_all'];
                                  if($all_of_them!=0)
                                  {
                                       $tab_inv=mrr_get_invoices_still_opened_v2($cid,$aging_to,$aging_from);		//get all the invoices that are aged...and still opened...
                                       if(trim($tab_inv)!="")
                                       {
                                            $total_line = 0;
                                            $total_line += $row['ar_fifteen'];
                                            $total_line += $row['ar_thirty'];
                                            $total_line += $row['ar_forty_five'];
                                            $total_line += $row['ar_old'];
                   
                                            $ar_fifteen+=$row['ar_fifteen'];
                                            $ar_thirty+=$row['ar_thirty'];
                                            $ar_forty_five+=$row['ar_forty_five'];
                                            $ar_old+=$row['ar_old'];
                                            $tline+=$total_line;
                                            $tot_all+=$row['ar_all'];
                   
                                            $avg_tot+=$avg;
                   
                                            echo "
                                                <tr class='".( $mncnt%2==0 ? 'even' : 'odd' )."'>          					
                                                    <td valign='top'>".$cname."</td>
                                                    <td valign='top' align='right'>$".number_format($row['ar_fifteen'],2)."</td>
                                                    <td valign='top' align='right'>$".number_format($row['ar_thirty'],2)."</td>
                                                    <td valign='top' align='right'>$".number_format($row['ar_forty_five'],2)."</td>
                                                    <td valign='top' align='right'>$".number_format($row['ar_old'],2)."</td>
                                                    <td valign='top' align='right'>$".number_format($total_line,2)."</td>
                                                    <td valign='top' align='right'>".number_format($avg,2)."</td>
                                                </tr>
                                                <tr class='mrr_show_invoices ".( $mncnt%2==0 ? 'even' : 'odd' )."'>          					
                                                    <td valign='top' colspan='6'>".$tab_inv."</td>
                                                    <td valign='top'>&nbsp;</td>
                                                </tr>  
                                                <tr class='mrr_show_invoices ".( $mncnt%2==0 ? 'even' : 'odd' )."'>          					
                                                    <td valign='top' colspan='7'>&nbsp;</td>
                                                </tr>    				
                                            ";
                                            $mncnt++;
                                            //$mrr_val +=$total_line;
                                       }
                                  }
                             }
                             $avg_cal=0;
                             if($mncnt>0)		$avg_cal=$avg_tot/$mncnt;
                             echo "
                                <tr>
                                    <td valign='top' colspan='7'>&nbsp;</td>
                                </tr>  
                                <tr>
                                    <td valign='top'><b>Customer</b></th>
                                    <td valign='top' nowrap align='right'><b>0 to 15</b></td>
                                    <td valign='top' nowrap align='right'><b>16 to 30</b></td>
                                    <td valign='top' nowrap align='right'><b>31 to 45</b></td>
                                    <td valign='top' nowrap align='right'><b>46+ Days</b></td>
                                    <td valign='top' nowrap align='right'><b>Total</b></td>
                                    <td valign='top' nowrap align='right'><b>Avg Age</b></td>
                                </tr>
                                <tr>
                                    <td valign='top'>Total</td>
                                    <td valign='top' align='right'>$".number_format($ar_fifteen,2)."</td>
                                    <td valign='top' align='right'>$".number_format($ar_thirty,2)."</td>
                                    <td valign='top' align='right'>$".number_format($ar_forty_five,2)."</td>
                                    <td valign='top' align='right'>$".number_format($ar_old,2)."</td>
                                    <td valign='top' align='right'>$".number_format($tline,2)."</td>
                                    <td valign='top' align='right'>".number_format($avg_cal,2)."</td>
                                </tr>
                            </tbody>
                            </table>		
                            ";                       
                         
                         ?>
                        <div id='mrr_report_view'></div>
                    </td>
                </tr>
                </table>
            </form>
     	</td>
     </tr>
     </tr>
	</table>
<script type='text/javascript'>
	$('#date_from').datepicker();
	//$('#date_to').datepicker();
	
	$().ready(function() 
	{	
		//mrr_load_up_ar_details();			

        $('.tablesorter').tablesorter();
        //mrr_toggle_inv_display();
        $('.mrr_show_invoices').hide();
    });
	
	function mrr_toggle_inv_display()
	{
		$('.mrr_show_invoices').toggle();		
	}

    function mrr_load_up_ar_details()
    {
        document.mainform.submit();
    }
	function mrr_load_up_ar_details_old()
	{
		$('#mrr_report_view').html('Loading...Takes about 38 Seconds...');				//mrr_get_ar_detail_info_find
		
		$.ajax({
			url: "ajax.php?cmd=mrr_get_ar_detail_info_find_v2",
			type: "post",
			dataType: "xml",
			data: {
				"date_from": $('#date_from').val(),
				"aging_from": $('#mrr_aging_from').val(),
				"aging_to": $('#mrr_aging_to').val()
			},
			error: function() {
				$.prompt("Error: AR Details could not be found.");
				$('#mrr_report_view').html('Done. No information found.');
			},
			success: function(xml) {
				if($(xml).find('mrrTab').text() == '')
				{
					$('#mrr_report_view').html('Done. No information found.');
				}
				else
				{
					$('#mrr_report_view').html($(xml).find('mrrTab').text());	
					$('.tablesorter').tablesorter();		
					mrr_toggle_inv_display();
				}
			}
		});
		
	}
	
</script>
<? include('footer.php') ?>