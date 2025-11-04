<?

class ConardRateSheets {
     
     public $archiveFiles = true; // when false, the completed files won't be moved to completed or problem
     public $renameToUniqueFilenames = true; // when false, the files won't be renamed to a unique filename
     public $filenameFilter = "";
     public $returnFirstRsltObjOnly = false; // when set, the file won't be fully processed, instead the rsltObj will be returned for the first match
     
     public function getRateSheets() {
          global $defaultsarray;
          
          $path = $defaultsarray['rate_sheets_to_be_processed_path'];
          $files = scandir($path);
          
          $fileArray = array();
          foreach($files as $file) {
               if(is_file($path.$file) && (stripos($file, ".pdf") !== false || stripos($file, ".doc") !== false)) {
                    $fileArray[] = $path.$file;
               }
          }
          
          return $fileArray;
     }
     
     public function processRateSheets() {
          
          $files = $this->getRateSheets();
          
          foreach($files as $file) {
               
               // build some temp filename parts
               $pathInfo = pathinfo($file);
               $path = $pathInfo['dirname']."\\";
               $basefile = $pathInfo['basename'];
               $tmpFile = $pathInfo['filename']."-".createuuid().".".$pathInfo['extension'];
               $tmpFileWithPath = $path.$tmpFile;
               
               $processFile = true;
               
               if(!empty($this->filenameFilter) && stripos($basefile, $this->filenameFilter) === false) {
                    // a filename filter is in place, and the filter isn't found in the file
                    // so, don't process this file
                    $processFile = false;
               }
               
               if($this->returnFirstRsltObjOnly) {
                    // this flag means we're in test mode, so don't rename anything
                    $this->renameToUniqueFilenames = false;
               }
               
               if($processFile) {
                    
                    // rename to a temp file so we have a unique filename, and can be sure
                    // we have exclusive file system access to the file (which also makes
                    // sure it's not in the process of being uploaded
                    if ($this->renameToUniqueFilenames) {
                         $renameRslt = rename($file, $tmpFileWithPath);
                    } else {
                         // flag is set to *not* rename the file to a temporary file,
                         // so manually set our flag that the rename was successful in order to process the file
                         $renameRslt = true;
                         
                         // since we aren't renaming our file, set the $tmpFileWithPath variable to be our real file
                         $tmpFileWithPath = $file;
                         $tmpFile = $basefile;
                    }
                    if ($renameRslt) {
     
                         if (stripos($basefile, "PC") !== false)
                         {    //Platicycle uses a NON PDF format (Word).
                              $pdfToText_temp = file_get_contents($tmpFileWithPath);
                              $pdfToText['content']=trim($pdfToText_temp);
                         }
                         else 
                         {
                              // get the raw text from the PDF
                              $pdfToText = get_text_from_pdf($tmpFileWithPath);
                         }                        
                         // set an empty rsltObj so we're sure to have something to work with at the end
                         $rsltObj = new ConardRateSheetResponseObj();
                         
                         if (stripos($basefile, "quad") !== false) {
                              $rsltObj = $this->processQuad($pdfToText['content']);
                         } else if (stripos($basefile, "CHR") !== false) {
                              $rsltObj = $this->processChRobinson($pdfToText['content']);
                         } else if (stripos($basefile, "Essex") !== false) {
                              $rsltObj = $this->processEssex($pdfToText['content']);
                         } else if (stripos($basefile, "Sonoco") !== false) {
                              $rsltObj = $this->processSonoco($pdfToText['content']);
                         } else if (stripos($basefile, "Koch") !== false) {
                              $rsltObj = $this->processKoch($pdfToText['content']);
                         } else if (stripos($basefile, "Schneider") !== false) {
                              $rsltObj = $this->processSchneider($pdfToText['content']);
                         } else if (stripos($basefile, "Agreement") !== false) {
                              $rsltObj = $this->processConard($pdfToText['content'],1);
                         } else if (stripos($basefile, "DeliverySheet") !== false) {
                              $rsltObj = $this->processConard($pdfToText['content'],0);
                         }
                         else if (stripos($basefile, "QL") !== false) {
                              $rsltObj = $this->processQL($pdfToText['content'],0);
                         }
                         else if (stripos($basefile, "GS") !== false) {
                              $rsltObj = $this->processGS($pdfToText['content'],0);
                         }
                         else if (stripos($basefile, "PFG") !== false) {
                              $rsltObj = $this->processPFG($pdfToText['content'],0);
                         }
                         else if (stripos($basefile, "JB") !== false) {
                              //$rsltObj = $this->processJB($pdfToText['content'],0);
                         }
                         else if (stripos($basefile, "PC") !== false) {
                              $rsltObj = $this->processPC($pdfToText['content'],0);
                         }
                         else if (stripos($basefile, "Echo") !== false) {
                              $rsltObj = $this->processEcho($pdfToText['content'],0);
                         }
                         
                         //debug($rsltObj);
                         //debug($pdfToText['content']);
                         
                         $rsltObj->pdfFileName = $basefile;
                         
                         if ($this->returnFirstRsltObjOnly) return $rsltObj;
                         
                         $validatedRslt = $this->validateRsltObj($rsltObj);
                         
                         // check if the rslt object contains everything we need
                         if ($validatedRslt->validFlag) {
                              $moveToFolder = "completed";
                         } else {
                              // something is missing in the obj
                              // log it
                              //echo "Problem found with file: $file<br>";
                              $moveToFolder = "problem";
                         }
                         
                         // we're done with the file, move it to an archive folder
                         if ($this->archiveFiles) {
                              rename($tmpFileWithPath, $path . $moveToFolder . "\\" . $tmpFile);
                              //send the new name of the file back, so it can be attached to the load ID.
                              $rsltObj->pdfFileName = $path . $moveToFolder . "\\" . $tmpFile;
                         }
                         
                    } // end of valid rename check
               } // end of process_file check
          } // end of file list loop
          
     }
     
     public function validateRsltObj($rsltObj) {
          
          global $defaultsarray;
          
          $rslt = new stdClass();
          $rslt->validFlag = false;
          $rslt->msg = ""; // store any validation errors in this field
          
          return $rslt;
     }
     
     private function handleError($message) {
          echo "$message<br>";
     }
     
     private function getField($content, $searchString, $startPos = 0, $maxLength = 99999, $regex = null, $endString = null, $requireEndStringtoBeFound = true, $mrrBackupEndString = null) {
          
          $rval = "";
          
          $searchPos = stripos($content, $searchString);
          if($searchPos === false) {
               //echo "Couldn't locate search ($searchString) in content: $content<br>";
               return "";
          }
          
          $searchTmp = trim(substr($content, $searchPos + strlen($searchString) + $startPos, $maxLength));
          //echo "searchTmp ($searchString || $endString): $searchTmp<br>";
          if(!empty($endString)) {
               //echo "SEARCHING FOR END STRING ($endString)<br>";
               $searchEndPos = stripos($searchTmp, $endString);
     
               if(!empty($mrrBackupEndString)) {
                    //if not found, check for the backup string to match... useful if the string is based on a loop and we are at the end of it.
                    $searchEndPos = stripos($searchTmp, $mrrBackupEndString);
               }
               
               if($searchEndPos === false && $requireEndStringtoBeFound) {
                    // couldn't find the end string, and the require end string to be found flag is set
                    // so, return nothing
                    return "";
               }
               
               if($searchEndPos !== false) {
                    //echo "FOUND END STRING ($endString)!!!<br>";
                    $searchTmp = substr($searchTmp, 0, $searchEndPos);
               }
          }
          
          if(!empty($regex)) {
               preg_match($regex,$searchTmp,$matches);
               
               if(is_array($matches)) {
                    $rval = $matches[0];
               }
          } else {
               $rval = $searchTmp;
          }
          
          return trim($rval);
     }
     
     private function cleanseQuadAddrText($text)
     {
          for($x=0;$x < 20;$x++)             $text=str_replace("  "," ",$text);
          
          //condense the extra pickup number text... whihc seems to always be the same as the main load number anyway.  Will separate it after the other details are pulled out.
          for($x=0;$x < 10;$x++)             $text=str_replace("".$x." Q","".$x."Q",$text);
          
          $text=str_replace("Pickup 1","",$text);
          $text=str_replace("Pickup 2","",$text);
          $text=str_replace("Pickup 3","",$text);
     
          $text=str_replace("Delivery 1","",$text);
          $text=str_replace("Delivery 2","",$text);
          $text=str_replace("Delivery 3","",$text);
          $text=str_replace("Delivery 4","",$text);
          $text=str_replace("Delivery 5","",$text);
          $text=str_replace("Delivery 6","",$text);
          
          return trim($text);
     }
     private function processQuadAddressBlock($addressBlock) {
          $rsltLines = explode("\r", $addressBlock);
                    
          $phone="";
          $date="";
          $skids="";
          $address = array();
          
          
          
          for($p=0;$p < count($rsltLines);$p++) 
          {
               $liner=trim($rsltLines[$p]);
               
               $marker=0;
               if(substr_count($liner,"Pickup") > 0)        $marker=1;
               
               
               $liner=$this->cleanseQuadAddrText($liner);
     
               echo "<br>Line ".$p." = ".trim($liner)."";
               
               if($p==0)  
               {
                    $address[]=trim($liner);
               }
               elseif($p==1)
               {
                    $my_addr="";
                    $cells = explode(" ", $liner);
                    for($c=0;$c < count($cells);$c++)
                    {                         
                         $my_addr.=" ".trim($cells[$c]);
     
                         if($c== count($cells) - 1)        $skids.=trim($cells[$c]);
                         elseif($c== count($cells) - 2)    $date.=" ".trim($cells[$c])."";
                         elseif($c== count($cells) - 3)    $date=trim($cells[$c]);
                         elseif($c== count($cells) - 4)    $phone=trim($cells[$c]);
                    }
                    $my_addr=str_replace(" ".$skids ,"",$my_addr);
                    $my_addr=str_replace(" ".$date ,"",$my_addr);
                    $my_addr=str_replace(" ".$phone ,"",$my_addr);
                    //$my_addr=str_replace(" ". ,"",$my_addr);
     
                    $skids=str_replace("Q"," Q",trim($skids));
                    $date.=":00";
                    
                    $address[]=trim($my_addr);   
               }
               elseif($p > 1)
               {
                    if(strpos($liner,", ") > 0)
                    {
                         $temp= $this->parseCityStateZip($liner);
     
                         $address[]=$temp['city'];
                         $address[]=$temp['state'];
                         $address[]=$temp['zip'];
                    }
                    else
                    {
                         $address[]=trim($liner);
                    }
               }
          }          
          
          $res['notes']=$skids. " Skids";
          $res['pickup']=$date;
          $res['phone']=$phone;
          $res['address']=$address;
          
          return $res;
     }
     
     private function processQuad($content) {
          echo "Processing quad<br>";
          
          $mrr_tmp_comments="";
          
          // get all the pickups content
          $contentPickups = $this->getField($content, "Stop Type", 0, 99999, null, "Additional Load Instructions:");
          $pickupArray = array();
          for($i=0;$i<1;$i++) {
               $rslt = $this->getField($contentPickups, "Receiver Confirmation", 0, 99999, null, "Stop 1 Instructions:", false);
               
               if(strlen($rslt)) {
                    // found a pickup, load it up
                    $entry = array();
                    $entry['number']="";
                    
                    $holder=$this->processQuadAddressBlock($rslt);
                    $entry['address'] = $holder['address'];
                    $entry['date'] = $holder['pickup'];
                    $entry['phone'] = $holder['phone'];
                    
                    $mrr_tmp_comments.="Pickup ".($i+1).": ".$holder['notes']."  ";
                    $entry['date2'] = $entry['date'];
                    $pickupArray[] = $entry;
               } else {
                    // no more pickups, break out
                    break;
               }
               
          }
          
          // get all the drops
          $dropArray = array();
          $contentDropoffs = $this->getField($content, "Stop Type", 0, 99999, null, "Additional Load Instructions:");
          for($i=0;$i<5;$i++) {
               $rslt = $this->getField($contentDropoffs, "Stop ".($i+1)." Instructions:", 0, 99999, null, "Stop ".($i+2)." Instructions:", false);
               
               if(strlen($rslt)) {
                    // found a drop, load it up
                    $entry = array();
                    $entry['number']="";
     
                    $holder=$this->processQuadAddressBlock($rslt);
                    $entry['address'] = $holder['address'];
                    $entry['date'] = $holder['pickup'];
                    $entry['phone'] = $holder['phone'];
                    
                    $mrr_tmp_comments.="Delivery ".($i+2).": ".$holder['notes']."  ";
                    $entry['date2'] = $entry['date'];
                    $dropArray[] = $entry;
               } else {
                    // no more drops, break out
                    break;
               }
          }
          
          $rsltObj = new ConardRateSheetResponseObj();
          
          $rsltObj->customerID = 929;
          $rsltObj->nameOfCustomer = "Quad Graphics Inc";     //$this->getField($content, " ", 0, 99999, null, "- LOAD RATE AGREEMENT");
          $rsltObj->loadNumber = $this->getField($content, "Load #:", 0, 99999, '/[\w]+/',"Quad Contact:");
          $rsltObj->rate = get_amount($this->getField($content, "Total Pay", 0, 99999, '/[\d,.]+/', "CARRIER SIGNATURE:"));
     
          $mrr_tmp_comments=str_replace($rsltObj->loadNumber,"",$mrr_tmp_comments);
          
          $rsltObj->comments = trim($mrr_tmp_comments);
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          
          //debug($rsltObj);
          return $rsltObj;
     }
     
     private function processQuadAddressBlock_old($addressBlock) {
          $rsltLines = explode("\r", $addressBlock);
          
          $address = array();
          for($p=0;$p < count($rsltLines);$p++) {
               $addressContent = trim(substr($rsltLines[$p], 0, 60));
               if(strlen($addressContent) && stripos($addressContent, "PHONE:") === false) {
                    if($p<2 || (substr_count($addressContent,", ")==0 && substr_count($addressContent," ")<2) ) {
                         $address[] = $addressContent;
                    }
                    else {
                         $temp= $this->parseCityStateZip($addressContent);
     
                         $address[]=$temp['city'];
                         $address[]=$temp['state'];
                         $address[]=$temp['zip'];
                    }
                    
               } else {
                    break;
               }
          }
          
          return $address;
     }
     
     private function processQuad_old($content) {
          echo "Processing quad<br>";
          
          $mrr_tmp_comments="";
     
          // get all the pickups content
          $contentPickups = $this->getField($content, "PICK UP INFORMATION:", 0, 99999, null, "DROP INFORMATION:");
          $pickupArray = array();
          for($i=0;$i<100;$i++) {
               $rslt = $this->getField($contentPickups, "PICK UP ".($i+1), 0, 99999, null, "PICK UP ".($i+2), false);
               
               if(strlen($rslt)) {
                    // found a pickup, load it up
     
                    $mrr_tmp_comments.= $this->getField($rslt, "INSTRUCTIONS:", 0, 99999, null, "PICK UP ".($i+2));
                    
                    $entry = array();
                    $entry['date'] = $this->getField($rslt, "PICK UP DATE:", 0, 99999, null, "\r");
                    $entry['number'] = $this->getField($rslt, "PICK UP #:", 0, 99999, null, "\r");
                    $entry['phone'] = $this->getField($rslt, "PHONE:", 0, 60);
                    $entry['address'] = $this->processQuadAddressBlock($rslt);
                    $entry['date2'] = $entry['date'];
                    $pickupArray[] = $entry;
               } else {
                    // no more pickups, break out
                    break;
               }
               
          }
          
          // get all the drops
          $dropArray = array();
          $contentDropoffs = $this->getField($content, "DROP INFORMATION:", 0, 99999, null, "TOTAL MILES:");
          for($i=0;$i<100;$i++) {
               $rslt = $this->getField($contentDropoffs, "DROP ".($i+1), 0, 99999, null, "DROP ".($i+2), false);
               
               if(strlen($rslt)) {
                    // found a drop, load it up
                    $mrr_tmp_comments.= $this->getField($rslt, "INSTRUCTIONS:", 17, 99999, null, "DROP ".($i+2), true, "\r");
                    
                    $entry = array();
                    $entry['date'] = $this->getField($rslt, "DROP DATE:", 0, 99999, null, "\r");
                    $entry['phone'] = $this->getField($rslt, "PHONE:", 0, 60);
                    $entry['address'] = $this->processQuadAddressBlock($rslt);
                    $entry['date2'] = $entry['date'];
                    $dropArray[] = $entry;
               } else {
                    // no more drops, break out
                    break;
               }
          }
          
          $rsltObj = new ConardRateSheetResponseObj();
     
          $rsltObj->customerID = 929;
          $rsltObj->nameOfCustomer = "Quad Graphics Inc";     //$this->getField($content, " ", 0, 99999, null, "- LOAD RATE AGREEMENT");
          $rsltObj->loadNumber = $this->getField($content, "AMS LOAD#", 0, 99999, '/[\w]+/');
          $rsltObj->rate = get_amount($this->getField($content, "TOTAL PAY:", 0, 99999, '/[\d,.]+/', "See Terms"));
          $rsltObj->comments = trim($mrr_tmp_comments);
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          
          //debug($rsltObj);
          return $rsltObj;
     }
     
     
     private function processChRobinsonAddressBlock($addressBlock, $stop_name="") {
     
          $addressBlock = $this->getField($addressBlock, "address:",0,99999);
     
          $rsltLines = explode("\r", $addressBlock);
          $address = array();
          $address[] = trim($stop_name);
          $addr_cntr=0;
          
          for($p=0;$p < count($rsltLines);$p++) {
               $addressContent = trim(substr($rsltLines[$p], 0, 52));
               if(stripos($addressContent, "PHONE:") !== false) {
                    break;
               } else {
                    if(strlen($addressContent) > 0)        $address[] = $addressContent;
               }
          }
          
          $temp = $this->parseCityStateZip($address[count($address)-1]);
          
          $address[count($address)-1]=$temp['city'];
          $address[]=$temp['state'];
          $address[]=$temp['zip'];
          
          return $address;
     }
     
     private function processChRobinson($content) {
          echo "Processing CH Robinson<br>";
          
          $mrr_comment="";
          
          // get all the pickups content
          $contentPickups = $this->getField($content, "Customer-Specified Equipment Requirements", 0, 99999, null, "Shipper Instructions");
          $pickupArray = array();
          for($i=0;$i<100;$i++) {
               $rslt = $this->getField($contentPickups, "SHIPPER#".($i+1), 0, 99999, null, "SHIPPER#".($i+2), false);
               if(strlen($rslt)) {
                    // found a pickup, load it up
                    $entry = array();
                    $entry['name'] = $this->getField($rslt, ":", 0, 99999, null, "Pick Up Date:");
                    $entry['date'] = $this->getField($rslt, "Pick Up Date:", 0, 99999, null, "\r");
                    $entry['time'] = $this->getField($rslt, "Pick Up Time:", 0, 99999, null, "\r");
                    $entry['number'] = $this->getField($rslt, "Pickup#:", 0, 99999, null, "\r");
                    $entry['phone'] = $this->getField($rslt, "Phone:", 0, 60);
                    $entry['address'] = $this->processChRobinsonAddressBlock($rslt,trim($entry['name']));
                    $entry['date2']="";
                    
                    $pickupArray[] = $entry;
               } else {
                    // no more pickups, break out
                    break;
               }
               
          }
          
          // get all the drops
          $dropArray = array();
          $contentDropoffs = $this->getField($content, "Shipper Instructions", 0, 99999, null, "Rate Details");

          for($i=0;$i<100;$i++) {
               $rslt = $this->getField($contentDropoffs, "Receiver #".($i+1), 0, 99999, null, "Receiver #".($i+2), false);
               if(strlen($rslt)) {
                    // found a drop, load it up
                    $entry = array();
                    $entry['name'] = $this->getField($rslt, ":", 0, 99999, null, "Delivery Date:");
                    $entry['date'] = $this->getField($rslt, "Delivery Date:", 0, 99999, null, "\r");
                    $entry['time'] = $this->getField($rslt, "Delivery Time:", 0, 99999, null, "\r");
                    $entry['number'] = $this->getField($rslt, "Delivery#:", 0, 99999, null, "\r");
                    
                    $entry['phone'] = $this->getField($rslt, "Phone:", 0, 60);
                    $entry['address'] = $this->processChRobinsonAddressBlock($rslt,trim($entry['name']));

                    $entry['date2']=$entry['date'];
                    
                    $dropArray[] = $entry;
     
               } else {
                    // no more drops, break out
                    break;
               }
          }
          
          $rsltObj = new ConardRateSheetResponseObj();
     
          $rsltObj->customerID = 50;         //50 is CH Robinson for 1871 CH Robinson (Norcross Project)
          $rsltObj->nameOfCustomer = $this->getField($content, "\r", 0, 99999, null, " Contract Addendum");
          $rsltObj->loadNumber = $this->getField($content, "Load Confirmation - #", 0, 99999, '/[\w]+/');
          $rsltObj->rate = get_amount($this->getField($content, "Total:", 0, 99999, '/[\d,.]+/', "submit freight bill"));
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
          $rsltObj->comments = trim($mrr_comment);
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          
          //debug($rsltObj);
          
          return $rsltObj;
     }
     
     private function processEssexAddressBlock($addressBlock) {
          $rsltLines = explode(",", $addressBlock);
          
          $address = array();
          
          for($i=0;$i<count($rsltLines)-2;$i++) $address[] = trim($rsltLines[$i]);
          $cityStateZip = trim($rsltLines[count($rsltLines)-2]) . ", " . trim($rsltLines[count($rsltLines)-1]);
          //$address['cityStateZip'] = $this->parseCityStateZip($cityStateZip);
          
          $temp= $this->parseCityStateZip($cityStateZip);
          
          $address[]=$temp['city'];
          $address[]=$temp['state'];
          $address[]=$temp['zip'];

          return $address;
          
     }
     
     private function processEssex($content) {
          echo "Processing Essex<br>";
          
          // get all the stops
          $contentStops = $this->getField($content, "Dimensions", 0, 99999, null, "Freight Terms");
          $pickupArray = array();
          $dropArray = array();
          $mrr_commodity="";
          for($i=0;$i<100;$i++) {
               $rslt = $this->getField($contentStops, "Stop ".($i+1), 0, 99999, null, "Stop ".($i+2), false);
               if(strlen($rslt)) {
                    // found a pickup, load it up
                    $entry = array();
                    
                    $entry['date'] = $this->getField($rslt, ")", 0, 200,null,"\r");
                    $myAddressBlock = $this->getField($rslt, $entry['date'], 0, 200, null, "\r");
                    $entry['address'] = $this->processEssexAddressBlock(trim($myAddressBlock));
                    $stopType = $this->getField($rslt, "(", 0, 200,null, ")");
                    
                    if(substr_count($entry['date']," - ") > 0)
                    {    //this datetime is actually TWO dates for a range,
                         $temp_date=trim($entry['date']);
                         $poser=stripos($temp_date," - ");
                         $entry['date']=trim(substr($temp_date,0,$poser));     //first datetime.. this one is the ETA
                         $entry['date2']=trim(substr($temp_date,($poser+3)));        //second date (PTA) ... after the " - " token text.
                    }
                    else
                    {
                         $entry['date2']=$entry['date'];         //only one date, so the second one is the same.
                    }
                    
                    $entry['phone']="";
                    $entry['number']=$this->getField($rslt, "Customer Reference Number:", 0, 200,null,"\r");
     
                    $mrr_commodity.="  ".($stopType=="Pickup" ? "Pick Up" : "Drop Off")."- ".$this->getField($rslt, "Stop Totals", 0, 200,null,"QTY")." QTY";
     
                    if($stopType == "Pickup") {
                         $pickupArray[] = $entry;
                    } else {
                         $dropArray[] = $entry;
                    }
                    
               } else {
                    // no more pickups, break out
                    break;
               }
               
          }
          
          $dropArray[0]['contact'] = $this->getField($content, "Contact:", 0, 99999, null, "\r");
          
          $contentPhoneBlock = $this->getField($content, "Contact:", 0, 99999, null, "Delivery:");
          $dropArray[0]['phone'] = $this->getField($contentPhoneBlock, "p:", 0, 99999, null, "\r");
          
          $rsltObj = new ConardRateSheetResponseObj();
          
          $rsltObj->customerID = 1920;
          $rsltObj->nameOfCustomer = $this->getField($content, "Bill To:", 0, 99999, null, "Special Instructions");
          $rsltObj->loadNumber = $this->getField($content, "Reference:", 0, 99999, '/[\w]+/', "(Load ID)");
          $rsltObj->rate = get_amount($this->getField($content, "Total:", 0, 99999, '/[\d,.]+/', "Freight Terms:"));
          
          $rsltObj->comments = $this->getField($content, "Comments:", 0, 99999, null, "Pickup:");
          $rsltObj->comments .= "  ". $this->getField($content, "Contact:", 0, 99999, null, "Delivery:");
          $rsltObj->comments .= "   Special Instructions: ".$this->getField($content, "Special Instructions", 0, 99999, null, "Items");
          
          $rsltObj->commodity = trim($mrr_commodity);
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          
          //debug($rsltObj);
          return $rsltObj;
     }
     
     private function processSonocoAddressBlock($addressBlock) {
          
          $res['date']="";
          $res['time']="";
          $res['number']="";
          $res['phone']="";
          $res['address']['name']="";
          $res['address']['addr']="";
          $res['address']['addr2']="";
          $res['address']['city']="";
          $res['address']['stare']="";
          $res['address']['zip']="";
     
          $temp=$this->parseTimezonesOut($addressBlock);
          $addressBlock=$temp;
          
          $rsltLines = explode("\r", $addressBlock);
          for($p=0;$p < count($rsltLines);$p++)
          {
               $blob=trim($rsltLines[$p]);
               if($p==1)
               {     //this is first address line... but datetime(s) and/or cost in USD is tacked on the end... remove them and set the date for the load.
                     $temp=$this->parseNameTimeEtc($blob);
     
                     $res['address']['name']=$temp['name'];
                     $res['date']=$temp['date'];
                     $res['time']=$temp['time'];
               }
               elseif($p==2)
               {
                     $res['address']['addr']=$blob;
               }
               elseif($p==3 && count($rsltLines)==4)
               {
                     $temp= $this->parseCityStateZip($blob);
                     
                     $res['address']['city']=$temp['city'];
                     $res['address']['stare']=$temp['state'];
                     $res['address']['zip']=$temp['zip'];
               }
               elseif($p==3)
               {
                     $res['address']['addr2']=$blob;
               }
               elseif($p > 3)
               {
                     $temp= $this->parseCityStateZip($blob);
                     
                     $res['address']['city']=$temp['city'];
                     $res['address']['stare']=$temp['state'];
                     $res['address']['zip']=$temp['zip'];
               }
          }
          return $res;
     }
     
     private function processSonoco($content) {
          
          echo "Processing Sonoco<br>";
     
          // get all the pickups content
          $entry = array();
          $contentPickups = $this->getField($content, "Origin Location", 0, 99999, null, ", USA");
          $mrr_res=$this->processSonocoAddressBlock($contentPickups);
          
          $entry['date'] = $mrr_res['date'];                     $entry['date2']=$entry['date'];
          $entry['time'] = $mrr_res['time'];
          $entry['number'] = $mrr_res['number'];
          $entry['phone'] = $mrr_res['phone'];
          $entry['address'][0] = $mrr_res['address']['name'];
          $entry['address'][1] = $mrr_res['address']['addr'];
          $entry['address'][2] = $mrr_res['address']['addr2'];
          $entry['address'][3] = $mrr_res['address']['city'];
          $entry['address'][4] = $mrr_res['address']['stare'];
          $entry['address'][5] = $mrr_res['address']['zip'];
          
          $pickupArray = array();
          $pickupArray[] = $entry;
          
     
          // get all the drops
          $entry = array();
          $contentDropoffs = $this->getField($content, "Destination Location", 0, 99999, null, ", USA");
          $mrr_res=$this->processSonocoAddressBlock($contentDropoffs);
         
          $entry['date'] = $mrr_res['date'];                     $entry['date2']=$entry['date'];
          $entry['time'] = $mrr_res['time'];
          $entry['number'] = $mrr_res['number'];
          $entry['phone'] = $mrr_res['phone'];
          $entry['address'][0] = $mrr_res['address']['name'];
          $entry['address'][1] = $mrr_res['address']['addr'];
          $entry['address'][2] = $mrr_res['address']['addr2'];
          $entry['address'][3] = $mrr_res['address']['city'];
          $entry['address'][4] = $mrr_res['address']['stare'];
          $entry['address'][5] = $mrr_res['address']['zip'];
     
          $dropArray = array();
          $dropArray[] = $entry;
          
          $rsltObj = new ConardRateSheetResponseObj();
     
          $rsltObj->customerID = 1828;
          $rsltObj->nameOfCustomer = "Sonoco";
          $rsltObj->loadNumber = $this->getField($content, "SHIPMENT_ID", 0, 99999, null, "\r");
          $contentShipmentCost = $this->getField($content, "Shipment Cost", 0, 99999, null, "usd");
          //debug($contentShipmentCost);
          $rsltObj->rate = trim(get_amount(substr($contentShipmentCost, -9)));
          //$rsltObj->comments = trim($contentPickups);
          //$rsltObj->commodity = trim($contentDropoffs);
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          
          //debug($rsltObj);
          return $rsltObj;
     }
     
     public function parseCityStateZip($cityStateZipString, $stateOffset=" ",$stateZipOnly=0) {
     
          if($stateZipOnly > 0)
          {
               $poser1=stripos($cityStateZipString,$stateOffset);
               $address['state']=trim(substr($cityStateZipString,0,3));                //State
               $address['zip']=trim(substr($cityStateZipString,$poser1+2, 5));         //Zip
          }
          else
          {
               if(stripos($cityStateZipString, ",") === false && $stateOffset!=" ")
               {
                    // no comma found to separate city and state
                    $address = array($cityStateZipString);
               }
               else
               {                    
                    $poser1=stripos($cityStateZipString,", ");
                    if($poser1==0)  
                    {    //remove space for QL
                         $cityStateZipString=str_replace("/ "," ",$cityStateZipString);     //for QL
                         
                         $poser1=stripos($cityStateZipString,",");
                         if($poser1==0)      $poser1=stripos($cityStateZipString," ");
                         $poser2=stripos($cityStateZipString, " ",$poser1+2);
     
                         $address['city']=substr($cityStateZipString,0,$poser1);                     //City
                         $address['state']=trim(substr($cityStateZipString,$poser1,3));            //State
                         $address['zip']=trim(substr($cityStateZipString,$poser2));                       //Zip
     
                         //debug($address);
                    }  
                    else 
                    {
                         $poser2=stripos($cityStateZipString, $stateOffset,$poser1+2 );
                         
                         $address['city']=substr($cityStateZipString,0,$poser1);                         //City
                         $address['state']=trim(substr($cityStateZipString,$poser1+2,3));         //State
                         $address['zip']=trim(substr($cityStateZipString,$poser2));                            //Zip
                    }                    
                    //$poser2=stripos($cityStateZipString, $stateOffset,$poser1+2 );
          
                    //$address['city']=substr($cityStateZipString,0,$poser1);                         //City
                    //$address['state']=trim(substr($cityStateZipString,$poser1+2,3));         //State
                    //$address['zip']=trim(substr($cityStateZipString,$poser2));                            //Zip
               }
          }
          
          return $address;
     }
     public function parseNameTimeEtc($nameTimeString) {
          
          $name=$nameTimeString;
          $date="";
          $time="";
          $cur_yr=(int) date("Y",time());
          $next_yr=$cur_yr+1;
          
          //text for a date with this year first...
          if(substr_count($nameTimeString," ".$cur_yr."-") > 0)
          {    //found this years date
               $poser=stripos($nameTimeString," ".$cur_yr."-");
               
               $name=trim(substr($nameTimeString,0,$poser));
               $date=trim(substr($nameTimeString,$poser,17));
               $time=trim(substr($nameTimeString,$poser,17));
          }
          elseif(substr_count($nameTimeString," ".$next_yr."-") > 0)
          {     //found next year's date sicne we are probably close to the end of a calendar year.
               $poser=stripos($nameTimeString," ".$next_yr."-");
               
               $name=trim(substr($nameTimeString,0,$poser));
               $date=trim(substr($nameTimeString,$poser,17));
               $time=trim(substr($nameTimeString,$poser,17));
          }
          
          $res['name']=$name;
          $res['date']=$date;
          $res['time']=$time;
          
          return $res;
     }
     public function parseTimezonesOut($timeZoneString) {
          //timezones.
          $timeZoneString=trim($timeZoneString);
          $timeZoneString=str_replace("America/New_York","",$timeZoneString);
          $timeZoneString=str_replace("America/New York","",$timeZoneString);
          $timeZoneString=str_replace("America/Washington_DC","",$timeZoneString);
          $timeZoneString=str_replace("America/Washington, DC","",$timeZoneString);
          $timeZoneString=str_replace("America/Chicago","",$timeZoneString);
          $timeZoneString=str_replace("America/Denver","",$timeZoneString);
          $timeZoneString=str_replace("America/Phoenix","",$timeZoneString);
          $timeZoneString=str_replace("America/Los Angeles","",$timeZoneString);
          $timeZoneString=str_replace("America/Los Angeles","",$timeZoneString);
          $timeZoneString=str_replace("America/Anchorage","",$timeZoneString);
          //column headers
          $timeZoneString=str_replace("Pickup Date","",$timeZoneString);
          $timeZoneString=str_replace("Tender Response Time","",$timeZoneString);
          $timeZoneString=str_replace("Delivery Date","",$timeZoneString);
          $timeZoneString=str_replace("Shipment Cost","",$timeZoneString);
          
          return $timeZoneString;
     }
     
     //new customers/brokers added
     private function processKoch($contentFull) {
          echo "Processing Koch<br>";
     
          $pose1=stripos($contentFull,"CARRIER NAME:");
          $pose2=stripos($contentFull,"As per our conversation, for",$pose1);        // + 27
          $content=substr($contentFull, $pose1, ($pose2 - $pose1));
          
          $load_number=$this->getField($content, "Order #", 0, 99999, '/[\w]+/',"\r");
          $load_number=trim($load_number);
          
          // get all the stops
          $contentStops = $this->getField($content, "Reference", 0, 99999, null, "Koch Order#");
          $mrr_comment= $this->getField($contentStops, "Special Pickup Driver Notes by Freight Bill:", 0, 99999, null, "Reference");
          $mrr_commodity= $this->getField($contentStops, "Commodity:", 0, 99999, null, "Contact:");
          $mrr_stops=0;
          
          $pickupArray = array();
          $dropArray = array();
          
          for($i=0;$i<2;$i++) {
               
               $stopType=2;
               if($mrr_stops==0)
               {    //pickup
                    $stopType=1;
                    $rslt = $this->getField($contentStops, "Details", 0, 99999, null, "Special Pickup Driver Notes by Freight Bill:",true,"Reference");
               }
               else
               {    //dropoff
                    $rslt = $this->getField($contentStops, "Reference", 0, 99999, null, "Special Pickup Driver Notes by Freight Bill:",false);
               }
               if(strlen($rslt)) {
                    
                    // found a stop, load it up
                    $entry = array();
     
                    $entry['date'] = $this->getField($rslt, $load_number, 0, 9999,null,"Appointment:");
                    $temp_date=$this->cleanKochDetails($entry['date'],$load_number);
                    $temp_date1=$this->kochDateSplitter($temp_date,0);
                    $temp_date2=$this->kochDateSplitter($temp_date,1);
                    $entry['date']=$temp_date1;
                    $entry['date2']=$temp_date2;
                    
                    if($mrr_stops==0)
                    {    //pickup
                         $myAddressBlock = $this->getField($rslt, "Appointment:", 0, 99999, null, "Commodity:",false, "Contact:");
                         
                         $entry['phone']=$this->getField($rslt, "Contact:", 0, 1000,null,"Temp Controlled:");
                    }
                    else
                    {    //dropoff(s)
                         $myAddressBlock = $this->getField($rslt, "Appointment:", 0, 99999, null, "Hazardous Material:",false);
                         
                         $entry['phone']=$this->getField($rslt, "Contact:", 0, 200,null,"\r",false);
                    }
                    $entry['phone']=$this->cleanKochDetails($entry['phone'],$load_number);
                    
                    $mrr_temp=$this->processKochAddressBlock(trim($myAddressBlock));
                    
                    $entry['address'] = $mrr_temp;
                    $entry['number']="";
                    
                    if($stopType==1) {
                         $pickupArray[] = $entry;
                         $mrr_stops++;
                    } else {
                         $dropArray[] = $entry;
                         $mrr_stops++;
                    }
               
               } elseif($mrr_stops > 1) {
                    // no more pickups, break out
                    break;
               }
          }
          
          $rsltObj = new ConardRateSheetResponseObj();
          
          $rsltObj->customerID = 273;         //273 is for Koch Logistics
          $rsltObj->nameOfCustomer = "KOCH LOGISTICS";     //$this->getField($content, "\r", 0, 99999, null, " Contract Addendum");
          $rsltObj->loadNumber = $load_number;
          $rsltObj->rate = get_amount($this->getField($content, "Rate All-In", 0, 99999, '/[\d,.]+/', "\r"));
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
          $rsltObj->comments = trim($mrr_comment);
          $rsltObj->commodity = trim($mrr_commodity);
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          
          //debug($rsltObj);
          
          return $rsltObj;
     }
     private function processSchneider($contentFull) {
          echo "Processing Schneider<br>";
          
          $pose1=stripos($contentFull,"Shipment ID :");
          $pose2=stripos($contentFull,"SERVICE PROVIDER MUST NOTIFY SCHNEIDER",$pose1) + 38;
          $content=substr($contentFull, $pose1, ($pose2 - $pose1));
          //debug($content);
          $pose1a=stripos($contentFull,"AGREED TO RATE");
          $pose2a=stripos($contentFull," USD",$pose1) + 4;
          $rate_content=substr($contentFull, $pose1a, ($pose2a - $pose1a));
          //debug($rate_content);
          
          
          $mrr_comment="";
          
          // get all the pickups content
          $contentPickups = $this->getField($content, "Pickup Information", 0, 99999, null, "Delivery Information",TRUE);
          $mrr_phone = $this->getField($content, "Phone :", 0, 999999, null, "Email :");
          
          //debug($contentPickups);
          
          $pickupArray = array();
          //for($i=0;$i<100;$i++) {
               $rslt = $this->getField($contentPickups, "Location ", 0, 99999, null, "Item Details:", true);
               //debug($rslt);
               if(strlen($rslt)) {
                    // found a pickup, load it up
                    $entry = array();
     
                    $temp_name= $this->getField($rslt, ":", 0, 99999, null, " USA",true);
                    //debug($temp_name);
                    
                    $entry['name'] = $temp_name;
                    $entry['date'] = $this->getField($rslt, "From :", 0, 99999, null, "To :");
                    $entry['date2']=$this->getField($rslt, "To :", 0, 17, null);
                    $entry['time'] = "";
                    
                    //$pu_no=$this->getField($rslt, "(MASTER BILL OF LADING), ", 0, 99, '/[\w]+/', " (Pickup)",false);
                    //debug($pu_no);
                    $entry['number'] = "";   //$pu_no;
                    //$entry['phone'] = $mrr_phone;
                    $entry['address'] = $this->processSchneiderAddressBlock($temp_name);
                    
                    //$notes1=$this->getField($rslt, "References :", 0, 99999, null, "Special Instructions :");
                    //$notes2=$this->getField($rslt, "Special Instructions :", 0, 99999, null, "\r");
     
                    //$mrr_comment.="  Pickup - ". $notes1;
                    //$mrr_comment.=" ".$notes2;
                    
                    $pickupArray[] = $entry;
               } else {
                    // no more pickups, break out
                    //break;
               }
               
          //}
          
          // get all the drops
          $dropArray = array();
          $contentDropoffs = $this->getField($content, "Delivery Information", 0, 99999, null, "SERVICE PROVIDER MUST NOTIFY SCHNEIDER",true);
          
          //for($i=0;$i<100;$i++) {
               $rslt = $this->getField($contentDropoffs, "Location ", 0, 99999, null, "Item Details:", true);
               //debug($rslt);
               if(strlen($rslt)) {
                    // found a drop, load it up
                    $entry = array();
                    
                    $temp_name=$this->getField($rslt, ":", 0, 99999, null, " USA");
                    //debug($temp_name);
                    
                    $entry['name'] = $temp_name;
                    $entry['date'] = $this->getField($rslt, "From :", 0, 99999, null, "To :");
                    $entry['date2']=$this->getField($rslt, "To :", 0, 17, null);
                    $entry['time'] = "";
                    $entry['number'] = $this->getField($rslt, "(MASTER BILL OF LADING),", 0, 50, null, "(Pickup),");
                    $entry['address'] = $this->processSchneiderAddressBlock($temp_name);
                    //$entry['phone'] = $mrr_phone;
     
                    $notes1=$this->getField($rslt, "References :", 0, 99999, null, "Special Instructions :");
                    $notes2=$this->getField($rslt, "Special Instructions :", 0, 99999, null, "\r");
     
                    $mrr_comment.="  Dropoff - ". $notes1;
                    $mrr_comment.=" ".$notes2;
                    
                    $dropArray[] = $entry;
                    
               } else {
                    // no more drops, break out
                    // break;
               }
          //}
          $pickupArray[0]['phone']=$mrr_phone;
          $dropArray[0]['phone']=$mrr_phone;
          $rsltObj = new ConardRateSheetResponseObj();
          
          $rsltObj->customerID = 113;         //113 is for Schneider Transportation.
          $rsltObj->nameOfCustomer = "Schneider Carriers";     //$this->getField($content, "\r", 0, 99999, null, " Shipment Tender");
          $rsltObj->loadNumber = $this->getField($content, "Shipment ID :", 0, 99999, '/[\w]+/',"Tender Sent :");
          //$rsltObj->loadNumber = $this->getField($content, "(SCAC),", 0, 99999, '/[\w]+/',"(MASTER BILL OF LADING),");
          $rsltObj->rate = get_amount($this->getField($rate_content, "$", 0, 20, '/[\d,.]+/', "USD"));
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
          $rsltObj->comments = trim($mrr_comment);
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
     
          
          
          return $rsltObj;
     }
     private function processConard($content,$mode=0) {
          
          echo "Processing Conard Logistics (".($mode > 0 ? "Conard Agreement" : "Conard DeliverySheet").")<br>";
          
          $rsltObj = new ConardRateSheetResponseObj();
          $rsltObj->customerID = 7;                         //7 is for Conard Logistics... not to be confused with Conard Transportation Inc(s). or the Warehouse customer.
          $rsltObj->nameOfCustomer = "Conard Logistics";    //$this->getField($content, "\r", 0, 99999, null, " Contract Addendum");
          $mrr_comment="";
          
          if($mode > 0)
          {    //document is empty for most of the details... so just make the placeholders.
               $entry = array();
               $entry['name'] = "";
               $entry['date'] = "";
               $entry['time'] = "";
               $entry['number'] = "";
               $entry['phone'] = "";
               $entry['address'][] = "";          //stop name
               $entry['address'][] = "";          //address line 1
               $entry['address'][] = "";          //address line 2
               $entry['address'][] = "";          //city
               $entry['address'][] = "";          //state
               $entry['address'][] = "";          //zip
               $entry['date2']="";
     
               $pickupArray = array();
               $pickupArray[] = $entry;
               $dropArray = array();
               $dropArray[] = $entry;
               
               $rsltObj->loadNumber = $this->getField($content, "Both parties agree that FORWARDERS reference number ", 0, 99999, null,", moving");
               $rsltObj->rate = get_amount($this->getField($content, "TOTAL:", 0, 99999, null, "\r"));
               $rsltObj->pickupDateTime = $pickupArray[0]['date'];
               $rsltObj->deliveryDateTime = $dropArray[0]['date'];
               $rsltObj->comments = trim($mrr_comment);
               $rsltObj->pickupObj = $pickupArray;
               $rsltObj->dropObj = $dropArray;
          }
          else
          {
               $all_stops=0;
               $mrr_commodity="";
               $pu_no="";
               // get all the pickups content
               $contentPickups = $this->getField($content, "Value:", 0, 99999, null, "Fahrenheit");
               $pickupArray = array();
               for($i=0;$i<100;$i++) {
                    $rslt = $this->getField($contentPickups, "[".($all_stops+1)."] Pickup", 0, 99999, null, "[".($all_stops+2)."]", false);
                    
                    if(strlen($rslt)) {
                         //debug($rslt);
                         // found a pickup, load it up
                         $entry = array();
                         
                         $grabDate=$this->getField($rslt, "Date:", 0, 99, null, "Commodity:");
                         $grabTime=$this->getField($rslt, "Time:", 0, 99, null, "P.O.#:");
                         $dateBlock1=$this->processConardDateTimeBlock($grabDate, $grabTime ,0);
                         $dateBlock2=$this->processConardDateTimeBlock($grabDate, $grabTime ,1);
                         
                         $entry['name'] = $this->getField($rslt, ":", 0, 999, null, "\r");
                         $entry['time'] = $grabTime;
                         $entry['date'] = $dateBlock1;
                         $entry['date2']=$dateBlock2;
     
                         $pu_no=$this->getField($rslt, "Appointment #:", 0, 99999, null, "SET BY:");
                         $entry['number'] = $pu_no;         //used in hte main load as well...see after all stops captured.
     
                         $addressBlock=$this->getField($rslt, ":", 0, 999, null, "Date:");
                         
                         $entry['phone'] = $this->processConardAddressBlock($addressBlock,1);
                         $entry['address'] = $this->processConardAddressBlock($addressBlock,0);
     
                         $mrr_commodity =  $this->getField($rslt, "Commodity:", 0, 999, null, "\r");
                         
                         $pickupArray[] = $entry;
                         $all_stops++;
                    } else {
                         // no more pickups, break out
                         break;
                    }
               }
     
               // get all the drops
               $contentDropoffs = $this->getField($content, "Fahrenheit", 0, 99999, null, "<<<");
               $dropArray = array();
               for($i=0;$i<100;$i++) {
                    $rslt = $this->getField($contentDropoffs, "[".($all_stops+1)."] Delivery", 0, 99999, null, "Receiver #".($i+2), false);
                    
                    if(strlen($rslt)) {
                         // found a drop, load it up
                         //debug($rslt);
                         $entry = array();
     
                         $grabDate=$this->getField($rslt, "Date:", 0, 99, null, "Commodity:");
                         $grabTime=$this->getField($rslt, "Time:", 0, 99, null, "P.O.#:");
                         $dateBlock1=$this->processConardDateTimeBlock($grabDate, $grabTime ,0);
                         $dateBlock2=$this->processConardDateTimeBlock($grabDate, $grabTime ,1);
                         
                         $entry['name'] = $this->getField($rslt, ":", 0, 999, null, "\r");
                         $entry['time'] = $grabTime;
                         $entry['date'] = $dateBlock1;
                         $entry['date2']=$dateBlock2;
                         
                         $entry['number'] = "";
     
                         $addressBlock=$this->getField($rslt, ":", 0, 999, null, "Date:");
     
                         $entry['phone'] = $this->processConardAddressBlock($addressBlock,1);
                         $entry['address'] = $this->processConardAddressBlock($addressBlock,0);
               
                         $dropArray[] = $entry;
                         $all_stops++;
                    } else {
                         // no more drops, break out
                         break;
                    }
               }
     
               //debug($content);
               
               $rsltObj->loadNumber = $this->getField($content, "==>", 0, 999, null,"\r");
               $rsltObj->rate = "0.00";
               $rsltObj->pickupDateTime = $pickupArray[0]['date'];
               $rsltObj->deliveryDateTime = $dropArray[0]['date'];
               
               $mrr_comment=$this->getField($content, "COMMENTS", 0, 99999, null,"------------");
               $rsltObj->comments = trim($mrr_comment);
               $rsltObj->commodity = trim($mrr_commodity);
               $rsltObj->pickupNumber = trim($pu_no);
               $rsltObj->pickupObj = $pickupArray;
               $rsltObj->dropObj = $dropArray;
          }
          
          //debug($rsltObj);
          
          return $rsltObj;
     }
     
     public function cleanKochDetails($detailsString,$loadText="")
     {
          $detailsString=trim($detailsString);
          //list of garbage to remove from the string... including the token load number that is in several places.
          $detailsString=str_replace("Hazardous Material: False","",$detailsString);
          $detailsString=str_replace("Hazardous Material: True","",$detailsString);
          
          if($loadText!="")        $detailsString=str_replace($loadText,"",$detailsString);
          
          return trim($detailsString);
     }
     public function kochDateSplitter($dateString,$returnHalf=0)
     {
          $dateString=trim($dateString);
          $poser1=stripos($dateString,"-");
          
          $date_only=substr($dateString,0,10);         //use top prefix to second date/time if needed.
          $first_date=trim(substr($dateString,0,$poser1));
          $last_date=substr($dateString,$poser1);
          if(strlen($last_date) < 10)      $last_date=$date_only." ".substr($dateString,$poser1);
          
          $last_date=trim(str_replace("-","",$last_date));
          
          $poser2=strripos($first_date," ");
          $time_only=trim(substr($first_date,$poser2));
          if(strlen($time_only)==4)
          {
               $time_only2=$time_only;
               $time_only2hr=substr($time_only2,0,2);
               $time_only2min=substr($time_only2,2);
               $time_only2=$time_only2hr.":".$time_only2min;
               
               $first_date=str_replace($time_only,$time_only2,$first_date);
          }
          
          $poser2=strripos($last_date," ");
          $time_only=trim(substr($last_date,$poser2));
          if(strlen($time_only)==4)
          {
               $time_only2=$time_only;
               $time_only2hr=substr($time_only2,0,2);
               $time_only2min=substr($time_only2,2);
               $time_only2=$time_only2hr.":".$time_only2min;
               
               $last_date=str_replace($time_only,$time_only2,$last_date);
          }
          
          if($returnHalf > 0 && $poser1 > 0)      return trim($last_date);
          
          return trim($first_date);
     }
     private function processKochAddressBlock($addressBlock, $stop_name="")
     {
          $rsltLines = explode("\r", $addressBlock);
          $address = array();
          for($p=0;$p < count($rsltLines);$p++)
          {
               $colLines = explode(chr(13), $rsltLines[$p]);
               for($c=0;$c < count($colLines);$c++)
               {
                    $addressContent = $this->filterKochAddressBlock($colLines[$c]);
                    
                    if(strlen($addressContent) > 0 && substr_count($addressContent,"Pieces:")==0)
                    {    //skip the first line which is the tail end of the Appointment section.
                         $address[] = trim($addressContent);
                    }
               }
          }
          //pick city state and zip out of last line... but the delimiter is not the same as it is in the others...
          $tmp_str=$address[count($address)-1];
          $poser1=stripos($tmp_str," ");          //first space
          $poser2=strripos($tmp_str," ");         //last space
          
          $city=trim(substr($tmp_str,0,$poser1));
          $state=trim(substr($tmp_str,$poser1,($poser2 - $poser1)));
          $zip=trim(substr($tmp_str,$poser2,6));
          
          //add to address, and replace the city section with ONLY the city section.
          $address[count($address)-1]=$city;
          $address[]=$state;
          $address[]=$zip;
          
          return $address;
     }
     public function filterKochAddressBlock($addressBlock)
     {
          $addressBlock=trim($addressBlock);
          
          //remove core sections oftext in address block for pickup and dropoff locations.
          $addressBlock=str_replace("YES","",$addressBlock);
          $addressBlock=str_replace("NO","",$addressBlock);
          $addressBlock=str_replace("Please pick up at:","",$addressBlock);
          $addressBlock=str_replace("Please deliver to:","",$addressBlock);
          
          //now block out any other "garbage" sent in with the address block.
          $poser=stripos($addressBlock,"Req'd Equip:");
          if($poser > 0)  $addressBlock=trim(substr($addressBlock,0,$poser));
     
          $poser=stripos($addressBlock,"Pieces:");
          if($poser > 0)  $addressBlock=trim(substr($addressBlock,0,$poser));
     
          $poser=stripos($addressBlock,"Commodity:");
          if($poser > 0)  $addressBlock=trim(substr($addressBlock,0,$poser));
     
          $poser=stripos($addressBlock,"Hazardous Material:");
          if($poser > 0)  $addressBlock=trim(substr($addressBlock,0,$poser));
     
          $poser=stripos($addressBlock,"Temp Controlled:");
          if($poser > 0)  $addressBlock=trim(substr($addressBlock,0,$poser));
     
          $poser=stripos($addressBlock,"Contact:");
          if($poser > 0)  $addressBlock=trim(substr($addressBlock,0,$poser));
          
          return trim($addressBlock);
     }
     
     private function processSchneiderAddressBlock($addressBlock) {
          
          $rsltLines = explode(",", $addressBlock);
          $address = array();
          
          for($p=0;$p < count($rsltLines);$p++) {
               $addressContent = trim(substr($rsltLines[$p], 0, 52));
               if(strlen($addressContent) > 0)        $address[] = trim($addressContent);
          }
          //in this customer, the state and zip are togetehr, but the city should be the second element form the last.  State + Zip = last element in array.
          $temp = $this->parseCityStateZip($address[count($address)-1],"-",1);
          
          $address[count($address)-1]=$temp['state'];
          $address[]=$temp['zip'];
          
          return $address;
     }
     private function processConardAddressBlock($addressBlock,$blockMode=0) {
          
          //$addressBlock = $this->getField($addressBlock, "address:",0,99999);
          
          $rsltLines = explode("\r", $addressBlock);
          $address = array();
          $phone_number="";
          //$address[] = trim($stop_name);
          //$addr_cntr=0;
          
          for($p=0;$p < count($rsltLines);$p++) {
               $addressContent = trim($rsltLines[$p]);
               if(strlen($addressContent) > 0)
               {
                    if(stripos($addressContent, "Contact:") !== false)
                    {
                         $phone_number.=" ".trim(str_replace("Contact:","",$addressContent));
                    }
                    else
                    {
                         $address[] = trim($addressContent);
                    }
     
               }
               
               //$addressContent = trim(substr($rsltLines[$p], 0, 52));
               //if(stripos($addressContent, "PHONE:") !== false) {
               //     break;
               //} else {
               //     if(strlen($addressContent) > 0)        $address[] = $addressContent;
               //}
          }
          if($blockMode > 0)       return trim($phone_number);         //only want the phone number(s), so just exit now.
          
          //in address mode, so split the city, state, and zip from the last line.
          $temp = $this->parseCityStateZip($address[count($address)-1]);
          
          $address[count($address)-1]=$temp['city'];
          $address[]=$temp['state'];
          $address[]=$temp['zip'];
          
          return $address;
     }
     private function processConardDateTimeBlock($dateString,$timeString,$blockMode=0)
     {
          $date1="";
          $date2="";
          
          //divide date parts
          if(strlen($dateString) > 0 && substr_count($dateString,"-") > 0)
          {
               $poser=stripos($dateString,"-");
               $date1=trim(substr($dateString,0,$poser));
               $date2=trim(substr($dateString,$poser));
          }
          //divide time parts... add to existing dates.
          if(strlen($timeString) > 0 && substr_count($timeString,"-") > 0)
          {
               $poserx=stripos($timeString,"-");
               $date1.=" ".trim(substr($timeString,0,$poserx));
               $date2.=" ".trim(substr($timeString,$poserx));
          }
          $date2=trim(str_replace("-","",$date2));
     
          if($blockMode > 0)       return $date2;           //use the second date instead of the first date
          return $date1;
     }
     
     
     
     
     
     private function processQLAddressBlock($addressBlock) {
     
          $addressBlock=str_replace("'","",$addressBlock);
          $addressBlock=str_replace("&","and",$addressBlock);
     
          //debug($addressBlock);
          
          $rsltLines = explode(chr(10), $addressBlock);
          $address = array();
          
          for($p=0;$p < count($rsltLines);$p++) {
               $addressContent = trim(substr($rsltLines[$p], 0, 33));
               if(strlen($addressContent) > 0)        $address[] = trim($addressContent);
          }
          
          $temp = $this->parseCityStateZip($address[count($address)-1],",",0);
          
          $address[count($address)-1]=$temp['city'];        //replace with just the city... had the entire address city, state and zip.
          $address[]=str_replace(",","",$temp['state']);
          $address[]=$temp['zip'];
          
          return $address;
     }
     private function processQL($contentFull) {
          
          echo "Processing Quality Logistics <br>";
     
          $pose1=stripos($contentFull,"Load & Rate Confirmation");
          $pose2=stripos($contentFull,"Pay Summary:",$pose1) + 12;
          $content=substr($contentFull, $pose1, ($pose2 - $pose1));
          //debug($content);
          $pose1a=stripos($contentFull,"Pay Summary:");
          $pose2a=stripos($contentFull,"Carrier agrees and warrants that:",$pose1) + 33;
          $rate_content=substr($contentFull, $pose1a, ($pose2a - $pose1a));
          //debug($rate_content);
          $mrr_comment="";
          
          //get the load number now... since it will be used as a delimiter for lower sections (shipper/consignee info).
          $orderNumber = $this->getField($content, "ORDER:", 0, 10, null);
          //debug($orderNumber);
          $loadNumber = $this->getField($content, "BL#", 0, 10, null);
                    
          $pose_mrr=0;   //stripos($content,"Deliver To:")-12;        ///pass this to the dropoff offset so that it does not accidently pick up the tail end of the address from the pickup.
     
          // get all the pickups content
          $contentPickups = $this->getField($content, "".$loadNumber."", 0, 99999, null, "Deliver To:",TRUE);
          $mrr_phone = "";    //$this->getField($content, "Phone :", 0, 999999, null, "Email :");
     
          //debug($contentPickups);
     
          $pickupArray = array();
          //for($i=0;$i<100;$i++) {
          $rslt = $contentPickups; //$this->getField($contentPickups, "Load At:", 0, 99999, null, "Weight:", true);          
          $rslt = str_replace("must be a 53 van with swing doors","",$rslt);
          
          //debug($rslt);
          if(strlen($rslt)) {
               // found a pickup, load it up
               $entry = array();
          
               $temp_name= $this->getField($rslt, "Load At:", 0, 99999, null, "Earliest:",true);
               //debug($temp_name);
          
               $entry['name'] = $temp_name;
               $entry['date'] = $this->getField($rslt, "Earliest:", 0, 99999, null, "BL#");
               $entry['date2']=$this->getField($rslt, "Latest:", 0, 22, null);
               $entry['time'] = "";
               
               $alt_addr=$rslt;
               $alt_addr=str_replace("Load At:","",$alt_addr);
               $alt_addr=str_replace("Earliest:","",$alt_addr);
               $alt_addr=str_replace("Latest:","",$alt_addr);
               $alt_addr=str_replace($entry['date'],"",$alt_addr);
               $alt_addr=str_replace($entry['date2'],"",$alt_addr);
               $alt_addr=str_replace("BL#","",$alt_addr);
               $alt_addr=str_replace($loadNumber,"",$alt_addr);
               $alt_addr=str_replace("Weight: 0","",$alt_addr);
               $alt_addr=str_replace("Instructions:","",$alt_addr);
               
          
               //$pu_no=$this->getField($rslt, "(MASTER BILL OF LADING), ", 0, 99, '/[\w]+/', " (Pickup)",false);
               //debug($pu_no);
               $entry['number'] = strtoupper($loadNumber);   //$pu_no;
               //$entry['phone'] = $mrr_phone;
               $entry['address'] = $this->processQLAddressBlock(trim($alt_addr));         //($temp_name);
          
               //$notes1=$this->getField($rslt, "References :", 0, 99999, null, "Special Instructions :");
               //$notes2=$this->getField($rslt, "Special Instructions :", 0, 99999, null, "\r");
          
               //$mrr_comment.="  Pickup - ". $notes1;
               //$mrr_comment.=" ".$notes2;
          
               $pickupArray[] = $entry;
          } else {
               // no more pickups, break out
               //break;
          }
     
          // get all the drops
          $dropArray = array();
          $contentDropoffs = $this->getField($content, "Instructions:", $pose_mrr, 99999, null, "Remarks:",true);
     
          //debug($contentDropoffs);
     
          //for($i=0;$i<100;$i++) {
          $rslt = $contentDropoffs;     //$this->getField($contentDropoffs, "Deliver To:", 0, 99999, null, "Weight:", true);          
          $rslt = str_replace("must be a 53 van with swing doors","",$rslt);
          
          //debug($rslt);
          if(strlen($rslt)) {
               // found a drop, load it up
               $entry = array();
          
               $temp_name=$this->getField($rslt, "Deliver To:", 0, 99999, null, "Earliest:",true);
               //debug($temp_name);
          
               $entry['name'] = $temp_name;
               $entry['date'] = $this->getField($rslt, "Earliest:", 0, 99999, null, "BL#");
               $entry['date2']=$this->getField($rslt, "Latest:", 0, 22, null);
               $entry['time'] = "";
     
               $alt_addr="";
     
               $alt_addr=$rslt;
               $posex=stripos($alt_addr,"Deliver To:");
               if($posex > 0)      $alt_addr=substr($alt_addr,$posex);
               $alt_addr=str_replace("Deliver To:","",$alt_addr);
               $alt_addr=str_replace("Earliest:","",$alt_addr);
               $alt_addr=str_replace("Latest:","",$alt_addr);
               $alt_addr=str_replace($entry['date'],"",$alt_addr);
               $alt_addr=str_replace($entry['date2'],"",$alt_addr);
               $alt_addr=str_replace("BL#","",$alt_addr);
               $alt_addr=str_replace($loadNumber,"",$alt_addr);
               $alt_addr=str_replace("Weight: 0","",$alt_addr);
               $alt_addr=str_replace("Instructions:","",$alt_addr);
               
               $entry['number'] = strtoupper($loadNumber);
               $entry['address'] = $this->processQLAddressBlock(trim($alt_addr));         //($temp_name);
               //$entry['phone'] = $mrr_phone;
     
               //$notes1=$this->getField($rslt, "Remarks:", 0, 99999, null, "Pay Summary:");
               //$notes2=$this->getField($rslt, "Special Instructions :", 0, 99999, null, "\r");
          
               //$mrr_comment.="". $notes1;
               //$mrr_comment.=" ".$notes2;
          
               $dropArray[] = $entry;
          
          } else {
               // no more drops, break out
               // break;
          }
          $mrr_comment=trim($this->getField($content, "Remarks:", 0, 99999, null, "Pay Summary:"));
          $pickupArray[0]['phone']=$mrr_phone;
          $dropArray[0]['phone']=$mrr_phone;          
     
          $rsltObj = new ConardRateSheetResponseObj();
          $rsltObj->customerID = 127;
          $rsltObj->nameOfCustomer = "Quality Logistics";
                   
          $rsltObj->loadNumber = strtoupper($orderNumber);         //$this->getField($content, "Shipment ID :", 0, 99999, '/[\w]+/',"Tender Sent :");
          $rsltObj->pickupNumber = strtoupper($loadNumber);
          //$rsltObj->loadNumber = $this->getField($content, "(SCAC),", 0, 99999, '/[\w]+/',"(MASTER BILL OF LADING),");
          
          $mrr_total=$this->getField($rate_content, "Total:", 0, 99999, null, "Carrier agrees and warrants that:");
          //debug($mrr_total);
          
          $mrr_total=str_replace(",","",trim($mrr_total));
     
          //$mrr_comment.="... Total=".$mrr_total.".";
          
          $rsltObj->rate = str_replace("$","",trim($mrr_total));    //get_amount()
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
     
          $rsltObj->pickupApptDateTime1 = $pickupArray[0]['date'];       //Appointment Window PU start
          $rsltObj->pickupApptDateTime2 = $pickupArray[0]['date2'];       //Appointment Window PU end
          $rsltObj->deliveryApptDateTime1 = $dropArray[0]['date'];       //Appointment Window DO start
          $rsltObj->deliveryApptDateTime2 = $dropArray[0]['date2'];       //Appointment Window DO end
                    
          $rsltObj->comments = trim("".$mrr_comment."");          //MRR TEST: 
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          
          //debug($rsltObj);
          
          return $rsltObj;
     }
     private function processGSAddressBlock($temp_name,$addressBlock) {
          
          $addressBlock=str_replace("'","",$addressBlock);
          $addressBlock=str_replace("&","and",$addressBlock);
     
          $addressBlock=str_replace(chr(9)," ",$addressBlock);
          
          //debug($addressBlock);
          
          $rsltLines = explode(chr(10), $addressBlock);
          $address = array();
          $address2 = array();
          
          for($p=0;$p < 2;$p++) {      //count($rsltLines)
               $addressContent = trim(substr($rsltLines[$p], 0, 33));
               if(strlen($addressContent) > 0)        $address[] = trim($addressContent);
          }
          $temp1=trim($address[0]);          //addr 1 and 2...separated by comma
          $temp2=trim($address[1]);          ///city state and zip...
          
          $temp = $this->parseCityStateZip($temp2," ",0);
          
          $address2[0]=trim($temp_name);
          
          if(substr_count($temp1,",")>0)
          {
               $poser_a=stripos($temp1,",");
               $address[0]=trim(substr($temp1,0,$poser_a));
               $address[1]=trim(substr($temp1,$poser_a));
               $address[1]=trim(str_replace(",","",$address[1]));
               
               $address[2]=$temp['city'];        //replace with just the city... had the entire address city, state and zip.
               $address[3]=str_replace(",","",$temp['state']);
               $address[4]=$temp['zip'];
               
               //restructure to keep the name in hte address...
               $address2[1]=trim($address[0]);
               $address2[2]=trim($address[1]);
               $address2[3]=trim($address[2]);
               $address2[4]=trim($address[3]);
               $address2[5]=trim($address[4]);
          }
          else
          {
               $address[1]=$temp['city'];        //replace with just the city... had the entire address city, state and zip.
               $address[2]=str_replace(",","",$temp['state']);
               $address[3]=$temp['zip'];
               
               //restructure to keep the name in hte address...
               $address2[1]=trim($address[0]);
               $address2[2]=trim($address[1]);
               $address2[3]=trim($address[2]);
               $address2[4]=trim($address[3]);
          }
          
          return $address2;
     }
     private function processGS($contentFull) {
          
          echo "Processing Goldstar <br>";
     
          $contentFull=str_replace(chr(9), " ",$contentFull);
          $contentFull=str_replace("_", "",$contentFull);
          for($i=0;$i<100;$i++)  $contentFull=str_replace("  ", " ",$contentFull);
     
          $pose1=stripos($contentFull,"Carrier:");
          $pose2=stripos($contentFull,"Instructions",$pose1) + 21;
          $content=substr($contentFull, $pose1, ($pose2 - $pose1));
          //debug($content);
          
          $pose1a=stripos($contentFull,"Total Carrier Pay:");
          $pose2a=stripos($contentFull,"Instructions",$pose1a);
          $rate_content=substr($contentFull, $pose1a, ($pose2a - $pose1a));
          $rate_content=str_replace(chr(10), "",$rate_content);
          $rate_content=str_replace(chr(13), "",$rate_content);
          $rate_content=str_replace(chr(13), "Total Carrier Pay:",$rate_content);
          //debug($rate_content);
          
          $mrr_comment="";
          $pu_no="";
     
          //get the load number now... since it will be used as a delimiter for lower sections (shipper/consignee info).
          $orderNumber = $this->getField($content, "File #:", 0, 999, null,"Commodity:",true);
          //debug($orderNumber);
          $loadNumber = $orderNumber;   //$this->getField($content, "BL#", 0, 10, null);    
     
          $pose_mrr=stripos($content,"Ref Number:")+10;        ///pass this to the dropoff offset so that it does not accidently pick up the tail end of the address from the pickup.
          
          // get all the pickups content
          $contentPickups = $this->getField($content, "Name:", 0, 99999, null, "Ref Number:",TRUE);
          $mrr_phone = "";    //$this->getField($content, "Phone :", 0, 999999, null, "Email :");     
          //debug($contentPickups);
     
          $pickupArray = array();
          //for($i=0;$i<100;$i++) {
          $rslt = $contentPickups; //$this->getField($contentPickups, "Name:", 0, 99999, null, "Name:", true);
     
          //debug($rslt);
          if(strlen($rslt)) {
               // found a pickup, load it up
               $entry = array();
               
               //debug($rslt);
          
               //$temp_name= $this->getField($rslt, "C", 0, 50, null, "PKU#",true);     
               $pose_name=stripos($rslt,"Date:");
               $temp_name = substr($rslt, 0, $pose_name);
               //debug($temp_name);
          
               $entry['name'] = $temp_name;
               $entry['date'] = $this->getField($rslt, "Date:", 0, 17, null);
               $entry['date2']= "";     //$this->getField($rslt, "Latest:", 0, 17, null);
               $entry['time'] = "";
          
               $alt_addr= $this->getField($rslt, "Address:", 0, 99999, null, "Ref Number:",false);
          
               $alt_addr=str_replace("Date:","",$alt_addr);
               $alt_addr=str_replace($entry['date'],"",$alt_addr);
               
                              
               $entry['number'] = "";   
               //$entry['phone'] = $mrr_phone;
               $entry['address'] = $this->processGSAddressBlock($temp_name,trim($alt_addr));         //($temp_name); 
          
               $pickupArray[] = $entry;
          } else {
               // no more pickups, break out
               //break;
          }
     
          // get all the drops     $pose_mrr
          $dropArray = array();
          $contentDropoffs = $this->getField($content, "Ref Number:", 0 , 99999, null, "Payment",true);
     
          //debug($contentDropoffs);
     
          //for($i=0;$i<100;$i++) {
          $rslt = $contentDropoffs;     //$this->getField($contentDropoffs, "Drop", 0, 99999, null, "DRIVERS ARE RESPONSIBLE FOR", false);
          //$rslt = str_replace("must be a 53 van with swing doors","",$rslt);
     
          //debug($rslt);
          if(strlen($rslt)) {
               // found a drop, load it up
               $entry = array();
     
               //debug($rslt);
          
               //$temp_name=$this->getField($rslt, chr(10), 0, 99999, null, "DELV#",true);   
               $pose_name=stripos($rslt,"Date:");
               $temp_name = substr($rslt, 0, $pose_name);
               $temp_name=str_replace("Name:","",$temp_name);
               //debug($temp_name);
          
               $entry['name'] = trim($temp_name);
               $entry['date'] = $this->getField($rslt, "Date:", 0, 17, null);
               $entry['date2']= "";     //$this->getField($rslt, "Latest:", 0, 17, null);
               $entry['time'] = "";
               
               $use_token="Ref Number:";
               if(substr_count($rslt,"PO:") > 0)      $use_token="PO:";
          
               $alt_addr= $this->getField($rslt, "Address:", 0, 99999, null, $use_token,true);
          
               $alt_addr=str_replace("Date:","",$alt_addr);
               $alt_addr=str_replace($entry['date'],"",$alt_addr);
                    
               $pu_no = $this->getField($rslt, "PO:", 0, 99999, null, "Ref Number:",true);
          
               $entry['number'] = strtoupper($pu_no);  
               $entry['address'] = $this->processGSAddressBlock($temp_name,trim($alt_addr));         //($temp_name);
               $entry['phone'] = $mrr_phone;
          
               $dropArray[] = $entry;
     
               $pickupArray[0]['number']=strtoupper($pu_no);
          
          } else {
               // no more drops, break out
               // break;
          }
     
          $mrr_phone="952-933-0221";
     
          $mrr_comment=trim($this->getField($contentFull, "Special instructions here", 0, 99999, null, "Remittance Email:"));
          $pickupArray[0]['phone']=$mrr_phone;
          $dropArray[0]['phone']=$mrr_phone;
                    
          $rsltObj = new ConardRateSheetResponseObj();
          $rsltObj->customerID = 451;
          $rsltObj->nameOfCustomer = "Goldstar";
     
          $rsltObj->loadNumber = strtoupper($orderNumber);         //$this->getField($content, "Shipment ID :", 0, 99999, '/[\w]+/',"Tender Sent :");
          $rsltObj->pickupNumber = strtoupper($pu_no);
          //$rsltObj->loadNumber = $this->getField($content, "(SCAC),", 0, 99999, '/[\w]+/',"(MASTER BILL OF LADING),");
     
          //$mrr_total=$this->getField($rate_content, "Total:", 0, 10, null, "ALL TRAVEL DIRECTIONS", false);
          $mrr_total=trim($rate_content);
          $mrr_total = str_replace("Total Carrier Pay:", "", $mrr_total);
          $mrr_total = trim(str_replace(",", "", $mrr_total));
          $mrr_total = trim(str_replace("$", "", $mrr_total));
          //debug($mrr_total);         
     
          //$mrr_comment.="... Total=".$mrr_total.".";
     
          $rsltObj->rate = $mrr_total;    //get_amount()
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
     
          $rsltObj->pickupApptDateTime1 = $pickupArray[0]['date'];       //Appointment Window PU start
          $rsltObj->pickupApptDateTime2 = $pickupArray[0]['date2'];       //Appointment Window PU end
          $rsltObj->deliveryApptDateTime1 = $dropArray[0]['date'];       //Appointment Window DO start
          $rsltObj->deliveryApptDateTime2 = $dropArray[0]['date2'];       //Appointment Window DO end
     
          $rsltObj->comments = trim("".$mrr_comment."");          //MRR TEST: 
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          
          //debug($rsltObj);
          
          return $rsltObj;
     }
     
     private function processPFGAddressBlock($addressBlock)
     {
          //$addressBlock=str_replace(chr(9)," --",$addressBlock);
          
          $pfg_name="PFG Nashville";
          $pfg_address="401 Maddox-Simpson Parkway";
          $pfg_city_state_zip="Lebanon, TN 37090";
          $pfg_phone="615-965-1454";
          
          $same_city=0;
          if(substr_count($addressBlock,$pfg_city_state_zip) > 1)          $same_city=1;       //same city used for shipper and delivery.
          
          //pull out the delivery address since it is always the same.     
          $addressBlock=str_replace($pfg_name,"",$addressBlock);
          $addressBlock=str_replace($pfg_address,"",$addressBlock);
          $addressBlock=str_replace($pfg_city_state_zip,"",$addressBlock);
          $addressBlock=str_replace($pfg_phone,"",$addressBlock);
          
          for($x=0;$x < 100;$x++)      $addressBlock=str_replace("  "," ",$addressBlock);
          
          //debug($addressBlock);
          
          $rsltLines = explode(chr(10), $addressBlock);
          $address = array();
          $lines=0;
          
          for($p=1;$p < count($rsltLines);$p++)
          {
               $addressContent = trim($rsltLines[$p]);
               //debug("[" . $p . "] = " . $addressContent . "");  
               
               $addressContent=str_replace("PO # ","",$addressContent);
               $addressContent=str_replace("Cases ","",$addressContent);
               $addressContent=str_replace("Weight ","",$addressContent);
               $addressContent=str_replace("Cube ","",$addressContent);
               $addressContent=trim($addressContent);
               
               $poser=strpos($addressContent," ");
               
               $addr=trim(substr($addressContent,$poser));
               
               if($addr!="" && $p < (count($rsltLines) - 1))
               {
                    $address[]=trim($addr);
                    $lines++;
               }
               else
               {
                    $temp = $this->parseCityStateZip(trim($addr),",",0);
                    $city=trim($temp['city']);       //replace with just the city... had the entire address city, state and zip.
                    $state=trim(str_replace(",","",$temp['state']));
                    $zip=trim($temp['zip']);
                    
                    $zip=str_replace($city,"",$zip);
                    $zip=str_replace($state,"",$zip);
                    $zip=str_replace(",","",$zip);
                    
                    $address[]=$city;
                    $address[]=$state;
                    $address[]=trim($zip);
                    
                    $lines++;
               }
          }
          
          if($same_city > 0 || $lines <= 3)
          {    //missign city/state/zip, so add it back... would have been stripped out in previous code above.  Same as the PFG main address.
               $address[]="Lebanon";
               $address[]="TN";
               $address[]="37090";
          }
          
          return $address;
     }
     private function processPFGOtherBlock($otherBlock) 
     {    
          //$otherBlock=str_replace(chr(9)," --",$otherBlock);
          for($x=0;$x < 100;$x++)      $otherBlock=str_replace("  "," ",$otherBlock);
                    
          //debug($addressBlock);
          
          $rsltLines = explode(chr(10), $otherBlock);
          $items = array(); 
          $lines=0;
          
          for($p=1;$p < count($rsltLines);$p++) 
          {
               $otherContent = trim($rsltLines[$p]);
               //debug("[" . $p . "] = " . $otherContent . "");  
     
               $otherContent=str_replace("PO # ","",$otherContent);
               $otherContent=str_replace("Cases ","",$otherContent);
               $otherContent=str_replace("Weight ","",$otherContent);
               $otherContent=str_replace("Cube ","",$otherContent);
               $otherContent=trim($otherContent);
               
               $poser=strpos($otherContent," ");
     
               $items[]=trim(substr($otherContent,0,$poser));
          }
          
          return $items;
     }
     private function processPFGTimeBlock($notesBlock)
     {    //times for PFG rate sheets are only within the notes/comment section....
          $res['tzone']="CST";
          $appt_from="";
          $appt_to="";
     
          for($x=0;$x < 100;$x++)      $notesBlock=str_replace("***",", ***",$notesBlock);
     
          $notesBlock=str_replace("  ","-",$notesBlock);
     
          $notesBlock=str_replace(" ","--SPACE--",$notesBlock);
          $notesBlock=str_replace(":","--COLON--",$notesBlock);
          $notesBlock=str_replace(",","--COMMA--",$notesBlock);
          $notesBlock=preg_replace('/[^A-Za-z0-9\-]/', '', $notesBlock);
          $notesBlock=str_replace("--COMMA--",",",$notesBlock);
          $notesBlock=str_replace("--COLON--",":",$notesBlock);
          $notesBlock=str_replace("--SPACE--"," ",$notesBlock);
     
          $notesBlock=str_replace("  ","-",$notesBlock);
                         
          if(substr_count($notesBlock," EST") > 0 || substr_count($notesBlock," EDT") > 0)          $res['tzone']="EST";
          if(substr_count($notesBlock," MST") > 0 || substr_count($notesBlock," MDT") > 0)          $res['tzone']="MST";
          if(substr_count($notesBlock," PST") > 0 || substr_count($notesBlock," PDT") > 0)          $res['tzone']="PST";
     
          $notesBlock=str_replace(" EST","",$notesBlock);        $notesBlock=str_replace(" EDT","",$notesBlock);
          $notesBlock=str_replace(" MST","",$notesBlock);        $notesBlock=str_replace(" MDT","",$notesBlock);
          $notesBlock=str_replace(" PST","",$notesBlock);        $notesBlock=str_replace(" PDT","",$notesBlock);
                    
          //debug($notesBlock);
          
          $poser=0;
          if(substr_count($notesBlock,"FCFS") > 0)          $poser=strpos($notesBlock,"FCFS");
     
          $notesBlock=str_replace(" - ","-",$notesBlock);     
          //$notesBlock=str_replace(" M - F,"," MON-FRI",$notesBlock);
          $notesBlock=str_replace(" M-F,"," MON-FRI",$notesBlock);
          $notesBlock=str_replace(" M-F,"," MON-FRI",$notesBlock);
          $notesBlock=str_replace(" MON-FRI","",$notesBlock);
          $notesBlock=str_replace("FCFS,","",$notesBlock);
          $notesBlock=str_replace("FCFS","",$notesBlock);
          
          if(substr_count($notesBlock,"SHIPPING HOURS:") > 0 || substr_count($notesBlock,"HOURS:") > 0 || substr_count($notesBlock,"Hours:") > 0)
          {
               //HOURS: FCFS MON-FRI 0800-1500
               //HOURS: 0800-1700
               //SHIPPING HOURS: M-F, 7:30AM – 3:00PM EST
               $poser1=strpos($notesBlock,"HOURS:")+6;
               $poser2=strpos($notesBlock,"-",$poser1);
               $poser3=strpos($notesBlock," ",$poser2);
               if($poser3==0)      $poser3=strpos($notesBlock,",",$poser2);
               if($poser3==0)      $poser3=strpos($notesBlock,chr(10),$poser2);
     
               $appt_from=trim(substr($notesBlock,$poser1,($poser2 - $poser1)));
               $appt_to=trim(substr($notesBlock,$poser2,($poser3 - $poser2)));
               if($poser3==0)      $appt_to=trim(substr($notesBlock,$poser2));
               //$appt_to=str_replace("-","",$appt_to);
          }
          else
          {
               //FCFS, M - F, 0800-1530
               //7-3,     
               $poser2=strpos($notesBlock,"-",$poser);
               $poser3=strpos($notesBlock," ",$poser2);
               if($poser3==0)      $poser3=strpos($notesBlock,",",$poser2);
               if($poser3==0)      $poser3=strpos($notesBlock,chr(10),$poser2);
     
               $appt_from=trim(substr($notesBlock,$poser,($poser2 - $poser)));
               $appt_to=trim(substr($notesBlock,$poser2,($poser3 - $poser2)));
               if($poser3==0)      $appt_to=trim(substr($notesBlock,$poser2));
     
               $appt_to=trim(str_replace("-","",$appt_to));
               $appt_to=trim(str_replace(",","",$appt_to));
     
               if(strlen(trim($appt_from))==1) 
               {
                    $appt_from.=":00";
                    if($appt_from > "6")     $appt_from.="AM";
                    if($appt_from <= "6")    $appt_from.="PM";
               }
               if(strlen(trim($appt_to))==1)  
               {
                    $appt_to.=":00";
                    if($appt_to > "6")     $appt_to.="AM";
                    if($appt_to <= "6")    $appt_to.="PM";
               }
               
               if($appt_from > $appt_to)    
               {
                    //$appt_from.="AM";
                    //$appt_to.="PM";
               }
               
               //remove and extra AM vs PM additions...
               $appt_from=str_replace("AMAM","AM",$appt_from);        $appt_from=str_replace("AMPM","AM",$appt_from);
               $appt_to=str_replace("AMAM","AM",$appt_to);            $appt_to=str_replace("AMPM","AM",$appt_to);
               
               $appt_from=str_replace("PMPM","PM",$appt_from);        $appt_from=str_replace("PMAM","PM",$appt_from);
               $appt_to=str_replace("PMPM","PM",$appt_to);            $appt_to=str_replace("PMAM","PM",$appt_to);
          }
          
          //debug($notesBlock);
     
          $appt_to=str_replace("-","",$appt_to);
     
          $res['from']=$appt_from;
          $res['to']=$appt_to;          
          
          return $res;
     }
     private function processPFG($contentFull) 
     {          
          echo "Processing PFG <br>";
          
          $rsltObj = new ConardRateSheetResponseObj();
          $rsltObj->customerID = 2302;
          $rsltObj->nameOfCustomer = "Performance Foodservices (PFG)";
          
          //$contentFull=str_replace(chr(9), " ",$contentFull);
          //$contentFull=str_replace("_", "",$contentFull);
          //for($i=0;$i<100;$i++)  $contentFull=str_replace("  ", " ",$contentFull);
     
          $contentFull=str_replace("Contact: ****** All Delivery Appts Set on", " ",$contentFull);
          $contentFull=str_replace("Retalixtraffic.com ******", " ",$contentFull);
          
          $pose1=stripos($contentFull,"Old Hickory Logistics, LLC");
          $pose2=stripos($contentFull,"Load Comments:",$pose1);
          $content=substr($contentFull, $pose1, ($pose2 - $pose1));
          //debug($content);
     
          $pose1a=stripos($contentFull,"Total:");
          $pose2a=stripos($contentFull,chr(10),$pose1a);
          $rate_content=substr($contentFull, $pose1a, ($pose2a - $pose1a));
          //$rate_content=str_replace(chr(10), "",$rate_content);
          //$rate_content=str_replace(chr(13), "",$rate_content);
          //$rate_content=str_replace(chr(13), "Total Carrier Pay:",$rate_content);
          //debug($rate_content);
     
          $mrr_comment="";
          $mrr_phone="";
          $pu_no="";
          $delivery_date="";
          $commodity="";
     
          //get the load number now... since it will be used as a delimiter for lower sections (shipper/consignee info).
          $loadNumber = $this->getField($content, "Load #", 0, 50, null,"Carrier",true);
          //debug($orderNumber);
          $orderNumber = $loadNumber;   //$this->getField($content, "BL#", 0, 10, null);    
     
          $pose_mrr=0;   //stripos($content,"Ref Number:")+10;        ///pass this to the dropoff offset so that it does not accidently pick up the tail end of the address from the pickup.
     
          // get all the pickups content
          $contentPickups = $this->getField($content, "Pick-up #", 0, 99999, null, "Load Totals:",TRUE);
          //$mrr_phone = $this->getField($content, "Phone :", 0, 999999, null, "Email :");     
          //debug($contentPickups);
     
          $pickupArray = array();
          //for($i=0;$i<100;$i++) {
          $rslt = $contentPickups; //$this->getField($contentPickups, "Name:", 0, 99999, null, "Name:", true);
     
          //debug($rslt);
          if(strlen($rslt)) {
               // found a pickup, load it up
               $entry = array();
          
               //debug($rslt);         
                  
               $temp_name = $this->getField($rslt, "Vendor", 0, 99, null, "PO #",false);;
               //debug($temp_name);
          
               $entry['name'] = $temp_name;
               $entry['date'] = trim($this->getField($rslt, "Pick-Up:", 0, 10, null));
               //$delivery_date = trim($this->getField($rslt, "Deliver:", 0, 11, null));
               $delivery_date = trim($this->getField($rslt, "Deliver:", 0, 99, null, "Equipment Needed:",false));
               $entry['date2']= $entry['date'];     //$this->getField($rslt, "Latest:", 0, 17, null);
               $entry['time'] = "";
     
               $mrr_phone = $this->getField($rslt, "Pick-Up:", 0, 99999, null, "615-965-1454",false);
               $mrr_phone = str_replace(trim($entry['date']),"",$mrr_phone);
               //debug($mrr_phone);
          
               $alt_addr= $this->getField($rslt, "Vendor", 0, 99999, null, "Pick-Up:",false);          
               //$alt_addr=str_replace("Date:","",$alt_addr);
               //$alt_addr=str_replace($entry['date'],"",$alt_addr);
               //debug($alt_addr);
     
               $pu_no = trim($this->getField($rslt, "PO #", 0, 14, null));
               $entry['number'] = strtoupper($pu_no);
               
               $entry['phone'] = $mrr_phone;
               $entry['address'] = $this->processPFGAddressBlock(trim($alt_addr));         //($temp_name);
               
               $temper = $this->processPFGOtherBlock(trim($alt_addr));
               $commodity= "Cases ".$temper[1]." | Weight ".$temper[2]." | Cube ".$temper[3]."";        // PO # ".$temper[0]."
                         
               $pickupArray[] = $entry;
               $pickupArray[0]['number']=strtoupper($pu_no);
          } else {
               // no more pickups, break out
               //break;
          }
     
          // get all the drops     $pose_mrr
          $dropArray = array();
          $contentDropoffs = $this->getField($content, "Ref Number:", 0 , 99999, null, "Payment",true);
     
          //debug($contentDropoffs);
     
          //for($i=0;$i<100;$i++) {
          //$rslt = $contentDropoffs;     //$this->getField($contentDropoffs, "Drop", 0, 99999, null, "DRIVERS ARE RESPONSIBLE FOR", false);
          //$rslt = str_replace("must be a 53 van with swing doors","",$rslt);
     
          //debug($rslt);
          if(1==1) 
          {      //strlen($rslt)
               // found a drop, load it up
               $entry = array();
     
               $entry['name'] = "PFG Nashville";     //trim($temp_name);
               $entry['date'] = $delivery_date;     //$this->getField($rslt, "Date:", 0, 17, null);
               $entry['date2']= $delivery_date;     //$this->getField($rslt, "Latest:", 0, 17, null);
               $entry['time'] = "";               
               
               /*
               //debug($rslt);
          
               //$temp_name=$this->getField($rslt, chr(10), 0, 99999, null, "DELV#",true);   
               $pose_name=stripos($rslt,"Date:");
               $temp_name = substr($rslt, 0, $pose_name);
               $temp_name=str_replace("Name:","",$temp_name);
               //debug($temp_name);                
          
               $use_token="Ref Number:";
               if(substr_count($rslt,"PO:") > 0)      $use_token="PO:";
          
               $alt_addr= $this->getField($rslt, "Address:", 0, 99999, null, $use_token,true);
          
               $alt_addr=str_replace("Date:","",$alt_addr);
               $alt_addr=str_replace($entry['date'],"",$alt_addr);
          
               //$pu_no = $this->getField($rslt, "PO:", 0, 99999, null, "Ref Number:",true); 
               
               //$entry['address'] = $this->processGSAddressBlock($temp_name,trim($alt_addr));         //($temp_name);          
               
               $pu_no = $this->getField($rslt, "PO #", 0, 10, null);
               $entry['number'] = strtoupper($pu_no);
               */
               $entry['number'] = "";     
               $entry['phone'] = "615-965-1454";
               
               $entry['address'][]="PFG Nashville";
               $entry['address'][]="401 Maddox-Simpson Parkway";
               //$entry['address'][]="";
               $entry['address'][]="Lebanon";
               $entry['address'][]="TN";
               $entry['address'][]="37090";     
     
               $dropArray[] = $entry;
     
               //$pickupArray[0]['number']=strtoupper($pu_no);
          
          } else {
               // no more drops, break out
               // break;
          }
     
          //$mrr_phone="952-933-0221";
     
          $mrr_comment=trim($this->getField($contentFull, "Pallet exchange:", 0, 99999, null, "Load Totals:"));
          $mrr_comment=str_replace("Yes","",$mrr_comment);
          $mrr_comment=str_replace("No","",$mrr_comment);
          $mrr_comment=str_replace("Comments:","",$mrr_comment);
                    
          //$mrr_comment=str_replace(" ","--SPACE--",$mrr_comment);  
          //$mrr_comment=preg_replace('/[^A-Za-z0-9\-]/', '', $mrr_comment);
          //$mrr_comment=str_replace("--SPACE--"," ",$mrr_comment);
          
          $res=$this->processPFGTimeBlock($mrr_comment);           //$res['tzone']         
          
          $pickupArray[0]['date'].=" ".$res['from'];        //Appointment Window PU start
          $pickupArray[0]['date2'].=" ".$res['to'];         //Appointment Window PU end
          $dropArray[0]['date'].=" ".$res['from'];          //Appointment Window DO start
          $dropArray[0]['date2'].=" ".$res['to'];           //Appointment Window DO end
     
          $pickupArray[0]['phone']=$mrr_phone;
          $dropArray[0]['phone']="615-965-1454";     
            
          $rsltObj = new ConardRateSheetResponseObj();
          $rsltObj->customerID = 2302;
          $rsltObj->nameOfCustomer = "Performance Foodservices (PFG)";     
              
          $rsltObj->loadNumber = strtoupper($orderNumber);         //$this->getField($content, "Shipment ID :", 0, 99999, '/[\w]+/',"Tender Sent :");
          $rsltObj->pickupNumber = strtoupper($pu_no);
          //$rsltObj->loadNumber = $this->getField($content, "(SCAC),", 0, 99999, '/[\w]+/',"(MASTER BILL OF LADING),");
     
          //$mrr_total=$this->getField($rate_content, "Total:", 0, 10, null, "ALL TRAVEL DIRECTIONS", false);
          $mrr_total=trim($rate_content);
          $mrr_total = str_replace("Total:", "", $mrr_total);
          $mrr_total = str_replace(chr(9), "", $mrr_total);
          $mrr_total = str_replace(chr(10), "", $mrr_total);
          $mrr_total = str_replace(chr(13), "", $mrr_total);
          $mrr_total = trim(str_replace(",", "", $mrr_total));
          $mrr_total = trim(str_replace("$", "", $mrr_total));
          //debug($mrr_total);         
     
          //$mrr_comment.="... Total=".$mrr_total.".";
     
          $rsltObj->rate = $mrr_total;    //get_amount()
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
          
          $rsltObj->commodity = trim($commodity);
     
          $rsltObj->pickupApptDateTime1 = $pickupArray[0]['date'];       //Appointment Window PU start
          $rsltObj->pickupApptDateTime2 = $pickupArray[0]['date2'];       //Appointment Window PU end
          $rsltObj->deliveryApptDateTime1 = $dropArray[0]['date'];       //Appointment Window DO start
          $rsltObj->deliveryApptDateTime2 = $dropArray[0]['date2'];       //Appointment Window DO end
     
          $rsltObj->comments = trim("".$mrr_comment."");          //MRR TEST: 
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
     
          //debug($rsltObj);
     
          return $rsltObj;
     }
     
     private function processJB($contentFull) {
          
          echo "Processing JB Hunt <br>";
          
          $rsltObj = new ConardRateSheetResponseObj();
          $rsltObj->customerID = 62;
          $rsltObj->nameOfCustomer = "JB Hunt";
          $mrr_comment="";
          
          //debug($rsltObj);
          
          return $rsltObj;
     }
     private function processPCAddressBlock($addressBlock) {
     
          $res['pickup'] = array();
          $res['dropoff'] = array();
          $res['pickup_phone']="";
          $res['dropoff_phone']="";
          $res['pickup_contacts']="";
          $res['dropoff_contacts']="";
          
          $mode=1;       //pickup... until the first blank line is found.
          $mode_offset=0;     
          
          $addressBlock = str_replace(chr(9)," ",$addressBlock);     
          $addressBlock = str_replace(chr(13),chr(10),$addressBlock);
          $addressBlock = str_replace(","," ",$addressBlock);
          $addressBlock = str_replace("  "," ",$addressBlock);
     
          $rsltLines = explode(chr(10), $addressBlock);
          for($p=0;$p < count($rsltLines);$p++) 
          {    
               $addressline = trim($rsltLines[$p]);
     
               $addressline = str_replace(" ","MRRSPACE",$addressline);
               $addressline = preg_replace('/[^A-Za-z0-9\-]/', ' ', $addressline);
               $addressline = str_replace("MRRSPACE"," ",$addressline);
     
               //debug("".$p." -- '".$addressline."' [".$mode."]");
               
               if(strlen(trim($addressline)) == 0) 
               {
                    $mode=2;       //switch to the Drop Off Location...
               }
               else 
               {
                    $skip_now=0;
                    if($mode==1) 
                    {    //first lines until the spacer line are the pickup info
                         if($p>=3)
                         {
                              $res['pickup_contacts'].=$addressline.". ";
                              $res['pickup_phone']=$addressline;
                         }
                         else
                         {      
                              if($p<=1)     $res['pickup'][]=$addressline;
                              if($p==2) 
                              {
                                   $temp = $this->parseCityStateZip($addressline," ",0);
                                   $res['pickup'][]=trim($temp['city']);
                                   $res['pickup'][]=trim($temp['state']);
                                   $res['pickup'][]=trim($temp['zip']);
                              }     
                         }
                         if(substr_count($addressline,"-")> 1 && strlen(trim( $rsltLines[($p+1)] ))!=0)
                         {
                              $mode=2;
                              $skip_now=1;        //don't advance too fast.... this is since they don't ALWAYS skip the line.
                         }
                    }
                    if($mode==2 && $skip_now==0)
                    {    //after the mode switches, everything should be the dropoff.
                         if($mode_offset>=3)
                         {
                              $res['dropoff_contacts'].=$addressline.". ";
                              $addressline = preg_replace('/[^A-Za-z0-9\-]/', '', $addressline);
                              if(trim($addressline)!="" && strlen($addressline)>0)     $res['dropoff_phone']=$addressline;
                         }
                         else
                         {
                              if($mode_offset<=1) 
                              {
                                   if(substr_count($addressline,"5801 Centennial blvd") > 0 && $mode_offset==0)
                                   {
                                        $res['dropoff'][]="Plasticycle";        //added to find gap in block
                                        $mode_offset++;
                                   }                       
                                   
                                   $res['dropoff'][]=$addressline;
                              }
                              if($mode_offset==2)
                              {
                                   $temp = $this->parseCityStateZip($addressline," ",0);
                                   $res['dropoff'][]=trim($temp['city']);
                                   $res['dropoff'][]=trim($temp['state']);
                                   $res['dropoff'][]=trim($temp['zip']);
                              }
                         }
                         $mode_offset++;
                    }
               }
               
          }
          $res['pickup_phone']=str_replace("Plasticycle","",$res['pickup_phone']);
          $res['dropoff_phone']=str_replace("Plasticycle","",$res['dropoff_phone']);
     
          //debug("Phone ".$res['dropoff_phone']." -- Contacts: '".$res['dropoff_contacts']."' [".$mode."]");
          
          return $res;
     }
     public function processPCDateFixes($date_str)
     {
          $date_str = str_replace(chr(13),chr(10),$date_str);
          
          //Already implied....no need to use them since the BETWEEN is the only true Appt Window on Conard side...all others are Appt., not a Window.     
          $date_str=trim(str_replace("BEFORE","",$date_str));
          $date_str=trim(str_replace("APPT TIME","",$date_str));
          $date_str=trim(str_replace("APT TIME","",$date_str));
          $date_str=trim(str_replace(" TO ","-",$date_str));
          
          //several typos that will kill the time processing...
          $date_str = trim(str_replace(" 90AM", " 9:00AM", $date_str));
          $date_str = trim(str_replace("10-11AM", "10:00AM-11:00AM", $date_str));
          
          for($p=1;$p < 13;$p++) 
          {
               $date_str = trim(str_replace(" ".$p."AM", " ".$p.":00AM", $date_str));
               $date_str = trim(str_replace(" ".$p."PM", " ".$p.":00PM", $date_str));
          }          
          
          return $date_str;
     }
     public function processPCDateSections($date_str)
     {
          $res['date1']="";
          $res['time1']="";
          $res['date2']="";
          $res['time2']="";
          $res['comment']="";
          
          //4/6/2021 BETWEEN 9:00AM-4:00PM
          $date_str = $this->processPCDateFixes(trim($date_str));             
          $date_str=trim($date_str);
          
          $rsltLines = explode(chr(10), $date_str);
          for($p=0;$p < count($rsltLines);$p++) {      //count($rsltLines)
               $dateline = trim($rsltLines[$p]);
               
               //debug("".$p." -- ".$dateline."");
               
               if($p > 0)
               {    //notes to be added to comments outside this function.
                    $res['comment'].="".$dateline." ";
               }
               else 
               {    //this is an actual date (with or without time(s))
                    if(substr_count(trim($dateline)," ") > 0)
                    {    //has more than one date.... likely Appt Time or a window...                         
                         $poser=stripos($dateline," ");
                         //pull the date out... up to first space...
                         $dater=trim(substr($dateline,0,$poser));                              
                         $res['date1']=$dater;         //date("Y-m-d",strtotime())
     
                         //Appt Window... show up between these two times on this date.
                         if(substr_count(trim($dateline),"BETWEEN") > 0) 
                         {                                  
                              //should have the same date for this window...
                              $res['date2']=$res['date1'];
                              //now pull both times...
                              $poser1=stripos($dateline,"BETWEEN");
                              $poser2=stripos($dateline,"-");
                              
                              $res['time1']=substr($dateline,$poser1,($poser2 - $poser1));
                              $res['time1']=trim(str_replace("BETWEEN","",$res['time1']));
     
                              $res['time2']=substr($dateline,$poser2);
                              $res['time2']=trim(str_replace("-","",$res['time2']));
                         }  
                         else
                         {    //should be left with only one time...so add it to the time for this date...
                              $res['time1']=substr($dateline,$poser);
                         }
                    }
                    else
                    {    //only has the date, so reformat and add it
                         $res['date1']=$dateline." 00:00:00";       //date("Y-m-d H:i",strtotime())
                    }
               }
          }
          
          return $res;
     }
     private function processPC($contentFull) {
          
          $cur_year=date("Y");          $next_year=(int) $cur_year + 1;
          
          echo "Processing Plasticycle ....from WORD file, not PDF.<br>";
     
          $pose1=stripos($contentFull,"www.plasticycle.com");
          $pose2=stripos($contentFull,"USD is agreed.",$pose1);
          if($pose2==0)       $pose2=stripos($contentFull,"USD for freight.",$pose1);
          $content=substr($contentFull, $pose1, ($pose2 - $pose1));
     
          $content = str_replace(chr(9)," ",$content);
          $content = str_replace("Message-","Message:",$content);
          $content = str_replace("Pickup Date-","Pickup Date:",$content);
          $content = str_replace("Delivery Date-","Delivery Date:",$content);
          $content = str_replace("PO#-","PO:",$content);
          $content = str_replace("Material-","Material:",$content);
          $content = str_replace("Trailer #","",$content);
          //$content = str_replace("Delivery Date-","Delivery Date:",$content);
          
          //repair dates... one sample has date typed in like 4/17/2  ...which is missing the "1" for this year (2021).  Also make it 4 digit while we are at it.
          for($i=$cur_year; $i<=$next_year; $i++)
          {
               $dub=$i-2000;       //2-digit year.
               $content = str_replace("/".$dub." ","/".$i." ",$content);
          }
          $content = str_replace("/2 ","/".$cur_year." ",$content);        //this fixes the typo...
          //time blocks.... to make them easier to play with...
          $content = str_replace(" NOON"," 12:00PM",$content);
          $content = str_replace("00AM",":00AM",$content);
          $content = str_replace("15AM",":15AM",$content);
          $content = str_replace("30AM",":30AM",$content);
          $content = str_replace("45AM",":45AM",$content);
          $content = str_replace("00PM",":00PM",$content);
          $content = str_replace("15PM",":15PM",$content);
          $content = str_replace("30PM",":30PM",$content);
          $content = str_replace("45PM",":45PM",$content);
          $content = str_replace("::",":",$content);
          //debug($content);
          
          $pose1a=stripos($contentFull,"Freight amount of $");
          $pose2a=stripos($contentFull,"USD is agreed",$pose1a);
          if($pose2a==0)       $pose2a=stripos($contentFull,"USD for freight",$pose1);
          $rate_content=substr($contentFull, $pose1a, ($pose2a - $pose1a));
          //debug($rate_content);
                    
          $mrr_comment=$this->getField($content, "Message:", 0, 99999, null,"Pickup Date:",true);
          //debug(trim($mrr_comment));
     
          //get the load number now... since it will be used as a delimiter for lower sections (shipper/consignee info).
          $content = str_replace("Driver must arrive with this pick-up number","",$content);
          $orderNumber = $this->getField($content, "PO:", 0, 99999, null,"Material:",true);
          $orderNumber = preg_replace('/[^0-9\-]/', '', $orderNumber);         // /[^A-Za-z0-9\-]/
          //debug($orderNumber);
          $loadNumber = $this->getField($content, "Cust Deli #", 0, 99999, null,"Freight amount of $",true);
          if(!isset($loadNumber) || trim($loadNumber)=="")
          {
               $loadNumber = $orderNumber;        //$this->getField($content, "BL#", 0, 10, null); 
          }
          else
          {    //found a customer delivery number... remove it so it does not get caught up in the contract/phone section...
               $content = str_replace("Cust Deli #".$loadNumber."","",$content);               
          }
          
          
                    
          
          $mrr_pu_date=$this->getField($content, "Pickup Date:", 0, 99999, null,"Delivery Date:",true);
          //debug(trim($mrr_pu_date));
     
          $res1=$this->processPCDateSections(trim($mrr_pu_date));
          $mrr_comment.=" ".$mrr_pu_date."";      //$res1['comment']   //Not blank, so add to comment for sepcial instructions.
          
          $pickupArray = array();
          $pickupArray[0]['name']="";
          $pickupArray[0]['date']=date("Y-m-d H:i",strtotime("".$res1['date1']." ".$res1['time1'].""));
          $pickupArray[0]['date2']="0000-00-00 00:00:00";
          if(trim($res1['date2'])!="")         $pickupArray[0]['date2']=date("Y-m-d H:i",strtotime("".$res1['date2']." ".$res1['time2'].""));
          
          $pickupArray[0]['time']="";
          $pickupArray[0]['number']=trim($orderNumber);
          $pickupArray[0]['phone']="";
          $pickupArray[0]['address']=array();
     
     
     
          $mrr_do_date=$this->getField($content, "Delivery Date:", 0, 99999, null,"PO:",true);
          //debug(trim($mrr_do_date));
     
          $res2=$this->processPCDateSections(trim($mrr_do_date));
          $mrr_comment.=" ".$mrr_do_date."";      //$res2['comment']   //Not blank, so add to comment for sepcial instructions.
          
          $dropArray = array();
          $dropArray[0]['name']="";
          $dropArray[0]['date']=date("Y-m-d H:i",strtotime("".$res2['date1']." ".$res2['time1'].""));
          $dropArray[0]['date2']="0000-00-00 00:00:00";
          if(trim($res2['date2'])!="")         $dropArray[0]['date2']=date("Y-m-d H:i",strtotime("".$res2['date2']." ".$res2['time2'].""));
          
          $dropArray[0]['time']="";
          $dropArray[0]['number']=trim($orderNumber);
          $dropArray[0]['phone']="";
          $dropArray[0]['address']=array();
          
          
     
          
     
          $mrr_goods=$this->getField($content, "Material:", 0, 99999, null,"Pickup location:",true);
          //debug(trim($mrr_goods));
     
          $content = str_replace("Ship To:","",$content);
     
          $mrr_addrs=$this->getField($content, "Pickup location:", 0, 99999, null,"Freight amount of $",true);
          //debug(trim($mrr_addrs));
          $all_addresses = $this->processPCAddressBlock(trim($mrr_addrs));
     
          $pickupArray[0]['address']=$all_addresses['pickup'];
          $dropArray[0]['address']=$all_addresses['dropoff'];
     
          $pickupArray[0]['name']=$all_addresses['pickup'][0];
          $dropArray[0]['name']=$all_addresses['dropoff'][0];
          
          $pickupArray[0]['phone']=$all_addresses['pickup_phone'];
          $dropArray[0]['phone']=$all_addresses['dropoff_phone'];
          
          
          $rsltObj = new ConardRateSheetResponseObj();
          $rsltObj->customerID = 59;
          $rsltObj->nameOfCustomer = "Plasticycle";
     
          $rsltObj->loadNumber = strtoupper($orderNumber);         //$this->getField($content, "Shipment ID :", 0, 99999, '/[\w]+/',"Tender Sent :");
          $rsltObj->pickupNumber = strtoupper($loadNumber);
          //$rsltObj->loadNumber = $this->getField($content, "(SCAC),", 0, 99999, '/[\w]+/',"(MASTER BILL OF LADING),");
                    
          $mrr_total=trim(str_replace("Freight amount of $","",$rate_content));
     
          //$mrr_comment.="... Total=".$mrr_total.".";
     
          $rsltObj->rate = str_replace("$","",trim($mrr_total));    //get_amount()
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
     
          $rsltObj->pickupApptDateTime1 = $pickupArray[0]['date'];       //Appointment Window PU start
          $rsltObj->pickupApptDateTime2 = $pickupArray[0]['date2'];       //Appointment Window PU end
          $rsltObj->deliveryApptDateTime1 = $dropArray[0]['date'];       //Appointment Window DO start
          $rsltObj->deliveryApptDateTime2 = $dropArray[0]['date2'];       //Appointment Window DO end
     
          $rsltObj->comments = trim("".$mrr_comment."");          //MRR TEST: 
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          $rsltObj->commodity = $mrr_goods;
                    
          //debug($rsltObj);
          
          return $rsltObj;
     }
     private function processEchoAddressBlock($temp_name,$addressBlock) {
          
          $addressBlock=str_replace("'","",$addressBlock);
          $addressBlock=str_replace("&","and",$addressBlock);
          
          //debug($addressBlock);
          
          $rsltLines = explode(chr(10), $addressBlock);
          $address = array();
          $address2 = array();
          
          for($p=0;$p < 2;$p++) {      //count($rsltLines)
               $addressContent = trim(substr($rsltLines[$p], 0, 33));
               if(strlen($addressContent) > 0)        $address[] = trim($addressContent);
          }
          $temp1=trim($address[0]);          //addr 1 and 2...separated by comma
          $temp2=trim($address[1]);          ///city state and zip...
     
          $temp = $this->parseCityStateZip($temp2," ",0);
     
          $address2[0]=trim($temp_name);
          
          if(substr_count($temp1,",")>0) 
          {
               $poser_a=stripos($temp1,",");
               $address[0]=trim(substr($temp1,0,$poser_a));
               $address[1]=trim(substr($temp1,$poser_a));
               $address[1]=trim(str_replace(",","",$address[1]));
     
               $address[2]=$temp['city'];        //replace with just the city... had the entire address city, state and zip.
               $address[3]=str_replace(",","",$temp['state']);
               $address[4]=$temp['zip'];
               
               //restructure to keep the name in hte address...
               $address2[1]=trim($address[0]);
               $address2[2]=trim($address[1]);
               $address2[3]=trim($address[2]);
               $address2[4]=trim($address[3]);
               $address2[5]=trim($address[4]);
          }
          else
          {               
               $address[1]=$temp['city'];        //replace with just the city... had the entire address city, state and zip.
               $address[2]=str_replace(",","",$temp['state']);
               $address[3]=$temp['zip'];
     
               //restructure to keep the name in hte address...
               $address2[1]=trim($address[0]);
               $address2[2]=trim($address[1]);
               $address2[3]=trim($address[2]);
               $address2[4]=trim($address[3]);
          }
          
          return $address2;
     }
     private function processEcho($contentFull) {
          
          echo "Processing Echo Global Logistics Inc <br>";
     
          $pose1=stripos($contentFull,"and ask for Load Number");
          $pose2=stripos($contentFull,"PAYMENT REQUIREMENTS:",$pose1) + 21;
          $content=substr($contentFull, $pose1, ($pose2 - $pose1));
          //debug($content);
          $pose1a=stripos($contentFull,"Service for Load #");
          $pose2a=stripos($contentFull,"ALL TRAVEL DIRECTIONS PROVIDED BY ECHO GLOBAL LOGISTICS",$pose1a);
          $rate_content=substr($contentFull, $pose1a, ($pose2a - $pose1a));
          //debug($rate_content);
          $mrr_comment="";
     
          //get the load number now... since it will be used as a delimiter for lower sections (shipper/consignee info).
          $orderNumber = $this->getField($content, "ORDER", 0, 10, null);
          //debug($orderNumber);
          $loadNumber = $orderNumber;   //$this->getField($content, "BL#", 0, 10, null);    
          
          $pose_mrr=0;   //stripos($content,"Deliver To:")-12;        ///pass this to the dropoff offset so that it does not accidently pick up the tail end of the address from the pickup.
          
          // get all the pickups content
          $contentPickups = $this->getField($content, "CARRIER hereby confirms current and valid insurance coverage", 0, 99999, null, "Drop",TRUE);
          $mrr_phone = "";    //$this->getField($content, "Phone :", 0, 999999, null, "Email :");     
          //debug($contentPickups);
     
          $pickupArray = array();
          //for($i=0;$i<100;$i++) {
          $rslt = $this->getField($contentPickups, "Pickup", 0, 99999, null, "Drop", false);          
              
          //debug($rslt);
          if(strlen($rslt)) {
               // found a pickup, load it up
               $entry = array();
          
               //$temp_name= $this->getField($rslt, "C", 0, 50, null, "PKU#",true);                              
               //if($temp_name=="entro NC" || $temp_name=="")         $temp_name="Centro NC";     
               $pose_name=stripos($rslt,"PKU#");
               $temp_name = substr($rslt, 0, $pose_name);
               //debug($temp_name);
          
               $entry['name'] = $temp_name;
               $entry['date'] = $this->getField($rslt, "Earliest:", 0, 17, null);
               $entry['date2']=$this->getField($rslt, "Latest:", 0, 17, null);
               $entry['time'] = "";
               
               $alt_addr= $this->getField($rslt, "Centro NC", 0, 99999, null, "Weight:",true);          
               
               $alt_addr=str_replace("PKU#","",$alt_addr);
               $alt_addr=str_replace("Earliest:","",$alt_addr);
               $alt_addr=str_replace("Latest:","",$alt_addr);
               $alt_addr=str_replace($entry['date'],"",$alt_addr);
               $alt_addr=str_replace($entry['date2'],"",$alt_addr);
               $entry['number'] = "";   //strtoupper($loadNumber);   //$pu_no;
               //$entry['phone'] = $mrr_phone;
               $entry['address'] = $this->processEchoAddressBlock($temp_name,trim($alt_addr));         //($temp_name); 
          
               $pickupArray[] = $entry;
          } else {
               // no more pickups, break out
               //break;
          }
          
          // get all the drops
          $dropArray = array();
          $contentDropoffs = $this->getField($content, "Pickup INSTRUCTIONS", $pose_mrr, 99999, null, "DRIVERS ARE RESPONSIBLE FOR",true);
     
          //debug($contentDropoffs);
     
          //for($i=0;$i<100;$i++) {
          $rslt = $this->getField($contentDropoffs, "Drop", 0, 99999, null, "DRIVERS ARE RESPONSIBLE FOR", false);          
          //$rslt = str_replace("must be a 53 van with swing doors","",$rslt);
     
          //debug($rslt);
          if(strlen($rslt)) {
               // found a drop, load it up
               $entry = array();
          
               //$temp_name=$this->getField($rslt, chr(10), 0, 99999, null, "DELV#",true);   
               $pose_name=stripos($rslt,"DELV#");
               $temp_name = substr($rslt, 0, $pose_name);
               //debug($temp_name);
          
               $entry['name'] = $temp_name;
               $entry['date'] = $this->getField($rslt, "Earliest:", 0, 17, null);
               $entry['date2']=$this->getField($rslt, "Latest:", 0, 17, null);
               $entry['time'] = "";
               
               $alt_addr= $this->getField($rslt, chr(10), 0, 99999, null, "Weight:",true); 
               
               $alt_addr=str_replace("DELV#","",$alt_addr);
               $alt_addr=str_replace("Earliest:","",$alt_addr);
               $alt_addr=str_replace("Latest:","",$alt_addr);
               $alt_addr=str_replace($entry['date'],"",$alt_addr);
               $alt_addr=str_replace($entry['date2'],"",$alt_addr);
                    
               $alt_phone= $this->getField($rslt, "Latest:", 0, 99999, null, "Weight:",true);
               $mrr_phone=str_replace($entry['date2'],"",$alt_phone);     
     
               $entry['number'] = "";   //strtoupper($loadNumber);
               $entry['address'] = $this->processEchoAddressBlock($temp_name,trim($alt_addr));         //($temp_name);
               $entry['phone'] = $mrr_phone;
          
               $dropArray[] = $entry;
          
          } else {
               // no more drops, break out
               // break;
          }     
               
          $mrr_comment=trim($this->getField($content, "Pickup INSTRUCTIONS", 0, 99999, null, "Drop"));
          $pickupArray[0]['phone']="";  //$mrr_phone;
          $dropArray[0]['phone']=$mrr_phone;     
           
          $mrr_comment = str_replace(chr(10), '-br-', $mrr_comment);
          $mrr_comment = str_replace(' ', '-', $mrr_comment);
          $mrr_comment = preg_replace('/[^A-Za-z0-9\-]/', '', $mrr_comment);
          $mrr_comment = str_replace('-br-', chr(10), $mrr_comment);
          $mrr_comment = str_replace('-', ' ', $mrr_comment);         
          
          $rsltObj = new ConardRateSheetResponseObj();
          $rsltObj->customerID = 76;
          $rsltObj->nameOfCustomer = "Echo Global Logistics Inc";
          
     
          $rsltObj->loadNumber = (int) strtoupper($orderNumber);         //$this->getField($content, "Shipment ID :", 0, 99999, '/[\w]+/',"Tender Sent :");
          $rsltObj->pickupNumber = (int) strtoupper($loadNumber);
          //$rsltObj->loadNumber = $this->getField($content, "(SCAC),", 0, 99999, '/[\w]+/',"(MASTER BILL OF LADING),");
     
          //$mrr_total=$this->getField($rate_content, "Total:", 0, 10, null, "ALL TRAVEL DIRECTIONS", false);
          $pose_tot=stripos($rate_content,"Total:");
          $mrr_total = substr($rate_content, $pose_tot);
          $mrr_total = str_replace("Total:", "", $mrr_total);
          $mrr_total = trim(str_replace(",", "", $mrr_total));
          //debug($mrr_total);         
     
          //$mrr_comment.="... Total=".$mrr_total.".";
     
          $rsltObj->rate = str_replace("$","",trim($mrr_total));    //get_amount()
          $rsltObj->pickupDateTime = $pickupArray[0]['date'];
          $rsltObj->deliveryDateTime = $dropArray[0]['date'];
     
          $rsltObj->pickupApptDateTime1 = $pickupArray[0]['date'];       //Appointment Window PU start
          $rsltObj->pickupApptDateTime2 = $pickupArray[0]['date2'];       //Appointment Window PU end
          $rsltObj->deliveryApptDateTime1 = $dropArray[0]['date'];       //Appointment Window DO start
          $rsltObj->deliveryApptDateTime2 = $dropArray[0]['date2'];       //Appointment Window DO end
     
          $rsltObj->comments = trim("".$mrr_comment."");          //MRR TEST: 
          $rsltObj->pickupObj = $pickupArray;
          $rsltObj->dropObj = $dropArray;
          
          //debug($rsltObj);
          
          return $rsltObj;
     }
}

class ConardRateSheetResponseObj {
     
     public $customerID;           //Conard ID for Customer in Dispatch side.
     public $nameOfCustomer;       //Name of customer.
     public $loadNumber;           //Load #
     public $rate;                 //Rate
     public $comments="";
     public $commodity="";
     public $pickupDateTime;       //PU and delivery date and times
     public $deliveryDateTime;
     public $consineeObj;          //Consignee name and address
     public $pickupObj;            //Shipper name and address
     public $dropObj;
     public $pickupNumber;         //Pick up #
     
     public $pickupApptDateTime1="";       //Appointment Window PU start
     public $pickupApptDateTime2="";       //Appointment Window PU end
     public $deliveryApptDateTime1="";     //Appointment Window DO start
     public $deliveryApptDateTime2="";     //Appointment Window DO end
          
     public $pdfFileName;          //filename for later reference.
  
     function __construct() {
          $this->consineeObj = new stdClass();
          $this->pickupObj = new stdClass();
          $this->dropObj = new stdClass();
     }
}