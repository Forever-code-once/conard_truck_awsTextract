<?

class ConardRateSheetsChris {

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
			if(is_file($path.$file) && stripos($file, ".pdf") !== false) {
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

                    // get the raw text from the PDF
                    $pdfToText = get_text_from_pdf($tmpFileWithPath);

                    // set an empty rsltObj so we're sure to have something to work with at the end
                    $rsltObj = new ConardRateSheetResponseObj();

                    if (stripos($basefile, "quad") !== false) {
                        $rsltObj = $this->processQuad($pdfToText['content']);

                        //debug($rsltObj);

                    } else if (stripos($basefile, "CH Robinson") !== false) {
                        $rsltObj = $this->processChRobinson($pdfToText['content']);
                        //debug($pdfToText['content']);
                    } else if (stripos($basefile, "Essex Geodis") !== false) {
                        $rsltObj = $this->processEssex($pdfToText['content']);

                    } else if (stripos($basefile, "Sonoco") !== false) {
                        $rsltObj = $this->processSonoco($pdfToText['content']);
                        //debug($pdfToText['content']);
                    }

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
                    }

                } // end of valid rename check
            } // end of process_file check
		} // end of file list loop

	}


	public function validateRsltObj($rsltObj) {

	    $rslt = new stdClass();
	    $rslt->validFlag = false;
	    $rslt->msg = ""; // store any validation errors in this field

		return $rslt;

	}

	private function handleError($message) {
		echo "$message<br>";
	}

	private function getField($content, $searchString, $startPos = 0, $maxLength = 99999, $regex = null, $endString = null, $requireEndStringtoBeFound = true) {

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

		return $rval;

	}

	private function processQuadAddressBlock($addressBlock) {
		$rsltLines = explode("\r", $addressBlock);

		$address = array();
		for($p=0;$p < count($rsltLines);$p++) {
			$addressContent = trim(substr($rsltLines[$p], 0, 60));
			if(strlen($addressContent) && stripos($addressContent, "PHONE:") === false) {
				$address[] = $addressContent;
			} else {
				break;
			}
		}

		return $address;
	}

	private function processQuad($content) {
		echo "Processing quad<br>";


		// get all the pickups content
		$contentPickups = $this->getField($content, "PICK UP INFORMATION:", 0, 99999, null, "DROP INFORMATION:");
		$pickupArray = array();
		for($i=0;$i<100;$i++) {
			$rslt = $this->getField($contentPickups, "PICK UP ".($i+1), 0, 99999, null, "PICK UP ".($i+2), false);
			if(strlen($rslt)) {
				// found a pickup, load it up
				$entry = array();
				$entry['date'] = $this->getField($rslt, "PICK UP DATE:", 0, 99999, null, "\r");
				$entry['number'] = $this->getField($rslt, "PICK UP #:", 0, 99999, null, "\r");
				$entry['phone'] = $this->getField($rslt, "PHONE:", 0, 60);
				$entry['address'] = $this->processQuadAddressBlock($rslt);
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
				$entry = array();
				$entry['date'] = $this->getField($rslt, "DROP DATE:", 0, 99999, null, "\r");
				$entry['phone'] = $this->getField($rslt, "PHONE:", 0, 60);
				$entry['address'] = $this->processQuadAddressBlock($rslt);
				$dropArray[] = $entry;
			} else {
				// no more drops, break out
				break;
			}
		}


		$rsltObj = new ConardRateSheetResponseObj();

		$rsltObj->nameOfCustomer = $this->getField($content, " ", 0, 99999, null, "- LOAD RATE AGREEMENT");
		$rsltObj->loadNumber = $this->getField($content, "AMS LOAD#", 0, 99999, '/[\w]+/');
		$rsltObj->rate = get_amount($this->getField($content, "TOTAL PAY:", 0, 99999, '/[\d,.]+/', "See Terms"));
		$rsltObj->pickupDateTime = $pickupArray[0]['date'];
		$rsltObj->deliveryDateTime = $dropArray[0]['date'];
		$rsltObj->pickupObj = $pickupArray;
		$rsltObj->dropObj = $dropArray;

		//debug($rsltObj);
		return $rsltObj;
	}

	private function processEssex($content) {
		echo "Processing essex<br>";

		// get all the stops
		$contentStops = $this->getField($content, "Dimensions", 0, 99999, null, "Freight Terms");
		$pickupArray = array();
		$dropArray = array();
		for($i=0;$i<100;$i++) {
			$rslt = $this->getField($contentStops, "Stop ".($i+1), 0, 99999, null, "Stop ".($i+2), false);
			if(strlen($rslt)) {
				// found a pickup, load it up
				$entry = array();
				$entry['date'] = $this->getField($rslt, ")", 0, 200,null,"\r");
				$entry['address'] = $this->getField($rslt, $entry['date'], 0, 200, null, "\r");
				$stopType = $this->getField($rslt, "(", 0, 200,null, ")");
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

		$rsltObj->nameOfCustomer = $this->getField($content, "Bill To:", 0, 99999, null, "Special Instructions");
		$rsltObj->loadNumber = $this->getField($content, "Reference:", 0, 99999, '/[\w]+/', "(Load ID)");
		$rsltObj->rate = get_amount($this->getField($content, "Total:", 0, 99999, '/[\d,.]+/', "Freight Terms:"));
		$rsltObj->comments = $this->getField($content, "Comments:", 0, 99999, null, "Pickup:");
		$rsltObj->comments .= $this->getField($content, "Special Instructions", 0, 99999, null, "Items");
		$rsltObj->pickupDateTime = $pickupArray[0]['date'];
		$rsltObj->deliveryDateTime = $dropArray[0]['date'];
		$rsltObj->pickupObj = $pickupArray;
		$rsltObj->dropObj = $dropArray;

		//debug($rsltObj);
		return $rsltObj;
	}

	private function processChRobinsonAddressBlock($addressBlock) {
		$rsltLines = explode("\r", $addressBlock);

		$address = array();
		for($p=0;$p < count($rsltLines);$p++) {
			$addressContent = trim(substr($rsltLines[$p], 20, 50));
			if(strlen($addressContent)) {
				$address[] = $addressContent;
			} else if(stripos($addressContent, "PHONE:") === false) {
				// once we've reached the phone number line, there isn't anything else to grab, break out
				break;
			}
		}

		return $address;
	}

	private function processChRobinson($content) {
		echo "Processing CH Robinson<br>";


		// get all the pickups content
		$contentPickups = $this->getField($content, "Customer-Specified Equipment Requirements", 0, 99999, null, "Shipper Instructions");
		$pickupArray = array();
		for($i=0;$i<100;$i++) {
			$rslt = $this->getField($contentPickups, "SHIPPER#".($i+1), 0, 99999, null, "SHIPPER#".($i+2), false);
			if(strlen($rslt)) {
				// found a pickup, load it up
				$entry = array();
				$entry['date'] = $this->getField($rslt, "Pick Up Date:", 0, 99999, null, "\r");
				$entry['time'] = $this->getField($rslt, "Pick Up Time:", 0, 99999, null, "\r");
				$entry['number'] = $this->getField($rslt, "Pickup#:", 0, 99999, null, "\r");
				$entry['phone'] = $this->getField($rslt, "Phone:", 0, 60);
				$entry['address'] = $this->processChRobinsonAddressBlock($rslt);
				$pickupArray[] = $entry;
			} else {
				// no more pickups, break out
				break;
			}

		}


		// get all the drops
		$dropArray = array();
		$contentDropoffs = $this->getField($content, "Shipper Instructions", 0, 99999, null, "Receiver Instructions");
		for($i=0;$i<100;$i++) {
			$rslt = $this->getField($contentDropoffs, "Receiver #".($i+1), 0, 99999, null, "Receiver #".($i+2), false);
			if(strlen($rslt)) {
				// found a drop, load it up
				$entry = array();
				$entry['date'] = $this->getField($rslt, "Delivery Date:", 0, 99999, null, "\r");
				$entry['time'] = $this->getField($rslt, "Delivery Time:", 0, 99999, null, "\r");
				$entry['number'] = $this->getField($rslt, "Delivery#:", 0, 99999, null, "\r");
				$entry['phone'] = $this->getField($rslt, "Phone:", 0, 60);
				$entry['address'] = $this->processChRobinsonAddressBlock($rslt);
				$dropArray[] = $entry;
			} else {
				// no more drops, break out
				break;
			}
		}


		$rsltObj = new ConardRateSheetResponseObj();

		$rsltObj->nameOfCustomer = $this->getField($content, "\r", 0, 99999, null, " Contract Addendum");
		$rsltObj->loadNumber = $this->getField($content, "Load Confirmation - #", 0, 99999, '/[\w]+/');
		$rsltObj->rate = get_amount($this->getField($content, "Total:", 0, 99999, '/[\d,.]+/', "submit freight bill"));
		$rsltObj->pickupDateTime = $pickupArray[0]['date'];
		$rsltObj->deliveryDateTime = $dropArray[0]['date'];
		$rsltObj->pickupObj = $pickupArray;
		$rsltObj->dropObj = $dropArray;

		//debug($rsltObj);

		return $rsltObj;
	}

	private function processSonoco($content) {
		echo "Processing sonoco<br>";



		$rsltObj = new ConardRateSheetResponseObj();

		//$rsltObj->nameOfCustomer = $this->getField($content, "\r", 0, 99999, null, " Contract Addendum");
		$rsltObj->loadNumber = $this->getField($content, "SHIPMENT_ID", 0, 99999, null, "\r");
		$contentShipmentCost = $this->getField($content, "Shipment Cost", 0, 99999, null, "usd");
		debug($contentShipmentCost);
		$rsltObj->rate = trim(get_amount(substr($contentShipmentCost, -9)));
		//$rsltObj->pickupDateTime = $pickupArray[0]['date'];
		//$rsltObj->deliveryDateTime = $dropArray[0]['date'];
		//$rsltObj->pickupObj = $pickupArray;
		//$rsltObj->dropObj = $dropArray;

		//debug($rsltObj);

		return $rsltObj;
	}
}