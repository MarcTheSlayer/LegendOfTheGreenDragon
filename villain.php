<?php
/*
	Modified by MarcTheSlayer.

	July 2008 - v1.0a
		+ Increased odds from 1in4 to 1in7 for death outcome.
		+ Added option to fight Balufac.
		+ Added HoF section.

	October 2008 - v1.0b
		+ Removed the HoF section. Pointless really.
*/
function villain_getmoduleinfo()
{
	$info = array(
		"name"=>"The Villain's Cave",
		"description"=>"The hero gets caught by an evil overlord and might have the chance to escape.",
		"version"=>"1.0b",
		"author"=>"`7Christian Rutsch,`ninspired by `4Talisman`7,`nimproved by `2Elessa`7,`nmodified and improved by `@MarcTheSlayer`0",
		"category"=>"Forest Specials",
		"download"=>"http://dragonprime.net/index.php?topic=9931.0",
		"settings"=>array(
			"encounters"=>"How many times can they encounter the villian in a Dragon cycle?,int|3",
			"forest"=>"Base chance in forest,range,5,100,5|100",
			"travel"=>"Base chance during travel,range,5,100,5|100",
			"gemswon"=>"Amount of gems won with fight victory,int|5"
		),
		"prefs"=>array(
			"Villain Prefs,title",
			"encounters"=>"How many times they've encountered the villain.,int|0"
		)
	);
	return $info;
}

function villain_install()
{
	output("`c`b`Q%s 'villain' Module.`b`n`c", translate_inline(is_module_active('villain')?'Updating':'Installing'));
	module_addeventhook('forest', "return get_module_setting('forest', 'villain');");
	module_addeventhook('travel', "return get_module_setting('travel', 'villain');");
	module_addhook('dragonkilltext');
	return TRUE;
}

function villain_uninstall()
{
	output("`n`c`b`Q'villain' Module Uninstalled`0`b`c");
	return TRUE;
}

function villain_dohook($hookname, $args)
{
	clear_module_pref('encounters','villain');
	return $args;
}

function villain_runevent($type, $from)
{
	global $session;

	$session['user']['specialinc'] = 'module:villain';
	$type = httpget('type');
	$op = httpget('op');

	if( get_module_pref('encounters') < get_module_setting('encounters') )
	{
		if( $op == '' )
		{
			if( $type == 'suicide' || $type == 'thrill' )
			{
				output("`4You walk through the forest, cutting away the underbrush with your %s`4.", $session['user']['weapon']);
				output("You are in the right mood to kill any of those vicious creatures lurking in the depth of the forest, ready to slay any uncautious adventurer.");
				output("However, you don't see the shadow appearing from behind a tree and slowly approaching you from your blindside.");
				output("Suddenly you have this strange feeling, that the world is spinning around you and the last thing you see, is someone bending over you and strapping your fists to your back.");
			}
			else
			{
				output("`6Cautiously you step through the forest, carefully observing every movement and sound.");
				output("With every sudden, unexpected noise you jump and only after close investigation you feel relieved enough to continue your search for one of those fabulous creatures living in this forest.");
				if( $session['user']['dragonkills'] < 5 )
				{
					output("You are going to be a great hero, that is sure.");
					output("And you are going to kill a lot of those creatures to become such a hero.");
					output("But you won't let yourself get surprised in any way, no.");
				}
				else
				{
					output("You have slain the dragon many times, but still you think, 'Better safe than sorry'.");
					output("And so you turn every leaf and every stone in the hope that you won't get surprised by any of those malevolent critters living here.");
				}
				output("But with you being so focussed on caring for your safety it is no problem for this shadow to appear behind you and send you to the vale of dreams.");
			}
			addnav('Continue',$from.'op=step1');
		}
		elseif( $op == 'step1' )
		{
			output("`5You open your eyes, the pain errupting in your head is almost overwhelming.");
			output("You try to lift your hands to your head, when you realize that you are tied to a table and feel yourself unable to move.`n`n");
			output("Looking around you find that you are within a cave. ");
			output("Torchlight is dancing on the walls and strange noises can be heard coming from the dark corners.");
			output("You tilt your head to the side to take a glimpse of what is going on in here, when you see a small creature appearing.");
			output("It is definitely too ugly for a troll, too small for a dwarf and too strange for an elf, but what it is you cannot tell.`n");
			output("As this `ithing`i notices that you are awake, it turns to you and begins to speak.`n`n");
			output("\"`7I am Balufac The Great.");
			output("I will be the ruler of this world. Soon!`6\"");
			output("With a really annoying giggling he continues, \"`7But for this to come to fruition, it takes only a little sacrifice.");
			output("And that where my plans involve... YOU!`6\"`n`n");
			output("You cannot believe what you are hearing. This squabbly little thing is going to succeed in taking over the entire world. ");
			output("And you have no chance to stop him.`n");
			output("But he continues, \"`7But before I can continue the `&Codex of the Villain, Annex B Chapter 6`7 tells me, I have to introduce you to my evil plan.");
			output("So, let's get this nonsense behind us.`6\"`n`n");
			output("And so he starts his liturgy about his almighty evilness and after a while... `^");

			switch( e_rand(1,7) )
			{
				case 1:
				case 2: // You free yourself and escape.
					output("you recognize, that the bonds which kept you to the table have become loose.");
					output("Fortunately, Balufac is so absorbed in telling you what he will do after taking over the world that he does not notice your escape. You make your way back to the forest and freedom.`n`n");

					if( $session['user']['turns'] > 0 )
					{
						output("`#During your stay you lost one forest fight.");
						$session['user']['turns']--;
					}
					else
					{
						output("`#During your stay you lose some time.");
					}

					$session['user']['specialinc'] = '';
					increment_module_pref('encounters',1,'villain');

					addnews("`#%s `3escaped the clutches of a villain. `7\"You wont get away so easily next time!\"`3, the villain was heard yelling when he noticed %s was gone.", $session['user']['name'], translate_inline($session['user']['sex']==1?'she':'he'));
				break;

				case 3:
				case 4: // You challenge him.
					output("he finishes talking about his plans.");
					output("You have looked for every single opportunity to escape but found nothing.");
					output("Then, you see your last chance.");
					output("Raising your voice you yell at Balufac, \"`3Hey, evil overlord, ruler of the world to-be! ");
					output("If you are so smart and almighty, why not battle me in a hand to hand contest?`6\"`n`n");
					output("You would have never thought, Balufac would ever consider your suggestion but he seems rather willing to let you contest him.");
					output("He cuts away your bonds and you prepare to fight him.");
					output("Then you see some sort of device, Balufac seems to use for his plans.");
					output("Some levers and buttons that are used to operate his device. ");
					output("And one big red button is labelled in big letters: `\$DO NOT PRESS`6.");
					output("This must be the self-destruct button!`n`n");
					output("`3You don't have time to do both, so you must pick. Fight Balufac, or push the button...");

					addnav('Options');
					addnav('Push Button!',$from.'op=button');
					addnav('Fight Balufac!',$from.'op=fight&go=gethim');
				break;

				case 5:
				case 6: // Villagers free you.
					output("you hear voices shouting from the entrance of the cave.");
					output("A struggling fight seems to be going on and it is coming closer.");
					output("Soldiers wearing black leather and with animal skins around there shoulders are fleeing.");
					output("Close behind them you spot a group of raging villagers, armed with pitchforks and torches hunting down Balufac's troops.");
					output("You rip at your bonds, but they keep you securely in place.`n`n");
					output("Helplessly you have to watch the villagers destroying the device Balufac wanted to use to take over the world.");
					output("After they have arrested Balufac and his minions, the leader frees you and leads you to the forest.");
					output("You feel a little ashamed, that you had to be freed by a group of villagers instead of fighting this army alone.`n`n");

					// Me just having some fun. :)
					$weapons_array = array('pitchforks and torches','buckets and spades','hair and beauty products',
								'nail clippers and tweezers','knives and forks','dentist\'s magazines',
								'sudoku puzzles','comfortable pillows','Barry Manilow records','feather dusters',
								'sticks and stones','spinach and toothpicks','cups of tea and biscuits');
					shuffle($weapons_array);

					if( $session['user']['gold'] )
					{
						output("`#You hand over all your gold to the villagers and hope they don't tell anyone that they had to rescue you.`0`n`n");

						addnews("`#%s `3was rescued by a group of villagers armed with %s. It cost %s `^%s gold`3.", $session['user']['name'], translate_inline($weapons_array[0]), translate_inline($session['user']['sex']==1?'her':'him'), $session['user']['gold']);
						$session['user']['gold'] = 0;
					}
					elseif( $session['user']['charm'] > 5 )
					{
						output("`#You lose some charm.`0`n`n");
						$session['user']['charm'] -= 5;
						addnews("`#%s `3lost charm after being rescued by a group of villagers armed with %s and couldn't reward them.", $session['user']['name'], translate_inline($weapons_array[0]));
					}
					else
					{
						addnews("`#%s `3was rescued by a group of villagers armed with %s.", $session['user']['name'], translate_inline($weapons_array[0]));
					}

					$session['user']['specialinc'] = '';
					increment_module_pref('encounters',1,'villain');
				break;

				case 7: // You're killed by Balufac.
					output("he stops.");
					output("You are looking at him quizzically for you don't know, what will happen next, but Balufac does not care for you.");
					output("Using some levers and buttons on his strange machine in the back you hear a hissing noise right above you.");
					output("Out of the dark a strange, crystalline object descends.");
					output("Flashes of light are dancing on its surface.");
					output("Unable to speak or move anymore you watch in terror what is happening right now, right above you.");
					output("The flashes stop dancing and start gathering at the tip of the object.");
					output("The last thing you can remember is the voice of Balufac, manically laughing and shouting, \"`7Finally!!!`6\"`n`n");
					output("`4`bYou are dead.`n");
					output("`4All gold on hand has been lost!`n");
					output("`410% of experience has been lost!`b`n");
					output("You may begin fighting again tomorrow.");

					$session['user']['experience'] = round($session['user']['experience'] * 0.9, 0);
					$session['user']['alive'] = FALSE;
					$session['user']['gold'] = 0;
					$session['user']['hitpoints'] = 0;
					$session['user']['specialinc'] = '';
					increment_module_pref('encounters',1,'villain');

					addnews("`#%s `3was killed by a villain with an evil plan.", $session['user']['name']);
					addnav("Daily news", "news.php");
				break;
			}
		}
		elseif( $op == 'button' )
		{
			// You push the button.
			output("`^Deciding not to attack him, you start circling around Balufac and slowly advance to his machine, ready to set this to an end.");
			output("Aiming for a heavy blow, Balufac loses his balance and this is the perfect moment for you to push the button.`n`n");
			output("\"`7Noooooooooooooo....`6\", you hear Balufac screaming and almost instantly after you release the button a deep grumbling sound can be heard.");
			output("You head for the exit of this cave and with a last jump make it out of it closely followed by a cloud of dust.");
			output("Where once was the entrance to Balufac's cave, is now only debris and dirt. You wonder if he to was also able to escape.`n`n");
			output("`&It will surely take some time for you to forget this terrible event, but you survived and that's what matters.`n`n");

			$bonus = round($session['user']['experience'] * 0.02);
			output("`#You gain %s experience.`0`n`n", $bonus);
			$session['user']['experience'] += $bonus;
			$session['user']['specialinc'] = '';
			increment_module_pref('encounters',1,'villain');

			addnews("`#%s `3stopped the plans of a villain by pushing a `\$BIG RED BUTTON`3.", $session['user']['name']);
		}
		elseif( $op == 'fight' )
		{
			$go = httpget('go');
			if( $go == 'gethim')
			{
				$badguy = array(
					"creaturename" => translate_inline('`6Balufac The Great'),
					"creatureweapon" => translate_inline('`4Ray Gun of `$DOOM`4!!!'),
					"creaturelevel" => $session['user']['level']+2,
					"creatureattack" => $session['user']['attack']+2,
					"creaturedefense" => $session['user']['defense']+2,
					"creaturehealth" => round($session['user']['maxhitpoints']*1.5, 0), 
					"diddamage"=>0
				);
				$session['user']['badguy'] = createstring($badguy);

				if( $session['user']['gold'] > 10000 )
				{
					// The gold slows you down.
					apply_buff('balufacraygun',array(
						"name"=>"`4Ray Gun of `\$DOOM",
						"roundmsg"=>"`6Balufac `3fires his `4Ray Gun of `\$DOOM `3at you!",
						"effectmsg"=>"`3Your gold slows you down and you're hit by one of the gun's rays for `#{damage} damage`3!",
						"effectnodmgmsg"=>"`3You dodge the gun's ray! `#YIKES`3, that would have hurt!",
						"rounds"=>20,
						"minioncount"=>1,
						"wearoff"=>"`3The `4Ray Gun's `3battery life hits low.",
						"maxgoodguydamage"=>$session['user']['level'],
						"expireafterfight"=>1,
						"schema"=>"module-villain")
					);
				}
				else
				{
					apply_buff('balufacraygun',array(
						"name"=>"`4Ray Gun of `\$DOOM",
						"roundmsg"=>"`6Balufac `3fires his `4Ray Gun of `\$DOOM `3at you!",
						"effectmsg"=>"`3You deflect the gun's ray back at Balufac for `#{damage} damage `3using your " . $session['user']['armor'] . "`3!",
						"effectnodmgmsg"=>"`3You deflect the gun's ray back at Balufac, but you missed him by THAT MUCH!",
						"rounds"=>20,
						"minioncount"=>1,
						"wearoff"=>"`3The `4Ray Gun's `3battery life hits low.",
						"maxbadguydamage"=>$session['user']['level'],
						"expireafterfight"=>1,
						"schema"=>"module-villain")
					);
				}
			}
			$battle = TRUE;
		}
		elseif( $op == 'won' )
		{
			output("`6You yell, `^\"I AM VICTORIOUS\" `6 as you stagger back slightly and watch as Balufac falls to the ground dead. From one of the cave tunnels you can hear his minions fast approaching so you quickly look around for treasure.`n`n");
			output("You search Balufac's body and besides a book on get rich quick schemes you find `ba nearly empty bottle of healing potion`b which you manage to get a few drops from.`n`n");
			output("You can't see any gold in this part of the cave, but you do see the `4Ray Gun of `\$DOOM`6. It's useless now that the battery, whatever that is, is flat, but at its heart is a large `%crystal `6which you smash into smaller pieces and stuff into your pockets.`n`n");
			output("As you run towards the exit you hit the `\$BIG RED BUTTON `6for good measure. A rumbling sound starts and large pieces of the cave roof start to fall down around you.");
			output("With a last jump you make it out of the cave, closely followed by a cloud of dust. Where once was the entrance to Balufac's cave, is now only debris and dirt.`n`n");
			output("It will surely take some time for you to forget this terrible event, but you survived and that's what matters.`n`n");

			$session['user']['hitpoints'] += 10;
			$bonus = round($session['user']['experience'] * 0.05); // 5% of current experience
			$session['user']['experience'] += $bonus;
			$session['user']['gems'] += get_module_setting('gemswon');
			$session['user']['specialinc'] = '';
			increment_module_pref('encounters',1,'villain');
			output("`#You gained %s experience from defeating `^Balufac `#and `%%s %s`#.`0`n`n", $bonus, get_module_setting('gemswon'), translate_inline(get_module_setting('gemswon')==1?'gem':'gems'));
		}
		elseif( $op == 'lost' )
		{
			output("`^\"YOU HAVE BEEN DEFEATED\" `6laughs Balufac, and with no energy left in you to struggle, you find yourself being tied back to the table.");
			output("Balufac goes back to his strange machine and using some levers and buttons you hear a hissing noise right above you.`n`n");
			output("Out of the dark a strange, crystalline object descends. Flashes of light are dancing on its surface.");
			output("Unable to speak or move anymore you watch in terror what is happening right now, right above you.");
			output("The flashes stop dancing and start gathering at the tip of the object.");
			output("The last thing you can remember is the voice of Balufac, manically laughing and shouting, `^\"FINALLY!!!\"`n`n");
			output("`4You are dead.`n");
			output("All gold on hand has been lost!`n");
			output("10% of experience has been lost!`n");
			output("You may begin fighting again tomorrow.`n`n");

			$session['user']['experience'] = round($session['user']['experience'] * 0.9, 0);
			$session['user']['alive'] = FALSE;
			$session['user']['gold'] = 0;
			$session['user']['hitpoints'] = 0;
			$session['user']['specialinc'] = '';
			increment_module_pref('encounters',1,'villain');

			addnav('Daily News','news.php');
		}
	}
	else
	{
		// What you see when you have had enough encounters.
		output("`2You come across a cave entrance that has been blocked by a cave-in. It looks all too familiar. Not wanting to waste time, you quickly move on.`n`n");
		$session['user']['specialinc'] = '';
	}

	if( $battle )
	{
		include('battle.php');
		if( $victory )
		{
			addnav('Continue',$from.'op=won');
			addnews("`#%s `3defeated `^Balufac The Great `3and for good measure pressed the `\$BIG RED BUTTON`3.", $session['user']['name']);
		}
		elseif( $defeat || $session['user']['hitpoints'] < 10 )
		{
			addnav('Continue',$from.'op=lost');
			addnews("`#%s `3was defeated and killed by a villain with an evil plan.", $session['user']['name']);
		}
		else
		{
			require_once('lib/fightnav.php');
			fightnav(TRUE, FALSE, $from);
		}
	}
}
?>