<?php
/**
	Modified by MarcTheSlayer.
	== 30/08/2008 - v1.2a ==
	Fixed some setting values that were missing and modified the code.
	If you have the Flu and come across this event, there's now a chance
	you'll be cured or you'll just get slightly better.

	== 17/09/2008 - v1.2b ==
	Added 'dayssafe' - Once cured you're safe from getting the flu again	for this amount of days.

	== 13/10/08 - v1.2c ==
	Fixed safedays bug where pref 'dayssafe' was being set by pref 'dayssafe' and not setting's 'dayssafe'.

	== 25/08/10 - v1.2d
	Added pvp win/loss hooks to spread the flu further plus HOF to show who in the Realm has the flu. :)
*/
function getflu_getmoduleinfo()
{
	$info = array(
		"name"=>"Get Flu",
		"description"=>"Catch the flu in the forest which then can get passed on to clan members. Negative buff that lasts several newdays.",
		"version"=>"1.2d",
		"author"=>"MR Zone, modified by `@MarcTheSlayer.",
		"category"=>"Forest Specials",
		"download"=>"http://dragonprime.net/index.php?topic=9245.0",
		"settings"=>array(
			"Get Flu Event Settings,title",
				"fluchance"=>"Chance to get the Flu,range,20,100,5|20",
				"fluclanchance"=>"Chance to get the Flu from another clansmen,range,0,100,10|20",
				"dayssafe"=>"Safe from catching Flu again for this many newdays,int|5",
				"buffname"=>"Name of Flu Buff,text|`@Flu Sickness",
				"elast"=>"Number of Days Flu lasts,range,1,6,1|3",
				"defeffect"=>"Amount of Defence Flu will deduct,floatrange,0.8,0.99,0.01|0.95",
				"atkeffect"=>"Amount of Attack Flu will deduct,floatrange,0.8,0.99,0.01|0.98",
				"trainapply"=>"Does Flu affect users when fighting Masters?,bool|1",
				"pvpapply"=>"Does Flu affect users when fighting PVP?,bool|1"
		),
		"prefs"=>array(
			"Flu preferences,title",
				"hasflu"=>"Does this user have the Flu?,bool|0",
				"fludays"=>"Days the Flu has left to go.,int|0",
				"dayssafe"=>"Days left this player is immune.,int|0"
		)
	);
	return $info;
}

function getflu_install()
{
	output("`c`b`Q%s 'getflu' Module.`b`n`c", translate_inline(is_module_active('getflu')?'Updating':'Installing'));
	module_addeventhook('forest', 'return 80;');
	module_addeventhook('travel', 'return 25;');
	module_addhook('newday');
	module_addhook('pvpwin');
	module_addhook('pvploss');
	module_addhook('footer-hof');
	return TRUE;
}

function getflu_uninstall()
{
	output("`n`c`b`Q'getflu' Module Uninstalled`0`b`c");
	return TRUE;
}

function getflu_dohook($hookname,$args)
{
	global $session;

	switch($hookname)
	{
		case 'newday':
			$hasflu = get_module_pref('hasflu');
			$fludays = get_module_pref('fludays');
			$dayssafe = get_module_pref('dayssafe');
			$clanid = $session['user']['clanid'];

			if( $hasflu && $fludays > 0 )
			{
				set_module_pref('fludays',$fludays-1);
				apply_buff('flu',array(
					"name"=>get_module_setting('buffname'),
					"rounds"=>-1,
					"defmod"=>get_module_setting('defeffect'),
					"atkmod"=>get_module_setting('atkeffect'),
					"allowinpvp"=>get_module_setting('pvpapply'),
					"allowintrain"=>get_module_setting('trainapply'),
					"schema"=>"module-getflu"
				));
				output("`n`n`^Cough... cough... (you still have the `\$FLU`^.)`0`n");
			}
			elseif( $hasflu && $fludays == 0 )
			{
				clear_module_pref('hasflu','getflu');
				clear_module_pref('fludays','getflu');
				set_module_pref('dayssafe',get_module_setting('dayssafe'));
				output("`n`n`^Today, You feel much better. You have beaten the `\$Flu `^and are 100% again.`0`n");
			}
			elseif( !$hasflu && $clanid > 0 && !$dayssafe )
			{
				$clanid = $session['user']['clanid'];
				$getflu = e_rand(0,100);
				if( $getflu <= get_module_setting('fluclanchance') )
				{
					$sql = "SELECT name
						FROM " . db_prefix('accounts') . "
						INNER JOIN " . db_prefix('module_userprefs') . "
						ON acctid = userid
						WHERE modulename = 'getflu'
						AND setting = 'hasflu'
						AND value = 1 
						AND clanid = $clanid
						AND acctid <> $clanid
						LIMIT 1";
					$result = db_query($sql);

					if( $row = db_fetch_assoc($result) )
					{
						set_module_pref('hasflu',1);
						set_module_pref('fludays',get_module_setting('elast'));
						apply_buff('flu',array(
							"name"=>get_module_setting('buffname'),
							"rounds"=>-1,
							"defmod"=>get_module_setting('defeffect'),
							"atkmod"=>get_module_setting('atkeffect'),
							"allowinpvp"=>get_module_setting('pvpapply'),
							"allowintrain"=>get_module_setting('trainapply'),
							"schema"=>"module-getflu"
						));
						output("`n`n`^You feel sick, you have caught the `\$FLU `^from your Clanmember %s`^.`0`n", $row['name']);
					}
				}
			}
			if( $dayssafe )
			{
				increment_module_pref('dayssafe','-1');
			}
		break;

		case 'pvpwin':
		case 'pvploss':
			if( get_module_pref('hasflu') == 1 && get_module_pref('hasflu','getflu',$args['badguy']['acctid']) == 0 )
			{
				set_module_pref('hasflu',1,'getflu',$args['badguy']['acctid']);
				set_module_pref('fludays',get_module_setting('elast'),'getflu',$args['badguy']['acctid']);
				require_once('lib/systemmail.php');
				$subject = translate_mail("`qThe `\$Flu`0");
				$message = translate_mail(array("`qYou have caught the `\$Flu from `Q%s!`0", $session['user']['name']));
				systemmail($args['badguy']['acctid'],$subject,$message);
				output('`n`qYou\'ve successfully attacked `Q%s `qwith the `$Flu`q!`0`n', $args['badguy']['creaturename']);
				debuglog("caught the flu from {$session['user']['name']}`0.", $args['badguy']['acctid']);
			}
			elseif( get_module_pref('hasflu','getflu',$args['badguy']['acctid']) == 1 && get_module_pref('hasflu') == 0 )
			{
				set_module_pref('hasflu',1);
				set_module_pref('fludays',get_module_setting('elast'));
				require_once('lib/systemmail.php');
				$subject = translate_mail("`qThe `\$Flu`0");
				$message = translate_mail(array("`qYou gave `Q%s `qthe `\$Flu`Q!`0", $args['badguy']['creaturename']));
				systemmail($args['badguy']['acctid'],$subject,$message);
				output('`n`Q%s `qhas also attacked you with the `$Flu`q!`0`n', $args['badguy']['creaturename']);
				debuglog("caught the flu from {$args['badguy']['creaturename']}`0.");
			}
		break;

		case 'footer-hof':
			addnav('Illnesses');
			addnav('The `$Flu`0','runmodule.php?module=getflu&op=hof');
		break;
	}
	
	return $args;
}

function getflu_runevent($type,$link)
{
	global $session;

	if( get_module_pref('hasflu') )
	{
		//
		// User already has the flu.
		//
		if( e_rand(1,3) == 3 )
		{
			// Heal them completely.
			clear_module_pref('hasflu','getflu');
			clear_module_pref('fludays','getflu');
			set_module_pref('dayssafe',get_module_setting('dayssafe'));
			strip_buff('flu');
			output("`^You're about to sneeze when you come across a box of soft moist tissues. Pulling out a couple, you give your nose a big long blow and almost immediately your `\$FLU `^vanishes.`n`n`#That was some blow!");
		}
		else
		{
			$fludays = get_module_pref('fludays');

			if( has_buff('flu') )
			{
				// Don't heal them, but make them a little bit better up to a point.
				$better = FALSE;
				if( $session['bufflist']['flu']['atkmod'] < 0.99 )
				{
					$better = TRUE;
					$session['bufflist']['flu']['atkmod'] += 0.01;
				}
				if( $session['bufflist']['flu']['defmod'] < 0.99 )
				{
					$better = TRUE;
					$session['bufflist']['flu']['defmod'] += 0.01;
				}
				if( $better == TRUE )
				{
					output("`^You come across a box of soft moist tissues and they help alleviate your `\$Flu `^a little.");
					// Take away a day.
					if( $fludays > 0 )
					{
						set_module_pref('fludays',$fludays-1);
					}
				}
				else
				{
					output("`^You come across a box of soft moist tissues, but they do nothing to alleviate your `\$Flu`^.");
				}

			}
			else
			{
				// If user gets here, but doesn't have the buff, give it back to them. :)
				apply_buff('flu',array(
					"name"=>get_module_setting('buffname'),
					"rounds"=>-1,
					"defmod"=>get_module_setting('defeffect'),
					"atkmod"=>get_module_setting('atkeffect'),
					"allowinpvp"=>get_module_setting('pvpapply'),
					"allowintrain"=>get_module_setting('trainapply'),
					"schema"=>"module-getflu"
				));
				output("`^You sneeze all of a sudden and wonder if you're starting to get the `\$Flu`^... Yep, you have the `\$Flu `^again.");
				// Punish them by adding one more day.
				increment_module_pref('fludays',1);
			}
		}
	}
	else
	{
		//
		// User doesn't have the flu.
		//
		$gflu = e_rand(0,100);
		if( $gflu <= get_module_setting('fluchance') && !get_module_pref('dayssafe') )
		{
			// Give it to them.
			set_module_pref('hasflu',1);
			set_module_pref('fludays',get_module_setting('elast'));
			apply_buff('flu',array(
				"name"=>get_module_setting('buffname'),
				"rounds"=>-1,
				"defmod"=>get_module_setting('defeffect'),
				"atkmod"=>get_module_setting('atkeffect'),
				"allowinpvp"=>get_module_setting('pvpapply'),
				"allowintrain"=>get_module_setting('trainapply'),
				"schema"=>"module-getflu"
			));
			output("`^Wandering around, you start sneezing, wheezing, then coughing. Ohh No!!!`nYou've caught the `\$FLU`^.");
		}
		else
		{
			output("`^You start to feel a little ill, but it soon passes. You hope it wasn't `\$Flu `^related.");
		}
	}
}

function getflu_run()
{
	page_header('The Flu');

	$op = httpget('op');
	if( $op == 'hof' )
	{
		$sql = "SELECT a.name
			FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
			WHERE b.modulename = 'getflu'
				AND b.setting = 'hasflu'
				AND a.acctid = b.userid
				AND b.value = 1
			ORDER BY a.name";
		$result = db_query($sql);

		output('`n`7The following people currently have the `$Flu`0.`n`n');

		$carriers = translate_inline('Flu Carriers');
		rawoutput('<table border="0" cellpadding="2" cellspacing="1" align="center" bgcolor="#999999">');
		rawoutput("<tr class=\"trhead\"><td align=\"center\">$carriers</td></tr>");
		if( db_num_rows($result) > 0 )
		{
			$i = 0;
			while( $row = db_fetch_assoc($result) )
			{
				rawoutput('<tr class="'.($i%2?'trdark':'trlight').'"><td align="center">');
				output_notl('`@%s`0', $row['name']);
				rawoutput('</td></tr>');
				$i++;
			}
		}
		else
		{
			$healthy = translate_inline('Everyone is healthy!');
			rawoutput('<tr class="trlight"><td align="center">'.$healthy.'</td></tr>');
		}

		rawoutput('</table>');

		addnav('Back');
		addnav('Back to HOF','hof.php');
	}
	else
	{
		addnav('The Forest','forest.php');
	}

	page_footer();
}
?>