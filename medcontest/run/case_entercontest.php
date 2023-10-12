<?php
	output_notl("`c`b%s`0`b`c`n`n",$shop_name);

	output("`3Lonny takes your %s and hands you a potion... you down it without hesitation as you trust Lonny implicitly. ", (get_module_setting('gemcost','medcontest')==1?$gems[0]:$gems[1]));
	output("He then tells you that this `^m`\$a`@g`%i`#c `^p`\$o`@t`%i`#o`^n `3will give you the perception you need to see the `Q%s `3that are hidden everywhere.`n`n", $meds);
	output("`#You have entered into the contest! Get out there and start collecting `Q%s`#!", $meds);

	$session['user']['gems'] -= get_module_setting('gemcost','medcontest');
	set_module_pref('medhunt',1,'medcontest');
	set_module_pref('medfind',e_rand(round(get_module_setting('medallionmax','medcontest') * .75),get_module_setting('medallionmax','medcontest')),'medcontest');

	addnav('Options');
	addnav('Continue','runmodule.php?module=medcontest');
?>