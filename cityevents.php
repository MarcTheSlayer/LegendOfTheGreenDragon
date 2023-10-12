<?php
/**
	Modified by MarcTheSlayer
	Based on the 'villageevents' module by Sixf00t4.
	Each city/village can now have their own events.

	22/04/09 - v0.0.1
	+ City-prefs settings so each city/village can have their own events. 
*/
function cityevents_getmoduleinfo()
{
	$info = array(
		"name"=>"City Events",
		"description"=>"Random events in the village chat areas to add to the RP atmosphere. Based on 'villageevents' module.",
		"version"=>"0.0.1",
		"author"=>"`@MarcTheSlayer`2. Original module by `^Sixf00t4`2.",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/index.php?topic=10054.0",
		"requires"=>array(
		   "cityprefs"=>"20070417|By Sixf00t4, available at Dragonprime"
		),
		"settings"=>array(
			"README,title",
			"`^Each city/village now has its own events. To edit these events please use 'City Prefs' in the grotto and select a city/village.,note"
		),
		"prefs-city"=>array(
			"village Events Settings,title",
			"howmuch"=>"How often do events occur?,enum,1500,A Lot (1500),2000,Quite a Bit (2000),2500,Less (2500),3000,Seldom (3000)|2500",
			"basevalue"=>"Base value.,int|12",
			"`^Note: 'rand(1&#44; events occur)' < 'base value'.,note",
			"comments"=>"Custom Commentary,textarearesizeable,40|",
			"`^Note: Keep each comment on its own line `$ and when using the colour code % keep a space afterwards.,note",
			"`#The following codes are supported in the comments box (case matters):`n%A = The players's name.`n%B = The players's weapon.`n%C = The players's armour.`n%s = Subjective pronoun for the player. (him her)`n%p = Possessive pronoun for the player. (his her)`n%o = Objective pronoun for the player. (he she)`n,note"
		),
	);
	return $info;
}

function cityevents_install()
{
	if( is_module_active('cityevents') )
	{
		output("`c`b`QUpdating 'cityevents' Module.`b`c`n");
	}
	else
	{
		output("`c`b`QInstalling 'cityevents' Module.`b`c`n");
		cityevents_install_default_comments();
	}

	module_addhook('village');
	return TRUE;
}

function cityevents_uninstall()
{
	output("`n`c`b`Q'cityevents' Uninstalled`0`b`c");
	return TRUE;
}

function cityevents_install_default_comments()
{
	require_once('modules/cityprefs/lib.php');

	$howmuch = 2500;
	$basevalue = 12;
	$comments = "`#A dark shadow is cast on the ground as a dragon flies overhead.\r\n`@An imp scuttles across the village square.\r\n`QA loud cry is heard coming from the forest.\r\n`LSomeone's goat comes over and starts to nibble on `^%A`L's clothing.\r\n`% A devilish imp scuttles away from the forest and hides behind %A`%'s leg.\r\n`$ A hush falls over the masses as a mysterious breeze flows through the village.\r\n`RThe village is full of talk about the latest dragon slayer.\r\n`2A dragon can be seen circling above the village taunting those below.\r\n`4A cold breeze sweeps through the village as another villager becomes a meal to the dragon\r\n`6An eagle soaring by drops some love in `^%A`6's eye.\r\n`1A drunk villager stumbles from the inn muttering unintelligibly.\r\n`7An imp escapes from `^%A`7's sack and scuttles across the village square.\r\n`^%A `Vcan be seen picking %p nose and wiping it on %p `^%C`V.";

	if( is_module_active('villageevents') )
	{
		output("`n`c`b`Q'Copying 'villageevents' module settings over.`0`b`c`n");
		$howmuch = get_module_setting('howmuch','villageevents');
		$basevalue = get_module_setting('basevalue','villageevents');
		$comments = get_module_setting('comments','villageevents');
		output("`n`c`b`Q'De-Activating 'villageevents' module...`0`b`c`n");
		deactivate_module('villageevents');
		output("`n`c`b`Q'Uninstalling 'villageevents' module...`0`b`c`n");
		uninstall_module('villageevents');
	}

	$vloc = array();
	$vname = getsetting('villagename', LOCATION_FIELDS);
	$vloc[$vname] = 'village';
	$vloc = modulehook('validlocation', $vloc);
	foreach( $vloc as $loc => $val )
	{
		$location = get_cityprefs_cityid('location',$loc);
		set_module_objpref('city',$location,'howmuch',$howmuch,'cityevents');
		set_module_objpref('city',$location,'basevalue',$basevalue,'cityevents');
		set_module_objpref('city',$location,'comments',$comments,'cityevents');
	}
}

function cityevents_dohook($hookname,$args)
{
	global $session;

	require_once('modules/cityprefs/lib.php');
	$loc = get_cityprefs_cityid('location',$session['user']['location']);
	$comments = get_module_objpref('city',$loc,'comments','cityevents');
	if( !empty($comments) )
	{
		if( e_rand(1,get_module_objpref('city',$loc,'howmuch','cityevents')) < get_module_objpref('city',$loc,'basevalue','cityevents') )
		{
			$search = array('%A','%B','%C','%s','%p','%o');
			$replace = array($session['user']['name'],$session['user']['weapon'],$session['user']['armor'],translate_inline($session['user']['sex']?"her":"him"),translate_inline($session['user']['sex']?"her":"his"),translate_inline($session['user']['sex']?"she":"he"));
			$comments = explode("\r\n",trim($comments,"\r\n"));
			shuffle($comments);
			$chat_line = str_replace($search, $replace, $comments[0]);
			db_query("INSERT INTO " . db_prefix('commentary') . " (postdate, section, author, comment) VALUES (now(), '" . $args['section'] . "', 0, '/game " . addslashes($chat_line) . "')");
			unset($comments);
		}
	}

	return $args;    
}

function cityevents_run()
{
}
?>