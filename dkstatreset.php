<?php
/**
	24/08/10 - v0.0.1
	15/06/11 - v0.0.2
	+ Had $key instead of $name so didn't work. :-O
*/
function dkstatreset_getmoduleinfo()
{
	$info = array(
			"name"=>"DK Stats Reset",
			"description"=>"Stop certain stats from being reset after a DK.",
			"version"=>"0.0.2",
			"author"=>"`@MarcTheSlayer",
			"category"=>"Administrative",
			"download"=>"http://dragonprime.net/index.php?topic=11204.0",
			"settings"=>array(
				"Readme,title",
					"`^`iSome things shouldn't be reset and some things can't be reset.`n`n
					The next tab has listed what I believe are the only stats you should mess with.`n`n
					Gold/gems/hitpoints/maxhitpoints stats are reset no matter what you do so I've not included them.`i,note",
				"Stats to Reset,title",
					"race"=>"Reset Race?,bool|1",
					"specialty"=>"Reset specialty?,bool|1",
					"hashorse"=>"Reset mount?,bool|0",
					"goldinbank"=>"Reset gold in Bank?,bool|1",
					"companions"=>"Reset companions?,bool|1",
					"charm"=>"Reset charm?,bool|0",
					"weapon"=>"Reset weapon?,bool|1",
					"armor"=>"Reset armour?,bool|1",
					"defense"=>"Reset defence?,bool|1",
					"attack"=>"Reset attack?,bool|1",
					"weaponvalue"=>"Reset weapon value?,bool|1",
					"armorvalue"=>"Reset armour value?,bool|1",
					"weapondmg"=>"Reset weapon damage?,bool|1",
					"armordef"=>"Reset armour defence?,bool|1",
					"soulpoints"=>"Reset soulpoints?,bool|1",
					"gravefights"=>"Reset torments?,bool|1",
					"deathpower"=>"Reset favour?,bool|1",
					"resurrections"=>"Reset resurrections?,bool|1"
			)
		);
	return $info;
}

function dkstatreset_install()
{
	module_addhook('dk-preserve');
	return TRUE;
}

function dkstatreset_uninstall()
{
	return TRUE;
}

function dkstatreset_dohook($hookname,$args)
{
	$settings = get_all_module_settings('dkstatreset');
	foreach( $settings as $name => $value )
	{
		$args[$name] = ( $value == 1 ) ? FALSE : TRUE;
	}
	return $args;
}
?>