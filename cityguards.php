<?php
function cityguards_getmoduleinfo()
{
	$info = array(
		"name"=>"City Guards",
		"description"=>"City hall leader can hire guards to protect players that sleep in the fields and have a bodyguard for themselves.",
		"version"=>"0.0.1",
		"author"=>"`@MarcTheSlayer",
		"category"=>"Cities",
		"download"=>"http://dragonprime.net/index.php?topic=10912.0",
		"requires"=>array(
			"cityfunds"=>"20080123|By Sixf00t4, available on DragonPrime",
		),
		"settings"=>array(
			"City Guards Settings,title",
				"goldcost"=>"Gold cost for low quality guards:,int|100000",
				"`^Medium quality = (low x 3); High quality = (low x 5),note",
				"gemscost"=>"Gems cost for low quality guards:,int|20",
				"`^Medium quality = (low x 2); High quality = (low x 3),note",
				"days"=>"How many system gamedays do the guards protect the fields?,int|10",
				"bodyguard"=>"Are Leaders allowed bodyguard protection?,bool",
				"bglevel"=>"What level is the Leader's bodyguard?,enum,1,1 - Lowest,2,2,3,3 - Medium,4,4,5,5 - Best|1",
				"xploss"=>"Percentage of exp lost on defeat:,range,1,50,1|15",
				"xpgain"=>"Percentage of exp gained on victory:,range,1,50,1|10",
				"goldgain"=>"Percentage of gold gained on victory:,range,1,100,1|10",
				"`^Based on player's onhand gold.,note",
			"Guard Details,title",
				"guardatk"=>"Multiplier of player's attack for guard attack:,floatrange,.5,5,.1|1",
				"guarddef"=>"Multiplier of player's defence for guard defence:,floatrange,.5,5,.1|1",
				"guardhp"=>"Multiplier of player's maxHP for guard hp:,floatrange,.5,5,.1|1",
				"`^Add how much extra on for medium/high quality guards?,note",
				"medium"=>"Medium - Add this to all multipliers:,floatrange,.5,5,.1|.5",
				"high"=>"High - Add this to all multipliers:,floatrange,.5,5,.1|1",
			"Other Module Settings,title",
				"`@When a guard has been killed. (Ignore if modules not installed),note",
				"`^Alignment module.,note",
				"alignment"=>"Decrease alignment by this much:,int|5",
				"`^Jail Module.,note",
				"jail"=>"Chance of being caught - 1 in X:,range,1,25,1|5",
				"`^Dag Module - Enter 0 for no bounty.,note",
				"bounty"=>"Offer bounty (level times X):,int|500",
		),
		"prefs-city"=>array(
			"City Guards Preferences,title",
				"guards"=>"Does this city currently have guards?,enum,0,0 - None,1,1 - Low Quality,2,2 - Medium Quality,3,3 - High Quality",
				"daysleft"=>"How many days left until the guards leave?,int",
				"allprefscity"=>"Serialised guard PVP win/loss data:,viewonly",
		),
	);
	return $info;
}

function cityguards_install()
{
	if( is_module_active('cityguards') )
	{
		output("`c`b`QUpdating 'cityguards' Module.`b`n`c");
	}
	else
	{
		output("`c`b`QInstalling 'cityguards' Module.`b`n`c");

		$sql = "SELECT cityid
				FROM " . db_prefix('cityprefs');
		$result = db_query($sql);
		$prefscity = array('time'=>date('Y-m-d H:i:s'),'pvpwin'=>0,'pvploss'=>0,'guardwin'=>0,'guardloss'=>0);
		for( $i=1; $i<=db_num_rows($result); $i++ )
		{
			set_module_objpref('city', $i, 'allprefscity', serialize($prefscity));
		}
	}

	module_addhook('newday-runonce');
	module_addhook('pvpmodifytargets');
	module_addhook('pvpadjust');
	module_addhook('pvpwin');
	module_addhook('pvploss');
	module_addhook('cityleaders-leader');
	return TRUE;
}

function cityguards_uninstall()
{
	output("`n`c`b`Q'cityguards' Module Uninstalled`0`b`c");
	return TRUE;
}

function cityguards_dohook($hookname,$args)
{
	global $session;

	switch( $hookname )
	{
		case 'newday-runonce':
			require_once('lib/systemmail.php');
			// Get the data for all city guards.
			$sql = "SELECT objid, setting, value
					FROM " . db_prefix('module_objprefs') . "
					WHERE modulename = 'cityguards'
						AND objtype = 'city'";
			$result = db_query($sql);
			$guards_array = array();
			while( $row = db_fetch_assoc($result) )
			{
				$guards_array[$row['objid']][$row['setting']] = $row['value'];
			}
			$sql = "SELECT cityid, cityname
					FROM " . db_prefix('cityprefs');
			$result = db_query($sql);
			while( $row = db_fetch_assoc($result) )
			{
				// Go through all the cities.
				if( isset($guards_array[$row['cityid']]['guards']) && $guards_array[$row['cityid']]['guards'] > 0 )
				{
					// Yes this city has hired guards.
					if( isset($guards_array[$row['cityid']]['daysleft']) && $guards_array[$row['cityid']]['daysleft'] > 0 )
					{
						// Still days left on the guards contract.
						$guards_array[$row['cityid']]['daysleft']--;
						set_module_objpref('city', $row['cityid'], 'daysleft', $guards_array[$row['cityid']]['daysleft']);
						if( $guards_array[$row['cityid']]['daysleft'] == 0 )
						{
							// No more days left.
							set_module_objpref('city', $row['cityid'], 'guards', 0);
							$leader = get_module_objpref('city', $row['cityid'], 'leader', 'cityleaders');
							$subject = translate_mail('`4The Guard Contract has Expired!');
							$mail_msg = translate_mail(array('`3The guards that were hired to protect %s have left due to the contract expiring.`n`n`#-The Guard Agency', $row['cityname']));
							systemmail($leader,$subject,$mail_msg);
							addnews('`&%s `7is no longer protected by guards.`0', $row['cityname']);
							set_module_objpref('city', $row['cityid'], 'allprefscity', serialize(array('time'=>date('Y-m-d H:i:s'),'pvpwin'=>0,'pvploss'=>0,'guardwin'=>0,'guardloss'=>0)));
						}
					}
				}
			}
			unset($guards_array);
		break;

		case 'pvpmodifytargets':
			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);		
			if( get_module_objpref('city', $cityid, 'guards') > 0 )
			{ 
				output('`&You notice some guards patrolling nearby and wonder if it would be wise to attack any sleeping warriors here.`0`n`n');
			}
		break;

		case 'pvpadjust':
			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);		
			if( get_module_objpref('city', $cityid, 'guards') > 0 )
			{ 
				redirect('runmodule.php?module=cityguards&op=pvp&name='.httpget('name'));
			}
			elseif( get_module_setting('bodyguard') == 1 )
			{
				if( $args['acctid'] == get_module_objpref('city', $cityid, 'leader', 'cityleaders') )
				{
					require_once('lib/battle-skills.php');
					apply_bodyguard(get_module_setting('bglevel'));
				}
			}
		break;

		case 'pvpwin':
		case 'pvploss':
			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
			$prefscity = unserialize(get_module_objpref('city', $cityid, 'allprefscity'));
			$prefscity[$hookname]++;
			set_module_objpref('city', $cityid, 'allprefscity', serialize($prefscity));
		break;

		case 'cityleaders-leader':
			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);		
			$prefscity = unserialize(get_module_objpref('city', $cityid, 'allprefscity'));
			$guards = get_module_objpref('city', $cityid, 'guards');
			if( $guards > 0 )
			{ 
				$days_left = get_module_objpref('city', $cityid, 'daysleft') - 1;
				output('`n`&There\'s a note in your inbox from the Guard Agency reminding you that the level %s guard contract runs out in `@%s %s`&.`n`n', $guards, $days_left, translate_inline($days_left==1?'day':'days'));
				$total = $prefscity['guardwin'] + $prefscity['guardloss'];
				if( $total > 0 )
				{
					output('You look at the stats and see that since you\'ve had the guards, they\'ve stopped %s %s.', $total, translate_inline($total==1?'attack':'attacks'));
					if( $prefscity['guardloss'] > 0 )
					{
						output(' Sadly %s %s died protecting %s.', $prefscity['guardloss'], translate_inline($prefscity['guardloss']==1?'guard':'guards'), $session['user']['location']);
					}
					else
					{
						output(' The guards defeated %s %s.', $prefscity['guardwin'], translate_inline($prefscity['guardwin']==1?'attacker':'attackers'));
					}
				}
			}
			else
			{
				$total = $prefscity['pvpwin'] + $prefscity['pvploss'];
				$days = round((strtotime('now') - strtotime($prefscity['time'])) / 86400,0);
				output('`n`&On the notice board you can see that there were %s %s in the last %s %s in %s.', $total, translate_inline($total==1?'attack':'attacks'), $days, translate_inline($days==1?'day':'days'), $session['user']['location']);
				if( $prefscity['pvpwin'] > 0 ) output(' %s of the attacks were successful.`n`nYou think about hiring some guards.', $prefscity['pvpwin']);
			}

            addnav('Options'); 
            addnav('Hire City Guards','runmodule.php?module=cityguards');
		break;
	}

	return $args;
}

function cityguards_run()
{
	global $session;

	$op = httpget('op');
	switch( $op )
	{
		case 'pvp':
			page_header('Guard Fight');

			$acctid = httpget('name');
			$sql = "SELECT name
					FROM " . db_prefix('accounts') . "
					WHERE acctid = '$acctid'";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);

			output('`n`&As you\'re about to attack `7%s`&, a large %s guard spots you and rushes over. You have no choice but to fight him.`0', $row['name'], $session['user']['location']);
			addnav('Fight!','runmodule.php?module=cityguards&op=guardfight');
		break;

		case 'guardfight':
			page_header('Guard Fight');

			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
			$guards = get_module_objpref('city', $cityid, 'guards', 'cityguards');
			$bonus = ( $guards == 3 ) ? get_module_setting('high') : (( $guards == 2 ) ? get_module_setting('medium') : 0 );

			$guard = translate_inline('Guard');
			$weapon = translate_inline('`&Sword of Protection`0');
			$guardatk = get_module_setting('guardatk') + $bonus;
			$guarddef = get_module_setting('guarddef') + $bonus;
			$guardhp = get_module_setting('guardhp') + $bonus;

			$badguy = array(
				'creaturename'		=>	'`&' . $session['user']['location'] . ' ' . $guard . '`0',
				'creatureweapon'	=>	$weapon,
				'creaturelevel'		=>	15,
				'creatureattack'	=>	round($session['user']['attack']*$guardatk),
				'creaturedefense'	=>	round($session['user']['defense']*$guarddef),
				'creaturehealth'	=>	round($session['user']['maxhitpoints']*$guardhp),
				'creatureexp'		=>	round(($session['user']['experience']/100)*get_module_setting('xpgain')),
				'createdby'			=>	'module-cityguards',
				'diddamage'			=>	0,
			);
			$session['user']['badguy'] = createstring($badguy);
			$session['user']['playerfights']--;
			$op = 'fight';
		break;

		case 'hide':
			page_header('Run and Hide');

			output('`n`&You take off as fast as you can with the guards in hot pursuit. They\'re clumsy at running and start lagging behind before disappearing from view after a short while. You\'ve lost them!`n`n');
			output('Taking a quick rest against a large oak tree, you laugh to yourself at the useless guards when all of a sudden a hand clamps down on your shoulder from behind and you hear the voice of the Sheriff %s`&, `@"GOTCHA!"`n`n', get_module_setting('sheriffname','jail'));
			output('`&Something hard hits the top of your head and darkness decends all around you.');

			addnav('Wakeup','runmodule.php?module=jail');
		break;

		case 'hire':
			page_header('Guard Agency');

			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);		
			$goldfund = get_module_objpref('city', $cityid, 'gold', 'cityfunds');
			$gemsfund = get_module_objpref('city', $cityid, 'gems', 'cityfunds');
			$goldcost = get_module_setting('goldcost');
			$gemscost = get_module_setting('gemscost');

			$sop = httpget('sop');
			$goldcost = ( $sop == 3 ) ? round($goldcost*5) : (( $sop == 2 ) ? round($goldcost*3) : $goldcost);
			$gemscost = ( $sop == 3 ) ? round($gemscost*3) : (( $sop == 2 ) ? round($gemscost*2) : $gemscost);

			if( $goldfund >= $goldcost && $gemsfund >= $gemscost )
			{
				set_module_objpref('city', $cityid, 'gold', ($goldfund-$goldcost), 'cityfunds');
				set_module_objpref('city', $cityid, 'gems', ($gemsfund-$gemscost), 'cityfunds');
				set_module_objpref('city', $cityid, 'guards', $sop);
				set_module_objpref('city', $cityid, 'daysleft', get_module_setting('days')+1);
				output('`n`&You hand over the `^%s gold `&and `% %s %s `&which you withdrew from the city funds. The contract is signed and you\'re told that guards will start patrolling immediately.`n`nYou return to your chambers.`0', $goldcost, $gemscost, translate_inline($gemscost==1?'gem':'gems'));
				addnews('`&`% `7is now protected by guards.`0', $session['user']['location']);
				// Reset time.
				$prefscity = unserialize(get_module_objpref('city', $cityid, 'allprefscity'));
				$prefscity['time'] = date('Y-m-d H:i:s');
				set_module_objpref('city', $row['cityid'], 'allprefscity', serialize($prefscity));
				debuglog("`3bought level `&$sop `3guards for {$session['user']['location']}. Cost `^$goldcost gold `3and `%$gemscost gems`3.`0");
			}
			else
			{
				output('`n`&You currently don\'t have the funds to hire guards, maybe you should think about raising the tax?`0');
			}

			addnav('Return');
			addnav('Leader Chambers','runmodule.php?module=cityleaders&op=chambers');
		break;

		case '':
			page_header('Guard Agency');

			output('`n`&You walk over to the Guard Agency and discuss hiring guards to protect the people in %s`&. You\'re told that there are 3 levels with the level 3 guards being the best and also the most expensive. All guard contracts last for `7%s %s`&.`n`n', $session['user']['location'], get_module_setting('days'), translate_inline(get_module_setting('days')==1?'day':'days'));

			$goldcost = get_module_setting('goldcost');
			$gemscost = get_module_setting('gemscost');
			output('`QLevel 1 guards cost `^%s gold `Qand `% %s %s`Q.`n', $goldcost, $gemscost, translate_inline($gemscost==1?'gem':'gems'));
			output('`qLevel 2 guards cost `^%s gold `qand `% %s %s`q.`n', round($goldcost*3), round($gemscost*2), translate_inline(round($gemscost*2)==1?'gem':'gems'));
			output('`QLevel 3 guards cost `^%s gold `Qand `% %s %s`Q.`n', round($goldcost*5), round($gemscost*3), translate_inline(round($gemscost*3)==1?'gem':'gems'));

			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
			$guards = get_module_objpref('city', $cityid, 'guards', 'cityguards');		
			if( $guards > 0 )
			{
				$days_left = get_module_objpref('city', $cityid, 'daysleft') - 1;
				output('`n`&You\'re also told that your current level %s guards still have `7%s %s `&left on the contract and you will have to wait until it ends to renew it.`n`nYou return to your chambers.`0', $guards, $days_left, translate_inline($days_left==1?'day':'days'));
			}
			else
			{
				addnav('Hire Guards');
				addnav('Level 1 Guards','runmodule.php?module=cityguards&op=hire&sop=1');
				addnav('Level 2 Guards','runmodule.php?module=cityguards&op=hire&sop=2');
				addnav('Level 3 Guards','runmodule.php?module=cityguards&op=hire&sop=3');
			}
			addnav('Return');
			addnav('Leader Chambers','runmodule.php?module=cityleaders&op=chambers');
		break;
	}

	$battle = ( $op == 'fight' ) ? TRUE : FALSE;

	if( $battle )
	{
		page_header('Guard Fight');

		require_once('battle.php');
		if( $victory )
		{
			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
			$guards = get_module_objpref('city', $cityid, 'guards', 'cityguards');		
			$prefscity = unserialize(get_module_objpref('city', $cityid, 'allprefscity'));
			$prefscity['guardloss']++;
			set_module_objpref('city', $cityid, 'allprefscity', serialize($prefscity));

			// For alignment module.
			if( is_module_active('alignment') )
			{
				set_module_pref('alignment',get_module_pref('alignment','alignment') - get_module_setting('alignment'),'alignment');
			}

			// For jail module.
			$jail = FALSE;
			$link = 'village.php';
			if( is_module_active('jail') )
			{
				$rand = mt_rand(1,get_module_setting('jail'));
				if( $rand == 1 )
				{
					$jail = TRUE;
					$link = 'runmodule.php?module=cityguards&op=hide';
					set_module_pref('injail',1,'jail');
				}
				else
				{
					increment_module_pref('wantedlevel', 1, 'jail');
				}
			}

			// For dag module.
			if( is_module_active('dag') && $jail == FALSE && get_module_setting('bounty') > 0 )
			{
				$bounty = get_module_setting('bounty') * $session['user']['level'];
				$goldfund = get_module_objpref('city', $cityid, 'gold', 'cityfunds');
				if( $bounty <= $goldfund )
				{
					$leader = get_module_objpref('city',$cityid,'leader','cityleaders');
					db_query("INSERT INTO " . db_prefix('bounty') . " (amount, target, setter, setdate) VALUES ($bounty,{$session['user']['acctid']},$leader,'".date("Y-m-d H:i:s",time())."')");
					set_module_objpref('city', $cityid, 'gold', ($goldfund-$bounty), 'cityfunds');
				}
			}

			$boost = ( $guards == 3 ) ? get_module_setting('guardhp') + get_module_setting('high') : (( $guards == 2 ) ? get_module_setting('guardhp') + get_module_setting('medium') : 1);

			$goldgain = round((($session['user']['gold']/100)*get_module_setting('goldgain')*$boost));
			if( $goldgain > 0 )
			{
				$session['user']['gold'] += $goldgain;
				output('`#You receive `^%s `#gold!`n', $goldgain);
				debuglog("received gold for slaying a {$session['user']['location']} city guard.",FALSE,FALSE,'cityguards',$goldgain);
			}

			output("`n`&The %s Guard is slain! But your fight has awoken some nearby warriors and more guards are on their way. You decide to make a swift exit and hide.`0`n", $session['user']['location']);
			addnews("`&%s `7killed a %s Guard!`0", $session['user']['name'], $session['user']['location']);
			addnav('Swift Exit');
			addnav('Run and hide!',$link);
		}
		elseif( $defeat )
		{
			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
			$prefscity = unserialize(get_module_objpref('city', $cityid, 'allprefscity'));
			$prefscity['guardwin']++;
			set_module_objpref('city', $cityid, 'allprefscity', serialize($prefscity));

			output("`n`&The %s Guard delivers the final blow and defeats you. The streets are once again safe from warriors like you.`0`n", $session['user']['location']);
			$exploss = round(($session['user']['experience']/100)*get_module_setting('xploss'));
			$session['user']['experience'] -= $exploss;
			$session['user']['gold'] = 0;
			$session['user']['alive'] = FALSE;
			$session['user']['hitpoints'] = 0;
			debuglog("`3lost `&$exploss experience `3and `^all gold `3from being slain by {$session['user']['location']} city guard.`0");

			addnews("`&%s `7tried to attack a sleeping warrior, but was slain by a %s Guard!`0", $session['user']['name'], $session['user']['location']);

			addnav("You're Dead!");
			addnav('Continue','news.php');
		}
		else
		{
			require_once('lib/fightnav.php');
			fightnav(FALSE,FALSE,'runmodule.php?module=cityguards');
		}
	}

	page_footer();
}
?>