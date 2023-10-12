<?php
/**
	Code taken from 'hiddenplayers' module by Sixf00t4 and altered to suit.
*/
	define('ALLOW_ANONYMOUS',TRUE);

	$args['handled'] = TRUE;
	$list_players = '';
	$onlinecount_players = 0;

	$sql = "SELECT name
			FROM " . db_prefix('accounts') . "
			WHERE locked = 0
				AND loggedin = 1
				AND laston > '" . date("Y-m-d H:i:s",strtotime("-".getsetting('LOGINTIMEOUT',900)." seconds")) . "'
			ORDER BY level DESC";

	if( get_module_setting('hide') == 1 )
	{
	    $sql = "SELECT a.name
	    		FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b,
	    		WHERE b.modulename = 'whosthere'
	    			AND b.setting = 'hidden'
	    			AND b.value != 1
	    			AND a.acctid = b.userid
	    			AND	a.locked = 0
	    			AND a.loggedin = 1
	    			AND a.laston > '" . date("Y-m-d H:i:s",strtotime("-".getsetting('LOGINTIMEOUT',900)." seconds")) . "'
	    		ORDER BY a.level DESC";
	}

	$result = db_query($sql);
	$count = db_num_rows($result);
	$list_players = appoencode(sprintf(translate_inline("`bCharacters Online`n(%s Players):`b`n"),$count));

	while( $row = db_fetch_assoc($result) )
	{
		$list_players .= appoencode("`^{$row['name']}`n");
		$onlinecount_players++;
	}
	db_free_result($result);

	if( $onlinecount_players == 0 )
	{
		$list_players .= appoencode(translate_inline("`inone`i"));
	}

	$args['list'] = $list_players;
	$args['count'] = $onlinecount_players;
?>