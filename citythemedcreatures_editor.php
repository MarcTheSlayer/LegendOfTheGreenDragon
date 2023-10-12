<?php
/**
	23/08/09 - v0.0.4
	+ Fixed a bug where if the setting didn't have a default value you couldn't save data to it.

	A lot of this code was taken from the files creatures.php and modules.php
	and then modified to suit.
*/
function citythemedcreatures_editor_getmoduleinfo()
{
	$info = array(
		"name"=>"City Themed Creatures Editor",
		"description"=>"Basically the creatures editor but with city themed support.",
		"version"=>"0.0.4",
		"author"=>"`@MarcTheSlayer `2(and Dragonprime Development Team) for `LR`va`Lyn`LD`va`Lrr`ve`Ln",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?topic=10401.0",
		"requires"=>array(
			"citythemedcreatures"=>"20070202|By Sixf00t4, available on DragonPrime",
		),
		"settings"=>array(
			"Module Settings,title",
				"block"=>"Block creature editor grotto link?,bool"
		)
	);
	return $info;
}

function citythemedcreatures_editor_install()
{
	output("`c`b`Q%s 'citythemedcreatures_editor' Module.`b`n`c", translate_inline(is_module_active('citythemedcreatures_editor')?'Updating':'Installing'));
	module_addhook('superuser');
	return TRUE;
}

function citythemedcreatures_editor_uninstall()
{
	output("`n`c`b`Q'citythemedcreatures_editor' Module Uninstalled`0`b`c");
	return TRUE;
}

function citythemedcreatures_editor_dohook($hookname,$args)
{
	global $session;

	if( $session['user']['superuser'] & SU_EDIT_CREATURES )
	{
		addnav('Editors');
		addnav('Themed Creatures Editor','runmodule.php?module=citythemedcreatures_editor');
		if( get_module_setting('block') == 1 ) blocknav('creatures.php');
	}

	return $args;
}

function citythemedcreatures_editor_run()
{
	global $session;

	page_header('Creature Editor');

	$op = httpget('op');
	$id = httpget('creatureid');
	$level = httpget('level');
	$level = ( !empty($level) ) ? $level : 1;
	$location = httpget('loc');

	$from = 'runmodule.php?module=citythemedcreatures_editor';

	if( $op == 'save' )
	{
		$post = httpallpost();
		$id = httppost('creatureid');
		// We want to check for these table fields only. Anything else belongs to a module
		$fields_array = array('creaturename','creatureweapon','creaturewin','creaturelose','creaturelevel','forest','graveyard','creatureaiscript');
		$fields_array2 = array('creaturehealth','creatureattack','creaturedefense','creatureexp','creaturegold');

		$sql = "SELECT modulename
				FROM " . db_prefix('modules') . "
				WHERE infokeys
				LIKE '%|prefs-creatures|%'
				ORDER BY formalname";
		$result = db_query($sql);
		$module_array = array();
		while( $row = db_fetch_assoc($result) )
		{
			$module_array[] = $row['modulename'];
		}

		require_once('lib/creatures.php');
		for( $i=1; $i<18; $i++ )
		{
			$creaturestats[$i] = creature_stats($i);
		}
		$lev = (int)httppost('creaturelevel');
		reset($creaturestats[$lev]);

		if( !empty($id) )
		{
			$oldvalues = stripslashes(httppost('oldvalues'));
			$oldvalues = unserialize($oldvalues);
			unset($post['oldvalues'], $post['creatureid']);

			$sql = '';
			reset($post);
			while( list($key,$val) = each($post) )
			{
				if( in_array($key, $fields_array) )
				{
					$sql .= "$key = '$val', ";
					unset($post[$key], $oldvalues[$key]);
				}
			}
			while( list($key,$val) = each($creaturestats[$lev]) )
			{
				if( in_array($key, $fields_array2) )
				{
					$sql .= "$key = '".addslashes($val)."', ";
				}
			}
			$sql = rtrim($sql, ', ');
			$sql = "UPDATE " . db_prefix('creatures') . " SET " . $sql . " WHERE creatureid = '$id'";
			db_query($sql);
			debug($sql);
			if( db_affected_rows() > 0 )
			{
				output('`@Creature\'s main details have been successfully updated!`n');
			}
			else
			{
				output('`$Creature\'s main details have not changed!`n');
			}

			foreach( $module_array as $mkey => $modulename )
			{
				$len = strlen($modulename);
				foreach( $post as $key => $val )
				{
					if( substr($key,0,$len) == $modulename )
					{
						if( isset($oldvalues[$key]) && $oldvalues[$key] != $val )
						{
							$len2 = strlen($key);
							$keyname = substr($key,$len+1,$len2);
							set_module_objpref('creatures', $id, $keyname, $val, $modulename);
							output('`7Module: `&%s `7Setting: `&%s `7ObjectID: `&%s `7Value changed from `&%s `7to `&%s`7.`n', $modulename, $keyname, $id, $oldvalues[$key], $val);
							unset($post[$key], $oldvalues[$key]);
						}
					}
				}
			}
		}
		else
		{
			unset($post['oldvalues'], $post['creatureid']);

			$cols = array();
			$vals = array();

			reset($post);
			while( list($key,$val) = each($post) )
			{
				if( in_array($key, $fields_array) )
				{
					array_push($cols,$key);
					array_push($vals,$val);
					unset($post[$key]);
				}
			}
			while( list($key,$val) = each($creaturestats[$lev]) )
			{
				if( in_array($key, $fields_array2) )
				{
					array_push($cols,$key);
					array_push($vals,$val);
				}
			}
			$sql = "INSERT INTO " . db_prefix('creatures') . " (" . join(",",$cols) . ",createdby) VALUES (\"".join("\",\"",$vals)."\",'" . addslashes($session['user']['login']) . "')";
			db_query($sql);
			debug($sql);
			$id = db_insert_id();
			if( db_affected_rows() > 0 )
			{
				output('`@Creature was successfully saved!`n');
			}
			else
			{
				output('`$Creature was NOT saved!`n');
			}

			foreach( $module_array as $mkey => $modulename )
			{
				$len = strlen($modulename);
				foreach( $post as $key => $val )
				{
					if( substr($key,0,$len) == $modulename )
					{
						$len2 = strlen($key);
						$keyname = substr($key,$len+1,$len2);
						set_module_objpref('creatures', $id, $keyname, $val, $modulename);
						output('`7Module: `&%s `7Setting: `&%s `7ObjectID: `&%s `7Value: `&%s`7.`n', $modulename, $keyname, $id, $val);
						unset($post[$key]);
					}
				}
			}
		}

		addnav('Editor');
		addnav('Re-Edit Creature',$from.'&op=edit&creatureid='.$id.'&level='.$level.'&loc='.$location);
		addnav('Previous Page',$from.'&level='.$level.'&loc='.$location);
		addnav('Main Page',$from);
		if( $level <= 16 )
		{
			addnav('Add a creature',$from.'&op=add&level='.$lev.'&loc='.$location);
		}
	}
	elseif( $op == 'del' )
	{
		db_query("DELETE FROM " . db_prefix('creatures') . " WHERE creatureid = '$id'");
		if( db_affected_rows() > 0 )
		{
			output('`@Creature successfully deleted.`0`n`n');
		}
		else
		{
			output('Creature not deleted: %s', db_error(LINK));
		}

		addnav('Editor');
		addnav('Previous Page',$from.'&level='.$level.'&loc='.$location);
		addnav('Main Page',$from);
		if( $level <= 16 )
		{
			addnav('Add a creature',$from.'&op=add&level='.$level.'&loc='.$location);
		}
	}
	elseif( $op == 'add' || $op == 'edit' )
	{
		$row = array('creatureid'=>0,'creaturelevel'=>$level);
		if( !empty($id) )
		{
			$sql = "SELECT *
					FROM " . db_prefix('creatures') . "
					WHERE creatureid = '$id'";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			if( db_num_rows($result) <> 1 )
			{
				output('`$Error: That creature was not found!');
				$id = 0;
			}
		}

		$form = array(
			"Creature Properties,title",
			"creatureid"=>"Creature id,hidden",
			"creaturename"=>"Creature Name",
			"creatureweapon"=>"Weapon",
			"creaturewin"=>"Win Message (Displayed when the creature kills the player)",
			"creaturelose"=>"Death Message (Displayed when the creature is killed by the player)",
			"creaturelevel"=>"Level,range,1,18,1",
			"forest"=>"Creature is in forest?,bool",
			"graveyard"=>"Creature is in graveyard?,bool",
			"creatureaiscript"=>"Creature's A.I.,textarearesizeable,40",
		);

		$sql = "SELECT formalname, modulename
				FROM " . db_prefix('modules') . "
				WHERE infokeys
				LIKE '%|prefs-creatures|%'
				ORDER BY formalname";
		$result = db_query($sql);
		while( $row2 = db_fetch_assoc($result) )
		{
			$formalname = $row2['formalname'];
			$modulename = modulename_sanitize($row2['modulename']);
			$modulefilename = "modules/{$modulename}.php";
			if( file_exists($modulefilename) )
			{
				require_once($modulefilename);
				$fname = $modulename.'_getmoduleinfo';
				if( function_exists($fname) )
				{
					$info = $fname();
					if( count($info['prefs-creatures']) > 0 )
					{
						$form[] = $formalname.',title'; // Each module gets its own title.
						while( list($key, $val) = each($info['prefs-creatures']) )
						{
							if( ($pos = strpos($val, ',title')) !== FALSE )
							{	// Any titles get converted to notes.
								$val = '`^`i'.str_replace(',title', '`i,note', $val);
							}
							if( is_array($val) )
							{
								$v = $val[0];
								$x = explode("|", $v);
								$val[0] = $x[0];
								$x[0] = $val;
							}
							else
							{
								$x = explode("|", $val);
							}
							$form[$modulename.'-'.$key] = $x[0];
							// Set up default values.
							$row[$modulename.'-'.$key] = ( isset($x[1]) ) ? $x[1] : '';
						}

						$sql = "SELECT setting, value
								FROM " . db_prefix('module_objprefs') . "
								WHERE modulename = '$modulename'
									AND objtype = 'creatures'
									AND objid = '$id'";
						$result2 = db_query($sql);
						while( $row3 = db_fetch_assoc($result2) )
						{
							$row[$modulename.'-'.$row3['setting']] = $row3['value'];
						}
					}
				}
			}
		}

		require_once('lib/showform.php');
		rawoutput('<form action="'.$from.'&op=save&level='.$level.'&loc='.$location.'" method="POST">');
		addnav('',$from.'&op=save&level='.$level.'&loc='.$location);
		showform($form, $row);
		rawoutput('<input type="hidden" name="oldvalues" value="'.htmlentities(serialize($row), ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" /></form>');

		addnav('Editor');
		addnav('Previous Page',$from.'&level='.$level.'&loc='.$location);
		addnav('Main Page',$from);
		if( $level <= 16 )
		{
			addnav('Add a creature',$from.'&op=add&level='.$level.'&loc='.$location);
		}
	}
	else
	{
		$q = httppost('q');
		$search = translate_inline('Search');
		rawoutput('<form action="'.$from.'&subop=search" method="POST">');
		addnav('',$from.'&subop=search');
		output('`2Search by field: ');
		rawoutput('<input type="text" name="q" value="'.$q.'" />');
		rawoutput('<input type="submit" class="button" value="'.$search.'"></form><br />');

		$subop = httpget('subop');
		if( $subop == 'search' )
		{
			addnav('Editor');
			addnav('Previous Page',$from.'&level='.$level.'&loc='.$location);
			addnav('Main Page',$from);
			if( $level <= 16 )
			{
				addnav('Editor');
				addnav('Add a creature',$from.'&op=add&level='.$level.'&loc='.$location);
			}
			$where = "creaturename LIKE '%$q%' OR creatureweapon LIKE '%$q%' OR creaturewin LIKE '%$q%' OR creaturelose LIKE '%$q%' OR createdby LIKE '%$q%'";
		}
		else
		{
			if( $level <= 16 )
			{
				addnav('Editor');
				addnav('Add a creature',$from.'&op=add&level='.$level.'&loc='.$location);
			}
			$where = "creaturelevel = '$level'";

			$vloc = array();
			$vname = getsetting('villagename', LOCATION_FIELDS);
			$vloc[$vname] = 'village';
			$vloc['all'] = 1;
			$vloc = modulehook('validlocation', $vloc);
			unset($vloc['all']);
			reset($vloc);

			addnav('Levels');
			$sql = "SELECT count(creatureid) AS n, creaturelevel
					FROM " . db_prefix('creatures') . "
					GROUP BY creaturelevel
					ORDER BY creaturelevel";
			$result = db_query($sql);
			while( $row = db_fetch_assoc($result) )
			{
				addnav(array('Level %s: (%s creatures)', $row['creaturelevel'], $row['n']),$from.'&level='.$row['creaturelevel']);
				if( $level == $row['creaturelevel'] )
				{
					foreach( $vloc as $loc => $val )
					{
						addnav(array('- %s',$loc),$from.'&level='.$row['creaturelevel'].'&loc='.$loc);
					}
					addnav('- No Location Set',$from.'&level='.$row['creaturelevel'].'&loc=notset');
				}
			}
		}

		$sql = "SELECT setting, objid, value
				FROM " . db_prefix('module_objprefs') . "
				WHERE modulename = 'citythemedcreatures'
					AND objtype = 'creatures'";
		$result = db_query($sql);
		$creature_array = array();
		while( $row = db_fetch_assoc($result) )
		{
			$creature_array[$row['objid']][$row['setting']] = $row['value'];
		}

		$opshead = translate_inline('Ops');
		$idhead = translate_inline('ID');
		$name = translate_inline('Name');
		$lev = translate_inline('Level');
		$loc = translate_inline('Location');
		$forest = translate_inline('Forest');
		$grave = translate_inline('Graveyard');
		$edit = translate_inline('Edit');
		$confirm = translate_inline('Are you sure you wish to delete this creature?');
		$del = translate_inline('Del');
		$all = translate_inline('All');
		$notset = translate_inline('Not Set');
		$yesno = translate_inline(array('Yes','No'));

		rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999'>");
		rawoutput("<tr class=\"trhead\"><td>$opshead</td><td>$idhead</td><td>$name</td><td>$lev</td><td align=\"center\">$loc</td><td>$forest</td><td>$grave</td></tr>");

		$sql = "SELECT *
				FROM " . db_prefix('creatures') . "
				WHERE $where
				ORDER BY creaturelevel, creaturename";
		$result = db_query($sql);

		$i = 0;
		while( $row = db_fetch_assoc($result) )
		{
			if( !empty($location) )
			{
				if( $location == 'notset' && !isset($creature_array[$row['creatureid']]) )
				{
					rawoutput('<tr class="'.($i%2==0?'trdark':'trlight').'"><td>[ <a href="'.$from.'&op=edit&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location.'">'.$edit.'</a> | <a href="'.$from.'&op=del&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location.'" onClick="return confirm(\''.$confirm.'\');">'.$del.'</a> ]</td><td>');
					addnav('',$from.'&op=edit&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location);
					addnav('',$from.'&op=del&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location);
					output_notl('%s', $row['creatureid']);
					rawoutput('</td><td>');
					output_notl('%s', $row['creaturename']);
					rawoutput('</td><td>');
					output_notl('%s', $row['creaturelevel']);
					rawoutput('</td><td align="center">');
					output_notl('%s', $notset);
					rawoutput('</td><td align="center">');
					output_notl('%s', ($row['forest']==1?$yesno[0]:$yesno[1]));
					rawoutput('</td><td align="center">');
					output_notl('%s', ($row['graveyard']==1?$yesno[0]:$yesno[1]));
					rawoutput('</td></tr>');
				}
				elseif( $location == $creature_array[$row['creatureid']]['creatureloc'] )
				{
					rawoutput('<tr class="'.($i%2==0?'trdark':'trlight').'"><td>[ <a href="'.$from.'&op=edit&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location.'">'.$edit.'</a> | <a href="'.$from.'&op=del&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location.'" onClick="return confirm(\''.$confirm.'\');">'.$del.'</a> ]</td><td>');
					addnav('',$from.'&op=edit&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location);
					addnav('',$from.'&op=del&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location);
					output_notl('%s', $row['creatureid']);
					rawoutput('</td><td>');
					output_notl('%s', $row['creaturename']);
					rawoutput('</td><td>');
					output_notl('%s', $row['creaturelevel']);
					rawoutput('</td><td align="center">');
					output_notl('%s', $location);
					rawoutput('</td><td align="center">');
					output_notl('%s', ($row['forest']==1?$yesno[0]:$yesno[1]));
					rawoutput('</td><td align="center">');
					output_notl('%s', ($row['graveyard']==1?$yesno[0]:$yesno[1]));
					rawoutput('</td></tr>');
				}
			}
			else
			{
				$locname = ( isset($creature_array[$row['creatureid']]) ) ? (( $creature_array[$row['creatureid']]['creatureanywhere'] == 1 ) ? $all : $creature_array[$row['creatureid']]['creatureloc']) : $notset;
				rawoutput('<tr class="'.($i%2==0?'trdark':'trlight').'"><td>[ <a href="'.$from.'&op=edit&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location.'">'.$edit.'</a> | <a href="'.$from.'&op=del&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location.'" onClick="return confirm(\''.$confirm.'\');">'.$del.'</a> ]</td><td>');
				addnav('',$from.'&op=edit&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location);
				addnav('',$from.'&op=del&creatureid='.$row['creatureid'].'&level='.$row['creaturelevel'].'&loc='.$location);
				output_notl('%s', $row['creatureid']);
				rawoutput('</td><td>');
				output_notl('%s', $row['creaturename']);
				rawoutput('</td><td align="center">');
				output_notl('%s', $row['creaturelevel']);
				rawoutput('</td><td align="center">');
				output_notl('%s', $locname);
				rawoutput('</td><td align="center">');
				output_notl('%s', ($row['forest']==1?$yesno[0]:$yesno[1]));
				rawoutput('</td><td align="center">');
				output_notl('%s', ($row['graveyard']==1?$yesno[0]:$yesno[1]));
				rawoutput('</td></tr>');
			}
			$i++;
		}
		rawoutput('</table><br /><br />');

		output('`2A location of `@Not Set `2just means that a location hasn\'t been set yet. In these cases the creature defaults to all forest locations.');
	}

	addnav('Navigation');
	require_once('lib/superusernav.php');
	superusernav();

	page_footer();
}
?>