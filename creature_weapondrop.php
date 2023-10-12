<?php
/**
	17/05/2013 - v1.0.0
	+ Based on the 'itemdrop' module by Turock.
*/
function creature_weapondrop_getmoduleinfo()
{
	$info = array(
		"name"=>"Creature Weapon and Armour Drop",
		"description"=>"Give each creature armour and a weapon which they can drop if you defeat them.",
		"version"=>"1.0.0",
		"author"=>"`@MarcTheSlayer",
		"category"=>"Forest",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1453",
		"settings"=>array(
			"Weapon/Armour Drop,title",
				"mindks"=>"Minimum DK's before weapon/armour drops:,int|10",
				"onlyonce"=>"Allow weapon/armour drop once per battle?,bool",
		),
		"prefs-creatures"=>array(
			"Weapon/Armour Drop,title",
				"weaponname"=>"Weapon Name:,string,40",
				"weaponatk"=>"Weapon Attack:,range,0,30,1",
				"weaponvalue"=>"Weapon Value:,int",
				"armourname"=>"Armour Name:,string,40",
				"armourdef"=>"Armour Defence:,range,0,30,1",
				"armourvalue"=>"Armour Value:,int",
				"`^Note: Leave the attack/defence/value blank to use player's current values.,note",
				"chance"=>"Chance for this drop? (%),range,1,100,1|5",
				"`^Note: Will be done twice&#44; once for the weapon and again for the armour. Try not to make this too high otherwise the player will be getting new gear too often.,note",
		)
	);
	return $info;
}

function creature_weapondrop_install()
{
	output("`c`b`Q%s 'creature_weapondrop' Module.`b`n`c", translate_inline(is_module_active('creature_weapondrop')?'Updating':'Installing'));
	module_addhook('battle-victory');
	return TRUE;
}

function creature_weapondrop_uninstall()
{
	output("`n`c`b`Q'creature_weapondrop' Module Uninstalled`0`b`c");
	return TRUE;
}

function creature_weapondrop_dohook($hookname,$args)
{
	global $session;

	if( $session['user']['dragonkills'] < get_module_setting('mindks') ) return $args;

	$weapon_runonce = $armour_runonce = FALSE;
	if( get_module_setting('onlyonce') == 1 )
	{
		static $weapon_runonce = FALSE;
		static $armour_runonce = FALSE;
	}

	if( $weapon_runonce == TRUE && $armour_runonce == TRUE ) return $args;

	$sql = "SELECT setting, value
			FROM " . db_prefix('module_objprefs') . "
			WHERE modulename = 'creature_weapondrop'
				AND objtype = 'creatures'
				AND objid = '{$args['creatureid']}'";
	$result = db_query($sql);
	$prefs = array('chance'=>5,'weaponname'=>'','weaponatk'=>0,'weaponvalue'=>0,'armourname'=>'','armordef'=>0,'armourvalue'=>0);
	while( $row = db_fetch_assoc($result) ) $prefs[$row['setting']] = $row['value'];

	if( $prefs['weaponname'] != '' && $weapon_runonce == FALSE )
	{
		if( mt_rand(1,100) <= $prefs['chance'] )
		{
			if( $prefs['weaponatk'] > $session['user']['weapondmg'] )
			{
				$session['user']['attack']-=$session['user']['weapondmg'];
				$session['user']['weapondmg'] = $prefs['weaponatk'];
				$session['user']['attack']+=$session['user']['weapondmg'];
				output("`n`3As the `#%s `3falls down dead, you're able to pick up their weapon, `#%s`3, which is better than your `#%s`3!`0`n`n", $args['creaturename'], $prefs['weaponname'], $session['user']['weapon']);
 			}
 			else output("`n`3As the `#%s `3falls down dead, you're able to pick up their weapon, `#%s`3, which looks cooler than your `#%s`3!`0`n`n", $args['creaturename'], $prefs['weaponname'], $session['user']['weapon']);
			if( $prefs['weaponvalue'] > 0 ) $session['user']['weaponvalue'] = $prefs['weaponvalue'];
			$session['user']['weapon'] = $prefs['weaponname'];
			$weapon_runonce = TRUE;
		}
	}
	if( $prefs['armourname'] != '' && $armour_runonce == FALSE )
	{
		if( mt_rand(1,100) <= $prefs['chance'] )
		{
			if( $prefs['armourdef'] > $session['user']['armordef'] )
			{
				$session['user']['defense']-=$session['user']['armordef'];
				$session['user']['armordef'] = $prefs['armordef'];
				$session['user']['defense']+=$session['user']['armordef'];
				output("`n`3As the `#%s `3falls down dead, you're able to pick up their armour, `#%s`3, which is better than your `#%s`3!`0`n`n", $args['creaturename'], $prefs['armourname'], $session['user']['armor']);
 			}
 			else output("`n`3As the `#%s `3falls down dead, you're able to pick up their armour, `#%s`3, which looks cooler than your `#%s`3!`0`n`n", $args['creaturename'], $prefs['armourname'], $session['user']['armor']);
			if( $prefs['armourvalue'] > 0 ) $session['user']['armorvalue'] = $prefs['armourvalue'];
			$session['user']['armor'] = $prefs['armourname'];
			$armour_runonce = TRUE;
		}
	}

	return $args;
}

function creature_weapondrop_run()
{
}
?>