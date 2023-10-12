<?php
/**
	Modified by MarcTheSlayer

	21/02/09 - v20090221
	- Removed NPC, wasn't needed, using /game instead.
	+ Moved all comments to a setting.
	+ Added grotto editor.

	17/03/09 - v20090317
	+ Added changesetting hook.
*/
function villageevents_getmoduleinfo()
{
	$info = array(
		"name"=>"Village Events",
		"description"=>"Random events in the commentary to add to the RP atmosphere.",
		"version"=>"20090317",
		"author"=>"`^Sixf00t4`2, modified by `@MarcTheSlayer",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/index.php?topic=9932.0",
		"settings"=>array(
			"village Events Settings,title",
			"inall"=>"Village events occur in all villages?,bool|1",
			"location"=>"If `bno`b to the above where does it occur?,location|".getsetting('villagename', LOCATION_FIELDS),
			"howmuch"=>"How often do events occur?,enum,1500,A Lot (1500),2000,Quite a Bit (2000),2500,Less (2500),3000,Seldom (3000)|2500",
			"basevalue"=>"Base value.,int|12",
			"`^Note: 'events occur' < 'base value'.,note",
			"comments"=>"Custom Commentary,textarearesizeable,40|",
			"`^Note: Keep each comment on its own line `$ and when using the colour code % keep a space afterwards.,note",
			"`#The following codes are supported in the comments box (case matters):`n%A = The players's name.`n%B = The players's weapon.`n%C = The players's armour.`n%s = Subjective pronoun for the player. (him her)`n%p = Possessive pronoun for the player. (his her)`n%o = Objective pronoun for the player. (he she)`n,note"
		),
	);
	return $info;
}

function villageevents_install()
{
	if( is_module_active('villageevents') )
	{
		output("`c`b`QUpdating 'villageevents' Module.`b`n`c");
	}
	else
	{
		output("`c`b`QInstalling 'villageevents' Module.`b`n`c");
		villageevents_install_default_comments();
	}
	module_addhook('changesetting');
	module_addhook('village');
	module_addhook('superuser');
	return TRUE;
}

function villageevents_uninstall()
{
	output("`n`c`b`Q'villageevents' Uninstalled`0`b`c");
	return TRUE;
}

function villageevents_install_default_comments()
{
	$comments = "`#A dark shadow is cast on the ground as a dragon flies overhead.\r\n`@An imp scuttles across the village square.\r\n`QA loud cry is heard coming from the forest.\r\n`LSomeone's goat comes over and starts to nibble on `^%A`L's clothing.\r\n`% A devilish imp scuttles away from the forest and hides behind %A`%'s leg.\r\n`$ A hush falls over the masses as a mysterious breeze flows through the village.\r\n`RThe village is full of talk about the latest dragon slayer.\r\n`2A dragon can be seen circling above the village taunting those below.\r\n`4A cold breeze sweeps through the village as another villager becomes a meal to the dragon\r\n`6An eagle soaring by drops some love in `^%A`6's eye.\r\n`1A drunk villager stumbles from the inn muttering unintelligibly.\r\n`7An imp escapes from `^%A`7's sack and scuttles across the village square.\r\n`^%A `Vcan be seen picking %p nose and wiping it on %p `^%C`V.";
	set_module_setting('comments',$comments,'villageevents');
}

function villageevents_dohook($hookname,$args)
{
	global $session;

	switch( $hookname )
	{
		case 'changesetting':
			if( $args['setting'] == 'villagename' && get_module_setting('inall') != 1 )
			{
				if( $args['old'] == get_module_setting('location') )
				{
					set_module_setting('location', $args['new']);
				}
			}
		break;

		case 'village':
			if( get_module_setting('inall') == 1 || get_module_setting('location') == $session['user']['location'] )
			{
				if( e_rand(1,get_module_setting('howmuch')) < get_module_setting('basevalue') )
				{
					$search = array('%A','%B','%C','%s','%p','%o');
					$replace = array($session['user']['name'],$session['user']['weapon'],$session['user']['armor'],translate_inline($session['user']['sex']?"her":"him"),translate_inline($session['user']['sex']?"her":"his"),translate_inline($session['user']['sex']?"she":"he"));
					$comments = explode("\r\n",trim(get_module_setting('comments','villageevents'),"\r\n"));
					shuffle($comments);
					$chat_line = str_replace($search, $replace, $comments[0]);
					$sql = "INSERT INTO " . db_prefix('commentary') . " (postdate, section, author, comment) VALUES (now(), '" . $args['section'] . "', 0, '/game " . addslashes($chat_line) . "')";
					db_query($sql);
					unset($comments);
				}
			}
		break;

		case 'superuser':
			myDefine("SU_EDIT_THIS", SU_EDIT_MOUNTS | SU_EDIT_CREATURES | SU_EDIT_COMMENTS);
			if( $session['user']['superuser'] & SU_EDIT_THIS )
			{
				addnav('Editors');
				addnav('Village Events Editor','runmodule.php?module=villageevents');
			}
		break;
	}

	return $args;    
}

function villageevents_run()
{
	global $session;

	page_header('Village Events Editor');

	$op = httpget('op');
	if( $op == 'submit' )
	{
		$comments = trim(stripslashes(httppost('comments')),"\r\n");
		set_module_setting('comments',$comments,'villageevents');
		output('`@Comments Saved!');
	}
	else
	{
		$comments = stripslashes(get_module_setting('comments','villageevents'));
	}

	$comments = array('comments'=>$comments);

	require_once('lib/showform.php');
	$form = array(
			"Village Events,title",
			"comments"=>"Comments:,textarearesizeable,60",
			"`^Note: Keep each comment on its own line `$ and when using the colour code % keep a space afterwards.,note",
			"`#The following codes are supported in the comments box (case matters):`n%A = The players's name.`n%B = The players's weapon.`n%C = The players's armour.`n%s = Subjective pronoun for the player. (him her)`n%p = Possessive pronoun for the player. (his her)`n%o = Objective pronoun for the player. (he she)`n,note"
	);

	rawoutput('<form action="runmodule.php?module=villageevents&op=submit" method="POST">');
	addnav('','runmodule.php?module=villageevents&op=submit');
	showform($form,$comments);
	rawoutput('</form>');

	addnav('Refresh');
	addnav('Refresh','runmodule.php?module=villageevents');

	if( $session['user']['superuser'] & SU_MANAGE_MODULES )
	{
		addnav('Settings');
		addnav('Settings','configuration.php?op=modulesettings&module=villageevents');
	}

	require_once('lib/superusernav.php');
	superusernav();

	page_footer();
}
?>