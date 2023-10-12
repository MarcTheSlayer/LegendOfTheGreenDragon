<?php
	output_notl("`c`b%s`0`b`c`n`n",$shop_name);

	addnav('Options');

	$sql = "SELECT a.acctid, a.name
			FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
			WHERE b.modulename = 'medcontest'
				AND b.setting = 'medpoints'
				AND b.value > 0
				AND a.acctid = b.userid
			ORDER BY value+0 DESC
			LIMIT 1";
	$result = db_query($sql);
	if( $row = db_fetch_assoc($result) )
	{
		$sure = httpget('sure');
		if( $sure == 'yes' )
		{
			require_once('lib/commentary.php');
			injectrawcomment('superuser', $session['user']['acctid'], ": `7has reset the `Q" . $med . " `QContest`7. The winner was " . $row['name'] . "`7.");

			include('modules/medcontest/medcontest_timeleft.php');
			debuglog("Medcontest Reset by {$session['user']['name']} (ID: {$session['user']['acctid']}). Time left: $time_left");

			include('modules/medcontest/medcontest_reset.php');
	
			output('`n`#You have reset the `Q%s `QContest`#.`n`n`3Everyone in the contest has been notified by YoM and the winners have received their gems.', $med);
			addnav('Continue','runmodule.php?module=medcontest');
		}
		else
		{
			output('`#Are you sure that you want to reset the contest?');
			addnav('Yes','runmodule.php?module=medcontest&op=reset&sure=yes');
			addnav('No','runmodule.php?module=medcontest');
		}
	}
	else
	{
		output('`#No point resetting the contest as nobody has any points.');
		addnav('Continue','runmodule.php?module=medcontest');
	}
?>