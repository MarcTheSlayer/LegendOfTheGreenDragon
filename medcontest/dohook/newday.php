<?php
	$lastreset = get_module_setting('lastreset','medcontest') + ((get_module_setting('resettimer','medcontest') * 60 * 60 * 24));
	if( time() > $lastreset )
	{
		debuglog("Medcontest Auto Reset. Time now: ".time()." > Last reset: $lastreset");
		include('modules/medcontest/medcontest_reset.php');
	}
	if( get_module_pref('medhunt','medcontest') && $session['user']['spirits'] != -6)
	{
		set_module_pref('medfind',e_rand(round(get_module_setting('medallionmax','medcontest') * .75),get_module_setting('medallionmax','medcontest')),'medcontest');
	}
?>