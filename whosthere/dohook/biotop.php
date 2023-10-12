<?php
	if( get_module_setting('hide') == 1 )
	{
		$op = httpget('hide');
		$id = $args['acctid'];
		if( get_module_pref('hidden','whosthere',$id) == 1 || $op == 'Hide' )
		{
			$args['loggedin'] = 0;
		}
		if( $op == 'Unhide' )
		{
			$args['loggedin'] = 1;
		}
	}
?>