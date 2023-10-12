<?php
	$op2 = httpget('op2');
	if( empty($op2) )
	{
		output('`2"`@Ah Welcome Welcome`2", shouts `7Wraith `2as he makes his way towards you. "`@You seem to have a confused look about you. Allow me to help.`2" he says.`n`n');
		output('`2"`@What is you preference... That is, do you prefer men or women?`2" he asks, but then adds with a wink, "`@Maybe both?`2"');

		addnav('Preference');
		addnav('I like Women','runmodule.php?module=wraiths&op=gender&op2=3');
		addnav('I like Men','runmodule.php?module=wraiths&op=gender&op2=2');
		addnav('I like Both','runmodule.php?module=wraiths&op=gender&op2=1');
	}
	else
	{
		set_module_pref('gender',$op2);
		$title = translate_inline($session['user']['sex']==1?'madam':'sir');
		switch( $op2 )
		{
			case 1:
				output('`n`2"`@An marvelous choice %s, best of both worlds eh?`2" says `7Wraith `2with a wink.`n`n', $title);
			break;

			case 2:
				output('`n`2"`@An wonderful choice %s, males have a better defined body of the two sexes.`2"`n`n', $title);
			break;

			case 3:
				output('`n`2"`@An excellent choice %s, females are naturally the more beautiful of the two sexes.`2"`n`n', $title);			
			break;
		}
		addnav('Options');
		addnav('Continue','runmodule.php?module=wraiths');
	}
?>