<?php
/**
	26/08/10 - v1.0.0
	+ Idea from the beautiful J.K. Rowling. :)
	+ Some settings and event code shamelessly borrowed from Sixf00t4's 'goldenegg' module.
	31/08/10 - v1.0.1
	+ Fixed quotes not being unslashed problem.
	+ Altered code to try and fix 'line-breaks/carriage-returns' problem in modules list.
	31/08/10 - v1.0.2
	+ Fixed pvpimmunity bug. Had setting instead of pref. :(
	+ Another attempt to fix '\r\n' problem. Possible mysql bug.
	+ Minor code changes.
	12/09/10 - v1.0.3
	+ Fixed an events bug. If a tunnel had a link in an events area then the link showed in the events.
	+ Added some continue links to the runevent code which I thought weren't needed.
	14/09/10 - v1.0.4
	+ Fixed 2 bugs in the 'newday-runonce' hook.
	+ Added 'delete_character' hook and code.
*/
function maraudersmap_getmoduleinfo()
{
	$info = array(
		"name"=>"The Marauder's Map",
		"description"=>"Based on the map from Harry Potter. Secret passageways to get about.",
		"version"=>"1.0.4",
		"author"=>"`@MarcTheSlayer`6, event code by `^Sixf00t4",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1438",
		"settings"=>array(
			"Map - Settings,title",
				"mapname"=>"Name of the map:,string,60|`3Marauder's Map`0",
				"mapowner"=>"Acctid of current map owner:,int",
				"expdays"=>"Taken from player after logged out for:,datelength|3 days",
				"maxuses"=>"Max uses per player (0 = unlimited)?,int|0",
				"`^If not unlimited&#44; use Lodge to offer more uses.,note",
				"maxused"=>"Current overall uses thus far:,int|0",
				"`^Current player only. Gets reset with each new player.,note",
				"maploc"=>"Which city is the map lost in?,location|".getsetting('villagename', LOCATION_FIELDS),
			"Hunter's Lodge,title",
				"cost"=>"Cost to buy more uses:,int|20",
				"lodgeuses"=>"Uses per purchase:,int|50",
				"`^If uses is zero (0) then the Lodge is disabled.,note",
			"Shades Tunnel,title",
				"`^`i`bShades secret tunnel is hard coded to the central village and is one way only.`b`i,note",
				"shadeuse"=>"Use this tunnel?,bool|1",
				"shadeuses"=>"Max uses to get out of Shades:,int|4",
				"`^Set to zero (0) for unlimited uses.,note",
				"shadeused"=>"Current shades uses thus far:,int|0",
				"`^Current player only. Gets reset with each new player.,note",
			"Event Odds,title",
				"`^`iMap owner will lose map with any of these events!`nSet to zero (0) to disable.`n`nIf you disable all of these then the only way to get the map is through PVP&#44; but first you'll have to give it to somebody.`i,note",
				"forest"=>"Chance of encounter in the forest:,range,0,100,1|75",
				"village"=>"Chance of encounter in the village:,range,0,100,1|25",
				"gardens"=>"Chance of encounter in the gardens:,range,0,100,1|25",
				"inn"=>"Chance of encounter in the inn:,range,0,100,1|25",
				"`^Will only lose map if they're drunk.,note",
				"travel"=>"Chance of encounter in travel:,range,0,100,1|0",
				"`^Requires 'cities' module.,note",
				"beach"=>"Chance of encounter at the beach:,range,0,100,1|0",
				"`^Requires 'beach' module.,note",
				"amusementpark"=>"Chance of encounter at the amusement park:,range,0,100,1|0",
				"`^Requires 'amusementpark' module.,note",
				"cellar"=>"Chance of encounter in the cellar:,range,0,100,1|0",
				"`^Requires 'cellar' module.,note",
				"darkalley"=>"Chance of encounter in the dark alley:,range,0,100,1|0",
				"`^Requires 'darkalley' module.,note",
			"Tunnel Data,title",
				"`^Use editor in the grotto to change this data.,note",
				"allprefs"=>"Tunnel allprefs data.,viewonly",
			"Module List,title",
				"`^This list of core files/modules is used to build the drop down menus in the editor.`nYou only need to have the ones that you're interested in and are in use. You can remove all others.`nEach file must be on its own line with no spaces before or after.,note",
				"modules"=>"List of modules:,textarearesizeable,40|forest\ngypsy\nhealer\ninn\nrock\nstables\namusementpark\nbakery\nbeach\nheidi\njail\njeweler\nlibrary\noldchurch\noldhouse\npetra",
		),
	);
	return $info;
}

function maraudersmap_install()
{
	if( is_module_active('maraudersmap') )
	{
		output("`c`b`QUpdating 'maraudersmap' Module.`0`b`c`n");
	}
	else
	{
		output("`c`b`QInstalling 'maraudersmap' Module.`0`b`c`n");
		maraudersmap_setexamples();
	}

	maraudersmap_sethooks();

	return TRUE;
}

function maraudersmap_uninstall()
{
	output("`n`c`b`Q'template' Module Uninstalled`0`b`c");
	return TRUE;
}

function maraudersmap_allprefs()
{
	$allprefs = @unserialize(get_module_setting('allprefs'));
	if( !is_array($allprefs) ) $allprefs = array();
	return $allprefs;
}

function maraudersmap_changeowner($acctid = 0, $swap = FALSE)
{
	set_module_setting('mapowner',$acctid);
	if( $acctid == 0 )
	{
		global $session;
		set_module_setting('maploc',$session['user']['location']);
	}
	if( $acctid == 0 || $swap == TRUE )
	{
		set_module_setting('shadeused',0);
		set_module_setting('maxused',0);
	}
}

function maraudersmap_setexamples()
{
	$examples = array(
		1=>array('use'=>1,'name1'=>'`V'.getsetting('innname', LOCATION_INN).'`0','door1'=>'inn','loc1'=>getsetting('villagename', LOCATION_FIELDS),'query1'=>'op=converse','name2'=>'`5Gypsy Seer`0','door2'=>'gypsy','loc2'=>getsetting('villagename', LOCATION_FIELDS),'query2'=>''),
		2=>array('use'=>1,'name1'=>"`EThe Veteran's Club`0",'door1'=>'rock','loc1'=>getsetting('villagename', LOCATION_FIELDS),'query1'=>'','name2'=>'`@The Forest`0','door2'=>'forest','loc2'=>getsetting('villagename', LOCATION_FIELDS),'query2'=>'')
	);
	set_module_setting('allprefs',serialize($examples));
}

function maraudersmap_buildlist()
{
	$modules = stripslashes(get_module_setting('modules'));
	$modules = str_replace("\r\n","\n", $modules);
	$modules = str_replace("\r","\n", $modules);
	$list_array = explode("\n", $modules);
	$list_built = '';
	sort($list_array);
	foreach( $list_array as $value )
	{
		$value = trim($value);
		$list_built .= ",$value,$value";
	}
	return $list_built;
}

function maraudersmap_sethooks($silent = FALSE)
{
	module_addeventhook('forest',"return get_module_setting('forest','maraudersmap');");
	module_addeventhook('village',"return get_module_setting('village','maraudersmap');");
	module_addeventhook('travel',"return get_module_setting('travel','maraudersmap');");
	module_addeventhook('inn',"return get_module_setting('inn','maraudersmap');");
	module_addeventhook('gardens',"return get_module_setting('gardens','maraudersmap');");
	module_addeventhook('beach',"return get_module_setting('beach','maraudersmap');");
	module_addeventhook('amusementpark',"return get_module_setting('amusementpark','maraudersmap');");
	module_addeventhook('cellar',"return get_module_setting('cellar','maraudersmap');");
	module_addeventhook('darkalley',"return get_module_setting('darkalley','maraudersmap');");
	module_addhook('changesetting');
	module_addhook('delete_character');
	module_addhook('newday-runonce');
	module_addhook('pvpwin');
	module_addhook('pvploss');
	module_addhook('lodge');
	module_addhook('lodge_incentives');
	module_addhook('pointsdesc');
	module_addhook('superuser');

	if( get_module_setting('shadeuse') == 1 ) module_addhook('ramiusfavors');

	$tunnels = maraudersmap_allprefs();
	$count = count($tunnels);
	$hooks = array();

	if( $count > 0 )
	{
		// Go through the tunnels and get the file names and add them as hooks.
		// $hooks array is just there to stop adding hooks you've just added.
		for( $i=1; $i<=$count; $i++ )
		{
			if( $tunnels[$i]['use'] == 1 )
			{
				for( $k=1; $k<=2; $k++ )
				{
					$hookname = 'footer-'.$tunnels[$i]["door$k"];
					if( !in_array($hookname, $hooks) )
					{
						module_addhook($hookname);
						$hooks[] = $hookname;
						if( $silent == FALSE ) output('`QTunnel hook `b%s`b added.`0`n', $hookname);
					}
				}
			}
		}
	}
	return TRUE;
}

function maraudersmap_dohook($hookname,$args)
{
	global $session;

	switch( $hookname )
	{
		case 'changesetting':
			if( $args['setting'] == 'villagename' )
			{
				$tunnels = maraudersmap_allprefs();
				$count = count($tunnels);
				if( $count > 0 )
				{
					for( $i=1; $i<=$count; $i++ )
					{
						if( $args['old'] == $tunnels[$i]['loc1'] ) $tunnels[$i]['loc1'] = $args['new'];
						if( $args['old'] == $tunnels[$i]['loc2'] ) $tunnels[$i]['loc2'] = $args['new'];
					}
					set_module_setting('allprefs',serialize($tunnels));
				}
				if( $args['old'] == get_module_setting('maploc') )
				{
					set_module_setting('maploc', $args['new']);
				}
			}
			if( $args['module'] == 'maraudersmap'  )
			{
				if( $args['setting'] == 'shadeuse' )
				{
					if( $args['new'] == 1 ) module_addhook('ramiusfavors');
					else module_drophook('ramiusfavors');
				}
			}
		break;

		case 'delete_character':
			if( $args['acctid'] == get_module_setting('mapowner') ) maraudersmap_changeowner();
		break;

		case 'newday-runonce':
			$mapowner = get_module_setting('mapowner');
			if( $mapowner != 0 )
			{
				$sql = "SELECT acctid, name, laston, loggedin
						FROM " . db_prefix('accounts') . "
						WHERE acctid = '$mapowner'";
				$res = db_query($sql);
				if( $row = db_fetch_assoc($res) )
				{
					if( (strtotime(get_module_setting('expdays'), strtotime($row['laston'])) < time()) || (is_module_active('pvpimmunity') && get_module_pref('check_willing','pvpimmunity') != 1) ) 
					{
						maraudersmap_changeowner();
						addnews('`&%s `^has abandoned the `&%s `^in %s!', $row['name'], get_module_setting('mapname'), $session['user']['location'], TRUE);
						debuglog("lost the ".get_module_setting('mapname')."`0 for being away for $expdays days or PVP immune.", $mapowner);
					}
					elseif( $row['loggedin'] == 0 )
					{
						addnews("`^Rumour has it that `&%s `^has the `&%s `^and that they're currently sleeping.`0", $row['name'], get_module_setting('mapname'), TRUE);
					}
				}
				else
				{
					maraudersmap_changeowner();
					addnews('`^The `&%s `^has been abandoned in %s!', get_module_setting('mapname'), $session['user']['location'], TRUE);
				}
			}
		break;

		case 'pvpwin':
			if( $args['badguy']['acctid'] == get_module_setting('mapowner') )
			{
				maraudersmap_changeowner($session['user']['acctid'], TRUE);
				output("`n`n`2You quickly search the body and find what you were looking for. The `@%s`2!`n", get_module_setting('mapname'));
				debuglog("won the marauder's map from ".$args['badguy']['creaturename']." in pvp.");
				addnews('`#%s `@now has the `#%s`@!', $session['user']['name'], get_module_setting('mapname'));
			}
		break;

		case 'pvploss':
			if( $session['user']['acctid'] == get_module_setting('mapowner') )
			{
				maraudersmap_changeowner($args['badguy']['acctid'], TRUE);
				output("`n`n`2Just before losing consiousness, you see `@%s `2take the `@%s `2from your pocket.`0`n", $args['badguy']['creaturename'], get_module_setting('mapname'));
				debuglog("lost the marauder's map to ".$session['user']['name']." in pvp.");
				addnews('`#%s `@now has the `#%s`@!', $args['badguy']['creaturename'], get_module_setting('mapname'));
			}
		break;

		case 'ramiusfavors':
			if( get_module_setting('mapowner') == $session['user']['acctid'] && get_module_setting('shadeuse') == 1 )
			{
				require_once('lib/sanitize.php');
				addnav(full_sanitize(get_module_setting('mapname')));
				addnav('Use Map','runmodule.php?module=maraudersmap&op=shades');
			}
		break;

		case 'lodge':
			if( get_module_setting('lodgeuses') > 0 && get_module_setting('maxuses') > 0 )
			{
				$cost = get_module_setting('cost');
				$points = translate_inline(array('point','points'));
				addnav('Use Points');
				addnav(array('%s`0 Uses (%s %s)',get_module_setting('mapname'), $cost, ($cost==1?$point[0]:$points[1])),'runmodule.php?module=maraudersmap&op=lodge&sop=buy');
			}
		break;

		case 'lodge_incentives':
			if( get_module_setting('lodgeuses') > 0 && get_module_setting('maxuses') > 0 )
			{
				$cost = get_module_setting('cost');
				$points = $args['points'];
				if( get_module_setting('lodgeuses') == 0 ) $points[$cost][] = translate(array("`#Unlimited use of the %s`#.", get_module_setting('mapname')));
				else $points[$cost][] = translate(array("`@%s `#extra uses for the %s`#.", get_module_setting('lodgeuses'), get_module_setting('mapname')));
				$args['points'] = $points;
			}
		break;

		case 'pointsdesc':
			if( get_module_setting('lodgeuses') > 0 && get_module_setting('maxuses') > 0 )
			{
				$cost = get_module_setting('cost');
				$args['count']++;
				if( get_module_setting('lodgeuses') == 0 ) $str = translate("Unlimited use of the %s`0 costs %s %s.");
				else $str = translate("Extra uses for the %s`0 costs %s %s.");
				$points = translate_inline($cost==1?'point':'points');
				$str = sprintf($str, get_module_setting('mapname'), $cost, $points);
				output($args['format'], $str, TRUE);
			}
		break;

		case 'superuser':
			if( $session['user']['superuser'] & SU_EDIT_MOUNTS )
			{
				addnav('Editors');
				addnav(array('%s`0 Editor',get_module_setting('mapname')),'runmodule.php?module=maraudersmap&op=editor');
			}
		break;

		default:
			// This default should pickup all the footer-$script hooks.
			if( $session['user']['acctid'] == get_module_setting('mapowner') && $session['user']['specialinc'] == '' && $session['user']['specialmisc'] == '' )
			{
				$tunnels = maraudersmap_allprefs();
				$count = count($tunnels);
				if( $count > 0 )
				{
					$link_text = translate_inline('Use map');
					require_once('lib/sanitize.php');
					addnav(full_sanitize(get_module_setting('mapname')));
					for( $i=1; $i<=$count; $i++ )
					{
						for( $k=1; $k<=2; $k++ )
						{	// Didn't want to duplicate the following IF statement for both entrance checks so used a for loop limited to 2. :)
							if( ('footer-'.$tunnels[$i]["door$k"]) == $hookname && $session['user']['location'] == $tunnels[$i]["loc$k"] )
							{
								$m = 1;
								if( $k == 1 ) $m = 2;
								if( !empty($tunnels[$i]["query$k"]) )
								{	// There's a query. Break it down into pairs.
									$pairs = explode('&', $tunnels[$i]["query$k"]);
									$count = count($pairs);
									$j = 0;
									foreach( $pairs as $pair )
									{	// Break pairs down to variable name and its value.
										list($key, $value) = explode('=', $pair);
										// Check to see if there's a match with the current url query. If there's a match increment $j.
										if( httpget($key) == $value ) $j++;
									}
									if( $count == $j )
									{	// If pair count and $j match then we're in the right place to display the tunnel link.
										$link_text = ( empty($tunnels[$i]["name$m"]) ) ? $link_text : stripslashes($tunnels[$i]["name$m"]);
										addnav($link_text.'`0',"runmodule.php?module=maraudersmap&op=footer{$tunnels[$i]["door$m"]}&sop=$i&top=$m");
									}
								}
								else
								{
									$link_text = ( empty($tunnels[$i]["name$m"]) ) ? $link_text : stripslashes($tunnels[$i]["name$m"]);
									addnav($link_text.'`0',"runmodule.php?module=maraudersmap&op=footer{$tunnels[$i]["door$m"]}&sop=$i&top=$m");
								}
							}
						}
					}
				}
			}
		break;
	}

	return $args;
}

function maraudersmap_runevent($type,$from)
{
	global $session;

	// Don't give the map to someone who can't pvp yet.
	if( $session['user']['age'] <= getsetting('pvpimmunity', 5) && $session['user']['dragonkills'] == 0 && $session['user']['pk'] == 0 && $session['user']['experience'] <= getsetting('pvpminexp', 1500) ) redirect('runmodule.php?module=maraudersmap&op=nopvpyet');
	// Don't give the map to someone who doesn't pvp.
	if( is_module_active('pvpimmunity') && get_module_pref('check_willing','pvpimmunity') != 1 ) redirect('runmodule.php?module=maraudersmap&op=nopvp');

	require_once('modules/maraudersmap/maraudersmap_runevent.php');
}

function maraudersmap_run()
{
	global $session;

	require_once('modules/maraudersmap/maraudersmap_run.php');
}
?>