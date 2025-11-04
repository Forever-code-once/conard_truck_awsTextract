<?
//new file to process the PDF files sent for rate sheets.  Added by MRR on 7/24/2019, but main class originally developed by Chris Sherrod.
include_once('application.php');
echo "<br><a href='mrr_rate_sheets_runner.php'>Reload for next run...</a><br>";
//$max_runs=7;
$max_runs=13;
$this_run=(int) $defaultsarray['rate_sheets_number_last_run'];

if($this_run==11)      $this_run=12;          //Skip JB Hunt for now... no recent loads.
if($this_run==12)      $this_run=13;          //Skip Plasticycle.... too unreliable in terms fo formatting.

$next_run = $this_run + 1;
if($next_run > $max_runs)        $next_run=0;
echo "<br><h2>Rate Sheets Company # Last Run was ".$this_run."... and Next Run will be ".$next_run."</h2><br><br>";

$actual_run_num=$this_run;
if($this_run==6)      $actual_run_num=7;          //Must Run Conard Logistics DeliverySheet version to make load before setting rate for the load... so it exists.
if($this_run==7)      $actual_run_num=6;          //Now set rate for load, since it should already be there afte last run. matched with Lading/Load no. (not Conard ID)
include_once('https://truckingdev.conardtransportation.com/mrr_rate_sheets.php?pdf_comp='.$actual_run_num.'');

//update setting for next run.
$sqlu="update defaults set xvalue_string='".$next_run."' where xname='rate_sheets_number_last_run'";
simple_query($sqlu);
echo "<br>Done.<br><a href='mrr_rate_sheets_runner.php'>Reload for next run...</a><br>";
?>