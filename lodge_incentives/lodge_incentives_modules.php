<?php
	if( get_module_setting('reset') == 1 )
	{
		/**
			I've tried to find as many modules as possible that are connected to the lodge
			and list them here in the array below with a description of what you can buy
			with the lodge points. I have done this because there's no hook for me to find
			out about these modules and categorise them easily.

			All this does is to fill out the incentives settings box with basic info on the
			modules which give donators things. Edit/delete/add to your liking.

			I have added a module hook for future modules to hook into and pass back their details.
			module_addhook('lodge_incentives');

			***IMPORTANT***
			Turn a single array value into an array with key 'cost' if you want to add string
			search and replace. Start spaceholders at 1. ie: %1 - %2 - %3, etc.
		*/

		$shownil = get_module_setting('shownil');

		$text = array(
			'amuletru'=>'`#An Amulet of Ru, which has random effects on the wearer.',
			'buyablog'=>'`#A blog to post whatever you like, within reason.',
			'citygeneric1'=>'`#Access to the village of Corinvale.',
			'chronosgem'=>'`#A Chronos Gem that when used, will grant a newday.',
			'commentdelete'=>'`#Delete the last comment you made with magic ink.',
			'customeq'=>array(
				'weaponcost'=>'`#Personalise your weapon by changing its name.',
				'armorcost'=>'`#Personalise your armour by changing its name.',
				'extraweapon'=>'`#Change your weapon name subsequent times.',
				'extraarmor'=>'`#Change your armour name subsequent times.'
			),
			'dragonpoints'=>'`#Allows you to reset your Dragon points and redistribute them.',
			'extraturns'=>'`#A tiny vial that when drunk will give you 10 extra forest fights.',
			'extrafights'=>array(
				'points'=>'`#Get 1 extra forest fight each game day for %1 game day(s).:length'
			),
			'fairydust'=>'`#A bottle of Fairy Dust that can be a little unpredictable.',
			'golinda'=>array(
				'points'=>'`#The ability to visit Golinda, the Lodge Healer and who\'s %1 percent cheaper than the normal healer.:costpercent'
			),
			'healerdiscount'=>array(
				'cost'=>'`#Get a %1 percent discount at the Healer\'s Hut for %2 day(s).:discount:days',
			),
			'inncoupons'=>'`#10 free stays at the Inn.',
			'lodge_avatars'=>'`#Display an avatar picture on your bio page.',
			'lodgegems'=>array(
				'cost1'=>'`#Buy`@ %1 `#Gem(s).:gems1',
				'cost2'=>'`#Buy`@ %1 `#Gem(s).:gems2',
				'cost3'=>'`#Buy`@ %1 `#Gem(s).:gems3'
			),
			'lodgegold'=>array(
				'cost1'=>'`#Buy`@ %1 `#Gold.:gold1',
				'cost2'=>'`#Buy`@ %1 `#Gold.:gold2',
				'cost3'=>'`#Buy`@ %1 `#Gold.:gold3'
			),
			'lodgemountbuff'=>array(
				'points'=>'`#Extend your mount\'s buff rounds by %1 percent for %2 day(s).:percent:length'
			),
			'lodgetravel'=>'`#Extra travel.',
			'namecolorchange'=>array(
				'ninitialpoints'=>'`^C`$o`%l`@o`#u`^r`$i`%s`@e `#your name for the 1st time.',
				'nextrapoints'=>'`^C`$o`%l`@o`#u`^r`$i`%s`@e `#your name subsequent times.'
			),
			'namecolor'=>array(
				'initialpoints'=>'`^C`$o`%l`@o`#u`^r`$i`%s`@e `#your name for the 1st time.',
				'extrapoints'=>'`^C`$o`%l`@o`#u`^r`$i`%s`@e `#your name subsequent times.'
			),
			'namedmount'=>array(
				'initialpoints'=>'`#Name your mount the 1st time.',
				'extrapoints'=>'`#Name your mount subsequent times.'
			),
			'newdaybutton'=>'`#A newday button, grants newdays to the purchaser.',
			'ostrichlodge'=>array(
				'cost'=>'`#An Ostrich egg that will go into a powerful mount. Also requires %1 quest point(s).:qcost'
			),
			'racebarb'=>'`#Access to the Barbarian race. You gain more experience from forest fights, but less gold.',
			'racecat'=>'`#Access to the Felyne race. Increase in defence buff, increase of gems from forest fights, but less gold.',
			'racedarkelf'=>'`#Access to the Dark Elf race. Increase in defence buff.',
			'racedrow'=>'`#Access to the Drow race. Increase in health is dependant on favour.',
			'racefaer'=>'`#Access to the Faerie race. Increase in defence buff is dependant on charm.',
			'racegargoyle'=>'`#Access to Gargoyle race. Increase in defence buff, gain 1 PVP, but lose 25% forest fights.',
			'raceghoul'=>'`#Access to the Ghoul race. Increase in health is dependant on favour.',
			'racegiant'=>'`#Access to the Giant race. Increase in attack decrease in defence buff and 1 extra forest fight',
			'racegnome'=>'`#Access to the Gnome race. Decrease in attack buff, increase in health, increase of gems from forest fights.',
			'racegoblin'=>'`#Access to the Goblin race. Increase in attack and defence buff.',
			'racehalfelf'=>'`#Access to the Half Elf race. Increase in defence buff, 1 extra forest fight, but find less gold.',
			'racehalfling'=>'`#Access to Halfling race. Increase in defence buff, 1 less forest fight, but extra gold.',
			'raceimp'=>'`#Access to the Imp race. Increase in attack buff based on charm.',
			'racelzrd'=>'`#Access to the Lizardman race. Increase in attack buff.',
			'racepaladin'=>'`#Access to the Paladin race. Increase in attack buff and a +/- attack bonus in PVP depending on how evil they are.',
			'racesearinoa'=>'`#Access to the Searinoa race. Increase in attack buff.',
			'racestorm'=>'`#Access to the Storm Giant race. Increase in defence buff.',
			'racevampire'=>array(
				'cost'=>'`#Access to the Vampire race. Turns get halved, but you get %1 extra travel(s) and %2 extra PVP fight(s).:travel:pvp'
			),
			'racevik'=>'`#Access to the Viking race. Increase in attack buff, increase of gems from forest fights, but less gold.',
			'revivalpotion'=>array(
				'serumcost'=>'`#A vial of Revival Potion that will get you out of Shades.'
			),
			'revive'=>array(
				'serumcost'=>'`#A vial of Revival Potion that will get you out of Shades.'
			),
			'scrollofescape'=>array(
				'price'=>'`#Scroll of Escape. Helps you run away from fights that will get you killed.'
			),
			'smokebomb'=>'`#A smokebomb that will help you escape the thieves in the forest should you encounter them.',
			'specialtyalchemist'=>'`4Alchemist specialty.',
			'specialtyarcanerunes'=>'`6Arcane Runes specialty.',
			'specialtybard'=>'`5Bard Songs specialty.',
			'specialtybeastcall'=>'`@Beast Calling specialty.',
			'specialtychickenmage'=>'`6Chicken Mage specialty.',
			'specialtycleric'=>'`1Cleric Spells specialty.',
			'specialtydaemon'=>'`)Daemonic Powers specialty.',
			'specialtydruid'=>'`6Druid Spells specialty.',
			'specialtyelementalistskills'=>'`^Elementalist Skills specialty.',
			'specialtymagician'=>'`#Magician specialty.',
			'specialtymonkskills'=>'`QMonk Skills specialty.',
			'specialtypaladin'=>'`#Paladin Gifts specialty.',
			'specialtyranger'=>'`@Ranger Senses specialty.',
			'specialtyrhetoric'=>'`4Rhetoric Skills specialty.',
			'specialtyseductiveskills'=>'`^Seductive Skills specialty.',
			'specialtywarriorskills'=>'`^Warrior Skills specialty.',
			'sptocp'=>'`#Exchange lodge points for 1 Clan point.',
			'titlechange'=>array(
				'initialpoints'=>'`#Give yourself your own title.',
				'extrapoints'=>'`#Change your title subsequent times.'
			),
			'visalogin'=>'`#Login via a special link, even when the site is full.',
		);

		$sql = "SELECT a.modulename, a.setting, a.value
				FROM " . db_prefix('module_settings') . " AS a, " . db_prefix('modules') . " AS b, " . db_prefix('module_hooks') . " AS c
				WHERE a.modulename = b.modulename
					AND b.modulename = c.modulename
					AND c.location = 'pointsdesc'
					AND b.active = 1";
		$result = db_query($sql);

		$modulename = array();
		while( $row = db_fetch_assoc($result) )
		{
			$modulename[$row['modulename']][$row['setting']] = $row['value'];
		}

		$incentives = '500:`#Have a creature in the forest named after you.' . "\r\n";
		$incentives .= '1000:`#Have 5 creatures in the forest named by you.' . "\r\n";

		foreach( $text as $key => $value )
		{
			if( isset($modulename[$key]) )
			{
				if( is_array($value) )
				{
					foreach( $value as $key2 => $value2 )
					{
						if( ($pos = strpos($value2, ':')) !== FALSE )
						{
							$parts = explode(':', $value2);
							$search = $replace = array();
							for( $i=1; $i<count($parts); $i++ )
							{
								$search[] = '%'.$i;
								$replace[] = $modulename[$key][$parts[$i]];
							}
							$value2 = str_replace($search, $replace, $parts[0]);
						}
						$incentives .= $modulename[$key][$key2] . ':' . $value2 . "\r\n";
					}
				}
				else
				{
					$incentives .= $modulename[$key]['cost'] . ':' . $value . "\r\n";
				}
			}
		}

		set_module_setting('incentives',$incentives);
		set_module_setting('reset',0);
	}
?>