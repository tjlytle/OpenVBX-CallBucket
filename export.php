<?php
OpenVBX::addJS('bucket.js');
OpenVBX::addJS('ui.datepicker.js');
require_once 'applets/bucket/bucket.php';
$bucket = new Bucket();
$bucket->syncLog();

$header = array('Bucket', 'Time', 'To', 'From', 'Duration', 'Cost');

//check for range
if(isset($_REQUEST['bucket'])){
	$start = $_REQUEST['bucket']['start'];
	$stop = $_REQUEST['bucket']['stop'];
} else {
	$stop = date('m/j/y', time());
	$start = date('m/j/y', time() - (60*60*24*7));
}


$query = $bucket->getRange($start, $stop);

if(isset($_REQUEST['export']) AND !empty($query) AND $query->num_rows() > 0){
	ob_end_clean(); //TODO: Nicer way to bypass layout
	header('Content-Type: application/csv');
	header('Content-Disposition: attachment;filename=callbucket.csv');
	$fp = fopen('php://output', 'w');
	fputcsv($fp, $header);
	foreach($query->result_array() as $row){
		unset($row['sid']);
		$row['start'] = date('D M jS, Y', $row['start']);
		fputcsv($fp, $row);
	}
	exit;
}

?>
<div class="vbx-plugin">
	<div id='dateRange'>
		<form action='' method='get'>
			Start Date: <input type='text' id='bucket-start' name='bucket[start]' value='<?php echo $start ?>'>
			End Date: <input type='text' id='bucket-end' name='bucket[stop]' value='<?php echo $stop ?>'>
			<input type='submit' value='View' name='view'>
			<?php if(!empty($query) AND $query->num_rows() > 0): ?>
				<input type='submit' value='Export' name='export'>
			<?php endif; ?>
		</form>
	</div>
	<div id='bucketData'>

<table>
	<thead>
		<tr>
			<th>Bucket</th>
			<th>Time</th>
			<th>To</th>
			<th>From</th>
			<th>Duration</th>
			<th>Cost</th>
		</tr>
	</thead>
	<tbody>
<?php if(empty($query) OR $query->num_rows() == 0): ?>
		<tr>
			<td colspan=6><i>No calls found.</i></td>
		</tr>
<?php else: ?>
	<?php foreach($query->result() as $row):?>
		<tr>
			<td><?echo $row->bucket ?></td>
			<td><?echo date('D M jS, Y', $row->start) ?></td>
			<td><?echo $row->to ?></td>
			<td><?echo $row->from ?></td>
			<td><?echo $row->duration ?></td>
			<td><?echo $row->price ?></td>
		</tr>
	<?php endforeach;?>
<?php endif; ?>

	</tbody>
</table>

	</div>

</div>
