<? include_once('application.php') ?>
<?
	//display_xml_response("<rslt>1</rslt><ReportInfo><![CDATA[$_POST[report_contents]]]></ReportInfo>");
	
	$print_contents_all = "";
	$file_array = array();
	$return_var_email = "";
	$id_array = array();
	
	if(!isset($_POST['report_contents']))		$_POST['report_contents']="";
	if(!isset($_POST['report_title']))			$_POST['report_title']="";
	if(!isset($_POST['script_name']))			$_POST['script_name']="";
	if(!isset($_POST['display_mode']))			$_POST['display_mode']=0;
	if(!isset($_POST['form_mode']))			$_POST['form_mode']=0;
	
	
	/*
	//create the Conard Load Printout.............................................
	if(!isset($_POST['print_load_summary_id']))	$_POST['print_load_summary_id']=0;
	if($_POST['print_load_summary_id'] > 0)
	{
		$_POST['script_name']="print_customer_load.php?load_id=".$_POST['print_load_summary_id']."";
		$_POST['report_title']="Conard Load Printout";
		$_POST['display_mode']=0;
		$_POST['form_mode']=1;
		
		
		
		
		
	}
	//............................................................................
	*/
	
	$_POST['report_contents'] = stripslashes(rawurldecode($_POST['report_contents']));
	
	$print_report=true;
	$report_title=$_POST['report_title'];
 	$report_url=$_POST['script_name'];
	$report_body=$_POST['report_contents'];
	$rep_id=date("YmdHis");
	
	$display_mode=$_POST['display_mode'];
	$form_mode=$_POST['form_mode'];
	$mrr_page_numbers=true;
		
	if($print_report) {
		ob_start(); 
		?>
		
			<style>
			<? include('style.css') ?>
			</style>
			<link rel="stylesheet" href="style.css" type="text/css">
			<?
			if($form_mode > 0)
			{	//report body already has the form, and thus no need for borders, page_numbers, etc.
			?>
				<table class='standard12' style='width:800px;border:0px black solid;border-width:0px 0px 0 0;margin-top:-15px' cellspacing='0'>
     			<tr>
     				<td valign='top'>
     				<? 
     				
     				echo $report_body;
     				$mrr_page_numbers=false;
     				
     				?>
     				</td>
     			</tr>	
     			</table>
			<?
			}
			else
			{
			?>			
     			<table class='standard12' style='width:800px;border:1px black solid;border-width:1px 1px 0 0;margin-top:-15px' cellspacing='0'>
     			<tr>
     				<td valign='top'>
     				<? 
     				echo "
     					<div><b>".$report_title."</b></div>
     					<br>
     				";
     				
     				echo $report_body;     				
     				$mrr_page_numbers=true;
     				
     				echo "
     					<br>
     					<div>..".$report_url."</div>";
     				?>
     				</td>
     			</tr>	
     			</table>
			<?
			}
			?>
			
		<? 
		$pdf_contents = ob_get_contents();
		ob_end_clean();
		ob_start(); 
		
			$print_contents_all .= $pdf_contents;
			
			if(isset($_GET['debug'])) {
				//echo $print_contents_all;
			} else {
				
				$fname = print_contents('printed_report_'.$rep_id.'', $pdf_contents, $display_mode, '', '',$mrr_page_numbers);
				$file_array[] = $fname;
				
				if(isset($_GET['email'])) {
					
					$use_email_to = $_GET['email_to'];
					
					$from = $defaultsarray['emails_from'];
					$fromname = $from;
					$to = $use_email_to;
					$toname = $to;
					
					$faxcode=0;
               		if(isset($_GET['fax_code']))	$faxcode=1;
                              		
               		$area_code=mrr_get_default_area_code();
               		$reply_addr=$from;
               		$reply_name=$fromname;
               		
               		if(isset($_GET['reply_addr']) && $_GET['reply_addr']!="")
               		{
               			$reply_addr=str_replace("+"," ",$_GET['reply_addr']);
               			$reply_addr=trim($reply_addr);
               		}
               		if(isset($_GET['reply_name']) && $_GET['reply_name']!="")
               		{
               			$reply_name=str_replace("+"," ",$_GET['reply_name']);
               			$reply_name=trim($reply_name);
               		}
               		                              		
               		//use fax instead......
               		if($faxcode==1)
               		{	//filter for fax number...
               			$to=strtolower($to);
               			$to=str_replace("ext","",$to);
               			$to=str_replace(".","",$to);
               			$to=str_replace("*","",$to);
               			$to=str_replace("#","",$to);
               			$to=str_replace("-","",$to);
               			$to=str_replace("(","",$to);
               			$to=str_replace(")","",$to);
               			$to=str_replace(" ","",$to);
               			if(strlen($to) == 7)  	$to="1615".$to;
						if(strlen($to) == 10) 	$to="1".$to;
               			$to=trim($to);
               			$to=(int) $to;
               			
               			if(is_numeric($to))		$to.="@rcfax.com";			
               		}
               		//.....................
               		
               		$tmp_subject="";
               		if(isset($_GET['email_subject']))	$tmp_subject=trim($_GET['email_subject']);
               		
               		$tmp_subject=str_replace("+"," ",$tmp_subject);
               		
               		if($tmp_subject!="")
               			$subject=$tmp_subject;		
               		else
						$subject = $report_title;
					
					$text = "Report number: ".$rep_id." is attached to this E-Mail as a PDF";
					$html = $text;
					$attm = $fname;
			
					//$_POST['email_section_id'] = SECTION_INVOICE;
					
					ob_start();
					sendMail($from,$fromname,$to,$toname,$subject,$text,$html,getcwd().'/'.$fname,$faxcode,$reply_name,$reply_addr);
					$email_result_text = ob_get_contents(); 
					ob_end_clean();
					
					if($email_result_text == '') {
						$email_result = 1;
					} else {
						$email_result = 0;
					}
					$return_var_email = "
						<EmailResult>$email_result</EmailResult>
						<EmailResultText>$email_result_text</EmailResultText>
					";
				}//end if email
			}//end else
			
		ob_end_clean();
     	ob_start();
     	$new_filename = "";
     	if(count($file_array) > 1 && !isset($_GET['email'])) {
     		// there are multiple files, so join them together
     		$filelist = implode("|", $file_array);
     		$new_filename = "temp/".$fname_pre.'_'.createuuid().'.pdf';
     
     		
     		$pdf_split = new COM("PDFSplitMerge.PDFSplitMerge");
     		$pdf_split->SetCode("9898885DCB97DE31B6D42C"); // sets the key so it knows it's been registered and is not in a trial mode
     		$pdf_split->Merge($filelist,getcwd().'/'.$new_filename);
     	} else {
     		$new_filename = trim($file_array[0]);
     	}
     	ob_end_clean();
     	
     	if(isset($_GET['debug1'])) {
     		//echo $filelist;
     	} else {
     				
     		$return_var = "<PDFName>$new_filename</PDFName>";
     		
     		display_xml_response($return_var.$return_var_email);
     	}
	
	//end if PRINT_REPORT==true
	}
	
?>