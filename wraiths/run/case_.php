<?php
	$op2 = httpget('op2');
	if( empty($op2) )
	{
		output('`n`2You enter into Wrongful Wraith\'s House of Spanking, a shoppe which caters to the kinky side of peoples nature.`n`n');
	}
	else
	{
		output('`n`2You found making those choices very titillating and can\'t wait to get to the spanking part.`n`n');
	}

	output('`2On one side of the shoppe you can see ');

	if( $gender == 3 || $gender == 1 )
	{
		output('`2a half dozen or so young maidens giggling as they see you standing there all excited.`n');
		output('Each one smiles and flutters their eyelids trying to catch your attention.`n`n');
	}
	if( $gender == 1 )
	{
		output('`2On the otherside of the shoppe you see ');
	}
	if( $gender == 2 || $gender == 1 )
	{
		output('`2a half dozen or so muscular handsome men sitting in chairs along the wall.');
		output('Each one smiles and flexes their biceps hoping to catch your attention.`n`n');
	}

	if( get_module_pref('totaltoday') >= get_module_setting('spanksperday') )
	{
		output('`7Wraith `2strokes his goatee and smiles at you, "`@Sorry friend, but you look like you\'ve had quite enough today.`2"`n`n');
		output('`2"`@Why don\'t you come back again tomorrow for more excitement.`2"`n`n`7Wraith `2then shows you the door.`n`n');

		addnav('Leave');
		addnav('E?Exit House','village.php');
	}
	else
	{
		$spankcost = get_module_setting('spankcost');
		output('`2A sign on the customer\'s counter reads, "`@The cost to indulge in pleasures of the spanking kind is only `^%s gold`@.`2"`n`n', $spankcost);

		if( $session['user']['gold'] > $spankcost )
		{
			output('`7Wraith `2sees you with a full pouch and says, "`@If you\'d like to pay upfront %s, you\'ll then be free to pick a spanking partner.`2"`n`n', translate_inline($session['user']['sex']==1?'madam':'sir'));	

			addnav('Options');
			addnav('P?Pay Gold','runmodule.php?module=wraiths&op=pay');

			if( get_module_setting('allowchoice') == 1 )
			{
				output('`2"`@Would you like to change your preference?`2"`7, asks Wraith.`n`n');
				addnav('Reset');
				addnav('Y?Yes','runmodule.php?module=wraiths&reset=yes');
			}
		}
		else
		{
			output('`7Wraith `2sees you with an empty pouch and sighs, "`@I see you have a cash flow problem, do come back when you have enough gold!`2"`n`n');	
		}

		addnav('Leave');
		addnav('E?Exit House','village.php');
	}
?>