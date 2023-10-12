<?php
	output("`c`b`#The Tournament`0`b`c`n");
	output("`3You walk into a dimly lit office and peer around at it's sparsely furnished interior. ");

	if( get_module_setting('status') == 0 )
	{
		if( strtotime(get_module_setting('offtime'),strtotime(get_module_setting('start'))) <= time() )
		{
			set_module_setting('status', 1);
		}
	}
	include('modules/tournament/tournament_timeleft.php');
	if( get_module_setting('status') == 1 )
	{
		output('`n`nA sign on the wall tells you a new Tournament will start in `@%s`2.`0`n`n', tournament_timeleft());
	}
	elseif( get_module_setting('status') == 2 )
	{
		output('`n`nA sign on the wall tells you the Tournament will end in `@%s`2.`0`n`n', tournament_timeleft(2));
	}

	if( get_module_setting('status') > 0 )
	{
		if( get_module_pref('entry') == 1 )
		{
			if( get_module_setting('status') == 2 )
			{
				output("`3Sir Tristan welcomes you. `#Welcome, competitor. You have paid the entrance fee and may continue onwards to the Tournament Arena.`0`n`n");
				addnav('Enter Tournament');
				addnav('T?Enter Arena',$from.'&op=main');
			}
			elseif( get_module_setting('status') == 1 )
			{
				output("`3Sir Tristan welcomes you. `#Welcome, competitor. You have paid the entrance fee, but the Tournament hasn't begun yet. Come back later.`0`n`n");
			}
		}
		else
		{
			output("`3As your eyes adjust to the light, you notice a tall warrior appraising you.`n `n ");
			output("`#I am Sir Tristan, Tournament Master, `3he informs you. `#You might not be the mightiest warrior I've seen...but yet...I sense potential within you. ");
			output("Do you think you have what it takes to win my Grand Tournament?`n`n");

			output("`3The entrance fee is `^%s Gold `3pieces and `% %s %s`3.`n`n", get_module_setting('efeegold'), get_module_setting('efeegems'), translate_inline(get_module_setting('efeegems')==1?'Gem':'Gems'));
			output("The 3 mightiest warriors will receive riches in reward for their efforts:`n`n");

			rawoutput('<span style="font-size:larger">');
			output("`b`c`&First Place: %s Gems - %s Gold Pieces`n", get_module_setting('r1gems'), number_format(get_module_setting('r1gold')));
			output("`@Second Place:  %s Gems -  %s Gold Pieces`n", get_module_setting('r2gems'), number_format(get_module_setting('r2gold')));
			output("`^Third Place:  %s Gems -  %s Gold Pieces`b`c`n", get_module_setting('r3gems'), number_format(get_module_setting('r3gold')));
			rawoutput('</span>');

			output("`#Why not take a chance and enter? Are ye strong enough? Powerful enough? If ye have what it takes, Victory may be yours!`n`n");
			output("`3Please note that everybody has an equal chance - it matters not how many dragons ye've slain!`n`n");

			addnav('Enter Tournament');
			// Seductiveskills and 1 in 6 chance. Removed the ['sex'] == 1 part, why can't a man seduce Sit Tristan? :D
			if( $session['user']['specialty'] == 'SE' && rand(1,6) == 1 ) addnav('Y?`@Yes`0',$from.'&op=seductiveskills');
			else addnav('Y?`@Yes`0',$from.'&op=enter');
			addnav('N?`$No`0','village.php');
		}

		if( get_module_setting('status') == 1 )
		{
			$sql = "SELECT userid
					FROM " . db_prefix('module_userprefs') . "
					WHERE modulename = 'tournament'
						AND setting = 'entry'
						AND value = 1";
			$result = db_query($sql);
			$count = db_num_rows($result);
			output("`3You see a piece of parchment on a table with %s %s on it. This must be how many people have entered so far.`n`n", $count, translate_inline($count==1?'name':'names'));
			if( $count < get_module_setting('minimum') )
			{
				$need = get_module_setting('minimum') - $count;
				output("Sir Tristan sees you looking at the list and says, `#We need %s more before the Tournanment can begin!`0`n`n", $need);
			}
		}
	}
	else
	{
		output("`3Sir Tristan is pleased by your eagerness, but informs you that as a Tournament has just finished, the trials must all be set up again. `#Try back later`3, he says.`n`n");
	}


	$wongems = get_module_pref('gemswon');
	$wongold = get_module_pref('goldwon');
	if( $wongems || $wongold )
	{
		if( $session['user']['level'] >= get_module_setting('collect') )
		{
			$session['user']['gems'] += $wongems;
			$session['user']['gold'] += $wongold;
			clear_module_pref('gemswon','tournament',$session['user']['acctid']);
			clear_module_pref('goldwon','tournament',$session['user']['acctid']);

			output("`3Sir Tristan pulls a pouch out of a large chest and hands it to you. `#Here's your winnings from the previous Tournament, well done! `3He locks the chest and leaves the office.`n`n");
			output("`3You look in the pouch and see `^%s gold `3and `% %s %s`3. ", $wongold, $wongems, translate_inline($wongems==1?'gem':'gems'));
			if( $session['user']['specialty'] == 'TS' && rand(1,3) == 3 )
			{	// Thief skills, 1 in 3 chance of getting here.
				output("Your eyes then dart quickly to the locked chest and you wonder how much more is in there... You have the skill, but do you have the nerve?");
				addnav('Large Chest');
				addnav('Pick Open',$from.'&op=thiefskills');
			}
			else
			{
				output("A thirst comes over you and you ponder going to %s for a drink.", getsetting('innname', LOCATION_INN));
			}
		}
		else
		{
			output("`3Sir Tristan pulls you aside. `#Um, due to some unforeseeable error on my part, I don't have your winnings from the previous Tournament. Come back when you reach level %s and I should have it ready for you.`n`n", get_module_setting('collect'));
		}
	}

	if( get_module_setting('status') == 2 )
	{
		addnav('Scores');
		addnav('Tournament Scores',$from.'&op=scores');
	}
	addnav('Leave');
	villagenav();
?>