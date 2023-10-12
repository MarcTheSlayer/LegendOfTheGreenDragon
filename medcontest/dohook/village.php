<?php
	if( $session['user']['location'] == get_module_setting('medloc') || get_module_setting('inallloc') == 1 )
	{
		tlschema($args['schemas']['marketnav']);
		addnav($args['marketnav']);
		tlschema();
		addnav(array('%s`0',get_module_setting('shopname','medcontest')),'runmodule.php?module=medcontest');
	}
?>