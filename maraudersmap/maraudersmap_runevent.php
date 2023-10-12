<?php
	$session['user']['specialinc'] = 'module:maraudersmap';

	switch( $type )
	{
		case 'forest':
			if( get_module_setting('mapowner') == $session['user']['acctid'] )
			{
				output("`n`6You come across a small cave entrance while in the forest and wonder if it's a tunnel to another location.`n`n");
				output("It's dark, muddy and doesn't look like any of the other tunnels. You decide that it's not safe and as you exit the tunnel the ceiling suddenly caves in on you!`n`n");
				output("You quickly dig yourself out, but as you check your pockets you find the `3%s `6missing!!!`n`n", get_module_setting('mapname'));
				if( $session['user']['turns'] >= 2 )
				{
					output("You spend 2 turns searching the muddy earth, but eventually give up.`0`n");
					$session['user']['turns'] -= 2;
				}
				else
				{
					output("You spend some time searching the muddy earth, but eventually give up.`0`n");
				}
				$session['user']['spirits'] = -2;
				addnews('`&%s `^lost the `&%s `^in the forest of %s!', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner();
				debuglog("lost the ".get_module_setting('mapname')."`0 in the forest of {$session['user']['location']}.");
			}
			elseif( get_module_setting('mapowner') == 0 && get_module_setting('maploc') == $session['user']['location'] )
			{
				output("`n`6You come across a small cave entrance while in the forest, but it looks as though there has been a cavein.`n`n");
				output("Just as you turn to leave a piece of parchment half buried in the earth happens to catch your eye. You bend down and dig it out and discover that it's the `3%s`^!`n`nIt must be your lucky day!!`0`n", get_module_setting('mapname'));
				$session['user']['spirits'] = 2;
				addnews('`&%s `^found the `&%s `^in the forest of %s!`0', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner($session['user']['acctid']);
				debuglog("found the ".get_module_setting('mapname')."`0 in the forest of {$session['user']['location']}.");
			}
			else
			{
				output("`n`6You come across a small cave entrance while in the forest, but it looks as though there has been a cavein.`n`n");
				output("Could this have been one of the mysterious `3%s `6tunnels you wonder to yourself.`0`n", get_module_setting('mapname'));
			}
			addnav('Continue','forest.php');
		break;

		case 'village':
			if( get_module_setting('mapowner') == $session['user']['acctid'] )
			{
				output("`n`6As you walk through %s, you reach into your pocket for the `&%s`6, but it's not there! Perhaps someone has stolen it from you. Thieves!!!`0`n`n", $session['user']['location'], get_module_setting('mapname'));
				$session['user']['spirits'] = -2;
				addnews('`&%s `^lost the `&%s `^in %s!', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner();
				debuglog("lost the ".get_module_setting('mapname')."`0 in {$session['user']['location']}.");
			}
			elseif( get_module_setting('mapowner') == 0 && get_module_setting('maploc') == $session['user']['location'] )
			{
				output("`n`6A dirty piece of parchment blows past your feet and you notice it has writing on it. Thinking it may be important to somebody, you pick it up.`n`n");
				output("All thought of returning it vanish from your mind as you discover that it's the `3%s`6! You tuck it into your pocket making sure that no one has seen you.`0`n", get_module_setting('mapname'));
				$session['user']['spirits'] = 2;
				addnews('`&%s `^found the `&%s `^in %s!`0', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner($session['user']['acctid']);
				debuglog("found the ".get_module_setting('mapname')."`0 in {$session['user']['location']}.");
			}
			else
			{
				output('`n`6A piece of parchment blows past your feet and thinking that it\'s the `3%s`6, you dive on it. It\'s a map alright, but one with directions to the nearest golf sale.`n`n', get_module_setting('mapname'));
				output('You lose some charm having embarressed yourself in front of everyone.`0`n`n');
				$session['user']['charm'] -= 5;
			}
			addnav('Continue','village.php');
		break;

		case 'travel':
			if( get_module_setting('mapowner') == $session['user']['acctid'] )
			{
				output("`n`6You come across a small cave entrance while taking a shortcut and wonder if it's a tunnel to another location.`n`n");
				output("It's dark, muddy and doesn't look like any of the other tunnels. You decide that it's not safe and as you exit the tunnel the ceiling suddenly caves in on you!`n`n");
				output("You quickly dig yourself out, but as you check your pockets you find the `3%s `6missing!!!`n`n", get_module_setting('mapname'));
				if( $session['user']['turns'] >= 2 )
				{
					output("You spend 2 turns searching the muddy earth, but eventually give up.`0`n");
					$session['user']['turns'] -= 2;
				}
				else
				{
					output("You spend some time searching the muddy earth, but eventually give up.`0`n");
				}
				$session['user']['spirits'] = -2;
				addnews('`&%s `^lost the `&%s `^during travels near %s!', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner();
				debuglog("lost the ".get_module_setting('mapname')."`0 whilst travelling.");
			}
			elseif( get_module_setting('mapowner') == 0 && get_module_setting('maploc') == $session['user']['location'] )
			{
				output("`n`6You come across a small cave entrance while taking a shortcut, but it looks as though there has been a cavein.`n`n");
				output("Just as you turn to leave a piece of parchment half buried in the earth happens to catch your eye. You bend down and dig it out and discover that it's the `3%s`6!`n`nIt must be your lucky day!!`0`n", get_module_setting('mapname'));
				$session['user']['spirits'] = 2;
				addnews('`&%s `^found the `&%s `^during travels near %s!`0', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner($session['user']['acctid']);
				debuglog("found the ".get_module_setting('mapname')."`0 during travels.");
			}
			else
			{
				output("`n`6You come across a small cave entrance while taking a shortcut, but it looks as though there has been a cavein.`n`n");
				output("Could this have been one of the mysterious `3%s `6tunnels you wonder to yourself.`0`n", get_module_setting('mapname'));
			}
			// Travel has a continue link.
		break;

		case 'inn':
			if( get_module_setting('mapowner') == $session['user']['acctid'] )
			{
				if( is_module_active('drinks') )
				{
					if( get_module_pref('drunkeness','drinks') > 50 )
					{
						output("`n`6You've drunk one too many and must have let slip about the `3%s `6as you can no longer find it in any of your pockets!`0`n`n", get_module_setting('mapname'));
						$session['user']['spirits'] = -2;
						addnews('`&%s `^lost the `&%s `^in `&%s `^after getting drunk!`0', $session['user']['name'], get_module_setting('mapname'), getsetting('innname', LOCATION_INN));
						maraudersmap_changeowner();
						debuglog("lost the ".get_module_setting('mapname')."`0 in the inn.");
					}
					else
					{
						output("`n`6You should be very careful who you tell about the `3%s `6in this place. Especially when you know how much you like to talk after a few drinks.`0`n", get_module_setting('mapname'));
					}
				}
				else
				{
					output("`n`6You should be very careful who you tell about the `3%s `6in this place. Especially when you know how much you like to talk after a few drinks.`0`n", get_module_setting('mapname'));
				}
			}
			elseif( get_module_setting('mapowner') == 0 && get_module_setting('maploc') == $session['user']['location'] )
			{
				output("`n`6A folded piece of parchment just happens to catch your eye. You bend down and grab it from under a table and discover that it's the `3%s`6!`n`nIt must be your lucky day!!`0`n", get_module_setting('mapname'));
				$session['user']['spirits'] = 2;
				addnews('`&%s `^found the `&%s `^in `&%s`^!`0', $session['user']['name'], get_module_setting('mapname'), getsetting('innname', LOCATION_INN));
				maraudersmap_changeowner($session['user']['acctid']);
				debuglog("found the ".get_module_setting('mapname')."`0 in the inn.");
			}
			else
			{
				output('`n`6Did you just hear someone say they had the `3%s`6?`0`n`n', get_module_setting('mapname'));
				$sql = "SELECT name
						FROM " . db_prefix('accounts') . "
						WHERE acctid = '" . get_module_setting('mapowner') . "'";
				$res = db_query($sql);
				$row = db_fetch_assoc($res);
 				if( $row['name'] != '' ) output("`6You scan the patrons for `&%s`6, but don't see them. Oh well.`0`n", $row['name']);
			}
			addnav('Continue','inn.php');
		break;

		case 'gardens':
			output("`n`6You stop by a stream running through the gardens and think this would be a great place to pause and drink some water.`n`n");
			if( get_module_setting('mapowner') == $session['user']['acctid'] )
			{
				output("As you bend down to scoop some water with your hands, the `3%s `6falls out of your pocket and into the stream where it is carried away.`n`n", get_module_setting('mapname'));
				if( $session['user']['turns'] >= 1 )
				{
					output("You spend 1 turn running after it, but the current is too fast and it's gone.`0`n");
					$session['user']['turns'] -= 1;
				}
				else
				{
					output("You run down stream after it, but the current is too fast. Eventually you give up.`0`n");
				}
				$session['user']['spirits'] = -2;
				addnews('`&%s `^lost the `&%s `^in the gardens of %s!', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner();
				debuglog("lost the ".get_module_setting('mapname')."`0 in the gardens of {$session['user']['location']}.");
			}
			elseif( get_module_setting('mapowner') == 0 && get_module_setting('maploc') == $session['user']['location'] )
			{
				output("`n`6As you scoop the water up in your hands, a piece of parchment floats past and you grab it.`n`n");
				output("When you open it up you discover that it's the `3%s`6!`n`nIt must be your lucky day!!`0`n", get_module_setting('mapname'));
				$session['user']['spirits'] = 2;
				addnews('`&%s `^found the `&%s `^in the gardens of %s!`0', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner($session['user']['acctid']);
				debuglog("found the ".get_module_setting('mapname')."`0 in the gardens of {$session['user']['location']}.");
			}
			else
			{
				output("`n`6The cold water refreshes you as you scoop it up in your hands and drink it.`n`n");
				output("You notice a piece of parchment floating past and think nothing of it.`0`n");
				if( $session['user']['hitpoints'] < $session['user']['maxhitpoints'] ) $session['user']['hitpoints'] = $session['user']['maxhitpoints'];
			}
			addnav('Continue','gardens.php');
		break;

		case 'beach':
			if( get_module_setting('mapowner') == $session['user']['acctid'] )
			{
				output("`n`6As you walk along the beach you suddenly feel yourself starting to sink. `^It's QUICKSAND!!!`n`n");
				output("The weight of your `&%s `6and `&%s `6pull you down quite quickly, but hope is not lost. Some nearby people see you in trouble and throw you a rope and together with their help you get free.`n`n", $session['user']['weapon'], $session['name']['armor']);
				output("As you stand there thanking them for saving your life, you discreetly reach into your pocket for the `&%s`6, but it's not there! You've lost it!`0`n`n", get_module_setting('mapname'));
				$session['user']['spirits'] = -2;
				addnews('`&%s `^lost the `&%s `^at the beach in %s!', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner();
				debuglog("lost the ".get_module_setting('mapname')."`0 at the beach in {$session['user']['location']}.");
			}
			elseif( get_module_setting('mapowner') == 0 && get_module_setting('maploc') == $session['user']['location'] )
			{
				output("`n`6As you play in the sand amongst the other people you pull a piece of parchment out of the hole you were going to fill with water.`n`n");
				output("You brush the sand off and discover that it's the `3%s`6! You tuck it into your pocket making sure that no one has seen you.`0`n", get_module_setting('mapname'));
				$session['user']['spirits'] = 2;
				addnews('`&%s `^found the `&%s `^at the beach!`0', $session['user']['name'], get_module_setting('mapname'));
				maraudersmap_changeowner($session['user']['acctid']);
				debuglog("found the ".get_module_setting('mapname')."`0 at the beach in {$session['user']['location']}.");
			}
			else
			{
				output('`n`6You see a piece of parchment sticking out of a sand castle. In your hurry to get it you demolish the castle leaving the small boy that built it in tears.`n`n');
				output('The parchment turns out to be the menu for Saucy\'s Kitchen. You\'re about to leave when the small boy\'s father comes over and thumps you in the face.`0`n');
				$session['user']['hitpoints'] *= 0.9;
				if( $session['user']['hitpoints'] <= 0 ) $session['user']['hitpoints'] = 1;
			}
			addnav('Continue','runmodule.php?module=beach');
		break;

		case 'amusementpark':
			if( get_module_setting('mapowner') == $session['user']['acctid'] )
			{
				output("`n`6The screams of excitement fills your ears and the smell of burgers and candyfloss do likewise for your nose. As you wonder what to do next a large crowd jostles past you towards a scary ride.`n`n");
				output("Suddenly you get the urge to check your pockets and are horrified to discover your `&%s `6 missing! Somebody must have stolen it. THIEVES!!!`0`n`n", get_module_setting('mapname'));
				$session['user']['spirits'] = -2;
				addnews('`&%s `^lost the `&%s `^at the amusement park in %s!', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
				maraudersmap_changeowner();
				debuglog("lost the ".get_module_setting('mapname')."`0 at the amusement park in {$session['user']['location']}.");
			}
			elseif( get_module_setting('mapowner') == 0 && get_module_setting('maploc') == $session['user']['location'] )
			{
				output("`n`6You walk over to the nearest rubbish bin to dispose of your half eaten candyfloss and a piece of parchment inside catches your eye.`n`n");
				output("You pull it out and wipe off the sticky red stuff and discover that it's the `3%s`6! You tuck it into your pocket making sure that no one has seen you.`0`n", get_module_setting('mapname'));
				$session['user']['spirits'] = 2;
				addnews('`&%s `^found the `&%s `^at the amusement park!`0', $session['user']['name'], get_module_setting('mapname'));
				maraudersmap_changeowner($session['user']['acctid']);
				debuglog("found the ".get_module_setting('mapname')."`0 at the amusement park in {$session['user']['location']}.");
			}
			else
			{
				output('`n`6You walk through the amusement park enjoying the excitement and eat some candyfloss.`n`n');
				output('A piece of parchment gets blown on to your leg and you brush it off.`0`n');
			}
			addnav('Continue','runmodule.php?module=amusementpark');
		break;

		case 'cellar':
			if( get_module_setting('mapowner') == $session['user']['acctid'] )
			{
				output("`n`6You come across an old man who can't seem to find his way out. After giving him directions to the exit you discover that your `&%s `6 missing! That old git stole it!!!`0`n`n", get_module_setting('mapname'));
				$session['user']['spirits'] = -2;
				addnews('`&%s `^lost the `&%s `^in a dark cellar!', $session['user']['name'], get_module_setting('mapname'));
				maraudersmap_changeowner();
				debuglog("lost the ".get_module_setting('mapname')."`0 in a dark cellar.");
			}
			elseif( get_module_setting('mapowner') == 0 && get_module_setting('maploc') == $session['user']['location'] )
			{
				output("`n`6As you fumble about in the dark you come across an old man who offers to sell you a map. Thinking it's a map for the cellar you readily agree.`n`n");
				if( $session['user']['gold'] > 0 )
				{
					output("You give the old man all your gold and get the map in return. As you examine it you discover that it's not a map of the cellar at all, but the `3%s `6which shows entrances to secret tunnels. What a bargin!`0`n", get_module_setting('mapname'));
					$session['user']['gold'] = 0;
					$session['user']['spirits'] = 2;
					addnews('`&%s `^found the `&%s `^in a dark cellar!`0', $session['user']['name'], get_module_setting('mapname'));
					maraudersmap_changeowner($session['user']['acctid']);
					debuglog("found the ".get_module_setting('mapname')."`0 in a dark cellar.");
				}
				else
				{
					output('You feel around in your pockets for some gold, but can\'t find any. Who wants a stupid map anyway?`0`n');
				}
			}
			else
			{
				output('`n`6You bump into an old man and ask if he has any sort of map that would help finding your way about down here.`n`n');
				output('He hands you a piece of parchment with `^"YOU ARE HERE -->" `6written on it.`0`n');
			}
			addnav('Continue','runmodule.php?module=cellar');
		break;

		case 'darkalley':
			if( get_module_setting('mapowner') == $session['user']['acctid'] )
			{
				output("`n`6While walking along the dark alley you're attacked without warning. It is all over within moments and when you regain yourself you're horrified to discover your `&%s `6 missing! You've been ROBBED!!!`0`n`n", get_module_setting('mapname'));
				$session['user']['spirits'] = -2;
				addnews('`&%s `^was robbed of the `&%s `^in the dark alley!', $session['user']['name'], get_module_setting('mapname'));
				maraudersmap_changeowner();
				debuglog("lost the ".get_module_setting('mapname')."`0 in the dark alley.");
			}
			elseif( get_module_setting('mapowner') == 0 && get_module_setting('maploc') == $session['user']['location'] )
			{
				output("`n`6As you walk down the dark alley carefully scanning for would be robbers, you scan something else that's in the gutter.`n`n");
				output("You quickly pick it up without stopping and discover that it's the `3%s`6! You tuck it into your pocket making sure that no one has seen you.`0`n", get_module_setting('mapname'));
				$session['user']['spirits'] = 2;
				addnews('`&%s `^found the `&%s `^in the dark alley!`0', $session['user']['name'], get_module_setting('mapname'));
				maraudersmap_changeowner($session['user']['acctid']);
				debuglog("found the ".get_module_setting('mapname')."`0 in the dark alley.");
			}
			else
			{
				output('`n`6You walk down the dark alley with one hand on your `&%s `6just in case. Some parchment lies in the gutter, but thinking it may be a trap and not wanting to stop, you keep going.`n`n', $session['user']['weapon']);
			}
			addnav('Continue','runmodule.php?module=darkalley');
		break;

		default:
			output('`n`6Whilst minding your own business you\'re clubbed from behind by Pedobear. You come around a short while later and notice that your underwear is missing...`0`n');
			if( get_module_setting('mapowner') == $session['user']['acctid'] ) output('`n`6Luckily you still have the `&%s`6. Phew!`0`n', get_module_setting('mapname'));
			addnav('Continue','village.php');
		break;
	}
	$session['user']['specialinc'] = '';

	if( $session['user']['superuser'] & SU_DEVELOPER )
	{
		addnav('Superuser');
		addnav('Leave Map Event','runmodule.php?module=maraudersmap&op=quitevent');
	}
?>