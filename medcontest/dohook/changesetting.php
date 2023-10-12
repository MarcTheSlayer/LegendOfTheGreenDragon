<?php
	if( $args['setting'] == 'villagename' )
	{
		if( $args['old'] == get_module_setting('medloc') )
		{
			set_module_setting('medloc', $args['new']);
		}
	}
?>