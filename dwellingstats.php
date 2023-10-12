<?php
/**
	Rewritten by MarcTheSlayer
	16/04/08 - v1.3
	+ Rewrote the code to display individual stats for each dwelling owned.
	+ Added the option to disable this in the users preferences.

	02/08/09 - v1.4
	+ Tidied up the code. Tried to make it cleaner with less sql queries.
	- Removed the get_module_objpref() function calls as there were too
	  many and with multiple dwellings these would add up. Used 3 queries
	  instead to get the data from the module_objprefs table. Saved at
	  least 10 queries per owned dwelling.
*/
function dwellingstats_getmoduleinfo()
{
	$info = array(
		"name"=>"Dwelling Stats Display",
		"description"=>"Display your dwellings, what's in each coffer, if you they have a guard and the upkeep for each on your stats.",
		"version"=>"1.4",
		"author"=>"SexyCook`2, Rewritten by `@MarcTheSlayer",
		"category"=>"Stat Display",
		"download"=>"http://dragonprime.net/index.php?topic=10360.0",
		"requires"=>array(
			"dwellings"=>"20060105|By Sixf00t4, available on DragonPrime",
			"dwellings_pvp"=>"1.2|By Chris Vorndran, available on DragonPrime",
			"upkeep"=>"20061009|By Sixf00t4, available on DragonPrime"
		),
		"prefs"=>array(
			"Dwelling Stats,title",
			"If you don't own a dwelling then it's safe to ignore this.,note",
			"user_stat"=>"Enable your dwelling information in your stats area?,bool"
		)
 	);
	return $info;
}

function dwellingstats_install()
{
	output("`c`b`Q%s 'dwellingstats' Module.`b`n`c", translate_inline(is_module_active('dwellingstats')?'Updating':'Installing'));
	module_addhook('charstats');
	return TRUE;
}

function dwellingstats_uninstall()
{
	output("`n`c`b`Q'dwellingstats' Module Uninstalled`0`b`c");
	return TRUE;
}

function dwellingstats_dohook($hookname,$args)
{
	if( get_module_pref('user_stat') == 1 )
	{
		global $session;

		$sql = "SELECT dwid, gold, gems, type, location
				FROM " . db_prefix('dwellings') . "
				WHERE ownerid = '" . $session['user']['acctid'] . "'
					AND status = 1";
		$result = db_query($sql);

		if( db_num_rows($result) > 0 )
		{
			$type = $gold = $gems = $guard = $days = '';
			$type_array = array();

			$none = translate_inline('None');
			$hours_left = translate_inline('hours left');
			$exempt = translate_inline('Exempt');
			$days_left = translate_inline(array('day','days left'));

			// Hours in a game day.
			$hours = 24 / getsetting('daysperday', '4');

			while( $row = db_fetch_assoc($result) )
			{
				if( !isset($type_array[$row['type']]['dwname']) )
				{
					$type_array[$row['type']]['dwname'] = appoencode(get_module_setting('dwname',$row['type']));
				}

				$type .= '<span title="' . $row['location'] . '"><span>' . $type_array[$row['type']]['dwname'] . '</span></span><br />';
				$gold .= ( $row['gold'] != 0 ) ? $row['gold'] . '<br />' : '0<br />';
				$gems .= ( $row['gems'] != 0 ) ? $row['gems'] . '<br />' : '0<br />';

				$sql = "SELECT setting, value FROM " . db_prefix('module_objprefs') . "
						WHERE modulename = 'dwellings_pvp'
							AND objtype = 'dwellings'
							AND objid = '" . $row['dwid'] . "' ";
				$result2 = db_query($sql);
				$guards_array = array();
				while( $row2 = db_fetch_assoc($result2) )
				{
					$guards_array[$row2['setting']] = $row2['value'];
				}

				if( $guards_array['bought'] >= 1 )
				{	// Coded for 1 guard per dwelling.
					if( $guards_array['run-out'] < 0 )
					{
						$guard .= $none;
					}
					elseif( $guards_array['run-out'] == 0 )
					{	// Guard is about to run out so make the text red.
						$guard .= ' <span class="colLtRed">' . ceil($hours) . ' ' . $hours_left . '</span>';
					}
					else
					{
						$guard .= $guards_array['run-out'] . ' ' . ($guards_array['run-out']==1?$days_left[0]:$days_left[1]);
					}
				}
				else
				{
					$guard .= $none;
				}
				$guard .= '<br />';

				if( !isset($type_array[$row['type']]['id']) )
				{
					$type_array[$row['type']]['id'] = get_module_setting('typeid',$row['type']);
				}
				$type_id = $type_array[$row['type']]['id'];

				if( !isset($type_array[$row['type']]['useupkeep']) )
				{	// Grab upkeep data for this type. Put in array incase it's needed again.
					$sql = "SELECT setting, value
							FROM " . db_prefix('module_objprefs') . "
							WHERE modulename = 'upkeep'
								AND objtype = 'dwellingtypes'
								AND objid = '$type_id' ";
					$result2 = db_query($sql);
					while( $row2 = db_fetch_assoc($result2) )
					{
						$type_array[$row['type']][$row2['setting']] = $row2['value'];
					}
				}
				// Grab upkeep data for this dwelling. Only required once.
				$sql = "SELECT setting, value
						FROM " . db_prefix('module_objprefs') . "
						WHERE modulename = 'upkeep'
							AND objtype = 'dwellings'
							AND objid = '" . $row['dwid'] . "' ";
				$result2 = db_query($sql);
				$dwid_array = array();
				while( $row2 = db_fetch_assoc($result2) )
				{
					$dwid_array[$row2['setting']] = $row2['value'];
				}

				// Check for individual exemption, or overall master exemption.
				if( $dwid_array['exempt'] == 1 || !$type_array[$row['type']]['useupkeep'] )
				{
					$days .= $exempt;
				}
				else
				{
					$turnsleft = $type_array[$row['type']]['upkeepturns'] - $dwid_array['upkeepturns'];
					$gemsleft = $type_array[$row['type']]['upkeepgems'] - $dwid_array['upkeepgems'];
					$goldleft = $type_array[$row['type']]['upkeepgold'] - $dwid_array['upkeepgold'];
					$end_in = $type_array[$row['type']]['upkeepdays'] - $dwid_array['upkeepdays'];

					if( $goldleft == 0 && $gemsleft == 0 && $turnsleft == 0 )
					{
						$days .= ( $end_in != 0 ) ? $end_in . ' ' . ($end_in==1?$days_left[0]:$days_left[1]) : ceil($hours) . ' ' . $hours_left;
					}
					else
					{	// Still got payments to make so make text red.
						$days .= ( $end_in != 0 ) ? '<span class="colLtRed">' . $end_in . ' ' . ($end_in==1?$days_left[0]:$days_left[1]) . '</span>' : '<span class="colLtRed">' . $end_in . ' ' . $hours_left . '</span>';
					}
				}
				$days .= '<br />';
			}

			unset($type_array, $dwid_array, $guards_array);

			addcharstat('Dwellings');
			addcharstat('Dwelling Type', $type);
			addcharstat('Gold in Coffers', $gold);
			addcharstat('Gems in Coffers', $gems);
			addcharstat('Guard on Duty', '<span style="font-size:85%">' . $guard . '</span>');
			addcharstat('Upkeep Left', '<span style="font-size:85%">' . $days . '</span>');
		}
	}

	return $args;
}

function dwellingstats_run()
{
}
?>