<?php
/*
* The Tournament v1.1
* by Excalibur (http://www.ogsi.it)
* excalthesword@fastwebnet.it
* English version by Talisman (http://dragonprime.cawsquad.net)
* Code Optimization: Talisman
* 0.98 updated:  Frederic Hutow (lotgd.togrc.com)
* 0.98 version bug fix: LonnyL (http://www.pqcomp.com)
*/
/**
	Modified by MarcTheSlayer
	14/05/2013 - v1.2
	+ Minimal contestants required. Tournament will start when this is reached.
	+ Tournament will end and reset when the duration time has been reached.
	+ Leader shown on index and village pages (can be turned off). Also shows the time left.
	+ Added secret bonus pages for people with thiefskills specialty and seductiveskills specialty. :D
	+ Winnings are collected on next visit. This stops the possibilty of the winnings being lost.
	+ HoF page showing previous champions. Bio page showing player's trial scores.
	+ Hooked into Blusprings to remind players to do their next trial.
	+ Modified code and reworded text here and there.

	27/06/2013 - v1.3
	+ Fixed a serious bug with the SQL query that deleted the 'allprefs', It wasn't just deleting this modules 'allprefs' :'(

	07/07/2013 - v1.4
	+ Small tweaks to the code.

	13/03/2014 - v1.5
	+ Bio bug where an empty array still had a count of 1 and caused a foreach error.
*/
function tournament_getmoduleinfo()
{
	$info = array(
		"name"=>"Tournament",
		"description"=>"Tournament is a 15 trial contest. 1 trial per level.",
		"version"=>"1.5",
		"author"=>"Excalibur / Talisman<br />0.98 conv: Frederic Hutow<br />bug fix: LonnyL`2, modified by `@MarcTheSlayer",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1452",
		"settings"=>array(
			"Tournament Settings,title",
				"tourloc"=>"Where is the Tournament located?,location|".getsetting('villagename', LOCATION_FIELDS),
				"leader"=>"Current Tournament Leader (userid),int",
				"indexstats"=>"Show Leader and time left on Login page?,bool|1",
				"villagestats"=>"Show Leader and time left in the village?,bool|1",
				"minimum"=>"Minimum contestants required:,int|3",
				"`^Note: Set to zero to disable.,note",
				"start"=>"When did the tournament start?,dayrange,+10 days,+1 day|2009-05-30 00:00:00",
				"duration"=>"How long does each tournament last?,datelength|1 week",
				"offtime"=>"Time between each Tournament:,datelength|2 days",
				"status"=>"Tournament status:,enum,0,Off,1,Signup,2,Running|1",
				"collect"=>"Allow won gold/gems collection after what level?,range,1,15,1|3",
				"`^Note: Stops people having lots of gold right after a DK.,note",
			"Entry Fees,title",
				"efeegold"=>"Entry Fee (Gold),int|500",
				"efeegems"=>"Entry Fee (Gems),int|1",
			"Rewards,title",
				"r1gold"=>"1st Position (Gold),int|10000",
				"r1gems"=>"1st Position (Gems),int|10",
				"r2gold"=>"2nd Position (Gold),int|8000",
				"r2gems"=>"2nd Position (Gems),int|8",
				"r3gold"=>"3rd Position (Gold),int|5000",
				"r3gems"=>"3rd Position (Gems),int|5",
			"Previous Champions,title",
				"champions"=>"Serialised array (IDs and points):,viewonly",
				"`^Winner of each Tournament get added here. Shown in HoF.,note",
		),
		"prefs"=>array(
			"Tournament Module User Preferences,title",
				"entry"=>"User has paid entry fee?,bool",
				"gemswon"=>"Gems won and waiting to be collected:,int",
				"goldwon"=>"Gold won and waiting to be collected:,int",
				"`^Note: Winning gems/gold will not be deleted with a DK but future gems will overwrite any existing.,note",
				"points"=>"Tournament Points:,int",
				"allprefs"=>"User Tournament Information:,viewonly",
				"super"=>"Admin/Moderator Control:,bool",
		),
	);
	return $info;
}

function tournament_install()
{
	module_addhook('changesetting');
	module_addhook('village-'.get_module_setting('tourloc','tournament'));
	module_addhook('village-desc-'.get_module_setting('tourloc','tournament'));
	module_addhook('index');
	module_addhook('newday-runonce');
	module_addhook('training-victory');
	module_addhook('charstats');
	module_addhook('biotop');
	module_addhook('footer-hof');
	module_addhook('superuser');
	return TRUE;
}

function tournament_uninstall()
{
	return TRUE;
}

function tournament_dohook($hookname,$args)
{
	global $session;

	switch( $hookname )
	{
		case 'changesetting':
			if( $args['setting'] == 'villagename' )
			{
				if( $args['old'] == get_module_setting('tourloc') )
				{
					set_module_setting('tourloc', $args['new']);
					module_drophook('village-'.$args['old']);
					module_addhook('village-'.$args['new']);
					module_drophook('village-desc-'.$args['old']);
					module_addhook('village-desc-'.$args['new']);
				}
			}
			if( $args['module'] == 'tournament' )
			{
				if( $args['setting'] == 'tourloc' )
				{
					module_drophook('village-'.$args['old']);
					module_addhook('village-'.$args['new']);
					module_drophook('village-desc-'.$args['old']);
					module_addhook('village-desc-'.$args['new']);
				}
				if( $args['setting'] == 'indexstats' )
				{
					if( $args['new'] == 1 ) module_addhook('index');
					else module_drophook('index');
				}
			}
		break;

		case 'newday-runonce':
			if( get_module_setting('status') == 0 )
			{
				if( strtotime(get_module_setting('offtime'),strtotime(get_module_setting('start'))) <= time() )
				{
					set_module_setting('status', 1);
				}
			}
			elseif( get_module_setting('status') == 2 )
			{
				if( strtotime(get_module_setting('duration'), strtotime(get_module_setting('start'))) <= time() )
				{
					debuglog("Tournament newday-runonce auto reset.");
					include('modules/tournament/tournament_reset.php');
				}
			}
		break;

		case 'training-victory':
			if( get_module_pref('entry') != 1 ) break;

			$allprefs = @unserialize(get_module_pref('allprefs'));
			if( !is_array($allprefs) ) $allprefs = array();
			if( !isset($allprefs[$session['user']['level']]) )
			{
				output("`n`3Sir Tristan comes over and congratulates you, `#Well done! Now don't forget to prove your might in the next Tournament Trial.`0`n");
			}
		break;

		case 'index':
			if( get_module_setting('status') == 0 )
			{
				if( strtotime(get_module_setting('offtime'),strtotime(get_module_setting('start'))) <= time() )
				{
					set_module_setting('status', 1);
					// Now fallthrough.
				}
			}
			if( get_module_setting('status') == 1 )
			{
				output("`@The Tournament is currently taking competitors.`nVisit %s and signup now!`0`n", get_module_setting('tourloc'));
			}
			elseif( get_module_setting('status') == 2 )
			{
		 		$leader = get_module_setting('leader');
				if( $leader != 0 )
				{
					$sql = "SELECT name FROM " . db_prefix('accounts') . " WHERE acctid = '$leader'";
					$result = db_query($sql);
					$row = db_fetch_assoc($result);
					$leadername = $row['name'];
				}
				if( $leadername )
				{
					output("`@The Tournament Leader is: `&%s`0`n", $leadername);
					include('modules/tournament/tournament_timeleft.php');
					output('`2Tournament ends in `@%s`2.`0`n', tournament_timeleft(2));
				}
				else output("`@There is `&no`@ leader in the Tournament. Will you be the first one?`0`n");
			}
		break;

		case 'village-'.$session['user']['location']:
			tlschema($args['schemas']['marketnav']);
			addnav($args['fightnav']);
			tlschema();
			addnav('Tournament Arena','runmodule.php?module=tournament');
		break;

		case 'village-desc-'.$session['user']['location']:
			if( get_module_setting('villagestats') != 1 ) break;
			if( get_module_setting('status') == 0 )
			{
				if( strtotime(get_module_setting('offtime'),strtotime(get_module_setting('start'))) <= time() )
				{
					set_module_setting('status', 1);
					// Now fallthrough to the next IF.
				}
				else
				{
					include('modules/tournament/tournament_timeleft.php');
					output("`n`c`@A new Tournament will start in `&%s`@.`0`c`n", tournament_timeleft());
				}
			}
			if( get_module_setting('status') == 1 )
			{
				output("`n`c`@The Tournament is currently taking competitors. Signup now!`0`c`n");
			}
			elseif( get_module_setting('status') == 2 )
			{
		 		$leader = get_module_setting('leader');
				if( $leader != 0 )
				{
					$sql = "SELECT name FROM " . db_prefix('accounts') . " WHERE acctid = '$leader'";
					$result = db_query($sql);
					$row = db_fetch_assoc($result);
					$leadername = $row['name'];
				}
				if( $leadername ) output("`n`c`@The Tournament Leader is: `&%s`@.`0`c`n", $leadername);
				else output("`n`c`@The Tournament has no leader. Will you be the first one?`0`c`n");
			}
		break;

		case 'charstats':
			$points = get_module_pref('points');
			if( $points > 0 )
			{
				addcharstat('Extra Info');
				addcharstat('Tournament Points', number_format($points));
			}
		break;

		case 'biotop':
			addnav('Tournament Score','runmodule.php?module=tournament&op=bio&id='.$args['acctid']);
		break;

		case 'footer-hof':
			addnav('Tournament');
			addnav('Highest Scorers','runmodule.php?module=tournament&op=hof');
		break;

		case 'superuser':
			addnav('Module Configurations');
			if( get_module_pref('super') || $session['user']['superuser'] & SU_MEGAUSER ) addnav('Tournament Reset','runmodule.php?module=tournament&op=reset');
		break;
	}

	return $args;
}

function tournament_run()
{
	global $session;

	$sop = httpget('sop');

	// Developer Stuff.
	if( $sop == 'start' )
	{
		increment_module_setting('status');
		set_module_setting('start',date("Y-m-d H:i:s"));
	}
	if( $sop == 'increase' ) $session['user']['level']++;
	if( $sop == 'decrease' ) $session['user']['level']--;

	page_header('The Tournament');

	$op = httpget('op');
	$from = 'runmodule.php?module=tournament';

	include("modules/tournament/run/case_$op.php");

	// Developer Stuff.
	if( $session['user']['superuser'] & SU_DEVELOPER )
	{
		addnav('Developer');
	//	addnav('Refresh',$from.'&op='.$op);
		if( get_module_setting('status') != 2 )
		{
			addnav('Start Now', $from.'&op='.$op.'&sop=start');
		}
	//	if( $session['user']['level'] < 15 ) addnav('Increase Level',$from.'&op='.$op.'&sop=increase');
	//	if( $session['user']['level'] > 1 ) addnav('Decrease Level',$from.'&op='.$op.'&sop=decrease');
	}

	require_once('lib/superusernav.php');
	superusernav();

	page_footer();
}
?>