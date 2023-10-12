<?php
/**
	17/02/09 - v0.0.1
	This module is to allow the setting 'minedeathchance' to be removed
	from the race modules.

	Feel free to add any races that are not here. :)
*/
function metalmine_raceminedeath_getmoduleinfo()
{
	$info = array(
		"name"=>"Metal Mine Race Death Chances",
		"description"=>"The chances of death in the metal mine for each race.",
		"version"=>"0.0.1",
		"author"=>"`@MarcTheSlayer",
		"category"=>"Materials",
		"download"=>"http://dragonprime.net",
		"requires"=>array(
		   "metalmine"=>"5.0|By DaveS, available on DragonPrime"
		), 
		"settings"=>array(
			"Race Chance Settings,title",
			"amazon"=>"Chance for Amazons to die in the mine,range,0,100,1|25",
			"barbarian"=>"Chance for Barbarians to die in the mine,range,0,100,1|25",
			"darkelf"=>"Chance for Dark Elves to die in the mine,range,0,100,1|10",
			"dragon"=>"Chance for Dragons to die in the mine,range,0,100,1|20",
			"drow"=>"Chance for Drows to die in the mine,range,0,100,1|80",
			"dwarf"=>"Chance for Dwarves to die in the mine,range,0,100,1|5",
			"elf"=>"Chance for Elves to die in the mine,range,0,100,1|90",
			"faerie"=>"Chance for Faerie to die in the mine,range,0,100,1|25",
			"felyn"=>"Chance for Felynes to die in the mine,range,0,100,1|40",
			"gargoyle"=>"Chance for Gargoyles to die in the mine,range,0,100,1|1",
			"ghoul"=>"Chance for Ghouls to die in the mine,range,0,100,1|25",
			"giant"=>"Chance for Giants to die in the mine,range,0,100,1|25",
			"gnome"=>"Chance for Gnomes to die in the mine,range,0,100,1|80",
			"goblin"=>"Chance for Goblins to die in the mine,range,0,100,1|30",
			"halfelf"=>"Chance for Half-Elves to die in the mine,range,0,100,1|90",
			"halfling"=>"Chance for Halflings to die in the mine,range,0,100,1|80",
			"human"=>"Chance for Humans to die in the mine,range,0,100,1|90",
			"imp"=>"Chance for Imps to die in the mine,range,0,100,1|25",
			"klingon"=>"Chance for Klingons to die in the mine,range,0,100,1|90",
			"lich"=>"Chance for Lichs to die in the mine,range,0,100,1|95",
			"lizardman"=>"Chance for Lizardmen to die in the mine,range,0,100,1|90",
			"paladin"=>"Chance for Paladins to die in the mine,range,0,100,1|15",
			"pirate"=>"Chance for Pirates to die in the mine,range,0,100,1|90",
			"searinoa"=>"Chance for Searinoas to die in the mine,range,0,100,1|60",
			"stormgiant"=>"Chance for Storm Giants to die in the mine,range,0,100,1|20",
			"troll"=>"Chance for Trolls to die in the mine,range,0,100,1|90",
			"vampire"=>"Chance for Vampires to die in the mine,range,0,100,1|20",
			"viking"=>"Chance for Vikings to die in the mine,range,0,100,1|40",
			"werewolf"=>"Chance for Werewolves to die in the mine,range,0,100,1|45",
			"default"=>"Chance for Unknown race to die in the mine,range,0,100,1|20",
			"`^Note: Default will be used if you have races installed that aren't listed here.,note"
		)
	);
	return $info;
}

function metalmine_raceminedeath_install()
{
	output("`4%s 'metalmine_raceminedeath' Module.`n", translate_inline(is_module_active('metalmine_raceminedeath')?'Updating':'Installing'));

    module_addhook('raceminedeath');
	return TRUE;
}

function metalmine_raceminedeath_uninstall()
{
	output("`4Un-Installing 'metalmine_raceminedeath' Module.`n`0");
	return TRUE;
}

function metalmine_raceminedeath_dohook($hookname,$args)
{
	global $session;

	switch( $session['user']['race'] )
	{
		case 'Amazon':
			$args['chance'] = get_module_setting('amazon');
			$args['racesave'] = "Fortunately your Amazon skill lets you escape unscathed.`n";
			$args['schema'] = 'module-raceamaz';
		break;

		case 'Barbarian':
			$args['chance'] = get_module_setting('barbarian');
			$args['racesave'] = "Fortunately your Barbarian skill lets you escape unscathed.`n";
			$args['schema'] = 'module-racebarb';
		break;

		case 'DarkElf':
			$args['chance'] = get_module_setting('darkelf');
			$args['racesave'] = "Fortunately, as a Dark Elf you are adept at seeing in darkness and you saw that things were not quite right. You escape unscathed.`n";
			$args['schema'] = 'module-racedarkelf';
		break;

		case 'Dragon':
			$args['chance'] = get_module_setting('dragon');
			$args['racesave'] = "Your Dragon fire melts the rocks that would harm you and lets you easily escape with no injury!`n";
			$args['schema'] = 'module-racedragon';
		break;

		case 'Drow':
			$args['chance'] = get_module_setting('drow');
			$args['racesave'] = "Fortunately your Drow powers of darkness let you escape unscathed.`n";
			$args['schema'] = 'module-racedrow';
		break;

		case 'Dwarf':
			$args['chance'] = get_module_setting('dwarf');
			$args['racesave'] = "Fortunately your Dwarven skills as an excellent miner let you escape unscathed.`n";
			$args['schema'] = 'module-racedwarf';
		break;

		case 'Elf':
			$args['chance'] = get_module_setting('elf');
			$args['racesave'] = "Fortunately your Elf skill lets you escape unscathed.`n";
			$args['schema'] = 'module-raceelf';
		break;

		case 'Faerie':
			$args['chance'] = get_module_setting('faerie');
			$args['racesave'] = "Fortunately your Faerie skills let you escape unscathed.`n";
			$args['schema'] = 'module-racefaer';
		break;

		case 'Felyne':
			$args['chance'] = get_module_setting('felyn');
			$args['racesave'] = "Fortunately your felyne athleticism lets you escape unscathed.`n";
			$args['schema'] = 'module-racecat';
		break;

		case 'Gargoyle':
			$args['chance'] = get_module_setting('gargoyle');
			$args['racesave'] = "The rocks bounce off your tough hide and you escape unharmed.`n";
			$args['schema'] = 'module-racegargoyle';
		break;

		case 'Ghoul':
			$args['chance'] = get_module_setting('ghoul');
			$args['racesave'] = "Fortunately your Ghoul skills let you escape unscathed.`n";
			$args['schema'] = 'module-raceghoul';
		break;

		case 'Giant':
			$args['chance'] = get_module_setting('giant');
			$args['racesave'] = "Fortunately your gigantic strength lets you escape unscathed.`n";
			$args['schema'] = 'module-racegiant';
		break;

		case 'Gnome':
			$args['chance'] = get_module_setting('gnome');
			$args['racesave'] = "It was dumb luck and your oblivious nature as a Gnome that allowed you to stumble out unscathed.`n";
			$args['schema'] = 'module-racegnome';
		break;

		case 'Goblin':
			$args['chance'] = get_module_setting('goblin');
			$args['racesave'] = "Made from mud, you are used to conditions like this and escape.`n";
			$args['schema'] = 'module-racegoblin';
		break;

		case 'Half-Elf':
			$args['chance'] = get_module_setting('halfelf');
			$args['racesave'] = "Fortunately your elvish athleticism lets you escape unscathed.`n";
			$args['schema'] = 'module-racehalfelf';
		break;

		case 'Halfling':
			$args['chance'] = get_module_setting('halfling');
			$args['racesave'] = "Though you are showered with dust, you nip out through your great speed.`n";
			$args['schema'] = 'module-racehalfling';
		break;

		case 'Human':
			$args['chance'] = get_module_setting('human');
			$args['racesave'] = "The Gods must like you today. You escape unscathed.`n";
			$args['schema'] = 'module-racehuman';
		break;

		case 'Imp':
			$args['chance'] = get_module_setting('imp');
			$args['racesave'] = "Fortunately your Imp skills let you escape unscathed.`n";
			$args['schema'] = 'module-raceimp';
		break;

		case 'Klingon':
			$args['chance'] = get_module_setting('klingon');
			$args['racesave'] = "Fortunately your Klingon skills let you escape unscathed.`n";
			$args['schema'] = 'module-raceklingon';
		break;

		case 'Lich':
			$args['chance'] = get_module_setting('lich');
			$args['racesave'] = "Despite your weak frame, you escape unscathed.`n";
			$args['schema'] = 'module-racelich';
		break;

		case 'Lizardman':
			$args['chance'] = get_module_setting('lizardman');
			$args['racesave'] = "Fortunately you slither through the rubble and escape unscathed.`n";
			$args['schema'] = 'module-racelzrd';
		break;

		case 'Paladin':
        	$args['chance'] = get_module_setting('paladin');
			$args['racesave'] = "It was your destiny as a Paladin that allowed you to survive unscathed.`n";
			$args['schema'] = 'module-racepaladin';
		break;

		case 'pirate':
			$args['chance'] = get_module_setting('pirate');
			$args['racesave'] = "Fortunately your Pirate skills of grabbing booty and running enable you to escape unscathed.`n";
			$args['schema'] = 'module-racepirate';
		break;

		case 'Searinoa':
			$args['chance'] = get_module_setting('searinoa');
			$args['racesave'] = "Blasting out violent water spurts, you are able to push boulders from collapsing upon you.`n";
			$args['schema'] = 'module-racesearinoa';
		break;

		case 'Storm Giant':
			$args['chance'] = get_module_setting('stormgiant');
			$args['racesave'] = "The massive girth of your Storm Giant muscles, allows you to escape unharmed.`n";
			$args['schema'] = 'module-racestorm';
		break;

		case 'Troll':
			$args['chance'] = get_module_setting('troll');
			$args['racesave'] = "You have always fended for yourself and now is no different. You escape unscathed.`n";
			$args['schema'] = 'module-racetroll';
		break;

		case 'Vampire':
			$args['chance'] = get_module_setting('vampire');
			$args['racesave'] = "Fortunately you transform into a small bat and escape unscathed.`n";
			$args['schema'] = 'module-racevampire';
		break;

		case 'Viking':
			$args['chance'] = get_module_setting('viking');
			$args['racesave'] = "Fortunately your Viking strength once again lets you escape.`n";
			$args['schema'] = 'module-racevik';
		break;

		case 'Werewolf':
			$args['chance'] = get_module_setting('werewolf');
			$args['racesave'] = "Your Werewolf strength lets you easily escape with no injury!`n";
			$args['schema'] = 'module-racewerewolf';
		break;

		case 'RACE_UNKNOWN':
		default:
			$args['chance'] = get_module_setting('default');
			$args['racesave'] = "Fortunately you're able to escape unscathed by the skin of your teeth.`n";
		break;
	}

	return $args;
}

function metalmine_raceminedeath_run()
{
}
?>