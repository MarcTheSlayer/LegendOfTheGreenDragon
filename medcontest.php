<?php
/*
	Modified by MarcTheSlayer

	10/02/09 - v3.0.0
	+ Setting for the shop name.
	+ Setting for the entry fee.
	+ User pref for won gems. User must visit shop to collect.
	+ On reset YoM gets sent to all the people that entered and not just the winning 3.
	+ Addnews messages saying contest has ended and a new one has begun.
	- Auto reset setting. Contest will reset when time runs out or if a superuser clicks the reset link.

	17/02/09 - v3.0.1
	+ Fixed a bug where the names weren't in the correct order.
	+ Changed times to unix timestamps.
	+ Code to display the time left until the end.
	- A setting and some code that was no longer used.

	24/02/09 - v3.0.2
	+ Fixed shopname missing from news/YoMs.
	+ Fixed db_free_result issue.
	+ Didn't translate YoM text, have now. :)

	29/03/09 - v3.0.3
	+ Minor code improvements and tweaks.
	+ Location setting so you can have the shop in one village, or all.
	+ No longer forced to enter the contest.

	19/04/09 - v3.0.4
	+ Time left to index page.
	+ Settings to change what's being hunted. Module is no longer hardcoded to medallions. Rubber Duck hunting anyone? :D
	+ PVP hooks to lose/gain medallions on pvp defeat/victory. Idea from Contessa. :)
*/
function medcontest_getmoduleinfo()
{
	$info = array(
		"name"=>"Medallion Contest",
		"version"=>"3.0.4",
		"author"=>"`#Lonny Luberts, modified by `@MarcTheSlayer",
		"category"=>"PQcomp",
		"download"=>"http://dragonprime.net/index.php?topic=9876.0",
		"settings"=>array(
			"Medallion Contest Settings,title",
				"shopname"=>"The contest shop name,string,30|`#C`3ontest `#C`3orner`0",
				"inallloc"=>"Show contest shop in all locations?,bool|0",
				"medloc"=>"Where does the contest shop appear if not in all?,location|".getsetting('villagename', LOCATION_FIELDS),
				"gemcost"=>"Cost of gems to play,int|2",
				"meds"=>"What object do you want players to find?,|`QMedallion`0::`QMedallions`0",
				"`^Singular and then plural separated by a double colon `$::`^.,note",
				"medimage"=>"Filename of object image?,string|med_coin.gif",
				"`^Images are to be placed in the 'root/images/' directory and should be no bigger than 16x16.,note",
				"medallionmax"=>"Max possible objects per gameday.,range,1,99|12",
				"resettimer"=>"How long to run contest for before auto reset,enum,4,4 Days,7,1 Week,14,2 Weeks,21,3 Weeks,28,4 Weeks|4",
				"lastreset"=>"Last Reset Date,string,20|",
				"Note: <a href=\"http://www.timestampconvert.com\" target=\"_blank\">Unix timestamp</a>. Tweak the Reset Date to get the next reset where you want it.,note",
				"indexstats"=>"Show Leader and time left on Login screen?,bool|0",
			"Medallion Contest Champion,title",
				"medconthigh"=>"Highest Score,int|",
				"medconthighid"=>"Highest Score User ID,int|"
		),
		"prefs"=>array(
			"Medallion Contest Module User Preferences,title",
				"medhunt"=>"Joined Contest,bool|0",
				"medpoints"=>"Current Score,int|0",
				"medallion"=>"Objects in possession,int|0",
				"Note: No more than 5 in possession as 5 maximum is hardcoded.,note",
				"medfind"=>"Maximum objects that can be found today,int|0",
				"lastloc"=>"Last Player Location,string,20|",
				"seclastloc"=>"Second From Last Player Location,string,20|",
				"gemswon"=>"Gems won and waiting to be collected,int|0",
				"Note: Winning gems will not be deleted with a DK but future gems will overwrite any existing.,note",
			"Contest Images (JAWS),title",
				"user_stat"=>"Show Numberical Stats? (JAWS Compatability),bool|0"
		)
	);
	return $info;
}

function medcontest_install()
{
	if( is_module_active('medcontest') )
	{
		output("`c`b`QUpdating 'medcontest' Module.`0`b`c`n");
	}
	else
	{
		output("`c`b`QInstalling 'medcontest' Module.`0`b`c`n");
		set_module_setting('lastreset',time(),'medcontest');
	}

	module_addhook('index');
	module_addhook('newday');
	module_addhook('village');
	module_addhook('village-desc');
	module_addhook('everyhit');
	module_addhook('charstats');
	module_addhook('pvpwin');
	module_addhook('pvploss');
	module_addhook('changesetting');
	return TRUE;
}

function medcontest_uninstall()
{
	output("`c`b`QUn-Installing 'medcontest' Module.`0`b`c`n");
	return TRUE;
}

function medcontest_dohook($hookname,$args)
{
	global $session;

	list($med, $meds) = explode('::',get_module_setting('meds'));
	require_once("modules/medcontest/dohook/$hookname.php");

	return $args;
}

function medcontest_run()
{
	global $session;

	$op = httpget('op');

	$shop_name = get_module_setting('shopname','medcontest');
	page_header(full_sanitize($shop_name));
	list($med, $meds) = explode('::',get_module_setting('meds'));
	$gems = translate_inline(array('gem','gems'));

	require_once("modules/medcontest/run/case_$op.php");

	addnav('Leave');
	addnav('Back to the Village','village.php');

	if( $session['user']['superuser'] & SU_MANAGE_MODULES )
	{
		addnav('Superuser');
		if( $session['user']['superuser'] & SU_DEVELOPER )
		{
			if( httpget('find') == 1 )
			{
				increment_module_pref('medallion');
			}
			addnav(array('Find %s',$med),'runmodule.php?module=medcontest&find=1');
		}
		addnav('Reset Contest','runmodule.php?module=medcontest&op=reset');
		addnav('Module Settings','configuration.php?op=modulesettings&module=medcontest');
		addnav('The Grotto','superuser.php');
	}

	// I've changed this to a copyright character as what was before IMHO spoiled the roleplay feel. - MarcTheSlayer.
	// I cannot make you keep this line here but would appreciate it left in. - Lonny.
	rawoutput('<br /><br /><div style="text-align: left;"><a href="http://www.pqcomp.com" target="_blank" title="Medallion Contest by Lonny @ http://www.pqcomp.com">&copy</a><br />');

	page_footer();
}
?>