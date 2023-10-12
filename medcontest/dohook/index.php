<?php
	if( get_module_setting('indexstats','medcontest') )
	{
		$sql = "SELECT a.name
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
			include('modules/medcontest/medcontest_timeleft.php');
			output("`@The %s `@Contest Leader is `Q%s`@.`0`n", $med, $row['name']);
			output('`2The contest ends in `@%s`2.`0`n', $time_left);
		}
		db_free_result($result);
	}
?>