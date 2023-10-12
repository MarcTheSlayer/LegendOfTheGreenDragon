<?php
/**
	A core file edit must be made to list.php
*/
	if( get_module_setting('hide') == 1 )
	{
		$hide_ids = array();

		$sql = "SELECT userid
				FROM " . db_prefix('module_userprefs') . "
				WHERE modulename = 'whosthere'
					AND setting = 'hidden'
					AND value = 1";
		$result = db_query($sql);

		while( $row = db_fetch_assoc($result) )
		{
			$hide_ids[] = $row['userid'];	
		}
		db_free_result($result);

		$count = count($args);
		for( $i=0; $i<$count; $i++ )
		{
			$args[$i]['loggedin'] = ( in_array($args[$i]['acctid'], $hide_ids) ) ? '0' : $args[$i]['loggedin'];
		}
		unset($hide_ids);
	}
?>