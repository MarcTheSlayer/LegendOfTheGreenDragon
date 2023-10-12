<?php
	$sql = "SELECT acctid, name, gems, goldinbank, value
			FROM " . db_prefix('accounts') . "
			LEFT JOIN " . db_prefix('module_userprefs') . "
				ON acctid = userid
			WHERE modulename = 'tournament'
				AND setting = 'points'
				AND value <> ''
			ORDER BY value + 0 DESC LIMIT 3";
	$result = db_query($sql);
	$count = db_num_rows($result);
	if( $count > 0 )
	{
		require_once('lib/systemmail.php');
		$i=0;
		$position1 = translate_inline(array('1st','2nd','3rd'));
		$position2 = translate_inline(array('First','Second','Third'));
		while( $row = db_fetch_assoc($result) )
		{
			if( $i == 0 )
			{	// For the HoF.
				$champions = @unserialize(get_module_setting('champions'));
				if( !is_array($champions) ) $champions = array();
				$champions[0][] = $row['acctid'];
				$champions[1][] = $row['value'];
				set_module_setting('champions',serialize($champions));
			}

			$gold = get_module_setting('r'.($i+1).'gold');
			$gems = get_module_setting('r'.($i+1).'gems');
			set_module_pref('gemswon',$gems,'tournament',$row['acctid']);
			set_module_pref('goldwon',$gold,'tournament',$row['acctid']);

			$subject = translate_inline(array("`%Congratulation!!! You came %s in the Tournament!", $position1[$i]));
			$message = translate_inline(array("`@You have won the %s Prize at The Tournament and have gained `^%s Gold `@and `% %s Gems`@!!!", $position2[$i], $gold, $gems));
			$message .= translate_inline(array("`n`n`3You can collect your winnings if you're over level %s the next time you visit the Tournament.`n`n-Sir Tristan", get_module_setting('collect')));
			systemmail($row['acctid'], $subject, $message, 'Sir Tristan');
			addnews("`@%s `#has finished in `^%s place`# in the `@Tournament `#and has won `^%s gold`# and `% %s gems`#!!`0", $row['name'], $position1[$i], number_format($gold), $gems);
			$i++;
		}
	}

	debuglog("The Tournament has ended.");

	db_query("DELETE FROM " . db_prefix('module_userprefs') . " WHERE modulename = 'tournament' AND (setting = 'entry' OR setting = 'points' OR setting = 'allprefs')");
	set_module_setting('leader', 0);
	set_module_setting('status', 0);
	set_module_setting('start',date("Y-m-d H:i:s"));

	addnews('`3A new Tournament is about to begin so sign up now for fame and riches!');
?>