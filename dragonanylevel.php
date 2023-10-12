<?php
function dragonanylevel_getmoduleinfo()
{
	$info = array(
		"name"=>"Dragon Any Level",
		"description"=>"Fight the Dragon at any level.",
		"version"=>"0.0.1",
		"author"=>"`@MarcTheSlayer`2 and Dragonprime Development Team",
		"category"=>"Forest",
		"download"=>"",
		"settings"=>array(
			"Module - Settings,title",
				"level"=>"Which level will the Dragon link show up?,int|15",
		),
	);
	return $info;
}

function dragonanylevel_install()
{
	output("`c`b`Q%s 'dragonanylevel' Module.`b`n`c", translate_inline(is_module_active('dragonanylevel')?'Updating':'Installing'));
	module_addhook('forest');
	return TRUE;
}

function dragonanylevel_uninstall()
{
	output("`n`c`b`Q'dragonanylevel' Module Uninstalled`0`b`c");
	return TRUE;
}

function dragonanylevel_dohook($hookname,$args)
{
	global $session;

	if( $session['user']['level'] != 15 && $session['user']['level'] >= get_module_setting('level') && $session['user']['seendragon'] == 0 )
	{
		// Code taken from /lib/forest.php
		$isforest = 0;
		$vloc = modulehook('validforestloc', array());
		foreach( $vloc as $i => $l )
		{
			if( $session['user']['location'] == $i )
			{
				$isforest = 1;
				break;
			}
		}
		if( $isforest || count($vloc) == 0 )
		{
			addnav('Fight');
			addnav('G?`@Seek Out the Green Dragon','forest.php?op=dragon');
		}
	}

	return $args;
}

function dragonanylevel_run()
{
}
?>