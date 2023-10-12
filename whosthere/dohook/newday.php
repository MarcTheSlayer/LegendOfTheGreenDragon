<?php
	$sql = "SELECT acctid FROM " . db_prefix('accounts') . " WHERE loggedin = 1 and laston < '" . date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",900)." seconds")) . "'";
	$result = db_query($sql);
	while( $row = db_fetch_assoc($result) )
	{
		db_query("UPDATE " . db_prefix('accounts') . " SET loggedin = 0 WHERE acctid = '" . $row['acctid'] . "'");
	}
	db_free_result($result);

	// If the player was dead, then this wont be empty.
	// This makes sure the person returns to the village that they died in.
	if( $deadloc = get_module_pref('deadloc') )
	{
		$session['user']['location'] = $deadloc;
		clear_module_pref('deadloc','whosthere',$session['user']['acctid']);
	}
?>