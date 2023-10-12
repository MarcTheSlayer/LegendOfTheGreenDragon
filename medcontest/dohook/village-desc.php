<?php
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
		output("`c`@The `Q%s `@Contest Leader is `Q%s`@.`c`n", $med, $row['name']);
	}
	db_free_result($result);
?>