<? include('header.php') ?>
<?
$use_path = $defaultsarray['base_path'].'/comdata/backup/';
$use_path_tmp = getcwd()."/temp/";

?>
<h2>Comdata Fuel Exports</h2>
<table class='tablesorter' style='width:700px'>
<thead>
	<tr>
		<th>Filename</th>
		<th>Date</th>
	</tr>
</thead>
<tbody>
<?
	$d = dir($use_path);
	$counter = 0;
	while (false !== ($entry = $d->read())) {
		
		if($counter > 90) break;
		if($entry != '.' && $entry != '..') {
			$p = filemtime($use_path.$entry);
			echo "
				<tr>
					<td><a href=\"javascript:view_entry('$entry')\">$entry</a></td>
					<td align='right'>".date("m/d/Y", filemtime($use_path.$entry))."</td>
				</tr>
			";
		}
	}

?>
</tbody>
</table>
<script type='text/javascript'>
	function view_entry(entry) {
		$.ajax({
			url: "ajax.php?cmd=view_comdata_log",
			dataType: "xml",
			type: "post",
			data: {
				fname: entry
			},
			error: function() {
				$.prompt("General error loading file");
			},
			success: function(xml) {
				window.location = "temp/" + $(xml).find('filename').text();
			}
		});
	}
	
	$('.tablesorter').tablesorter();
</script>
<? include('footer.php') ?>