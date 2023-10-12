<?php
	set_module_setting('lastreset',time(),'medcontest');
	$gems = translate_inline(array('gem','gems'));

	//
	// To cut down on a SQL query I'm assuming they've joined the hunt
	// if they have medallion points. :)
	//
	$sql = "SELECT a.name, a.sex, b.userid, b.value
		FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
		WHERE b.modulename = 'medcontest'
		AND b.setting = 'medpoints'
		AND a.acctid = b.userid
		ORDER BY b.value+0 DESC";
	$result = db_query($sql);

	$topmed = 0;
	$topplayer = '';

	if( ($count = db_num_rows($result)) > 0 )
	{
		require_once('lib/systemmail.php');

		$subject = translate_mail('`4Congratulations!');

		$totalpot = $count * get_module_setting('gemcost','medcontest');
		$thirdplace = round($totalpot*.10);
		$secondplace = round($totalpot*.15);
		$firstplace = ($totalpot - $secondplace) - $thirdplace;

		$i = 0;
		$top3_msg = '';
		$name_msg = '';
		$no_points = FALSE;
		$id_array = array();
		while( $row = db_fetch_assoc($result) )
		{
			$points = $row['value'];
			if( $i == 0 )
			{	// First place.
				$topmed = $points;
				$topplayer = $row['userid'];

				if( $points > 0 )
				{
					set_module_pref('gemswon', $firstplace, 'medcontest', $row['userid']);

					addnews("`@%s `^has won 1st place in the %s `^contest!", $row['name'], $med, TRUE);
					$message = translate_mail(array('`#You have won 1st place in the %s `#Contest!`n`nYou have been awarded `% %s %s`#!`n`nLonny.', $med, $firstplace, ($firstplace==1?$gems[0]:$gems[1])));
					systemmail($row['userid'],$subject,$message);
					$top3_msg .= translate_mail(array('`^Taking 1st place with `% %s %s `^was %s`^.`n', $firstplace, ($firstplace==1?$gems[0]:$gems[1]), $row['name']));
				}
				else
				{
					// If the first person has no points, then it's safe to assume that nobody else has either.
					$no_points = TRUE;
					$name_msg .= '`2' . $row['name'] . '`0`n';
				}
			}
			elseif( $i == 1 )
			{	// Second place.

				if( $points > 0 )
				{
					set_module_pref('gemswon', $secondplace, 'medcontest', $row['userid']);

						addnews("`@%s `&has won 2nd place in the %s `&contest!", $row['name'], $med, TRUE);
					$message = translate_mail(array('`#You have come second in the %s `#Contest!`n`nYou have been awarded `% %s %s`#!`n`nLonny.', $med, $secondplace, ($secondplace==1?$gems[0]:$gems[1])));
					systemmail($row['userid'],$subject,$message);
					$top3_msg .= translate_mail(array('`&Taking 2nd place with `% %s %s `&was %s`&.`n', $secondplace, ($secondplace==1?$gems[0]:$gems[1]), $row['name']));
				}
				else
				{
					$name_msg .= '`2' . $row['name'] . '`0`n';
				}
			}
			elseif( $i == 2 )
			{	// Third place.

				if( $points > 0 )
				{
					set_module_pref('gemswon', $thirdplace, 'medcontest', $row['userid']);

					addnews("`@%s `Qhas won 3rd third place in the %s `Qcontest!", $row['name'], $med, TRUE);
					$message = translate_mail(array('`#You have come third in the %s `#Contest!`n`nYou have been awarded `% %s %s`#!`n`nLonny.', $med, $thirdplace, ($thirdplace==1?$gems[0]:$gems[1])));
					systemmail($row['userid'],$subject,$message);
					$top3_msg .= translate_mail(array('`QTaking 3rd place with `% %s %s `Qwas %s`Q.`n`n', $thirdplace, ($thirdplace==1?$gems[0]:$gems[1]), $row['name']));
				}
				else
				{
					$name_msg .= '`2' . $row['name'] . '`0`n';
				}
			}
			else
			{	// Losers.
				$name_msg .= '`2' . $row['name'] . '`0`n';
			}
			$id_array[$i] = $row['userid'];
			$i++;
		}
		db_free_result($result);

		$subject = translate_mail(array('`Q%s `QContest Winners!`0',$med));
		$mail_msg = translate_mail('`3This Contest has ended.`n`n');
		$mail_msg .= $top3_msg;
		$mail_msg1 = translate_mail('Nobody had any points, so there are no winners. No winners except me as I\'m keeping the gems. No points makes you a runner up.`n`n');
		$mail_msg2 = translate_mail('`#The runners up are as follows:`3`n');
		if( $count > 3 )
		{
			if( $no_points == TRUE )
			{
				$mail_msg .= $mail_msg1;
			}
			$mail_msg .= $mail_msg2;
		}
		$mail_msg .= $name_msg;
		$mail_msg .= translate_mail(array('`n`3Winners can collect their gems the next time they visit the %s`3. Please do so before the end of the next contest.', get_module_setting('shopname','medcontest')));
		$mail_msg .= translate_mail('`n`nA new contest has now begun so go join and get hunting.`n`nLonny.');

		for( $i=0; $i<count($id_array); $i++ )
		{
			systemmail($id_array[$i],$subject,$mail_msg);
		}

		unset($id_array);
	}
	else
	{
		addnews('`6The `@%s `6Contest came to an end, but nobody won! Maybe somebody should enter next time.', $med, TRUE);
	}

	addnews('`6A new `@%s `6contest has begun. Visit the `3%s `6to join.', $med, get_module_setting('shopname','medcontest'), TRUE);

	// Check to see if high score has been broken.
	if( get_module_setting('medconthigh','medcontest') < $topmed )
	{
		set_module_setting('medconthigh',$topmed,'medcontest');
		set_module_setting('medconthighid',$topplayer,'medcontest');
	}

	// Now clear everything.
	$sql = "DELETE FROM " . db_prefix('module_userprefs') . "
			WHERE modulename = 'medcontest'
				AND setting = 'medallion'
				OR setting = 'medhunt'
				OR setting = 'medpoints'
				OR setting = 'medfind'
				OR setting = 'lastloc'
				OR setting = 'seclastloc'";
	db_query($sql);

	$sql = "DELETE FROM " . db_prefix('module_userprefs') . "
			WHERE modulename = 'medcontest'
				AND setting = 'user_stat'
				AND value != 1";
	db_query($sql);
?>