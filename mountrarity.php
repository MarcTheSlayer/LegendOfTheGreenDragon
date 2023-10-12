<?php

/*
Mount Rarity
File: mountrarity.php
Author:  Red Yates aka Deimos
Date:    1/10/2005
Version: 1.1 (1/16/2005)

Attaches a setting to each mount for rarity percentage.
Each game day the module roles for each mount to be available or not.
Done by request of the jcp.

v1.02
Fixed stupid error wherein nothing actually happened.

v1.1
Made changes so that it blocks the navs on every stables page, not just the
main.
Flipped the available/unavailable pref for more sensible boolean operating.

	Modified by MarcTheSlayer
	24/08/09 - v1.2
	+ I noticed that the 'newday-runonce' code ate *alot* of sql queries so I improved all the
	  hook code by removing the get_module_objpref function calls and adding 1 query that put
	  all the objprefs into an array for checking against.
	- Removed the modulehook 'mountfeatures'.
*/

function mountrarity_getmoduleinfo()
{
	$info=array(
		"name"=>"Mount Rarity",
		"description"=>"Mounts will not always be available due to their rarity.",
		"version"=>"1.2",
		"author"=>"`\$Red Yates`2, modified by `@MarcTheSlayer",
		"category"=>"Mounts",
		"download"=>"http://dragonprime.net/index.php?topic=10406.0",
		"settings"=>array(
			"Mount Rarity settings,title",
			"showout"=>"Show missing mounts list,bool|0",
		),
		"prefs-mounts"=>array(
			"Mount Rarity Mount Preferences,title",
			"rarity"=>"Percentage chance of mount being available each day,range,1,100,1|100",
			"`@1% = so rare that you'll never see one available...ever!`n100% = Not even the slightest bit rare&#44; always available.,note",
			"unavailable"=>"Is mount `bun`bavailable today?,bool",
		),
	);
	return $info;
}

function mountrarity_install()
{
	output("`c`b`Q%s 'mountrarity' Module.`b`n`c", translate_inline(is_module_active('mountrarity')?'Updating':'Installing'));
	module_addhook('newday-runonce');
	module_addhook('stables-desc');
	module_addhook('stables-nav');
	return TRUE;
}

function mountrarity_uninstall()
{
	output("`n`c`b`Q'mountrarity' Module Uninstalled`0`b`c");
	return TRUE;
}

function mountrarity_dohook($hookname, $args)
{
	global $session;

	// Get objpref values for all mounts.
	$sql = "SELECT objid, value
			FROM " . db_prefix('module_objprefs') . "
			WHERE modulename = 'mountrarity'
				AND objtype = 'mounts'
				AND setting = '" . ($hookname == 'newday-runonce' ? 'rarity' : 'unavailable') . "'";
	$result = db_query($sql);
	$unavailable_array = array();
	while( $row = db_fetch_assoc($result) )
	{
		$unavailable_array[$row['objid']] = $row['value'];
	}

	// Get mount data.
	$sql = "SELECT mountname, mountid
			FROM " . db_prefix('mounts') . "
			WHERE mountactive = 1
				" . ($hookname == 'newday-runonce' ? '' : 'AND mountlocation = \'all\' OR mountlocation = \'' . $session['user']['location'] . '\'');
	$result = db_query($sql);

	switch( $hookname )
	{
		case 'newday-runonce':
			// Reset all unavailable mounts back to being available.
			db_query("UPDATE " . db_prefix('module_objprefs') . " SET value = 0 WHERE modulename = 'mountrarity' AND objtype = 'mounts' AND setting = 'unavailable'");

			while( $row = db_fetch_assoc($result) )
			{
				if( isset($unavailable_array[$row['mountid']]) && e_rand(2,100) > $unavailable_array[$row['mountid']] )
				{	// Make this mount rare.
					set_module_objpref('mounts', $row['mountid'], 'unavailable', 1);
				}
			}
		break;

		case 'stables-desc':
			if( get_module_setting('showout') )
			{
				$showsign = TRUE;
				while( $row = db_fetch_assoc($result) )
				{
					if( isset($unavailable_array[$row['mountid']]) && $unavailable_array[$row['mountid']] == 1 )
					{
						if( $showsign == TRUE )
						{
							output('`n`n`7A sign by the door tells you that the following mounts are out of stock today:`n');
							$showsign = FALSE;
						}
						output('`&%s`0`n', $row['mountname']);
					}
				}
			}
			else
			{
				output("`n`7If you don't see something you like today, perhaps you should check again tomorrow.`0`n");
			}
		break;

		case 'stables-nav':
			while( $row = db_fetch_assoc($result) )
			{
				if( isset($unavailable_array[$row['mountid']]) && $unavailable_array[$row['mountid']] == 1 )
				{	// Remove mount from navigation menu.
					blocknav('stables.php?op=examine&id='.$row['mountid']);
				}
			}
		break;
	}

	unset($unavailable_array);

	return $args;
}

function mountrarity_run()
{
}
?>