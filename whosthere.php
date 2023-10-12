<?php
/*
	Modified and rewritten by MarcTheSlayer

	== 07/10/08 - v2.0a ==
	+ Added shades as there was chat.
	- Removed forest, graveyard, gypsy, inn, stables.
	- Removed a bunch of code that I don't think was really necessary.
	+ Everyone will now show up in the gardens no matter what village they entered from.
	
	== 12/10/08 - v2.0b ==
	+ In clanhall, only people in your clan now get listed.
	
	== 08/02/09 - v0.0.1 ==
	- Removed all location hooks. No longer rely on scriptnames for locations.
	+ No longer backwards compatible so module name changing to 'whosthere' and version becomes 0.0.1. :)
	+ Added shades and valhalla code to show your location as being there on warrior list.
	+ Added modulehook to core file '/lib/commentary.php' function commentdisplay() and hooked onto the section name
	  instead of using scriptnames. Now 'whosthere' can appear above *any* chat section that uses the commentdisplay()
	  function. This will not work if viewcommentary() function has been used instead.
	+ Added 'hiddenplayers' module by Sixf00t4 code to hide players from being seen as online. Hide/unhide links on players bio page.

	== 20/02/09 - v0.0.2 ==
	+ Fixed a bug in the 'onlinecharlist.php' file. Online character's list wouldn't display if the setting to hide players was off.
*/
function whosthere_getmoduleinfo()
{
	$info = array(
		"name"=>"Who's There",
		"description"=>"Show a list of player's names above commentary sections. Requires 4 core file edits.",
		"version"=>"0.0.2",
		"author"=>"`#Lonny Luberts`0, `#Sixf00t4`0, modified heavily by `@MarcTheSlayer.",
		"category"=>"General",
		"download"=>"http://dragonprime.net/index.php?topic=9803.0",
		"settings"=>array(
			"Who's Here Module Settings,title",
			"View the Comment Moderation page for more section names.`nTip: The section names are in brackets.`n`bKeep each name on its own line.`b,note",
			"list"=>"The commentary section names where you want to have 'whosthere' appear.,textarearesizeable,25|superuser\r\ninn\r\nvillage\r\nvillage-Dwarf\r\nvillage-Elf\r\nvillage-Human\r\nvillage-Troll\r\nvillage-Vampire\r\nvillage-icetown\r\nvillage-citygeneric1\r\nvillage-newbie\r\nshade\r\nvalhalla\r\ngrassyfield\r\nveterans\r\nhunterlodge\r\ngardens",
			"hide"=>"Allow players to be hidden?,bool|0",
			"The two location names below are to show where a person is when they're dead.,note",
			"shade_name"=>"Shades location name:,string,15|Shades",
			"Note: If 'ramiusnpc' module is installed then you need to enter 'The Shades'.,note",
			"valhalla_name"=>"Valhalla location name:,string,15|Valhalla"
		),
		"prefs"=>array(
			"Who's Here User Preferences,title",
			"playerloc"=>"Player Location.,text|",
			"deadloc"=>"Village player died in.,text|",
			"hidden"=>"Hide player from name list?,bool|0",
			"NOTE: Quick hide/unhide links on player's bio page. Only if hide option activated in settings.,note"
		)
	);
	return $info;
}

function whosthere_install()
{
	output("`4%s Who's There Module.`0`n", translate_inline(is_module_active('whosthere')?'Updating':'Installing'));
	module_addhook('bioinfo');
	module_addhook('biotop');
	module_addhook('onlinecharlist');
	module_addhook('warriorlist');
	module_addhook('newday');
	module_addhook('sectionname');
	return TRUE;
}

function whosthere_uninstall()
{
	output("`4Un-Installing Who's There Module.`n");
	return TRUE;
}

function whosthere_dohook($hookname,$args)
{
	global $session;

	require_once("modules/whosthere/dohook/$hookname.php");

	return $args;
}
?>