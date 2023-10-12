<?php
	include('modules/medcontest/medcontest_timeleft.php');

	output("`c`b`#%s`0`b`c`n`c`3Time Left: `#%s`0`c`n`n", $shop_name, $time_left);

	if( $wongems = get_module_pref('gemswon','medcontest') )
	{
		$session['user']['gems'] += $wongems;
		clear_module_pref('gemswon','medcontest',$session['user']['acctid']);

		output("`3Lonny sees you enter and comes over and hands you `% %s %s `3and says, \"`#Congratulations %s`#, You won these from the previous contest. I hope you'll use some to enter the new contest that's started.`3\"`n`n", $wongems, ($wongems==1?$gems[0]:$gems[1]), $session['user']['name']);
	}

	output("`3\"`#%s `#Hunting!`3\" shouts Lonny, taking you by surprise. \"`#It's all the rage. The person who finds the most `Q%s `#and hands them in here for points in the alotted time period wins!`3\" he eagerly says to you, not caring if you're listening or not.`n`n", $med, $meds);
	output("He continues, \"`#You can carry up to `Q5 %s `#on your person at any given time and if you fail to hand in any when the time is up, you get nothing!`3\". He seems to go through all this each time you enter, so you decide to quickly look at the scores and then go on your way.`n`n", $meds);

	$hunting = get_module_pref('medhunt','medcontest');
	if( empty($hunting) )
	{
		$gem_cost = get_module_setting('gemcost','medcontest');
		output("`3\"`#It only costs `% %s %s `#to enter the contest.`3\", you hear Lonny say casually. \"`#The winner gets 75 percent of the gems collected from contestants. So if there are 50 contestants 1st prize will be `% %s gems`#.`3\"`n`n", $gem_cost, ($gem_cost==1?$gems[0]:$gems[1]), round(50*$gem_cost*0.75));
		output('Your mouth begins to water at the thought of all those gems in your pouch.`n`n');
	}
	else
	{
		if( ($medallion = get_module_pref('medallion','medcontest')) > 0 )
		{
			output('`3You feel `Q%s %s `3in your pocket and decide to turn the %s `3in for points.`n`n', $medallion, ($medallion==1?$med:$meds), ($medallion==1?$med:$meds));	

			addnav('Options');
			addnav(array('Turn in %s',$medallions),'runmodule.php?module=medcontest&op=turnin');
		}
	}

	if( $high_id = get_module_setting('medconthighid','medcontest') )
	{
		$sql = "SELECT name
				FROM " . db_prefix('accounts') . "
				WHERE acctid = " . $high_id;
		$result = db_query($sql);
		if( $row = db_fetch_assoc($result) )
		{
			output("`#All time high score: `^%s `3by `^%s`#.`n`n", get_module_setting('medconthigh','medcontest'), $row['name']);
		}
		db_free_result($result);
	}

	$sql = "SELECT value
			FROM " . db_prefix('module_userprefs') . "
			WHERE modulename = 'medcontest'
				AND setting = 'medhunt'
				AND value > 0
			ORDER BY value+0 DESC";
	$result = db_query($sql);
	$total = db_num_rows($result);
	db_free_result($result);
	$totalpot = $total * get_module_setting('gemcost','medcontest');

	$secondplace = round($totalpot*.15);
	$thirdplace = round($totalpot*.10);
	$firstplace = $totalpot - $secondplace;
	$firstplace = $firstplace - $thirdplace;

	output("`b`#Current Pot: %s %s`b`n", $totalpot, ($totalpot==1?$gems[0]:$gems[1]));
	output("`^First Place: %s %s`n", $firstplace, ($firstplace==1?$gems[0]:$gems[1]));
	output("`&Second Place: %s %s`n", $secondplace, ($secondplace==1?$gems[0]:$gems[1]));
	output("`QThird Place: %s %s`n`n", $thirdplace, ($thirdplace==1?$gems[0]:$gems[1]));

	if( !empty($hunting) )
	{
		$points = translate_inline(array('point','points'));
		output("`#You currently have %s %s!`n`n", get_module_pref('medpoints','medcontest'), (get_module_pref('medpoints','medcontest')==1?$points[0]:$points[1]));
		output("Current Scores: `n");

		$sql = "SELECT a.name, b.value
				FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
				WHERE b.modulename = 'medcontest'
					AND b.setting = 'medpoints'
					AND a.acctid = b.userid
				ORDER BY value+0 DESC";
		$result = db_query($sql);

		$i = 0;
		while( $row = db_fetch_assoc($result) )
		{
			if( $i == 0 )
			{
				output_notl('`^%s `0- `^%s`n', $row['name'], $row['value']);
			}
			elseif( $i == 1 )
			{
				output_notl('`&%s `0- `&%s`n', $row['name'], $row['value']);
			}
			elseif( $i == 2 )
			{
				output_notl('`Q%s `0- `Q%s`n', $row['name'], $row['value']);
			}
			else
			{
				output_notl('`3%s `0- `3%s`n', $row['name'], $row['value']);
			}
			$i++;
		}
		db_free_result($result);
	}
	else
	{
		if( $session['user']['gems'] >= $gem_cost )
		{
			addnav('Options');
			addnav('Enter the Contest','runmodule.php?module=medcontest&op=entercontest');	
		}
		else
		{
			output('`3You look in your pouch and see that you don\'t have enough to enter.`0`n');	
		}
	}
?>