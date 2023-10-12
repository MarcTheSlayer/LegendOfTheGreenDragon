<?php
/**
	Code was taken from the files creatures.php, modules.php
	and mounts.php and then modified to suit.
*/
function alternative_mounts_editor_getmoduleinfo()
{
	$info = array(
		"name"=>"Alternative Mounts Editor",
		"description"=>"An alternative mounts editor.",
		"version"=>"0.0.1",
		"author"=>"`@MarcTheSlayer `2and Dragonprime Development Team",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?topic=10407.0",
		"settings"=>array(
			"Module Settings,title",
				"defcat"=>"Default mount category:,|Horses",
				"block"=>"Block mounts editor grotto link?,bool",
				"lastcat"=>"Last mount category looked at:,viewonly"
		)
	);
	return $info;
}

function alternative_mounts_editor_install()
{
	output("`c`b`Q%s 'alternative_mounts_editor' Module.`b`n`c", translate_inline(is_module_active('alternative_mounts_editor')?'Updating':'Installing'));
	module_addhook('superuser');
	module_addhook('footer-user');
	return TRUE;
}

function alternative_mounts_editor_uninstall()
{
	output("`n`c`b`Q'alternative_mounts_editor' Module Uninstalled`0`b`c");
	return TRUE;
}

function alternative_mounts_editor_dohook($hookname,$args)
{
	global $session;

	if( $session['user']['superuser'] & SU_EDIT_MOUNTS )
	{
		switch( $hookname )
		{
			case 'superuser':
				addnav('Editors');
				addnav('Alt Mounts Editor','runmodule.php?module=alternative_mounts_editor');
				if( get_module_setting('block') == 1 ) blocknav('mounts.php');
			break;

			case 'footer-user':
				$op = httpget('op');
				$cat = get_module_setting('lastcat');
				if( !empty($op) && $op != 'search' && !empty($cat) )
				{
					addnav('Navigation');
					addnav('Return to Mount Editor','runmodule.php?module=alternative_mounts_editor&cat='.$cat);
				}
			break;
		}
	}

	return $args;
}

function alternative_mounts_editor_run()
{
	global $session;

	page_header('Mount Editor');

	if( httpget('lastcat') == 1 )
	{	// Finished with the user editor.
		set_module_setting('lastcat','');
	}

	$op = httpget('op');
	$id = httpget('mountid');
	$cat = httpget('cat');
	$cat = ( !empty($cat) ) ? $cat : get_module_setting('defcat');

	$from = 'runmodule.php?module=alternative_mounts_editor';

	if( $op == 'save' )
	{
		//
		// Save submitted mount data.
		//
		$post = httpallpost();
		$id = httppost('mountid');
		// These fields are the ones we want so there can be no mistake.
		$field_array = array('mountactive','mountname','mountdesc','mountcategory','mountlocation','mountdkcost','mountcostgems','mountcostgold','mountfeedcost','mountforestfights','newday','recharge','partrecharge');
		$field_array2 = array('mbuffname','mbuffroundmsg','mbuffwearoff','mbuffeffectmsg','mbuffeffectnodmgmsg','mbuffeffectfailmsg','mbuffrounds','mbuffallowinpvp','mbuffallowintrain','mbuffatkmod','mbuffdefmod','mbuffinvulnerable','mbuffregen','mbuffminioncount','mbuffminbadguydamage','mbuffmaxbadguydamage','mbuffmingoodguydamage','mbuffmaxgoodguydamage','mbufflifetap','mbuffdamageshield','mbuffbadguydmgmod','mbuffbadguyatkmod','mbuffbadguydefmod');
		$mount = array();

		$sql = "SELECT modulename
				FROM " . db_prefix('modules') . "
				WHERE infokeys
				LIKE '%|prefs-mounts|%'
				ORDER BY formalname";
		$result = db_query($sql);
		$module_array = array();
		while( $row = db_fetch_assoc($result) )
		{
			$module_array[] = $row['modulename'];
		}

		if( !empty($id) )
		{
			//
			// An existing mount.
			//
			$oldvalues = stripslashes(httppost('oldvalues'));
			$oldvalues = unserialize($oldvalues);
			unset($post['oldvalues'], $post['mountid']);

			//
			// Deal with the mount table data first.
			//
			$sql = '';
			reset($post);
			while( list($key,$val) = each($post) )
			{
				if( in_array($key, $field_array) )
				{
					if( $key == 'mountcategory' ) $val = color_sanitize($val);
					$sql .= "$key = '".addslashes($val)."', ";
					unset($post[$key], $oldvalues[$key]);
				}
				if( in_array($key, $field_array2) )
				{
					if( !empty($val) )
					{
						$len = strlen($key);
						$keyname = substr($key,5,$len);
						$mount[$keyname] = $val;
					}
					unset($post[$key], $oldvalues[$key]);
				}
			}
			$mount['schema'] = 'mounts';
			$sql .= "mountbuff = '".addslashes(serialize($mount))."'";
			$sql = "UPDATE " . db_prefix('mounts') . " SET " . $sql . " WHERE mountid = '$id'";
			db_query($sql);
			invalidatedatacache("mountdata-$id");
			if( db_affected_rows() > 0 )
			{
				output('`@Creature\'s main details have been successfully updated!`n');
			}
			else
			{
				output('`$Creature\'s main details have not changed!`n');
			}
			//
			// Now deal with the different module data.
			//
			foreach( $module_array as $mkey => $modulename )
			{
				$len = strlen($modulename);
				foreach( $post as $key => $val )
				{
					if( substr($key,0,$len) == $modulename )
					{
						if( isset($oldvalues[$key]) && $oldvalues[$key] != $val )
						{
							//
							// Only take data that has been changed.
							//
							$len2 = strlen($key);
							$keyname = substr($key,$len+1,$len2);
							set_module_objpref('mounts', $id, $keyname, $val, $modulename);
							output('`7Module: `&%s `7Setting: `&%s `7ObjectID: `&%s `7Value changed from `&%s `7to `&%s`7.`n', $modulename, $keyname, $id, $oldvalues[$key], $val);
							unset($post[$key], $oldvalues[$key]);
						}
					}
				}
			}
		}
		else
		{
			//
			// A new mount has been submitted.
			//
			unset($post['oldvalues'], $post['mountid']);

			//
			// Deal with the mount table data first.
			//
			$cols = array();
			$vals = array();

			reset($post);
			while( list($key,$val) = each($post) )
			{
				if( in_array($key, $field_array) )
				{
					array_push($cols,$key);
					array_push($vals,addslashes($val));
					unset($post[$key]);
				}
				if( in_array($key, $field_array2) )
				{
					if( !empty($val) )
					{
						$len = strlen($key);
						$keyname = substr($key,5,$len);
						$mount[$keyname] = $val;
					}
					unset($post[$key]);
				}
			}
			$mount['schema'] = 'mounts';
			array_push($cols, 'mountbuff');
			array_push($vals, addslashes(serialize($mount)));
			$sql = "INSERT INTO " . db_prefix('mounts') . " (" . join(",",$cols) . ") VALUES (\"" . join("\",\"",$vals) . "\")";
			db_query($sql);
			$id = db_insert_id();
			if( db_affected_rows() > 0 )
			{
				output('`@Mount was successfully saved!`n');
			}
			else
			{
				output('`$Mount was NOT saved!`n');
			}
			//
			// Now deal with the different module data.
			//
			foreach( $module_array as $mkey => $modulename )
			{
				$len = strlen($modulename);
				foreach( $post as $key => $val )
				{
					if( substr($key,0,$len) == $modulename )
					{
						$len2 = strlen($key);
						$keyname = substr($key,$len+1,$len2);
						set_module_objpref('mounts', $id, $keyname, $val, $modulename);
						output('`7Module: `&%s `7Setting: `&%s `7ObjectID: `&%s `7Value: `&%s`7.`n', $modulename, $keyname, $id, $val);
						unset($post[$key]);
					}
				}
			}
		}

		addnav('Editor');
		addnav('Re-Edit Mount',$from.'&op=edit&cat='.$cat.'&mountid='.$id);
		addnav('Add a Mount',$from.'&op=add&cat='.$cat);
		addnav('Previous Page',$from.'&cat='.$cat);
		addnav('Main Page',$from);
	}
	elseif( $op == 'deactivate' )
	{
		db_query("UPDATE " . db_prefix('mounts') . " SET mountactive = 0 WHERE mountid = '$id'");
		invalidatedatacache("mountdata-$id");

		output('`n`2Mount `@%s `2has been `@Deactivated`2.`0`n', $id);

		addnav('Editor');
		addnav('Previous Page',$from.'&cat='.$cat);
	}
	elseif( $op == 'activate' )
	{
		db_query("UPDATE " . db_prefix('mounts') . " SET mountactive = 1 WHERE mountid = '$id'");
		invalidatedatacache("mountdata-$id");

		output('`n`2Mount `@%s `2has been `@Activated`2.`0`n', $id);

		addnav('Editor');
		addnav('Previous Page',$from.'&cat='.$cat);
	}
	elseif( $op == 'del' )
	{
		//
		// Delete a mount and refund anyone the current cost.
		//
		$sql = "SELECT mountcostgems, mountcostgold
				FROM " . db_prefix('mounts') . "
				WHERE mountid = '$id'";
		$result = db_query_cached($sql, "mountdata-$id", 3600);
		$row = db_fetch_assoc($result);
		db_query("UPDATE " . db_prefix('accounts') . " SET gems = gems+{$row['mountcostgems']}, goldinbank = goldinbank+{$row['mountcostgold']}, hashorse = 0 WHERE hashorse = '$id'");
		db_query("DELETE FROM " . db_prefix('mounts') . " WHERE mountid = '$id'");
		if( db_affected_rows() > 0 )
		{
			output('`n`@Mount successfully deleted.`0`n`n');
		}
		else
		{
			output('`n`$Mount not deleted: %s.`0`n`n', db_error(LINK));
		}
		module_delete_objprefs('mounts', $id);
		invalidatedatacache("mountdata-$id");

		addnav('Editor');
		addnav('Previous Page',$from.'&cat='.$cat);
	}
	elseif( $op == 'give' )
	{
		//
		// Give yourself a mount, allows for testing.
		//
		$session['user']['hashorse'] = $id;
		$sql = "SELECT mountname, mountbuff
				FROM " . db_prefix('mounts') . "
				WHERE mountid = '$id'";
		$result = db_query_cached($sql, "mountdata-$id", 3600);
		$row = db_fetch_assoc($result);
		$buff = unserialize($row['mountbuff']);
		if( $buff['schema'] == '' ) $buff['schema'] = 'mounts';
		apply_buff('mount',$buff);

		output('`n`2You have given yourself a `@%s`2.`0`n`n', $row['mountname']);

		addnav('Editor');
		addnav('Previous Page',$from.'&cat='.$cat);
	}
	elseif( $op == 'owners' )
	{
		//
		// Show a list of players who have a certain mount.
		//
		$sql = "SELECT mountname
				FROM " . db_prefix('mounts') . "
				WHERE mountid = '$id'";
		$result = db_query($sql);
		$row = db_fetch_assoc($result);

		output('`n`2The following people have a `@%s `2as a mount.`0`n`n', $row['mountname']);

		$name = translate_inline('Name');
		$level = translate_inline('Level');
		$race = translate_inline('Race');
		rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999'>");
		rawoutput("<tr class=\"trhead\"><td>$name</td><td>$race</td><td align=\"center\">$level</td></tr>");

		$sql = "SELECT acctid, name, level, race
				FROM " . db_prefix('accounts') . "
				WHERE hashorse = '$id'";
		$result = db_query($sql);
		$i = 0;
		while( $row = db_fetch_assoc($result) )
		{
			rawoutput('<tr class="'.($i%2?'trlight':'trdark').'"><td>');
			if( $session['user']['superuser'] & SU_EDIT_USERS )
			{
				rawoutput('<a href="user.php?op=edit&editorid='.$session['user']['acctid'].'&userid='.$row['acctid'].'">');
				addnav('','user.php?op=edit&editorid='.$session['user']['acctid'].'&userid='.$row['acctid']);
				output_notl('%s', $row['name']);
				rawoutput('</a></td><td>');
			}
			else
			{
				output_notl('%s', $row['name']);
				rawoutput('</td><td>');
			}
			output_notl('%s', $row['race']);
			rawoutput('</td><td align="center">');
			output_notl('%s', $row['level']);
			rawoutput('</td></tr>');
			$i++;
		}
		rawoutput('</table><br /><br />');

		if( $session['user']['superuser'] & SU_EDIT_USERS )
		{
			output('`2Click a name to go into the user editor and then click the "Special" tab to change the player\'s mount.`0`n');
		}

		set_module_setting('lastcat',$cat);

		addnav('Editor');
		addnav('Previous Page',$from.'&lastcat=1&cat='.$cat);
	}
	elseif( $op == 'add' || $op == 'edit' )
	{
		if( !empty($id) )
		{
			//
			// Get mount data and send it for checking.
			//
			$sql = "SELECT *
					FROM " . db_prefix('mounts') . "
					WHERE mountid = '$id'";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			if( db_num_rows($result) <> 1 )
			{
				output('`$Error: That mount was not found!');
			}
			$row['mountbuff'] = unserialize(stripslashes($row['mountbuff']));
			$row = alternative_mounts_editor_array_check($row, $row['mountcategory']);
		}
		else
		{
			$row = alternative_mounts_editor_array_check();
		}

		//
		// Get the locations of the stables.
		//
		$vname = getsetting('villagename', LOCATION_FIELDS);
		$locs = array($vname => sprintf_translate('The Village of %s', $vname));
		$locs = modulehook('stablelocs', $locs);
		$locs['all'] = translate_inline('Everywhere');
		ksort($locs);
		reset($locs);
		$mount_loc = '';
		foreach($locs as $loc => $name)
		{
			$mount_loc .= ','.$loc.','.$name;
		}

		$form = array(
			'Mount Details,title',
				'mountactive'=>'Is Mount Active:,bool',
				'mountid'=>'Mount ID:,hidden',
				'mountname'=>'Mount Name:,string,50',
				'mountdesc'=>'Mount Desc:,',
				'mountcategory'=>'Mount Category:,string,50',
				'mountlocation'=>'Mount Availability:,enum'.$mount_loc,
				'mountdkcost'=>'DK Requirement:,int',
				'mountcostgems'=>'Gem Cost:,int',
				'mountcostgold'=>'Gold Cost:,int',
				'mountfeedcost'=>'Feed Cost (Gold per level):,int',
				'mountforestfights'=>'Forest Fights Given:,int',
				'`b`i`#Messages:`i`b,note',
				'newday'=>'Newday Message:,',
				'recharge'=>'Full Recharge Message:,',
				'partrecharge'=>'Partial Recharge Message:,',
				'`3Message replacements:`n{weapon}&#44; {armor}.,note',
			'Mount Buff,title',
				'mbuffname'=>'Buff name:,',
				'`b`i`#Messages:`i`b,note',
				'mbuffroundmsg'=>'Buff Round Message:,',
				'mbuffwearoff'=>'Buff Wearoff Message:,',
				'mbuffeffectmsg'=>'Buff Effect Message:,',
				'mbuffeffectnodmgmsg'=>'Buff No Damage Message:,',
				'mbuffeffectfailmsg'=>'Buff Fail Message:,',
				'`3Message replacements:`n{badguy}&#44; {goodguy}&#44; {weapon}&#44; {armor}&#44; {creatureweapon}&#44; and where applicable {damage}.,note',
				'`b`i`#Effects:`i`b,note',
				'mbuffrounds'=>'Rounds To Last:,int',
				'mbuffallowinpvp'=>'Allow in PVP:,bool',
				'mbuffallowintrain'=>'Allow in Training:,bool',
				'mbuffinvulnerable'=>'Player is Invulnerable:,bool',
				'mbuffatkmod'=>'Player Attack Mod (multiplier):,',
				'mbuffdefmod'=>'Player Defence Mod (multiplier):,',
				'mbuffregen'=>'Regeneration (healing):,',
				'mbuffminioncount'=>'Minion Count:,',
				'mbuffminbadguydamage'=>'Min Badguy Damage:,',
				'mbuffmaxbadguydamage'=>'Max Badguy Damage:,',
				'mbuffmingoodguydamage'=>'Min Goodguy Damage:,',
				'mbuffmaxgoodguydamage'=>'Max Goodguy Damage:,',
				'mbufflifetap'=>'Lifetap (multiplier):,',
				'mbuffdamageshield'=>'Damageshield (multiplier):,',
				'mbuffbadguydmgmod'=>'Badguy Damage Mod (multiplier):,',
				'mbuffbadguyatkmod'=>'Badguy Attack Mod (multiplier):,',
				'mbuffbadguydefmod'=>'Badguy Defence Mod (multiplier):,',
				'`b`i`#On Dynamic Buffs:`i`b,note',
				'`3In the above&#44; for most fields&#44; you can choose to enter valid PHP code&#44; substituting <fieldname> for fields in the user\'s account table.`nExamples of code you might enter:`n
				`^&lt;charm&gt;`nround(&lt;maxhitpoints&gt;/10)`nround(&lt;level&gt;/max(&lt;gems&gt;&#44;1)),note',
				'`3Fields you might be interested in for this:`n`Qname&#44; sex `7(0=male 1=female)`Q&#44; specialty `7(DA=darkarts MP=mystical TS=thief)`Q&#44; 
				experience&#44; gold&#44; weapon `7(name)`Q&#44; armor `7(name)`Q&#44; level&#44;`ndefense&#44; attack&#44; alive&#44; goldinbank&#44; 
				spirits `7(-2 to +2 or -6 for resurrection)`Q&#44; hitpoints&#44; maxhitpoints&#44; gems&#44; 
				weaponvalue `7(gold value)`Q&#44; armorvalue `7(gold value)`Q&#44; turns&#44; title&#44; weapondmg&#44; armordef&#44; 
				age `7(days since last DK)`Q&#44; charm&#44; playerfights&#44; dragonkills&#44; resurrections `7(times died since last DK)`Q&#44; 
				soulpoints&#44; gravefights&#44; deathpower `7('.getsetting('deathoverlord', '`$Ramius').' `7favor)`Q&#44; race&#44; dragonage&#44; bestdragonage.,note',
				'`3You can also use module preferences by using &lt;modulename|preference&gt;`n(for instance &#34;&lt;specialtymystic|uses&gt;&#34; or &#34;&lt;drinks|drunkeness&gt;&#34;,note',
				'`@Finally&#44; starting a field with &#34;debug:&#34; will enable debug output for that field to help you locate errors in your implementation. While testing new buffs&#44; you should be sure to debug fields before you release them on the world&#44; as the PHP script will otherwise throw errors to the user if you have any&#44; and this can break the site at various spots (as in places that redirects should happen).,note',
				'`n`#More information on buffs can be found at <a href="http://wiki.dragonprime.net/index.php?title=Buffs" target="_blank">wiki.dragonprime.net</a>.`n`n,note',
		);

		//
		// Get the names of the modules that have 'prefs-mounts' setting.
		//
		$sql = "SELECT formalname, modulename
				FROM " . db_prefix('modules') . "
				WHERE infokeys
				LIKE '%|prefs-mounts|%'
				ORDER BY formalname";
		$result = db_query($sql);
		while( $row2 = db_fetch_assoc($result) )
		{
			$formalname = $row2['formalname'];
			$modulename = modulename_sanitize($row2['modulename']);
			$modulefilename = "modules/{$modulename}.php";
			if( file_exists($modulefilename) )
			{
				//
				// Include the module file so that we can can the settings and default data from it.
				//
				require_once($modulefilename);
				$fname = $modulename.'_getmoduleinfo';
				if( function_exists($fname) )
				{
					$info = $fname();
					if( count($info['prefs-mounts']) > 0 )
					{
						$form[] = $formalname.',title'; // Each module gets its own title.
						while( list($key, $val) = each($info['prefs-mounts']) )
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

						//
						// Get the actual data for the settings.
						//
						$sql = "SELECT setting, value
								FROM " . db_prefix('module_objprefs') . "
								WHERE modulename = '$modulename'
									AND objtype = 'mounts'
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

		//
		// Display form.
		//
		require_once('lib/showform.php');
		rawoutput('<form action="'.$from.'&op=save&cat='.$cat.'" method="POST">');
		addnav('',$from.'&op=save&cat='.$cat);
		showform($form, $row);
		rawoutput('<input type="hidden" name="oldvalues" value="'.htmlentities(serialize($row), ENT_COMPAT, getsetting("charset", "ISO-8859-1")).'" /></form>');

		addnav('Editor');
		addnav('Add a Mount',$from.'&op=add&cat='.$cat);
		addnav('Previous Page',$from.'&cat='.$cat);
		addnav('Main Page',$from);
	}
	else
	{
		//
		// Create the search box.
		//
		$q = httppost('q');
		$search = translate_inline('Search');
		rawoutput('<form action="'.$from.'&subop=search" method="POST">');
		addnav('',$from.'&subop=search');
		output('`2Search by field: ');
		rawoutput('<input type="text" name="q" value="'.$q.'" />');
		rawoutput('<input type="submit" class="button" value="'.$search.'"></form><br />');

		addnav('Editor');
		$subop = httpget('subop');
		if( $subop == 'search' )
		{
			$where = "WHERE mountname LIKE '%$q%' OR mountdesc LIKE '%$q%'";
			addnav('Add a Mount',$from.'&op=add&cat='.$cat);
			$cat = '';
		}
		else
		{
			$where = ( $cat == 'all' ) ? '' : "WHERE mountcategory = '$cat'";
			addnav('Add a Mount',$from.'&op=add&cat='.$cat);
		}

		//
		// Create the navigation category links.
		//
		$sql = "SELECT mountcategory
				FROM " . db_prefix('mounts') . "
				GROUP BY mountcategory
				ORDER BY mountcategory";
		$result = db_query($sql);
		addnav('Mount Categories');
		while( $row = db_fetch_assoc($result) )
		{
			if( $cat == $row['mountcategory'] )
			{
				addnav(array('`QView by %s`0', $row['mountcategory']), $from.'&cat='.$row['mountcategory']);
			}
			else
			{
				addnav(array('View by %s', $row['mountcategory']), $from.'&cat='.$row['mountcategory']);
			}
		}
		if( $cat == 'all' )
		{
			addnav(array('`QView All`0', $row['mountcategory']), $from.'&cat=all');
		}
		else
		{
			addnav(array('View All', $row['mountcategory']), $from.'&cat=all');
		}

		//
		// Count how many players have which type of mount.
		//
		$sql = "SELECT count(acctid) AS c, hashorse
				FROM " . db_prefix('accounts') . "
				GROUP BY hashorse";
		$result = db_query($sql);
		$mounts = array();
		while( $row = db_fetch_assoc($result) )
		{
			$mounts[$row['hashorse']] = $row['c'];
		}

		$opshead = translate_inline('Ops');
		$name = translate_inline('Name');
		$costgems = translate_inline('Cost Gems');
		$costgold = translate_inline('Cost Gold');
		$location = translate_inline('Location');
		$fights = translate_inline('Fights');
		$cities = translate_inline('Travel');
		$alignment = translate_inline('Alignment');
		$darkhorse = translate_inline('Tavern');
		$extrav = translate_inline('Traveller');
		$mountrarity = translate_inline('Rarity');
		$activity = translate_inline('Activity');
		$owners = translate_inline('Owners');
		$edit = translate_inline('Edit');
		$give = translate_inline('Give');
		$del = translate_inline('Del');
		$deac = translate_inline('Deactivate');
		$act = translate_inline('Activate');
		$yesno = translate_inline(array('`@Yes','`$No'));
		$conf = translate_inline('There are %s user(s) who own this mount, are you sure you wish to delete it?');

		$header = '';
		$colspan = 7;

		// Of the 1500+ modules available for download at DragonPrime.net, only 9 use "prefs-mounts". Of those 9, only 6 were suitable for columns.
		// This is to replace the "mountfeatures" hook which was very SQL hungry.
		$mods_array = array('cities'=>array('active'=>FALSE,'name'=>'extratravel','bool'=>0),'alignment'=>array('active'=>FALSE,'name'=>'al','bool'=>0),'darkhorse'=>array('active'=>FALSE,'name'=>'findtavern','bool'=>1),'extrav'=>array('active'=>FALSE,'name'=>'ist','bool'=>1),'mountrarity'=>array('active'=>FALSE,'name'=>'rarity','bool'=>0),'activity'=>array('active'=>FALSE,'name'=>'ac','bool'=>0));

		//
		// Get the data from the 6 modules if they're installed/active.
		//
		foreach( $mods_array as $modulename => $setting )
		{
			$sql = "SELECT objid, value
					FROM " . db_prefix('module_objprefs') . "
					WHERE modulename = '$modulename'
						AND objtype = 'mounts'
						AND setting = '{$setting['name']}'";
			$result = db_query($sql);
			if( db_num_rows($result) > 0 )
			{
				$mods_array[$modulename]['active'] = TRUE;
				while( $row = db_fetch_assoc($result) )
				{
					$mods_array[$modulename][$row['objid']] = $row['value'];
				}
				$header .= "<td align=\"center\">${$modulename}</td>";
				$colspan++;
			}
			else
			{
				if( is_module_active($modulename) )
				{
					$mods_array[$modulename]['active'] = TRUE;
					$header .= "<td align=\"center\">${$modulename}</td>";
					$colspan++;
				}
			}
		}
		$header .= "<td>$owners</td></tr>";

		//
		// Table header links for ordering.
		//
		$order = httpget('order');
		$order2 = ( $order == 1 ) ? 'DESC' : 'ASC';
		$sortby = httpget('sortby');
		if( empty($sortby) )
		{
			$orderby = ( $cat == 'all' ) ? 'mountcategory, mountname '.$order2 : 'mountname '.$order2;
		}
		else
		{
			if( $sortby == 'name' ) $orderby = 'mountcategory, mountname '.$order2;
			if( $sortby == 'gems' ) $orderby = 'mountcategory, mountcostgems '.$order2.', mountcostgold '.$order2;
			if( $sortby == 'gold' ) $orderby = 'mountcategory, mountcostgold '.$order2.', mountcostgems '.$order2;
			if( $sortby == 'loc' ) $orderby = 'mountcategory, mountlocation '.$order2.', mountname '.$order2;
		}

		addnav('',$from.'&cat='.$cat.'&sortby=name&order='.($sortby=='name'?!$order:1));
		addnav('',$from.'&cat='.$cat.'&sortby=gems&order='.($sortby=='gems'?!$order:1));
		addnav('',$from.'&cat='.$cat.'&sortby=gold&order='.($sortby=='gold'?!$order:1));
		addnav('',$from.'&cat='.$cat.'&sortby=loc&order='.($sortby=='loc'?!$order:1));

		//
		// Get mount data and output to page.
		//
		$sql = "SELECT *
				FROM " . db_prefix('mounts') . "
				$where
				ORDER BY $orderby";
		$result = db_query($sql);

		rawoutput("<table border=0 cellpadding=2 cellspacing=1 bgcolor='#999999'>");

		$i = 0;
		$category = '';
		while( $row = db_fetch_assoc($result) )
		{
			if( $category != $row['mountcategory'] )
			{
				rawoutput("<tr class=\"trhead\"><td>$opshead</td><td><a href=\"$from&cat=$cat&sortby=name&order=".($sortby=='name'?!$order:1)."\">$name</a></td><td nowrap=\"nowrap\"><a href=\"$from&cat=$cat&sortby=gems&order=".($sortby=='gems'?!$order:1)."\">$costgems</a></td><td nowrap=\"nowrap\"><a href=\"$from&cat=$cat&sortby=gold&order=".($sortby=='gold'?!$order:1)."\">$costgold</a><td align=\"center\"><a href=\"$from&cat=$cat&sortby=loc&order=".($sortby=='loc'?!$order:1)."\">$location</a></td><td align=\"center\">$fights</td>$header");
				rawoutput('<tr class="trlight"><td colspan="'.$colspan.'">');
				output('Category: %s', $row['mountcategory']);
				rawoutput('</td></tr>');
				$category = $row['mountcategory'];
				$i = 0;
			}
			rawoutput('<tr class="'.($i%2?'trlight':'trdark').'">');
			rawoutput('<td align="left" nowrap="nowrap">[ <a href="'.$from.'&op=edit&cat='.$cat.'&mountid='.$row['mountid'].'">'.$edit.'</a> | <a href="'.$from.'&op=give&cat='.$cat.'&mountid='.$row['mountid'].'">'.$give.'</a> |');
			addnav('',$from.'&op=edit&cat='.$cat.'&mountid='.$row['mountid']);
			addnav('',$from.'&op=give&cat='.$cat.'&mountid='.$row['mountid']);
			if( $row['mountactive'] == 1 )
			{
				rawoutput('<a href="'.$from.'&op=deactivate&cat='.$cat.'&mountid='.$row['mountid'].'">'.$deac.'</a> ]</td>');
				addnav('',$from.'&op=deactivate&cat='.$cat.'&mountid='.$row['mountid']);
			}
			else
			{
				$mconf = sprintf($conf, (isset($mounts[$row['mountid']])?$mounts[$row['mountid']]:0));
				rawoutput('<a href="'.$from.'&op=del&cat='.$cat.'&mountid='.$row['mountid'].'" onClick="return confirm(\''.$mconf.'\');">'.$del.'</a> |');
				addnav('',$from.'&op=del&cat='.$cat.'&mountid='.$row['mountid']);
				rawoutput('<a href="'.$from.'&op=activate&cat='.$cat.'&mountid='.$row['mountid'].'">'.$act.'</a> ]</td>');
				addnav('',$from.'&op=activate&cat='.$cat.'&mountid='.$row['mountid']);
			}
			rawoutput('<td>');
			output_notl('`&%s`0', $row['mountname']);
			rawoutput('</td><td align="center">');
			output('`%%s gems`0', $row['mountcostgems']);
			rawoutput('</td><td align="center">');
			output('`^%s gold`0', $row['mountcostgold']);
			rawoutput('</td><td align="center">');
			output_notl('%s', ucfirst($row['mountlocation']));
			rawoutput('</td><td align="center">');
			output_notl('%s', $row['mountforestfights']);
			rawoutput('</td><td align="center">');

			foreach( $mods_array as $modulename => $value )
			{
				if( $value['active'] == TRUE )
				{
					if( $value['bool'] == 1 )
					{
						output_notl('%s', (isset($value[$row['mountid']])&&$value[$row['mountid']]==1?$yesno[0]:$yesno[1]));
					}
					else
					{
						output_notl('%s', (isset($value[$row['mountid']])?$value[$row['mountid']]:0));
					}
					rawoutput('</td><td align="center">');
				}
			}

			if( isset($mounts[$row['mountid']]) && !empty($mounts[$row['mountid']]) )
			{
				rawoutput('<a href="'.$from.'&op=owners&cat='.$cat.'&mountid='.$row['mountid'].'">');
				addnav('', $from.'&op=owners&cat='.$cat.'&mountid='.$row['mountid']);
		 		output_notl('`#%s`0', (int)$mounts[$row['mountid']]);
				rawoutput('</a>');
			}
			else
			{
				output_notl('`30`0');
			}
			rawoutput('</td></tr>');
			$i++;
		}
		rawoutput('</table><br /><br />');

		output('`2If you wish to delete a mount, you have to deactivate it first. If there are any owners of the mount when it is deleted then they will no longer have a mount, but they will get a FULL refund of the price of the mount at the time of deletion.`0`n');
	}

	addnav('Navigation');
	require_once('lib/superusernav.php');
	superusernav();

	page_footer();
}

function alternative_mounts_editor_array_check($mount=FALSE,$cat=FALSE)
{
	//
	// Make sure that all the variables exist.
	//
	if( !is_array($mount) ) $mount = array();
	if( empty($cat) ) $cat = get_module_setting('defcat');

	if( !isset($mount['mountactive']) )						$mount['mountactive'] = 0;
	if( !isset($mount['mountname']) )						$mount['mountname'] = '';
	if( !isset($mount['mountid']) )							$mount['mountid'] = 0;
	if( !isset($mount['mountdesc']) )						$mount['mountdesc'] = '';
	if( !isset($mount['mountcategory']) )					$mount['mountcategory'] = $cat;
	if( !isset($mount['mountlocation']) )					$mount['mountlocation'] = 'all';
	if( !isset($mount['mountdkcost']) )						$mount['mountdkcost'] = 0;
	if( !isset($mount['mountcostgems']) )					$mount['mountcostgems'] = 0;
	if( !isset($mount['mountcostgold']) )					$mount['mountcostgold'] = 0;
	if( !isset($mount['mountfeedcost']) )					$mount['mountfeedcost'] = 0;
	if( !isset($mount['mountforestfights']) )				$mount['mountforestfights'] = 0;
	if( !isset($mount['newday']) )							$mount['newday'] = '';
	if( !isset($mount['recharge']) )						$mount['recharge'] = '';
	if( !isset($mount['partrecharge']) )					$mount['partrecharge'] = '';
	if( !isset($mount['mountbuff']) )						$mount['mountbuff'] = array();
	if( !isset($mount['mountbuff']['name']) )				$mount['mountbuff']['name'] = '';
	if( !isset($mount['mountbuff']['roundmsg']) )			$mount['mountbuff']['roundmsg'] = '';
	if( !isset($mount['mountbuff']['wearoff']) )			$mount['mountbuff']['wearoff'] = '';
	if( !isset($mount['mountbuff']['effectmsg']) )			$mount['mountbuff']['effectmsg'] = '';
	if( !isset($mount['mountbuff']['effectnodmgmsg']) )		$mount['mountbuff']['effectnodmgmsg'] = '';
	if( !isset($mount['mountbuff']['effectfailmsg']) )		$mount['mountbuff']['effectfailmsg'] = '';
	if( !isset($mount['mountbuff']['rounds']) )				$mount['mountbuff']['rounds'] = 0;
	if( !isset($mount['mountbuff']['allowinpvp']) )			$mount['mountbuff']['allowinpvp'] = 0;
	if( !isset($mount['mountbuff']['allowintrain']) )		$mount['mountbuff']['allowintrain'] = 0;
	if( !isset($mount['mountbuff']['atkmod']) )				$mount['mountbuff']['atkmod'] = '';
	if( !isset($mount['mountbuff']['defmod']) )				$mount['mountbuff']['defmod'] = '';
	if( !isset($mount['mountbuff']['invulnerable']) )		$mount['mountbuff']['invulnerable'] = '';
	if( !isset($mount['mountbuff']['regen']) )				$mount['mountbuff']['regen'] = '';
	if( !isset($mount['mountbuff']['minioncount']) )		$mount['mountbuff']['minioncount'] = '';
	if( !isset($mount['mountbuff']['minbadguydamage']) )	$mount['mountbuff']['minbadguydamage'] = '';
	if( !isset($mount['mountbuff']['maxbadguydamage']) )	$mount['mountbuff']['maxbadguydamage'] = '';
	if( !isset($mount['mountbuff']['mingoodguydamage']) )	$mount['mountbuff']['mingoodguydamage'] = '';
	if( !isset($mount['mountbuff']['maxgoodguydamage']) )	$mount['mountbuff']['maxgoodguydamage'] = '';
	if( !isset($mount['mountbuff']['lifetap']) )			$mount['mountbuff']['lifetap'] = '';
	if( !isset($mount['mountbuff']['damageshield']) )		$mount['mountbuff']['damageshield'] = '';
	if( !isset($mount['mountbuff']['badguydmgmod']) )		$mount['mountbuff']['badguydmgmod'] = '';
	if( !isset($mount['mountbuff']['badguyatkmod']) )		$mount['mountbuff']['badguyatkmod'] = '';
	if( !isset($mount['mountbuff']['badguydefmod']) )		$mount['mountbuff']['badguydefmod'] = '';

	foreach( $mount as $key => $value )
	{
		if( $key == 'mountbuff' )
		{
			//
			// Unarray the 'mountbuff' array.
			//
			foreach( $value as $key2 => $value2 )
			{
				$value2 = ( is_string($value2) ) ? $value2 : (int)$value2;
				$mount["mbuff$key2"] = $value2; // Stripslashes is done before the function call.
			}
		}
		else
		{
			$value = ( is_string($value) ) ? $value : (int)$value;
			$mount[$key] = stripslashes($value);
		}
	}
	unset($mount['mountbuff']);

	return $mount;
}
?>