<?php
/**
	Bug Fixed by MarcTheSlayer
	25/06/09 - v1.1
	+ Added drinks.php module requirement.
	+ Fixed bug that stopped this working correctly.
*/
function drnknsbar_getmoduleinfo()
{
	$info = array(
		"name"=>"Drunkness Bar",
		"version"=>"1.1",
		"author"=>"Death Dragon`2, bug fixed by `@MarcTheSlayer",
		"category"=>"Stat Display",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=423",
		"requires"=>array(
			"drinks"=>"1.1|John J. Collins`nHeavily modified by JT Traub, core_module"
		)
	);
	return $info;
}

function drnknsbar_install()
{
	output("`c`b`Q%s 'drnknsbar' Module.`0`b`c`n", translate_inline(is_module_active('drnknsbar')?'Updating':'Installing'));
	module_addhook('charstats');
    return TRUE;
}

function drnknsbar_uninstall()
{
	output("`c`b`QUn-Installing 'drnknsbar' Module.`0`b`c`n");
	return TRUE;
}

function drnknsbar_dohook($hookname,$args)
{
	$drunk = get_module_pref('drunkeness','drinks');
	if( $drunk > 100 ) $drunk = 100;

	$drunklist = array(
		5=>"Sober",
		20=>"Buzzed",
		40=>"Tipsy",
		60=>"Drunk",
		80=>"Sloshed",
		100=>"Hammered"
	);

	$drunklist = translate_inline($drunklist);
	$keys = array_keys($drunklist);

	foreach( $drunklist as $key => $value )
	{
		if( $drunk <= $key )
		{
			$drunk = $key;
			break;
		}
	}

	setcharstat('Personal Info', 'Drunkeness', $drunklist[$drunk]);

	return $args;
}

function drnknsbar_run()
{
}
?>