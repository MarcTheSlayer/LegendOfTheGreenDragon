<?php
	$op2 = httpget('op2');
	if( empty($op2) )
	{
		output('`2"`@And now if you could choose a preference of being the spanker or the spankee, or would you prefer to leave that decision till the last moment?`2" `7Wraith `2asks you without bothering to lower his voice.');

		addnav('Preference');
		addnav('Spanker','runmodule.php?module=wraiths&op=spanks&op2=2');
		addnav('Spankee','runmodule.php?module=wraiths&op=spanks&op2=3');
		addnav('Decide Later','runmodule.php?module=wraiths&op=spanks&op2=1');
	}
	else
	{
		set_module_pref('spanks',$op2);
		switch( $op2 )
		{
			case 1:
				$title = translate_inline($session['user']['sex']==1?'madam':'sir');
				output('`n`2"`@Very well %s, I\'ll leave you to decide when you choose your spanking partner.`2" says `7Wraith`2.`n`n', $title);
			break;
	
			case 2:
				$sex = translate_inline(($gender==1)?'girls and boys':($gender==2)?'boys':($gender==3)?'girls':'');
				output('`n`2"`@Ah yes, I thought I glimpsed a dominant streak in you. I hope my %s are prepared for you!`2" laughs `7Wraith`2.`n`n', $sex);
			break;
	
			case 3:
				output('`n`2"`@Being the spankee is so much fun, my bum is still red and tender from an earlier session.`2" `7Wraith `2informs you.`n`n');			
			break;
		}
		addnav('Options');
		addnav('Continue','runmodule.php?module=wraiths&op2=1');
	}
?>