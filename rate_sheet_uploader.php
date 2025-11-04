<? include('application.php') ?>
<? include('header.php') ?>
<?
$target_dir = "../incoming_rate_sheets/";

function mrr_get_text_from_pdf($filename_with_path)
{
     global $defaultsarray;
     
     $tmp_filename = "temp\\".createuuid()."txt";
     
     $cmd = $defaultsarray['pdftotext_path'].'\pdftotext.exe -simple "'.$filename_with_path.'" "'.__DIR__.'\\'.$tmp_filename.'"';
     
     exec($cmd, $output, $rslt);
     
     $output = file_get_contents($tmp_filename);
     unlink($tmp_filename);
     
     $return_array = array('cmd_result' => $rslt,
          'content' => $output);
     
     return $return_array;
}
function mrr_get_text_from_word($filename_with_path)
{
     //global $defaultsarray;
     
     //$tmp_filename = "temp\\".createuuid()."txt";
     
     //$cmd = $defaultsarray['pdftotext_path'].'\pdftotext.exe -simple "'.$filename_with_path.'" "'.__DIR__.'\\'.$tmp_filename.'"';
     
     //exec($cmd, $output, $rslt);
     
     $output = file_get_contents($filename_with_path);
     //unlink($tmp_filename);
     
     $return_array = array('cmd_result' => true,
          'content' => $output);
     
     return $return_array;
}

if(isset($_POST['upload']))
{
     //upload the file(s) here if any selected.
     for($i=0; $i<count($_FILES["fileToUpload"]); $i++)
     {
          if(isset($_FILES["fileToUpload"]["name"][$i]))
          {
               $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"][$i]);
               $fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
               //$check = getimagesize($_FILES["fileToUpload"]["tmp_name"][$i]);                   //$check["mime"]
     
               //if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" )
     
               //if(file_exists($target_file))
               //if($_FILES["fileToUpload"][$i]["size"] > 500000)         //=500KB
     
               if($fileType != "pdf" && $fileType != "doc" )
               {
                    echo "<br>File ".($i+1).". Sorry, only PDF/Word files are supported at this time. {". basename( $_FILES["fileToUpload"]["name"][$i]). "}";
               }
               else
               {
                    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"][$i], $target_file))
                    {
                         echo "<br>File ".($i+1).". The file ". basename( $_FILES["fileToUpload"]["name"][$i]). " has been uploaded.";
                    }
                    else
                    {
                         echo "<br>File ".($i+1).". Sorry, there was an error uploading ".basename( $_FILES["fileToUpload"]["name"][$i])." file.";
                    }
               }
          }
     }
}
?>
<form name='mainform' action='rate_sheet_uploader.php' method='post' style='text-align:left' enctype="multipart/form-data">
     <h2>Upload Rate Sheets to Create Loads</h2>
     <table class='section0_long' border='0'>
          <tr>
               <td valign='top'>Upload to</td>
               <td valign='top'><b><?=$target_dir ?></b></td>
               <td valign='top'>
                    <input type="file" name="fileToUpload[]" id="fileToUpload" multiple>
                    <input type="submit" value="Upload Rate Sheets" name="upload">
               </td>
          </tr>
          <tr>
               <td valign="top" colspan="3">
                    <b>PDF files supported for these Customers/Brokers:</b>
                    <table cellpadding="0" cellspacing="0" border='0' width="100%">
                         <tr>
                              <td valign="top">COMPANY</td>
                              <td valign="top"><span title="Use the Naming text in the file so the system can pick the correct company.">FILE NAMING/KEYWORD</span></td>
                              <td valign="top" colspan="2" align="center">PROCESS MANUALLY</td>
                         </tr>
                         <tr>
                              <td valign="top">* Conard Logistics</td>
                              <td valign="top">"DeliverySheet" or "Agreement"</td>
                              <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=7" target="_blank">DeliverySheet</a></td>
                              <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=6" target="_blank">Agreements</a></td>
                         </tr>
                         <tr>
                              <td valign="top">* CH Robinson</td>
                              <td valign="top">"CHR"</td>
                              <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=0" target="_blank">Process</a></td>
                              <td valign="top">&nbsp;</td>
                         </tr>
                         <tr>
                              <td valign="top">* Essex Geodis</td>
                              <td valign="top">"Essex"</td>
                              <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=1" target="_blank">Process</a></td>
                              <td valign="top">&nbsp;</td>
                         </tr>
                         <tr>
                              <td valign="top">* Koch Logistics</td>
                              <td valign="top">"Koch"</td>
                              <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=4" target="_blank">Process</a></td>
                              <td valign="top">&nbsp;</td>
                         </tr>
                        <!---
                         <tr>
                              <td valign="top">* Quad Graphics, Inc. (was Quad Logistics Services LLC)</td>
                              <td valign="top">"Quad"</td>
                              <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=2" target="_blank">Process</a></td>
                              <td valign="top">&nbsp;</td>
                         </tr>
                         <tr>
                              <td valign="top">* Sonoco </td>
                              <td valign="top">"Sonoco"</td>
                              <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=3" target="_blank">Process</a></td>
                              <td valign="top"></td>
                         </tr>
                         ----->
                         <tr>
                              <td valign="top">* Schneider </td>
                              <td valign="top">"Schneider"</td>
                              <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=5" target="_blank">Process</a></td>
                              <td valign="top"></td>
                         </tr>

                        <tr>
                             <td valign="top">* Quality Logistics </td>
                             <td valign="top">"QL"</td>
                             <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=8" target="_blank">Process</a></td>
                             <td valign="top"></td>
                        </tr>
                        <tr>
                            <td valign="top">* Goldstar </td>
                            <td valign="top">"GS"</td>
                            <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=9" target="_blank">Process</a></td>
                            <td valign="top"></td>
                        </tr>
                        <tr>
                            <td valign="top">* Performance Foodservice </td>
                            <td valign="top">"PFG"</td>
                            <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=10" target="_blank">Process</a></td>
                            <td valign="top"></td>
                        </tr>
                        <!---
                        <tr>
                            <td valign="top">* JB Hunt </td>
                            <td valign="top">"JB"</td>
                            <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=11" target="_blank">...</a><i>Coming Soon...</i></td>
                            <td valign="top"></td>
                        </tr>
                        ---->
                        <tr>
                            <td valign="top">* Plasticycle </td>
                            <td valign="top">"PC"</td>
                            <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=12" target="_blank">Process</a></td>
                            <td valign="top"> <i>< - - Manual Only, no Auto.</i></td>
                        </tr>
                        <tr>
                            <td valign="top">* Echo Global Logistics Inc </td>
                            <td valign="top">"Echo"</td>
                            <td valign="top"><a href="mrr_rate_sheets.php?pdf_comp=13" target="_blank">Process</a></td>
                            <td valign="top"></td>
                        </tr>
                        
                        
                    </table>
               </td>
          </tr>
          <tr style="background-color:#FFFFFF; padding-left:10px;">
               <td valign="top" colspan="3">
                    <div style="float:right; margin-right:10px;"><a href="rate_sheet_uploader.php"><input type="button" value="Refresh/Scan/Rename Files"></a></div>
                    <b>Contents of <?=$target_dir ?>:</b
                    <br>
                    <table cellpadding="0" cellspacing="0" border='0' width="100%">
                         <tr>
                              <td valign="top"><b>#</b></td>
                              <td valign="top"><b>TYPE</b></td>
                              <td valign="top"><b>NAME</b></td>
                              <td valign="top"><b>COMPANY</b></td>
                              <td valign="top"><b>ARCHIVE</b></td>
                         </tr>
                         <?
                         $mrr_full_path="c:\\web\\trucking.conardlogistics.com\\incoming_rate_sheets\\";
                         
                         $file_cntr=0;
                         $files_list = scandir($target_dir);
                         foreach ($files_list as $key => $value)
                         {
                              $type="File";
                              if($value=="." || $value==".." || $value=="MRR_TestFiles")       $type="N/A";
                         
                              if($value=="problem" || $value=="hold" || $value=="completed")   $type="Folder";
                                                            
                              if($type=="File" || $type=="Folder")
                              {
                                   $file_cntr++;
                                   $mrrCompanyType="";
                                   if($type=="File")
                                   {
                                        if(stripos($value,"Quad") !== false)                $mrrCompanyType="Quad";
                                        elseif(stripos($value,"CHR") !== false)             $mrrCompanyType="CHR";
                                        elseif(stripos($value,"Koch") !== false)            $mrrCompanyType="Koch";
                                        elseif(stripos($value,"Schneider") !== false)       $mrrCompanyType="Schneider";
                                        elseif(stripos($value,"Essex") !== false)           $mrrCompanyType="Essex";
                                        elseif(stripos($value,"Sonoco") !== false)          $mrrCompanyType="Sonoco";
                                        elseif(stripos($value,"DeliverySheet") !== FALSE)   $mrrCompanyType = "DeliverySheet";
                                        elseif(stripos($value,"Agreement") !== FALSE)       $mrrCompanyType = "Agreement";
                                        elseif(stripos($value,"QL") !== false)              $mrrCompanyType="QL";
                                        elseif(stripos($value,"GS") !== false)              $mrrCompanyType="GS";
                                        elseif(stripos($value,"PFG") !== false)             $mrrCompanyType="PFG";
                                        elseif(stripos($value,"JB") !== false)              $mrrCompanyType="JB";
                                        elseif(stripos($value,"PC") !== false)              $mrrCompanyType="PC";
                                        elseif(stripos($value,"Echo") !== false)            $mrrCompanyType="Echo";
     
                                        //No name match found in filename, so attempt to pull it from the file contents     
                                        if(trim($mrrCompanyType)=="")   
                                        {
                                             if(stripos($value,"PC") !== false)
                                             {
                                                  $pdfToText = mrr_get_text_from_word($mrr_full_path . "" . $value);
                                             }
                                             else 
                                             {     
                                                  $pdfToText = mrr_get_text_from_pdf($mrr_full_path . "" . $value);
                                             }
     
                                             //test for various key words/phrases in the the file...
                                             if(stripos($pdfToText['content'],"Quad Logistics") !== false)                             $mrrCompanyType="Quad";
                                             elseif(stripos($pdfToText['content'],"C.H. Robinson") !== false)                          $mrrCompanyType="CHR";
                                             elseif(stripos($pdfToText['content'],"Koch Logistics") !== false)                         $mrrCompanyType="Koch";
                                             elseif(stripos($pdfToText['content'],"Schneider Shipment") !== false)                     $mrrCompanyType="Schneider";
                                             elseif(stripos($pdfToText['content'],"Essex") !== false)                                  $mrrCompanyType="Essex";
                                             elseif(stripos($pdfToText['content'],"Sonoco") !== false)                                 $mrrCompanyType="Sonoco";
                                             elseif(stripos($pdfToText['content'],"CONARD LOGISTICS, INC.") !== false)
                                             {
                                                  if(stripos($pdfToText['content'], "CARRIER PICKUP & DELIVERY SCHEDULE") !== FALSE)   $mrrCompanyType = "DeliverySheet";
                                                  elseif(stripos($pdfToText['content'], "CARRIER VERBAL RATE AGREEMENT") !== FALSE)    $mrrCompanyType = "Agreement";
                                             }
                                             elseif(stripos($pdfToText['content'],"Quality Logistics") !== false)                      $mrrCompanyType="QL";
                                             elseif(stripos($pdfToText['content'],"QUALITY LOGISTICS") !== false)                      $mrrCompanyType="QL";
                                             elseif(stripos($pdfToText['content'],"Goldstar") !== false)                               $mrrCompanyType="GS";
                                             elseif(stripos($pdfToText['content'],"Gold Star") !== false)                              $mrrCompanyType="GS";
                                             elseif(stripos($pdfToText['content'],"PFG Nashville") !== false)                          $mrrCompanyType="PFG";
                                             elseif(stripos($pdfToText['content'],"Performance Foodservices") !== false)               $mrrCompanyType="PFG";
                                             elseif(stripos($pdfToText['content'],"JB Hunt") !== false)                                $mrrCompanyType="JB";
                                             elseif(stripos($pdfToText['content'],"Plasticycle") !== false)                            $mrrCompanyType="PC";
                                             elseif(stripos($pdfToText['content'],"www.plasticycle.com") !== false)                    $mrrCompanyType="PC";
                                             elseif(stripos($pdfToText['content'],"Echo Global Logistics") !== false)                  $mrrCompanyType="Echo";
                                             elseif(stripos($pdfToText['content'],"Echo Shipment") !== false)                          $mrrCompanyType="Echo";
     
                                             if(trim($mrrCompanyType)!="")
                                             {    //rename the file and continue so the file has the right namining convention.
                                                  $new_filer=trim($mrrCompanyType)."_".$value;
                                                  if(rename($mrr_full_path."".$value, $mrr_full_path."".$new_filer))
                                                  {
                                                       $value=$new_filer;
                                                  }
                                             }
                                        }                              
                                        
                                   }                                 
                                   ?>
                                   <tr class="file_<?=$file_cntr ?>_row">
                                        <td valign="top">[<?=$file_cntr ?>]</td>
                                        <td valign="top"><?=$type ?></td>
                                        <td valign="top"><?=($type=="Folder" ? "<b><i>".$value."</i></b>" : "".$value."") ?></td>
                                        <td valign="top"><?=$mrrCompanyType ?></td>
                                        <td valign="top">
                                             <?=($type=="Folder" ? "&nbsp;" : "<span style='color:#00CC00; cursor:pointer;' onClick=\"mrr_update_ratesheet_status('".$value."',".$file_cntr.",1);\">Complete</span> | ") ?>
                                             <?=($type=="Folder" ? "&nbsp;" : "<span style='color:#0000CC; cursor:pointer;' onClick=\"mrr_update_ratesheet_status('".$value."',".$file_cntr.",2);\">Hold</span> | ") ?>
                                             <?=($type=="Folder" ? "&nbsp;" : "<span style='color:#CC0000; cursor:pointer;' onClick=\"mrr_update_ratesheet_status('".$value."',".$file_cntr.",3);\">Problem</span>") ?>
                                        </td>
                                   </tr>
                                   <?
                              }
                         }
                         ?>
                    </table>
               </td>
          </tr>
     </table>
     <script type='text/javascript'>
          function mrr_update_ratesheet_status(namer,cntr,moder)
          {
              $.ajax({
                  url: "ajax.php?cmd=mrr_rate_sheet_upload_mover",
                  type: "post",
                  dataType: "xml",
                  data: {
                      "file": namer,
                      "mode": moder
                  },
                  error: function() {
                      $.prompt("Error: Cannot remove "+namer+" file. :( ");
                  },
                  success: function(xml) {
                      $.prompt("Moved File "+namer+" from Incoming Rate Sheets processing directory. :) ");
                      $('.file_'+cntr+'_row').hide();
                  }
              });
          }
     </script>
</form>
<? include('footer.php') ?>