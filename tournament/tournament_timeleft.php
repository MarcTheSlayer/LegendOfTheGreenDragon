<?php
function tournament_timeleft($status=0)
{
// Main function code written by skyhawk133 - March 2, 2005
// http://www.dreamincode.net/code/snippet86.htm

	$time = time();
	if( $status == 2 )
	{
		$end = strtotime(get_module_setting('duration'), strtotime(get_module_setting('start')));
		if( $end <= $time )
		{
			debuglog("Tournament auto reset.");
			include('modules/tournament/tournament_reset.php');
		}
	}
	elseif( $status == 0 )
	{
		$end = strtotime(get_module_setting('offtime'), strtotime(get_module_setting('start')));
	}

	$since = $end - $time;

	$clock = translate_inline(array('minute','minutes','hour','hours','day','days','week','weeks','month','months','year','years'));

	// array of time period chunks
	$chunks = array(
		array(60 * 60 * 24 * 365, $clock[10],$clock[11]),
		array(60 * 60 * 24 * 30, $clock[8],$clock[9]),
		array(60 * 60 * 24 * 7, $clock[6],$clock[7]),
		array(60 * 60 * 24, $clock[4],$clock[5]),
		array(60 * 60, $clock[2],$clock[3]),
		array(60, $clock[0],$clock[1]),
	);

	// $j saves performing the count function each time around the loop
	for( $i=0, $j=count($chunks); $i<$j; $i++ )
	{
		$seconds = $chunks[$i][0];
		$name = $chunks[$i][1];
		$names = $chunks[$i][2];

		// finding the biggest chunk (if the chunk fits, break)
		if( ($count = floor($since / $seconds)) != 0 ) break;
	}

	$print = ($count == 1) ? '1 '.$name : "$count $names";

	if( $i + 1 < $j )
	{
		// now getting the second item
		$seconds2 = $chunks[$i + 1][0];
		$name2 = $chunks[$i + 1][1];
		$names2 = $chunks[$i + 1][2];

		// add second item if it's greater than 0
		if( ($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0 )
		{
			$print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 $names2";
		}
	}
	return $print;
}
?>