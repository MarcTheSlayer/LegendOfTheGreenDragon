<?php
/*
/ Magic Stones V0.3.0
/ Originally by Excalibur (www.ogsi.it)
/ English cleanup by Talisman (dragonprime.cawsquad.net)
/ Contribution LonnyL (www.pqcomp.com)
/ Original concept from Aris (www.ogsi.it)
/ July 2004
/
/ Aris Stones V1.0
/ Converted to LoGD v1.x 04/13/07
/ By Dragon89
/
/ v1.1 added missing db_prefixes
/ v1.2 added synctable support as suggested by XChrisX
*/
/**
	Modified/Rewritten by MarcTheSlayer

	01/06/09 - v1.3.0
	+ Copied the layout I used in my Amulets rewrite.
	+ Changeable forest/travel settings.
	+ User pref to disable amulet on stats.
	+ Coded in HoF.
	+ Translation ready.

	21/03/10 - v1.3.1
	+ Fixed a bug where a player wouldn't have a stone taken from them.

	30/10/2011 - v1.3.2
	+ Fixed a missing argument bug. Reported by Morbius
*/
function aris_stones_getmoduleinfo()
{
	$info = array(
		"name"=>"Aris' Stones",
		"description"=>"Stones that give the owner something special each newday.",
		"version"=>"1.3.2",
		"author"=>"Excalibur converted by Dragon89`2, modified by `@MarcTheSlayer",
		"category"=>"Forest Specials",
		"settings"=>array(
			"Stone Settings,title",
				"forestodds"=>"Base chance of forest event:,range,0,100,5|75",
				"travelodds"=>"Base chance of travel event:,range,0,100,5|35",
				"takeperc"=>"Chance that a stone will be taken from one player and given to another (percentage):,range,0,100,5|35",
				"dklose"=>"Does player keep stone after Dragon Kill?,bool",
		),
		"prefs"=>array(
			"Stones - User Prefs,title",
				"stone"=>"Has which Stone:,enum".aris_stone_details(0)."|0",
			"Aris' Stones,title",
				"user_stone"=>"Show your stone under equipment info?,bool|1",
		),
	);

	return $info;
}

function aris_stones_install()
{
	if( is_module_active('aris_stones') )
	{
		output("`c`b`QUpdating 'aris_stones' Module.`0`b`c`n");
	}
	else
	{
		output("`c`b`QInstalling 'aris_stones' Module.`0`b`c`n");

		if( is_module_active('stones') )
		{
			output("`3'`bstones`b' module is installed, converting database over, then uninstalling.`n");
			$sql = "SELECT s.stone, a.acctid
					FROM " . db_prefix('stones') . " s, " . db_prefix('accounts') . " a
					WHERE s.owner = a.acctid";
			$result = db_query($sql);
			while( $row = db_fetch_assoc($result) )
			{
				set_module_pref('stone',$row['stone'],'aris_stones',$row['acctid']);	
			}
			deactivate_module('stones');
			uninstall_module('stones');
		}

		if( is_module_active('stones_hof') )
		{
			output("`3'`bstones_hof`b' module is installed, uninstalling.`n");
			deactivate_module('stones_hof');
			uninstall_module('stones_hof');		
		}
	}

	module_addhook('charstats');
	module_addhook('newday');
	module_addhook('dragonkill');
	module_addhook('footer-hof');
	module_addeventhook('forest', "return get_module_setting('forestodds','aris_stones');");
	module_addeventhook('travel', "return get_module_setting('travelodds','aris_stones');");
	return TRUE;
}

function aris_stones_uninstall()
{
	output("`c`b`QUn-Installing 'aris_stones' Module.`0`b`c`n");
	return TRUE;
}

function aris_stones_dohook($hookname, $args)
{
	$stone_id = get_module_pref('stone');
	if( !empty($stone_id) )
	{
		$stone_details = aris_stone_details($stone_id);

		global $session;

		switch( $hookname )
		{
			case 'charstats':
				if( get_module_pref('user_stone') == 1 )
				{
					addcharstat('Equipment Info');
					addcharstat('Stone',$stone_details['name']);
				}
			break;

			case 'newday':
				aris_stones_rewards($stone_details);
			break;

			case 'dragonkill':
				if( get_module_setting('dklose') == 0 )
				{
					clear_module_pref('stone');
				}
			break;
		}
	}

	if( $hookname == 'footer-hof' )
	{
		addnav('Aris\' Stones');
	 	addnav('Stone Owners','runmodule.php?module=aris_stones');
	}

	return $args;
}

function aris_stones_runevent($type,$from)
{
	global $session;

	$session['user']['specialinc'] = 'module:aris_stones';

	output("<font size='+1'>`c`b`!Aris' Spring`b`c`n</font>",true);

	$stone_id = get_module_pref('stone');
	if( empty($stone_id) )
	{
		$op = httpget('op');

		switch( $op )
		{
			case 'press':
				output("`@Your hand pauses over the button as you sense the strength of the mystical power coming from `&Aris' Spring `@and its hidden treasures. ");
				output("You begin to wonder if the legends were true, or if you're about to make a deadly mistake.`n`nAs you feel a flux of energy coming from the stone, you close your eyes and push the button firmly. ");
				output("As the button gives way, you hear mechanical noises coming from inside the bedrock. When you open your eyes, you see a pool of water has been revealed. A golden glitter in the water makes you wonder if the Earth Goddess will grant you a favour .... `n`n");

				$stone_id = e_rand(1,20);
				$stone_details = aris_stone_details($stone_id);

				$sql = "SELECT a.acctid, a.name
						FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
						WHERE b.modulename = 'aris_stones'
							AND b.setting = 'stone'
							AND a.acctid = b.userid
							AND b.value = '$stone_id'
						LIMIT 1";
				$result = db_query($sql);
				if( db_num_rows($result) == 0 )
				{
					// The stone is available
					output("`#... you hear something rolling inside the bedrock, then a marvelous stone magically appears in the pool!!`n`nIt has some runes engraved on it, ");
					if( $stone_id == 1 )
					{
						output("and you discover to you horror that it's the %s`#!!!`n`n", $stone_details['name']);
						output("Owning this stone will cost you each day. Your only hope is that some other unlucky warrior will unwittingly stumble onto `&Aris' Spring `#and claim the stone from you. ");
					}
					else
					{
						output("and you discover with great joy that it's the %s`#!!`n`n", $stone_details['name']);
						output("As owner of this stone, you gain a special bonus each newday.`n`nToday has been your lucky day, %s`#!!!`n", $session['user']['name']);
					}
					set_module_pref('stone', $stone_id);
					addnews("`#%s `7came across `&Aris' Spring `7and has received the `#%s`&!`n", $session['user']['name'], $stone_details['name']);
				}
				else
				{
					$row = db_fetch_assoc($result);
					output("`#You hear a whistle sound which grows in intensity, until it becomes a lament, stopping as suddenly as it started. A deep, calm voice speaks: `n`n\"");

					if( rand(1,100) <= get_module_setting('takeperc') )
					{
						output("`^The stone selected for you is possessed by `@%s`^. As they have fallen from my favour, I have chosen to retrieve and place it in your deserving care.`#\". `n`nYou see a beautiful stone materialize in the pool, and you grab it.", $row['name']);
						if( $stone_id > 1 )
						{
							output("You admire the %s`#, knowing you'll have a special power each day.`n", $stone_details['name']);
						}
						else
						{
							output("You discover with horror that it is the %s`#!!! Ownership of this stone will cost you each day. Your only hope is that some other unlucky warrior will unwittingly stumble onto `&Aris' Spring`# and claim the stone from you.", $stone_details['name']);
						}
						set_module_pref('stone', $stone_id);
						clear_module_pref('stone','aris_stones',$row['acctid']);

						addnews("`&The `#%s `&has been taken from %s `&and given to %s`&!`n", $stone_details['name'], $row['name'], $session['user']['name']);

						require_once('lib/systemmail.php');
						$subject = translate_mail('`@Your Stone has been taken!`0');
						$message = translate_mail(array('`@%s `2found `&Aris\' Spring `2and the Earth Goddess has decided to give them your %s `2stone`@!!!', $session['user']['name'], $stone_details['name']));
						systemmail($row['acctid'], $subject, $message);
					}
					else
					{
						$lucky = translate_inline(array('Luckily','Unluckily'));
						output("`% %s my dear %s`%, the %s `%is in the possession of `@%s`%. ", ($stone_id==1?$lucky[0]:$lucky[1]), $session['user']['name'], $stone_details['name'], $row['name']);
						output("It is not in my nature to take it from them so you'll have to be satisfied with `^`b5`b`% more forest fights which I will grant you instead.`#\" `n`n");
						output("You feel a flow of energy run through your body, and discover the voice's promise was kept!!! `n");
						$session['user']['turns'] += 5;
					}
				}
				$session['user']['specialinc'] = '';
			break;

			case 'leave':
				$session['user']['specialinc'] = '';
				$loss = round($session['user']['hitpoints']/2);
				if( $loss <= 0 ) $loss = 1;
				$session['user']['hitpoints'] -= $loss;
				output("`6Terrified by the power of the spring, you decide not to tempt fate.`n`nYou turn back to the forest and its relative safety, but as you do so you hear a bubbling sound coming from the pool of water.`n`n`^A jet of water hits the back of your head like a mallet and knocks you to the ground!`n`n`\$`bYou lose %s hit points from the fall!!!!`b", $loss);
			break;

			default:
			case '':
				output("`@Wandering through the forest looking for adventure, you find a natural spring from which emanates a mysterious glow. You've stumbled across the mythical `&Aris' Spring'`@, named after the wandering sage said to have discovered it.");
				output("`n`nAlthough little is known of the spring, Aris did discover some knowledge of it, which is recorded in the annals of legend.`n`nHe found that the stones found in the spring emanate a powerful force, able to augment the energy of its owners, giving them an additional powers each day.`n`n");
				output("The stones are limited in quantity, and each stone can be possessed by only one warrior at a time. You could become the holder of one of these magic stones, with a bit of luck.`n`n");
				output("You noticed a rough button inserted in the bedrock near the spring, with some runic symbols engraved around it.`n`n");
				output("You do not understand the symbols - are they an invitation? Or...perhaps...a warning?");

				addnav('`@Push the Button',$from.'op=press');
				addnav('`$Leave the Spring',$from.'op=leave');
			break;
		}
	}
	else
	{
		$stone_details = aris_stone_details($stone_id);

		output("`@Wandering through the forest looking for adventure, you find a natural spring from which emanates a mysterious glow. You've stumbled across the mythical `&Aris' Spring'`@, named after the wandering sage said to have discovered it.");
		output("You are no stranger to the spring, and are well aware of its power, as you already possess the %s`@.`n`n", $stone_details['name']);
		output("While you're here, you drink some of the clear water and feel refreshed.`n`n");

		if( $session['user']['turns'] >= 1 )
		{
			output("`%You lose 1 turn for the time spent here,");
			$session['user']['turns'] -= 1;
		}
		else
		{
			output("`%You waste a little time,");
		}

		if( $session['user']['hitpoints'] < $session['user']['maxhitpoints'] )
		{
			output(" but are fully healed.");
			$session['user']['hitpoints'] = $session['user']['maxhitpoints'];
		}
		else
		{
			output(" but gain a little more health.");
			$session['user']['hitpoints'] += $session['user']['level'] * 2;
		}

		addnews("`#%s `7came across `&Aris' Spring `7but already owns a stone.`n", $session['user']['name'], $stone_details['name']);

		$session['user']['specialinc'] = '';
	}
}

function aris_stones_run()
{
	global $session;

	if( httppost('stone') !== FALSE )
	{
		$stone_id = httppost('stone');
		set_module_pref('stone',$stone_id);
		$stone_detail = aris_stone_details($stone_id);
		aris_stones_rewards($stone_detail);
	}
	else
	{
		$stone_id = get_module_pref('stone');
	}

	page_header("Aris' Stones");

	rawoutput('<big>');
	output('`c`b`^Stone Holders`0`b`c`n');
	rawoutput('</big>');

	output("`@You want to know who the owners of `&Aris' Stones`@ are and if any of them are still available? Here we go, my young warrior.`n`n");

	$stone_details = aris_stone_details();

	$sql = "SELECT a.acctid, a.name, b.value
		FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
		WHERE b.modulename = 'aris_stones'
			AND b.setting = 'stone'
			AND a.acctid = b.userid
			AND b.value+0 > 0
		ORDER BY b.value+0 ASC";
	$result = db_query($sql);
	$holders = array();
	while( $row = db_fetch_assoc($result) )
	{
		debug("Stone #:".$row['value']." User ID:".$row['acctid']." User name:".$row['name']);
		$holders[$row['value']]['id'] = $row['acctid'];
		$holders[$row['value']]['name'] = $row['name'];
	}

	$name = translate_inline('Stones');
	$holder = translate_inline('Holders');
	rawoutput('<table border="0" cellpadding="2" cellspacing="1" align="center" bgcolor="#999999">');
	rawoutput("<tr class=\"trhead\"><td align=\"center\">$name</td><td align=\"center\">$holder</td></tr>");

	for( $i=1; $i<21; $i++)
	{
		if( isset($holders[$i]) && $holders[$i]['id'] == $session['user']['acctid'] )
		{
			rawoutput('<tr class="trhilight"><td align="center">');
		}
		else
		{
			rawoutput('<tr class="'.($i%2?'trdark':'trlight').'"><td align="center">');
		}

		output_notl('`@%s`0', $stone_details[$i]);
		rawoutput('</td><td align="center">');
		if( isset($holders[$i]) )
		{
			output_notl('`@%s`0', $holders[$i]['name']);
		}
		else
		{
			output_notl('`@-`0');
		}
		rawoutput('</td></tr>');

	}

	rawoutput('</table>');

	addnav('Back');
	addnav('Back to HOF','hof.php');

	if( $session['user']['superuser'] & SU_DEVELOPER )
	{
		output('`n`nDevelopers, this dropdown menu is for testing.`n');

		rawoutput('<form action="runmodule.php?module=aris_stones" method="POST">');
		addnav('','runmodule.php?module=aris_stones');
		$select = '<select name="stone">';
		foreach( $stone_details as $key => $value )
		{
			$selected = ( $stone_id == $key ) ? ' selected="selected"' : '';
			$select .= '<option value="'.$key.'"'.$selected.'>'.full_sanitize($value).'</option>';
		}
		rawoutput("$select".'</select>&nbsp;<input type="submit" value="Submit" /></form>');
	}

	addnav('Return');
	villagenav();

	page_footer();
}

function aris_stone_details($id = FALSE)
{
	$stone_names = translate_inline(array('None','`7Cursed Stone','`$L`4o`$v`4e `$S`4t`$o`4n`$e','`^Friendship Stone',"`^K`Qing's `^S`Qtone",'`#MightyE Stone',"`#P`3e`#g`3a`#s`3u`#s `3S`#t`3o`#n`3e","`@Aris' Stone",'`QE`^xcalibur `QS`^tone','`QLuke Stone','`$P`&o`$k`&e`$r `&S`$t`&o`$n`&e',"`%Queen's Stone","`LImperator's Stone",'`^Gold Stone','`%Power Stone','`$Ramius Stone','`2Cedrik Stone','`%Honour Stone','`&Purity Stone','`^L`6i`^g`6h`^t `6S`^t`6o`^n`6e','`&D`#i`&a`#m`&o`#n`&d `#S`&t`#o`&n`#e'));
	$available = translate_inline('Available');

	if( $id === FALSE )
	{
		return $stone_names;
	}
	elseif( $id == 0 )
	{
		require_once('lib/sanitize.php');
		$sql = "SELECT value
				FROM " . db_prefix('module_userprefs') . "
				WHERE modulename = 'aris_stones'
					AND setting = 'stone'
					AND value+0 > 0";
		$result = db_query($sql);
		$have = array(1=>'','','','','','','','','','','','','','','','','','','','');
		while( $row = db_fetch_assoc($result) )
		{
			if( isset($have[$row['value']]) )
			{
				unset($have[$row['value']]);
			}
		}
		$stone = '';
		foreach( $stone_names as $key => $value )
		{
			if( isset($have[$key]) )
			{
				$value .= " ($available)";
			}
			$stone .= ','.$key.','.full_sanitize($value);
		}
	}
	elseif( array_key_exists($id, $stone_names) )
	{
		$stone = array();
		$stone['id'] = $id;
		$stone['name'] = $stone_names[$id];
	}

	return $stone;
}

function aris_stones_rewards($stone_details)
{
	global $session;

	switch( $stone_details['id'] )
	{
		case 1:
			// Cursed Stone.
			output("`n`\$As you own the %s`$, You lose a bunch of stuff!`0`n", $stone_details['name']);
			$session['user']['turns']--;
			$session['user']['gems']--;
			$session['user']['charm'] -= 2;
			$lose = e_rand(300,1000);
			$session['user']['gold'] -= $lose;
			$session['user']['hitpoints'] -= round($session['user']['hitpoints']*0.9); // Take 10%.

			if( $session['user']['turns'] < 0 ) $session['user']['turns'] = 0;
			if( $session['user']['gems'] < 0 ) $session['user']['gems'] = 0;
			if( $session['user']['gold'] < 0 ) $session['user']['gold'] = 0;
			if( $session['user']['hitpoints'] <= 0 ) $session['user']['hitpoints'] = 1;
		break;

		case 2:
			// Love Stone.
			output("`n`\$As you own the %s`$, you gain a charm point!`0`n", $stone_details['name']);
			$session['user']['charm'] += 1;
		break;

		case 3:
			// Friendship Stone.
			output("`n`\$As you own the %s`$, you gain a forest fight!`0`n", $stone_details['name']);
			$session['user']['turns'] += 1;
		break;

		case 4:
			// King's Stone.
			output("`n`\$As you own the %s`$, you gain 500 gold!`0`n", $stone_details['name']);
			$session['user']['gold'] += 500;
		break;

		case 5:
			// MightyE Stone.
			output("`n`\$As you own the %s`$, you gain attack!`0`n", $stone_details['name']);
			apply_buff('stones5',array(
				"name"=>$stone_details['name'],
				"rounds"=>200,
				"wearoff"=>"`4The glow fades from your " . $stone_details['name'] . "`4.",
				"atkmod"=>1.5,
				"roundmsg"=>"`4Your " . $stone_details['name'] . " `4enhances your attacking ability!.",
				"activate"=>"offense",
				"schema"=>"module-stones"
			));
		break;

		case 6:
			// Pegasus Stone.
			output("`n`\$As you own the %s`$, you gain defence!`0`n", $stone_details['name']);
			apply_buff('stones6',array(
				"name"=>$stone_details['name'],
				"rounds"=>200,
				"wearoff"=>"`4The glow fades from your " . $stone_details['name'] . ".",
				"defmod"=>1.5,
				"roundmsg"=>"`4Your " . $stone_details['name'] . " `4glows intensely as it protects you!.",
				"activate"=>"offense",
				"schema"=>"module-stones"
			));
		break;

		case 7:
			// Aris Stone.
			output("`n`\$As you own the %s`$, you attack and defend better!`0`n", $stone_details['name']);
			apply_buff('stones7',array(
				"name"=>$stone_details['name'],
				"rounds"=>300,
				"wearoff"=>"`4The glow fades from your " . $stone_details['name'] . "`4.",
				"atkmod"=>1.3,
				"defmod"=>1.3,
				"roundmsg"=>"`4Your " . $stone_details['name'] . " `4glows intensely as it empowers you!!",
				"activate"=>"offense",
				"schema"=>"module-stones"
			));
		break;

		case 8:
			// Excalibur Stone.
			output("`n`\$As you own the %s`$, you gain attack!`0`n", $stone_details['name']);
			apply_buff('stones8',array(
				"name"=>$stone_details['name'],
				"rounds"=>500,
				"wearoff"=>"`4The glow fades from your " . $stone_details['name'] . "`4.",
				"atkmod"=>1.5,
				"roundmsg"=>"`4Your " . $stone_details['name'] . " `4enhances your attacking ability!",
				"activate"=>"offense",
				"schema"=>"module-stones"
			));
		break;

		case 9:
			// Luke Stone.
			output("`n`\$As you own the %s`$, you gain extra skill points in your specialty today!`0`n", $stone_details['name']);
			require_once('lib/increment_specialty.php');
			increment_specialty("`^");
		break;

		case 10:
			// Poker Stone.
			output("`n`\$The %s `\$is a stone of chance. Your fate is randomly decided and today is a ", $stone_details['name']);
			$chance = e_rand(1,100);
			if( $chance <= 50 )
			{
				// Good.
				output("good day. ");
				if( $chance <= 10 )
				{
					output("`@You gain 2 forest fights!`0`n");
					$session['user']['turns'] += 2;
				}
				elseif( $chance <= 20 )
				{
					output("`%You gain 2 Gems!`0`n");
					$session['user']['gems'] += 2;
				}
				elseif( $chance <= 30 )
				{
					output("`#You gain some charm!`0`n");
					$session['user']['charm'] += 5;
				}
				elseif( $chance <= 40 )
				{
					$gain = e_rand(1000,3200);
					output("`^You gain %s gold!`0`n", $gain);
					$session['user']['gold'] += $gain;
				}
				else
				{
					$gain = e_rand(800,2000);
					output("You gain `@1 turn`$, `%1 gem`$, `#charm `\$and `^%s gold`$!`0`n", $gain);
					$session['user']['turns']++;
					$session['user']['gems']++;
					$session['user']['charm']++;
					$session['user']['gold'] += $gain;
				}
			}
			else
			{
				// Bad.
				output("bad day. ");
				if( $chance >= 90 )
				{
					output("`2You lose 2 forest fights!`0`n");
					$session['user']['turns'] -= 2;
				}
				elseif( $chance >= 80 )
				{
					if( $session['user']['gems'] >= 2 )
					{
						output("`5You lose 2 Gems!`0`n");
						$session['user']['gems'] -= 2;
					}
					elseif( $session['user']['gems'] > 0 )
					{
						output("You would have lost 2 gems, however you only have 1 so you lose that.`0`n");
						$session['user']['gems'] = 0;
					}
					else
					{
						output("You would have lost 2 gems, however you don't have 2 to lose.`0`n");
					}
				}
				elseif( $chance >= 70 )
				{
					output("`3You lose some charm!`0`n");
					$session['user']['charm'] -= 5;
				}
				elseif( $chance >= 60 )
				{
					$lose = e_rand(1000,3200);
					if( $session['user']['gold'] >= $lose )
					{
						output("`6You lose %s gold!`0`n", $lose);
						$session['user']['gold'] -= $lose;
					}
					elseif( $session['user']['gold'] > 0 )
					{
						output("You would have lost %s gold, however you only have %s so you lose that.`0`n", $lose, $session['user']['gold']);
						$session['user']['gold'] = 0;
					}
					else
					{
						output("You would have lost %s gold, however you don't any to lose.`0`n", $lose);
					}
				}
				else
				{
					output("You lose a bunch of stuff!`0`n");
					$session['user']['turns']--;
					$session['user']['gems']--;
					$session['user']['charm'] -= 2;
					$lose = e_rand(300,1000);
					$session['user']['gold'] -= $lose;

					if( $session['user']['turns'] < 0 ) $session['user']['turns'] = 0;
					if( $session['user']['gems'] < 0 ) $session['user']['gems'] = 0;
					if( $session['user']['gold'] < 0 ) $session['user']['gold'] = 0;
				}
			}
		break;

		case 11:
			// Queen's Stone.
			$gain = e_rand(300,1000);
			output("`n`\$As you own the %s`$, you gain `^%s gold`$!`0`n", $stone_details['name'], $gain);
			$session['user']['gold'] += $gain;
		break;

		case 12:
			// Imperator Stone.
			$gain = e_rand(800,2000);
			output("`n`\$As you own the %s`$, you gain `^%s gold`$!`0`n", $stone_details['name'], $gain);
			$session['user']['gold'] += $gain;
		break;

		case 13:
			// Gold Stone.
			$gain = e_rand(1000,3200);
			output("`n`\$As you own the %s`$, you gain `^%s gold`$!`0`n", $stone_details['name'], $gain);
			$session['user']['gold'] += $gain;
		break;

		case 14:
			// Power Stone.
			output("`n`\$As you own the %s`$, your attack and defence are increased!`0`n", $stone_details['name']);
			apply_buff('stones15',array(
				"name"=>$stone_details['name'],
				"rounds"=>200,
				"wearoff"=>"`4The glow fades from your " . $stone_details['name'] . "`4.",
				"atkmod"=>1.5,
				"defmod"=>1.5,
				"roundmsg"=>"`4Your " . $stone_details['name'] . " `4glows intensely as it empowers you!!",
				"activate"=>"offense",
				"schema"=>"module-stones"
			));
		break;

		case 15:
			// Ramius Stone.
			output("`n`\$As you own the %s`$, you gain favor with Ramius!`0`n", $stone_details['name']);
			$session['user']['deathpower'] += 200;
		break;

		case 16:
			// Cedrik Stone.
			if( is_module_active('drinks') )
			{
				$drunk = e_rand(20,65);
				set_module_pref('drunkeness',$drunk,'drinks');
			}
			output("`n`\$As you own the %s`$, you become drunk!`0`n", $stone_details['name']);
			apply_buff('stones16',array(
				"name"=>$stone_details['name'],
				"rounds"=>150,
				"wearoff"=>"`4The glow fades from your " . $stone_details['name'] . "`4.",
				"atkmod"=>1.5,
				"roundmsg"=>"`4Your " . $stone_details['name'] . " `4causes you to become drunk and adds to your attack!!",
				"activate"=>"offense",
				"schema"=>"module-stones"
			));
		break;

		case 17:
			// Honour Stone.
			output("`n`\$As you own the %s`$, you `@gain 3 extra forest fights`$!`0`n", $stone_details['name']);
			$session['user']['turns'] += 3;
		break;

		case 18:
			// Purity Stone.
			output("`n`\$As you own the %s`$, you `@gain 2 extra forest fight`$!`0`n", $stone_details['name']);
			$session['user']['turns'] += 2;
		break;

		case 19:
			// Light Stone.
			output("`n`\$As you own the %s`$, you `@gain 1 extra forest fight`$!`0`n", $stone_details['name']);
			$session['user']['turns'] += 1;
		break;

		case 20:
			// Diamond Stone.
			output("`n`\$As you own the %s`$, you `%gain a gem`$!`0`n", $stone_details['name']);
			$session['user']['gems'] += 1;
		break;
	}
}
?>