<?php
function dragon_aiscript_getmoduleinfo()
{
	$info = array(
		"name"=>"Dragon AI Script",
		"description"=>"Add AI code to your Green Dragon.",
		"version"=>"0.0.1",
		"author"=>"`@MarcTheSlayer",
		"category"=>"Forest",
		"download"=>"http://dragonprime.net/index.php?topic=9910.0",
		"settings"=>array(
			"Dragon AI Script Settings,title",
			"aiscript"=>"Dragon AI.,textarearesizeable,40",
			"`^Note: This works exactly the same as the creature's AI.`nExamples can be found at <a href=\"http://dragonprime.net/index.php?board=52.0\" target=\"blank\">DragonPrime.net</a>,note"
		)
	);
	return $info;
}

function dragon_aiscript_install()
{
	output("`c`b`Q%s 'dragon_aiscript' Module.`b`n`c", translate_inline(is_module_active('dragon_aiscript')?'Updating':'Installing'));
	module_addhook('buffdragon');
}

function dragon_aiscript_uninstall()
{
	output("`n`c`b`Q'dragon_aiscript' Module Uninstalled`0`b`c");
}

function dragon_aiscript_dohook($hookname,$args)
{
	$dragon_aiscript = get_module_setting('aiscript');
	if( !empty($dragon_aiscript) )
	{
		$args['creatureaiscript'] = $dragon_aiscript;
	}

	return $args;
}
?>