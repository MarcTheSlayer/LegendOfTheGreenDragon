<?php
	$spankcost = get_module_setting('spankcost');
	$session['user']['gold'] -= $spankcost;
	increment_module_pref('totaltoday');
	increment_module_pref('totaldk');

	output('`n`2You give `7Wraith `2the `^%s gold`2. He strokes his goatee and asks, "`@Which one of these ', $spankcost);

	if( $gender == 3 || $gender == 1 )
	{
		output('fair maidens');

		addnav('Select a Maiden');
		for( $i=1; $i<=5; $i++ )
		{
			addnav(array('%s', $partners[$i]),"runmodule.php?module=wraiths&op=partner&op2=$i");
		}
	}
	if( $gender == 1 )
	{
		output(' or ');
	}
	if( $gender == 2 || $gender == 1 )
	{
		output('handsome men');

		addnav('Select a Stud');
		for( $i=6; $i<=10; $i++ )
		{
			addnav(array('%s', $partners[$i]),"runmodule.php?module=wraiths&op=partner&op2=$i");
		}

	}

	output(' would you like to be you spanking partner?`2"`n`n');
?>