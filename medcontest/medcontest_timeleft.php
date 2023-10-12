<?php
function time_left($original)
{
// Function written by skyhawk133 - March 2, 2005
// http://www.dreamincode.net/code/snippet86.htm

	// array of time period chunks
	$chunks = array(
		array(60 * 60 * 24 * 365, 'year','years'),
		array(60 * 60 * 24 * 30, 'month','months'),
		array(60 * 60 * 24 * 7, 'week','weeks'),
		array(60 * 60 * 24, 'day','days'),
		array(60 * 60, 'hour','hours'),
		array(60, 'minute','minutes'),
	);

	$today = time();
	$since = $today - $original;

	// $j saves performing the count function each time around the loop
	for( $i=0, $j=count($chunks); $i<$j; $i++ )
	{
		$seconds = $chunks[$i][0];
		$name = translate_inline($chunks[$i][1]);
		$names = translate_inline($chunks[$i][2]);

		// finding the biggest chunk (if the chunk fits, break)
		if( ($count = floor($since / $seconds)) != 0 )
		{
			break;
		}
	}

	$print = ($count == 1) ? '1 '.$name : "$count $names";

	if( $i + 1 < $j )
	{
		// now getting the second item
		$seconds2 = $chunks[$i + 1][0];
		$name2 = translate_inline($chunks[$i + 1][1]);
		$names2 = translate_inline($chunks[$i + 1][2]);

		// add second item if it's greater than 0
		if( ($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0 )
		{
			$print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 $names2";
		}
	}
	return $print;
}

	$time = time();
	$lastreset = get_module_setting('lastreset','medcontest') + ((get_module_setting('resettimer','medcontest') * 60 * 60 * 24));

	if( $lastreset > $time )
	{
		$when = $time - ($lastreset - $time);
	}
	else
	{
		debuglog("Medcontest Auto Reset. Time now: $time > Last reset: $lastreset");
		include('modules/medcontest/medcontest_reset.php');
		$when = $time - (($time * 60 * 60 * 24) - $time);
	}

	$time_left = time_left($when);
?>