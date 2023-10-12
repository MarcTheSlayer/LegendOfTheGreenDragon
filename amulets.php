<?php
/**
	Modified by MarcTheSlayer

	29/04/09 - v0.0.1
	+ Rewrote most of the code, removed duplicate code, fixed a few bugs.
	+ Rewrote some text and added text.
	+ Translation ready.

	01/05/09 v0.0.2
	+ Problem with `% in the buffs.
*/
function amulets_getmoduleinfo()
{
	$info = array(
		"name"=>"Amulets",
		"version"=>"0.0.2",
		"author"=>"`#Lonny Luberts`2, rewritten by `@MarcTheSlayer",
		"category"=>"PQcomp",
		"download"=>"http://dragonprime.net/index.php?topic=10068.0",
        "settings"=>array(
        	"Amulets Behavior Settings,title",
        	"godname"=>"Name of the God who gives Amulets (created this so we could honor a moderator),string|Zeus",
        	"findperc"=>"Chance of a player being awarded an amulet (percentage):,int|33",
        	"takeperc"=>"Chance that an amulet will be taken from one player and given to another (percentage):,int|33",
        	"lostperc"=>"Chance that an owner might lose an amulet (percentage):,int|33",
        	"dragloose"=>"Does player keep amulet after Dragon Kill?,bool|1",
        ),
		"prefs"=>array(
			"Amulets User Prefs,title",
            "amulet"=>"Has which Amulet:,enum".amulet_details(0)."|0",
        ),
	);
	return $info;
}

function amulets_install()
{
	output("`c`b`Q%s 'amulets' Module.`0`b`c`n", translate_inline(is_module_active('amulets')?'Updating':'Installing'));
	module_addhook('charstats');
	module_addhook('newday');
	module_addhook('battle-victory');
	module_addhook('shades');
	module_addhook('dragonkill');
	module_addhook('footer-hof');
	module_addeventhook('forest', 'return 100;');
	module_addeventhook('travel', 'return 33;');
	return TRUE;
}

function amulets_uninstall()
{
	output("`4Un-Installing 'amulets' Module.`0`n");
	return TRUE;
}

function amulets_dohook($hookname,$args)
{
	$amulet_id = get_module_pref('amulet');
	if( !empty($amulet_id) )
	{
		$amulet_details = amulet_details($amulet_id);

		switch( $hookname )
		{
			case 'charstats':
				addcharstat('Equipment Info');
				addcharstat('Amulet',$amulet_details['name']);
			break;

			case 'newday':
				amulet_rewards($amulet_details);
			break;

			case 'battle-victory':
				global $session;
				if( $amulet_id == 15 )
				{	// Shamrock
					$before = $badguy['creaturegold'];
					$extra = round($before * .25);
					$badguy['creaturegold'] += $extra;
					debug("Shamrock Amulet Boosts Badguy's Gold: Before:$before After:{$badguy['creaturegold']} Extra:$extra (Remember, you get a percent of badguy's gold, not all.)");
				}
				if( $amulet_id == 1 && $session['user']['hitpoints'] < $session['user']['maxhitpoints'] )
				{	// Ankh
					output("`@Your %s `@Amulet glows with a healing power.`0`n", $amulet_details['name']);
					$session['user']['hitpoints'] = $session['user']['maxhitpoints'];
				}
			break;

			case 'shades':
				if( $amulet_id == 18 )
				{	// Triquetra
					addnav('Amulet');
					addnav(array('`^%s Amulet`0',$amulet_details['name']),'runmodule.php?module=amulets&op=shades');
				}
			break;

			case 'dragonkill':
				if( get_module_setting('dragloose') == 0 )
				{
					clear_module_pref('amulet');
				}
			break;
		}
	}

	if( $hookname == 'footer-hof' )
	{
		addnav('Amulets');
		addnav('Amulet Holders','runmodule.php?module=amulets');
	}
	return $args;
}

function amulets_runevent($type)
{
	global $session;

	$session['user']['specialinc'] = 'module:amulets';

	$amulet_id = get_module_pref('amulet');
	if( empty($amulet_id) )
	{
		if( e_rand(1,100) <= get_module_setting('findperc') )
		{
			$amulet_id = e_rand(1,20);
			$amulet_details = amulet_details($amulet_id);

			output("`2The land grows dim, you look above you and the sky fills with dark rolling clouds.`n");
			output("As you stand there the clouds suddenly part and a flash of light comes from the sky toward you!`n");
			output("You hear the voice of %s`2. `@\"I am the God of Amulets, do you think you are worthy mortal?\"`n`n", get_module_setting('godname'));

			output("`2The `@%s `2Amulet appears before you just as you finish replying that you are.`n`n", $amulet_details['name']);

			if( isset($amulet_details['sex']) && $amulet_details['sex'] != $session['user']['sex'] )
			{
				$male = translate_inline('masculine');
				$female = translate_inline('feminine');
				output("`2You try to grab it, but it fades as quickly as it appeared. %s `2laughs at you, `@\"The %s `@Amulet is for the %s gender.\"`n", get_module_setting('godname'), $amulet_details['name'], ($session['user']['sex']==1?$male:$female));
			}
			else
			{
				$sql = "SELECT a.acctid, a.name
					FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
					WHERE b.modulename = 'amulets'
						AND b.setting = 'amulet'
						AND a.acctid = b.userid
						AND b.value = '$amulet_id'
					LIMIT 1";
				$result = db_query($sql);
				$row = db_fetch_assoc($result);

				if( !isset($row['name']) && empty($row['name']) )
				{
					output("`2You grab it quickly and tie it around your neck.`n");
					addnews("`#%s `&has recieved the `#%s `&Amulet!`n", $session['user']['name'], $amulet_details['name']);
					set_module_pref('amulet',$amulet_id);
					amulet_rewards($amulet_details);
				}
				else
				{
					if( e_rand(1,100) <= get_module_setting('takeperc') )
					{
						output("`@\"You are more worthy than %s`@. You may have theirs!\" `2says %s`2.`n`n", $row['name'], get_module_setting('godname'));
						output("`2You take it and tie it around your neck.`n");
						addnews("`&The `#%s `&Amulet has been taken from %s `&and given to %s`&!`n", $amulet_details['name'], $row['name'], $session['user']['name']);
						clear_module_pref('amulet','amulets',$row['acctid']);
						set_module_pref('amulet',$amulet_id);
						amulet_rewards($amulet_details);

						require_once('lib/systemmail.php');
						$subject = translate_mail('`@Your Amulet has been taken!`0');
						$message = translate_mail(array('`@%s `2has found you lacking and has taken back the %s `2Amulet and given it to `@%s`2.', get_module_setting('godname'), $amulet_details['name'], $session['user']['name']));
						systemmail($row['acctid'], $subject, $message);
					}
					else
					{
						output("`2But it disappears as quickly as it appeared. `@\"I am sorry, but %s `@already has the %s `@Amulet and at this time I feel that you are not as worthy.\" `2%s says to you.`n", $row['name'], $amulet_details['name'], get_module_setting('godname'));
						addnews("`&%s has been found unworthy of the `#%s `&Amulet.", $session['user']['name'], $amulet_details['name']);
					}
				}
			}
		}
		else
		{
			output('`n`2You see an oddly shaped something half buried in the ground. Half hoping that it\'s an Amulet you pick it up, but it turns out to just be a weird looking stone.`0`n`n');
		}
	}
	else
	{
		$amulet_details = amulet_details($amulet_id);
		if( e_rand(1,100) <= get_module_setting('lostperc') )
		{
			output('`n`2Your `@%s `2Amulet falls to the ground and bounces off into the bushes. You spend ages looking, but eventually give up.', $amulet_details['name']);
			if( $type == 'forest' )
			{
				output('You go looking for something to kill, trying not to think about your loss.`0`n');
			}
			elseif( $type == 'travel' )
			{
				output('You decide to head back to %s, trying not to think about your loss.`0`n', $session['user']['location']);
				addnav('Return');
				villagenav();
			}
			if( $session['user']['turns'] > 0 )
			{
				$session['user']['turns']--;
			}
			if( $session['user']['spirits'] > -2 )
			{
				$session['user']['spirits']--;
			}
			clear_module_pref('amulet');
			addnews('`&%s `&lost the `#%s `&Amulet. Keep a look out for it.', $session['user']['name'], $amulet_details['name']);
		}
		else
		{
			output('`n`2Your `@%s `2Amulet falls to the ground and comes to rest at your feet. You quickly pick it up and tie it back around you neck trying not to think about what you would do without it.`0`n', $amulet_details['name']);
		}
	}
	$session['user']['specialinc'] = '';
}

function amulets_run()
{
	global $session;

	page_header('Amulets');

	$op = httpget('op');

	if( $op == 'shades' )
	{
		$amulet_details = amulet_details('18');

		output("`n`2You grab hold of the `@%s `2Amulet around your neck. The warmth of it increases, as does the warmth of your body! You are once again alive, but only just.`0`n", $amulet_details['name']);
		$session['user']['alive'] = 1;
		$session['user']['hitpoints'] = 1;
	}
	else
	{
		rawoutput('<big>');
		output('`c`b`^Amulet Holders`0`b`c`n');
		rawoutput('</big>');

		$amulet_details = amulet_details();

		$sql = "SELECT a.acctid, a.name, b.value
			FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
			WHERE b.modulename = 'amulets'
				AND b.setting = 'amulet'
				AND a.acctid = b.userid
				AND b.value > 0";
		$result = db_query($sql);
		$holders = array();
		while( $row = db_fetch_assoc($result) )
		{
			$holders[$row['value']]['id'] = $row['acctid'];
			$holders[$row['value']]['name'] = $row['name'];
		}

		$name = translate_inline('Amulets');
		$holder = translate_inline('Holders');
		rawoutput('<table border="0" cellpadding="2" cellspacing="1" align="center" bgcolor="#999999">');
		rawoutput("<tr class=\"trhead\"><td align=\"center\">$name</td><td align=\"center\">$holder</td></tr>");

		for( $i=1; $i<21; $i++)
		{
			if( $holders[$i]['id'] == $session['user']['acctid'] )
			{
				rawoutput('<tr class="trhilight"><td align="center">');
			}
			else
			{
				rawoutput('<tr class="'.($i%2?'trdark':'trlight').'"><td align="center">');
			}

			output_notl('`@%s`0', $amulet_details[$i]);
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
			if( httppost('amulet') )
			{
				$amulet_id = httppost('amulet');
				set_module_pref('amulet',$amulet_id);
				$amulet_detail = amulet_details($amulet_id);
				amulet_rewards($amulet_detail);
			}
			else
			{
				$amulet_id = get_module_pref('amulet');
			}

			rawoutput('<form action="runmodule.php?module=amulets" method="POST">');
			addnav('','runmodule.php?module=amulets');
			$select = '<select name="amulet">';
			foreach( $amulet_details as $key => $value )
			{
				$selected = ( $amulet_id == $key ) ? ' selected="selected"' : '';
				$select .= '<option value="'.$key.'"'.$selected.'>'.color_sanitize($value).'</option>';
			}
			rawoutput("$select".'</select>&nbsp;<input type="submit" value="Submit" /></form>');
		}
	}

	addnav('Return');
	villagenav();

	page_footer();
}

function amulet_details($id = FALSE)
{
	$amulet_names = translate_inline(array('None','Ankh','Anubis','Apollo','Artemis','Bastet','Cross','Dionysos','Flying Dragon','Heart','Hermes','Horace','Pegasus','Phoenix','Salamander','Shamrock','Star of Solomon','Thor','Triquetra','Unicorn','Yinyang'));
	$amulet_sex = array(4=>1,5=>1,17=>0);

	$amulet = array();
	if( $id === FALSE )
	{
		return $amulet_names;
	}
	elseif( $id == 0 )
	{
		$amulet = '';
		foreach( $amulet_names as $key => $value )
		{
			$amulet .= ','.$key.','.$value;
		}
	}
	elseif( array_key_exists($id, $amulet_names) )
	{
		$amulet['id'] = $id;
		$amulet['name'] = $amulet_names[$id];
		if( array_key_exists($id, $amulet_sex) )
		{
			$amulet['sex'] = $amulet_sex[$id];
		}
	}

	return $amulet;
}

function amulet_rewards($amulet_details)
{
	global $session;

	output("`n`2You rub your `@%s `2Amulet between your fingers.`n", $amulet_details['name']);

	switch( $amulet_details['id'] )
	{
		case 0: // none
		break;

		case 1: // Ankh - A tau cross with a loop at the top, used as a symbol of generation or enduring life.
				// Battle-victory hook.
		break;

		case 2: // Anubis - God of tombs and weigher of the hearts of the dead.
			output('You feel its incredible power!`0`n');
			$session['user']['deathpower'] += 10;
		break;

		case 3: // Apollo - Greek and Roman god of light, healing, music, poetry, prophecy, and manly beauty; brother of Artemis.
			output('You feel its incredible power!`0`n');
			if( is_module_active('secondweapon') )
			{
				set_module_pref('weaponskill',get_module_pref('weaponskill','secondweapon') + 5,'secondweapon');
			}
			else
			{
				$session['user']['hitpoints'] = $session['user']['hitpoints'] * 2;
			}
		break;

		case 4: // Artemis - Greek goddess, sister of Apollo.
			output('You feel its incredible power!`0`n');
			apply_buff('amulet',array(
	           		 "name"=>array('`QPower of %s`0',$amulet_details['name']),
					 "startmsg"=>"`n`^You feel the power of the Artemis within you!`0",
	          		 "rounds"=>200,
	           		 "atkmod"=>1.2,
	           		 "scheme"=>"module-amulets"
				));
		break;

		case 5: // Bastet - Egyptian goddess.
			output('You feel its incredible power!`0`n');
			$session['user']['charm'] += 1;
		break;

		case 6: // Cross - Symbol of Christianity.
			output('You feel its incredible power!`0`n');
			if( is_module_active('alignment') )
			{
				increment_module_pref('alignment',2,'alignment');
			}
			else
			{
				$session['user']['hitpoints'] = $session['user']['hitpoints'] * 2;
			}
		break;

		case 7: // Dionysos - God of fertility, wine, and drama.
			output("You *hic* feel its *hic* incred*hic*ible power!");
			if( is_module_active('drinks') )
			{
				set_module_pref('drunkeness',66,'drinks');
			}
			else
			{
				$session['user']['hitpoints'] = $session['user']['hitpoints'] * 2;
			}
		break;

		case 8: // Dragon - A Dragon. :)
			output('You feel its incredible power!`0`n');
			apply_buff('amulet',array(
	           		 "name"=>array('`@%s`0',$amulet_details['name']),
					 "startmsg"=>"`n`^You feel the power of the Flying Dragon in you!`0",
	          		 "rounds"=>200,
	           		 "atkmod"=>1.2,
	           		 "scheme"=>"module-amulets"
				));
		break;

		case 9: // Heart - A heart.
			output('You feel its incredible power and become more charming!`0`n');
			$session['user']['charm'] += 1;
		break;

		case 10: // Hermes - Greek herald and messenger of the gods.
			output('You feel its incredible power!`0`n');
			if( is_module_active('trading') )
			{
				set_module_pref('dailytrades', get_module_pref('dailytrades','trading') + 10,'trading');
			}
			else
			{
				$session['user']['hitpoints'] = $session['user']['hitpoints'] * 2;
			}
		break;

		case 11: // Horace - Roman poet.
		break;

		case 12: // Pegasus - Winged horse.
			output('You feel its incredible power and gain `@3 Turns`2!`0`n');
			$session['user']['turns'] += 3;
		break;

		case 13: // Phoenix - Bird of fire and immortality.
			output('You feel its incredible power!`0`n');
			apply_buff('amulet',array(
	           		 "name"=>array('`^%s Amulet`0',$amulet_details['name']),
					 "startmsg"=>"`n`^Your Phoenix Amulet Heals you!`0",
	          		 "rounds"=>300,
	          		 "minioncount"=>1,
	          		 "regen"=>"(<level> * 2)",
	          		 "effectmsg"=>"`!You heal for ".($session['user']['level'] * 2)." hitpoints!",
	           		 "scheme"=>"module-amulets"
				));
		break;

		case 14: // Salamander - Tailed amphibian.
			output('You feel its incredible power!`0`n');
			apply_buff('amulet',array(
	           		 "name"=>array('`2Power of the %s`0',$amulet_details['name']),
					 "startmsg"=>"`n`^The Power of the Salamander is with you!`0",
	          		 "rounds"=>200,
		 			 "badguyatkmod"=>0.8,
	           		 "scheme"=>"module-amulets"
				));
		break;

		case 15: // Shamrock - 3 leaf clover (lucky).
				// Battle-victory hook.
		break;

		case 16: // Star - Big, bright ball of buring fire.
			output('You feel its incredible power and suddenly notice `^500 gold `2on the ground!`0`n');
			$session['user']['gold'] += 500;
		break;

		case 17: // Thor - God of thunder.
			output('You feel its incredible power!`0`n');
			apply_buff('amulet',array(
	           		 "name"=>array("`q%s's Hammer`0",$amulet_details['name']),
					 "startmsg"=>"`n`^You feel the power of Thor's Hammer in your Weapon!",
	          		 "rounds"=>200,
	           		 "atkmod"=>1.3,
	           		 "scheme"=>"module-amulets"
				));
		break;

		case 18: // Triquetra - Geometrical figure having three points, often used in ancient art to symbolize a triune deity.
				// Shades hook.
		break;

		case 19: // Unicorn - Horse, with a single horn in the center of its forehead.
			output('You feel its incredible power!`0`n');
			apply_buff('amulet',array(
	           		 "name"=>array('`&%s Amulet`0',$amulet_details['name']),
					 "startmsg"=>"`n`^Your Unicorn Amulet Heals you!",
	          		 "rounds"=>200,
	          		 "minioncount"=>1,
	          		 "regen"=>"<level>",
	          		 "effectmsg"=>"`!You heal for ".$session['user']['level']." hitpoints!",
	           		 "scheme"=>"module-amulets"
				));
		break;

		case 20: // Yinyang - Black/white, good/bad, opposing forces.
			output('You feel its incredible power!`0`n');
			if( $session['user']['attack'] < $session['user']['defense'] )
			{
				$session['user']['attack'] = $session['user']['defense'];
			}
			if( $session['user']['defense'] < $session['user']['attack'] )
			{
				$session['user']['defense'] = $session['user']['attack'];
			}
		break;
	}

	output_notl('`n');
}
?>