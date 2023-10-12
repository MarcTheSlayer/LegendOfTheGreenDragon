<?php
	output_notl("`c`b%s`0`b`c`n`n",$shop_name);

	$medallion = get_module_pref('medallion','medcontest');
	output("`n`3You hand over your `Q%s %s `3to Lonny who goes off to recalculate the scores.", $medallion, ($medallion==1?$med:$meds));

	increment_module_pref('medpoints',$medallion,'medcontest');
	clear_module_pref('medallion','medcontest');

	if( get_module_setting('medconthigh','medcontest') < get_module_pref('medpoints','medcontest') )
	{
		set_module_setting('medconthigh',get_module_pref('medpoints','medcontest'),'medcontest');
		set_module_setting('medconthighid',$session['user']['acctid'],'medcontest');
	}

	addnav('Options');
	addnav('See New Score','runmodule.php?module=medcontest');
?>