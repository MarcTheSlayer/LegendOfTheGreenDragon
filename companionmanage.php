<?php
/**
	This is based on the 'buffmanage' module but with changes for companions.

	11/02/2010 - v0.0.2
	+ Fixed an issue where when you edited a companion, 'cannotbehealed' would default to yes if the setting didn't exist.
*/
function companionmanage_getmoduleinfo()
{
	$info = array(
		"name"=>"Player Companion Manager",
		"description"=>"Manage a player's companions (give/edit/remove). Based on the 'buffmanage' module by `4Th`\$ri`Qce`^bo`&r`^nP`Qho`\$en`4ix`0.",
		"version"=>"0.0.2",
		"author"=>"`@MarcTheSlayer",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?topic=10901.0",
		"settings"=>array(
			"Settings,title",
			"limit"=>"Override 'max companions allowed game setting' when giving?,bool"
		)
	);
	return $info;
}

function companionmanage_install()
{
	output("`c`b`Q%s 'companionmanage' Module.`b`n`c", translate_inline(is_module_active('companionmanage')?'Updating':'Installing'));
	module_addhook('biostat');
	module_addhook('footer-user');
	return TRUE;
}

function companionmanage_uninstall()
{
	output("`n`c`b`Q'companionmanage' Module Uninstalled`0`b`c");
	return TRUE;
}

function companionmanage_dohook($hookname, $args)
{
	global $session;

	switch( $hookname )
	{
		case 'biostat':
			if( $session['user']['superuser'] & SU_EDIT_USERS )
			{
				$acctid = $args['acctid'];
				$sql = "SELECT companions
						FROM " . db_prefix('accounts') . "
						WHERE acctid = " . $acctid;
				$result = db_query($sql);

				if( db_num_rows($result) > 0 )
				{
					$row = db_fetch_assoc($result);
					$companionlist = unserialize($row['companions']);
					if( !empty($companionlist) )
					{
						output('`^Companions: `@');
						$companions = '';
						foreach( $companionlist as $name => $ofcomp)
						{
							$name2 = ( empty($companionlist[$name]['name']) ) ? $name : $companionlist[$name]['name'];
							$companions .= $name2.'`@, ';
						}
						$companions = rtrim($companions,', ');
						output_notl('%s.`0`n', $companions);
					}
				}
				addnav('Superuser');
				addnav('Edit Companions', 'runmodule.php?module=companionmanage&op=view&acctid=' . $acctid);
			}
		break;

		case 'footer-user':
			if( $session['user']['superuser'] & SU_EDIT_USERS )
			{
				$acctid = httpget('userid');
				addnav('Operations');
				addnav('Edit Companions', 'runmodule.php?module=companionmanage&op=view&acctid=' . $acctid);
			}
		break;
	}

	return $args;
}

function companionmanage_run()
{
	global $session;

	page_header('Companion Management');

	$acctid = httpget('acctid');
	$companion = httpget('companion');
	$op = httpget('op');

	switch( $op )
	{
		case '':
		case 'view':
			$sql = "SELECT name
					FROM " . db_prefix('accounts') . "
					WHERE acctid = " . $acctid;
			$result = db_query($sql);
			$row = db_fetch_assoc($result);

			$user_companions = companionmanage_getcompanions($acctid);

			if( count($user_companions) > 0 )
			{
				$save = FALSE;
				output('`n`3Companions for %s:`0`n`n', $row['name']);
				foreach( $user_companions as $name => $ofbuff )
				{
					if( !isset($user_companions[$name]['companionid']) )
					{
						// Campanion has no ID so is likely from a module and not the database.
						// An ID must be given for management to work, so create a random number.
						// Unlikely to have any existing IDs over 100, but give a wide berth anyway.
						$user_companions[$name]['companionid'] = mt_rand(5000,15000);
						$save = TRUE;
					}
					output_notl('`bArray name`b: %s`0`n', $name);
					foreach( $user_companions[$name] as $key => $val )
					{

						if( $key == 'name' )
						{
							if( empty($val) ) $val = $name;
							addnav('Remove Companions');
							addnav(array('`4Remove`0 %s`0', $val), "runmodule.php?module=companionmanage&acctid=$acctid&op=remove&companion={$user_companions[$name]['companionid']}");
							addnav('Edit Companions');
							addnav(array('`^Edit`0 %s`0', $val), "runmodule.php?module=companionmanage&acctid=$acctid&op=edit&companion={$user_companions[$name]['companionid']}");
							output_notl('`b%s`b: %s`0`n', $key, $val);
						}
						else
						{
							if( is_array($val) )
							{
								rawoutput('<b>'.$key.'</b>: ');

								$keys = array_keys($val);
								for( $i=0; $i<count($keys); $i++ )
								{
									if( empty($val[$keys[$i]]) ) $val[$keys[$i]] = 0;
									rawoutput('(<i>'.$keys[$i].'</i>: '.$val[$keys[$i]].') ');
								}
								rawoutput('<br />');
							}
							else
							{
								if( empty($val) ) $val = 0;
								rawoutput('<b>'.$key.'</b>: '.stripslashes($val).'<br />');
							}
						}
					}
					rawoutput('<br /><br />');
				}

				if( $save == TRUE )
				{
					debug('At least 1 companion without an ID. IDs given, saving companion array!');
					if( $acctid == $session['user']['acctid'] )
					{
						global $companions;
						$companions = $user_companions;
						debug($companions);
						$session['user']['companions'] = serialize($companions);
					}
					else
					{
						debug($user_companions);
						db_query("UPDATE " . db_prefix('accounts') . " SET companions = '" . addslashes(serialize($user_companions)) . "' WHERE acctid = '$acctid'");
					}
				}
			}
			else
			{
				output('`n`@%s `2currently has no companions.`n`n', $row['name']);
			}
			blocknav('runmodule.php?module=companionmanage&op=view&acctid=' . $acctid);
		break;

		case 'give':
			output('`n`3Players can currently have a total of %s %s. If you give a companion that the player already has, the companion will be reset.`n`n', getsetting('companionsallowed', 1), translate_inline(getsetting('companionsallowed', 1)==1?'companion':'companions'));

			$sql = "SELECT companionid, name, description, companionlocation, companioncostdks, companioncostgold, companioncostgems
					FROM " . db_prefix('companions') . "
					WHERE companionactive = 1
					ORDER BY companionid";
			$result = db_query($sql);
			if( db_num_rows($result) > 0 )
			{
				$ops = translate_inline('Ops');
				$name = translate_inline('Name');
				$description = translate_inline('Description');
				$location = translate_inline('Location');
				$dks = translate_inline('DKs');
				$gold = translate_inline('Gold');
				$gems = translate_inline('Gems');
				$give = translate_inline('Give');

				rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999">');
				rawoutput("<tr class=\"trhead\"><td>$ops</td><td>$name</td><td>$description</td><td>$location</td><td>$dks</td><td>$gold</td><td>$gems</td></tr>");

				$i = 1;
				while( $row = db_fetch_assoc($result) )
				{
					rawoutput('<tr class="'.($i%2?'trlight':'trdark').'"><td valign="top" nowrap="nowrap">[ ');
					rawoutput('<a href="runmodule.php?module=companionmanage&acctid='.$acctid.'&op=give2&companion='.$row['companionid'].'">'.$give.'</a>');
					addnav('',"runmodule.php?module=companionmanage&acctid=$acctid&op=give2&companion={$row['companionid']}");
					rawoutput(' ]</td><td valign="top" nowrap="nowrap">');
					output_notl('%s', $row['name']);
					rawoutput('</td><td valign="top">');
					output_notl('%s', $row['description']);
					rawoutput('</td><td align="center" valign="top" nowrap="nowrap">');
					output_notl('%s', $row['companionlocation']);
					rawoutput('</td><td align="center" valign="top">');
					output_notl('%s', $row['companioncostdks']);
					rawoutput('</td><td align="center" valign="top">');
					output_notl('`^%s`0', $row['companioncostgold']);
					rawoutput('</td><td align="center" valign="top">');
					output_notl('`% %s`0', $row['companioncostgems']);
					rawoutput('</td></tr>');
					$i++;
				}

				rawoutput('</table><br />');
			}
			else
			{
				output('`n`3Sadly there are no active companions to give.');
			}
			blocknav('runmodule.php?module=companionmanage&op=give&acctid=' . $acctid);
		break;

		case 'give2':
				$sql = "SELECT *
						FROM " .  db_prefix('companions') . "
						WHERE companionid = '$companion'
						LIMIT 1";
				$result = db_query($sql);
				if( $companion = db_fetch_assoc($result) )
				{
					$companion['attack'] = $companion['attack'] + $companion['attackperlevel'] * $session['user']['level'];
					$companion['defense'] = $companion['defense'] + $companion['defenseperlevel'] * $session['user']['level'];
					$companion['maxhitpoints'] = $companion['maxhitpoints'] + $companion['maxhitpointsperlevel'] * $session['user']['level'];
					$companion['hitpoints'] = $companion['maxhitpoints'];
					$companion = modulehook('alter-companion', $companion);
					$companion['abilities'] = @unserialize($companion['abilities']);

					$user_companions = companionmanage_getcompanions($acctid);
					$given = FALSE;
					$ignorelimit = ( get_module_setting('limit') == 1 ) ? TRUE : (( isset($user_companions[$companion['name']]) ) ? TRUE : FALSE);
					if( $acctid == $session['user']['acctid'] )
					{
						require_once('lib/buffs.php');
						if( apply_companion($companion['name'], $companion, $ignorelimit) !== FALSE ) $given = TRUE;
					}
					else
					{
						if( count($user_companions) < getsetting('companionsallowed', 1) || $ignorelimit == TRUE )
						{
							$given = TRUE;
							$user_companions[$companion['name']] = $companion;
							db_query("UPDATE " . db_prefix('accounts') . " SET companions = '" . addslashes(serialize($user_companions)) . "' WHERE acctid = '$acctid'");
						}
					}

					if( $given == TRUE )
					{
						debuglog("`qcompanion {$companion['name']} `qwas given.`0", $acctid);
						output('`n`2The companion `@%s `2has been given.', $companion['name']);
					}
					else
					{
						output('`n`2The companion cannot be given because the max allowed limit has already be reached.`0');
					}
				}
				else
				{
					output('`n`2Sadly that companion can\'t be found in the database.');
				}
		break;

		case 'remove':
			$companion_name = '';
			if( $acctid == $session['user']['acctid'] )
			{
				$user_companions = @unserialize($session['user']['companions']);
				foreach( $user_companions as $name => $value )
				{
					if( $user_companions[$name]['companionid'] == $companion )
					{
						$companion_name = $name;
						break;
					}
				}
				if( !empty($companion_name) )
				{
					debuglog("`qstripped $companion_name `qfrom self.`0");
					output('`n`2The companion `@%s `2has been removed.', $companion_name);
					unset($user_companions[$companion_name]);
					global $companions;
					$companions = $user_companions;
					$session['user']['companions'] = serialize($user_companions);
				}
				else
				{
					output('`n`2There was an error. No companion with that ID was found.');
				}
			}
			else
			{
				$user_companions = companionmanage_getcompanions($acctid);
				if( count($user_companions) > 0 )
				{
					foreach( $user_companions as $name => $value )
					{
						if( $user_companions[$name]['companionid'] == $companion )
						{
							$companion_name = $name;
							break;
						}
					}
					if( !empty($companion_name) )
					{
						output('`n`2The companion `@%s `2has been removed.', $companion_name);
						debuglog("`qhad companion {$user_companions[$companion_name]['name']} `qremoved.`0", $acctid);
						unset($user_companions[$companion_name]);
						$newcompanions = ( count($user_companions) < 1 ) ? '' : addslashes(serialize($user_companions));
						db_query("UPDATE " . db_prefix('accounts') . " SET companions = '$newcompanions' WHERE acctid = '$acctid'");
					}
					else
					{
						output('`n`2There was an error. No companion with that ID was found.');
					}
				}
				else
				{
					output('`n`2There was an error. No companion was found so could not be removed.');
				}
			}
		break;

		case 'edit':
			$user_companions = companionmanage_getcompanions($acctid);
			if( count($user_companions) > 0 )
			{
				$companion_name = '';
				foreach( $user_companions as $name => $value )
				{
					if( $user_companions[$name]['companionid'] == $companion )
					{
						$companion_name = $name;
						break;
					}
				}
				if( !empty($companion_name) )
				{
					rawoutput('<form action="runmodule.php?module=companionmanage&op=save&companion=' . $companion . '&acctid=' . $acctid . '" method="POST">');
					addnav('',"runmodule.php?module=companionmanage&op=save&companion=$companion&acctid=$acctid");
					companionmanage_showform($user_companions[$companion_name]);
					$save = translate_inline('Save');
					rawoutput('<input type="submit" class="button" value="'.$save.'" /></form>');
				}
				else
				{
					output('`n`2There was an error. No companion with that ID could be found.');
				}
			}
			else
			{
				output('`n`2There was an error. No companions were found.');
			}
		break;

		case 'save':
			$user_companions = companionmanage_getcompanions($acctid);
			if( count($user_companions) > 0 )
			{
				$companion_name = '';
				foreach( $user_companions as $name => $value )
				{
					if( $user_companions[$name]['companionid'] == $companion )
					{
						$companion_name = $name;
						break;
					}
				}

				if( !empty($companion_name) )
				{
					$formcompanion = httpallpost('companion');
					$user_companions[$companion_name] = $formcompanion['companion'];
					unset($formcompanion['companion']);

					if( $acctid == $session['user']['acctid'] )
					{
						while( list($key, $val) = each($user_companions[$companion_name]) )
						{
							if( is_array($val) ) $user_companions[$companion_name][$key] = $val;
							elseif( $val > '' ) $user_companions[$companion_name][$key] = stripslashes($val);
							else $user_companions[$companion_name][$key] = '';
						}

						global $companions;
						$companions = $user_companions;
						$session['user']['companions'] = serialize($companions);
					}
					else
					{
						db_query("UPDATE " . db_prefix('accounts') . " SET companions = '" . addslashes(serialize($user_companions)) . "' WHERE acctid = '$acctid'");
					}

					debuglog("`qcompanion {$user_companions[$companion_name]['name']} `qwas edited.`0", $acctid);
					output('`n`2The companion `@%s `2has been saved.', $user_companions[$companion_name]['name']);
				}
				else
				{
					output('`n`2There was an error. No companion with that ID could be found. Any changes were not saved.');
				}
			}
			else
			{
				output('`n`2There was an error. No companion was found so could not be save.');
			}
		break;
	}

	addnav('Options');
	addnav('Return to Bio', 'bio.php?char=' . $acctid);
	addnav('Edit User', 'user.php?op=edit&userid=' . $acctid);
	addnav('View Companions', 'runmodule.php?module=companionmanage&op=view&acctid=' . $acctid);
	addnav('Give Companion', 'runmodule.php?module=companionmanage&op=give&acctid=' . $acctid);

	require_once('lib/superusernav.php');
	superusernav();

	page_footer();
}

function companionmanage_getcompanions($acctid = FALSE)
{
	if( $acctid > 0 )
	{
		global $session;
		if( $acctid == $session['user']['acctid'] )
		{
			$companions = @unserialize($session['user']['companions']);
		}
		else
		{
			$sql = "SELECT companions
					FROM " . db_prefix('accounts') . "
					WHERE acctid = " . $acctid;
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			$companions = @unserialize($row['companions']);
		}
		if( !is_array($companions) ) $companions = array();
		return $companions;
	}

	return FALSE;
}

function companionmanage_showform($companion)
{
	if( !isset($companion['companionactive']) )			$companion['companionactive'] = 0;
	if( !isset($companion['companionid']) )				$companion['companionid'] = '';
	if( !isset($companion['category']) )				$companion['category'] = '';
	if( !isset($companion['name']) )					$companion['name'] = 'Nameless One';
	if( !isset($companion['description']) )				$companion['description'] = '';
	if( !isset($companion['jointext']) )				$companion['jointext'] = '';
	if( !isset($companion['dyingtext']) )				$companion['dyingtext'] = "I'm dying, ARRRRRGGGGGH!";
	if( !isset($companion['companionlocation']) )		$companion['companionlocation'] = 'all';
	if( !isset($companion['companioncostdks']) )		$companion['companioncostdks'] = 0;
	if( !isset($companion['companioncostgems']) )		$companion['companioncostgems'] = 0;
	if( !isset($companion['companioncostgold']) )		$companion['companioncostgold'] = 0;

	if( !isset($companion['attack']) )					$companion['attack'] = '';
	if( !isset($companion['attackperlevel']) )			$companion['attackperlevel'] = '';
	if( !isset($companion['defense']) )					$companion['defense'] = '';
	if( !isset($companion['defenseperlevel']) )			$companion['defenseperlevel'] = '';
	if( !isset($companion['hitpoints']) )				$companion['hitpoints'] = '';
	if( !isset($companion['maxhitpoints']) )			$companion['maxhitpoints'] = '';
	if( !isset($companion['maxhitpointsperlevel']) )	$companion['maxhitpointsperlevel'] = '';

	if( !isset($companion['abilities']) )				$companion['abilities'] = array();
	if( !isset($companion['abilities']['fight']) )		$companion['abilities']['fight'] = 0;
	if( !isset($companion['abilities']['defend']) )		$companion['abilities']['defend'] = 0;
	if( !isset($companion['abilities']['heal']) )		$companion['abilities']['heal'] = 0;
	if( !isset($companion['abilities']['magic']) )		$companion['abilities']['magic'] = 0;

	if( !isset($companion['cannotdie']) )				$companion['cannotdie'] = 0;
	if( !isset($companion['cannotbehealed']) )			$companion['cannotbehealed'] = 0;
	if( !isset($companion['allowinshades']) )			$companion['allowinshades'] = 0;
	if( !isset($companion['allowinpvp']) )				$companion['allowinpvp'] = 0;
	if( !isset($companion['allowintrain']) )			$companion['allowintrain'] = 0;
	if( !isset($companion['used']) )					$companion['used'] = 0;
	if( !isset($companion['suspended']) )				$companion['suspended'] = 0;
	if( !isset($companion['ignorelimit']) )				$companion['ignorelimit'] = 0;

	$no_yes = translate_inline(array('No','Yes'));

	rawoutput('<table width="100%" cellpadding="2" cellspacing="0">');
	rawoutput('<tr><td colspan="2" class="trhead">'.appoencode('`b'.$companion['name'].'`b').'</td></tr>');
	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('ID in Database:');
	rawoutput('</td><td class="trlight">'.$companion['companionid'].'</td></tr>');
	rawoutput('<tr><td class="trdark" nowrap="nowrap">');
	output('Active in Database?');
	rawoutput('</td><td class="trdark">'.($companion['companionactive']==1?$no_yes[1]:$no_yes[0]).'</td></tr>');
	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('Been Used:');
	rawoutput('</td><td class="trlight">'.($companion['used']==1?$no_yes[1]:$no_yes[0]).'</td></tr>');
	rawoutput('<tr><td class="trdark" nowrap="nowrap">');
	output('Suspended:');
	rawoutput('</td><td class="trdark">'.($companion['suspended']==1?$no_yes[1]:$no_yes[0]).'</td></tr>');
	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('Cost (DKs):');
	rawoutput('</td><td class="trlight">'.htmlentities((int)$companion['companioncostdks'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'</td></tr>');
	rawoutput('<tr><td class="trdark" nowrap="nowrap">');
	output('Cost (Gems):');
	rawoutput('</td><td class="trdark">'.htmlentities((int)$companion['companioncostgems'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'</td></tr>');
	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('Cost (Gold):');
	rawoutput('</td><td class="trlight">'.htmlentities((int)$companion['companioncostgold'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'</td></tr>');
	rawoutput('<tr><td class="trdark" nowrap="nowrap">');
	output('Location Where Bought:');
	rawoutput('</td><td class="trdark">'.htmlentities($companion['companionlocation'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'</td></tr>');


	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('Category:');
	rawoutput('</td><td class="trlight">'.htmlentities($companion['category'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'</td></tr>');

	rawoutput('<tr><td class="trdark" nowrap="nowrap">');
	output('Name:');
	rawoutput('</td><td class="trdark"><input name="companion[name]" value="'.htmlentities($companion['name'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" size="33" maxlength="50" /></td></tr>');
	rawoutput('<tr><td valign="top" class="trlight" nowrap="nowrap">');
	output('Description:');
	rawoutput('</td><td class="trlight">');
	output_notl('%s', htmlentities($companion['description'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')));
	rawoutput('</td></tr><tr><td valign="top" class="trdark" nowrap="nowrap">');
	output('Join Text:');
	rawoutput('</td><td class="trdark">');
	output_notl('%s', htmlentities($companion['jointext'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')));
	rawoutput('</td></tr><tr><td class="trlight" nowrap="nowrap">');
	output('Dying Text:');
	rawoutput('</td><td class="trlight"><input name="companion[dyingtext]" value="'.htmlentities($companion['dyingtext'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" size="33" maxlength="250" /></td></tr>');

	rawoutput('<tr><td colspan="2" class="trhead">');
	output('Battle Strength');
	rawoutput('</td></tr><tr><td colspan="2" class="trlight">');
	output('`^`iWhat the companion currently has, plus the bonus which is added with each increase of level.`i`0');
	rawoutput('</td></tr><td class="trdark" nowrap="nowrap">');
	output('Hitpoints:');
	rawoutput('</td><td class="trdark"><input name="companion[hitpoints]" value="'.htmlentities($companion['hitpoints'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" size="6" maxlength="6" /></td></tr>');
	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('Maxhitpoints / Bonus per level:');
	rawoutput('</td><td class="trlight"><input name="companion[maxhitpoints]" value="'.htmlentities($companion['maxhitpoints'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" size="6" maxlength="6" /> / <input name="companion[maxhitpointsperlevel]" value="'.htmlentities($companion['maxhitpointsperlevel'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" size="6" maxlength="6" /></td></tr>');
	rawoutput('<tr><td class="trdark" nowrap="nowrap">');
	output('Attack / Bonus per level:');
	rawoutput('</td><td class="trdark"><input name="companion[attack]" value="'.htmlentities($companion['attack'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" size="6" maxlength="6" /> / <input name="companion[attackperlevel]" value="'.htmlentities($companion['attackperlevel'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" size="6" maxlength="6" /></td></tr>');
	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('Defense / Bonus per level:');
	rawoutput('</td><td class="trlight"><input name="companion[defense]" value="'.htmlentities($companion['defense'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" size="6" maxlength="6" /> / <input name="companion[defenseperlevel]" value="'.htmlentities($companion['defenseperlevel'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" size="6" maxlength="6" /></td></tr>');
	rawoutput('<tr><td colspan="2" class="trhead">');
	output('Battle Ability');
	rawoutput('</td></tr><tr><td colspan="2" class="trlight">');
	output('`^`iOnly one of the following 4 abilities should be chosen, as a companion can only do 1 thing!`i`0');
	rawoutput('</td></tr><td class="trdark" nowrap="nowrap">');
	output('Can Heal:');
	rawoutput('</td><td class="trdark" valign="top"><select name="companion[abilities][heal]">');
	for( $i=0; $i<=30; $i++ )
	{
		$name = ( $i == 0 ) ? $no_yes[0] : $i;
		rawoutput('<option value="'.$i.'"'.($companion['abilities']['heal']==$i?' selected="selected"':'').'>'.$name.'</option>');
	}
	rawoutput('</select>');
	output('`^`iMax amount of HP healed per round`i`0');
	rawoutput('</td></tr><tr><td class="trlight" nowrap="nowrap">');
	output('Can Fire Arrows:');
	rawoutput('</td><td class="trlight" valign="top"><select name="companion[abilities][magic]">');
	for( $i=0; $i<=30; $i++ )
	{
		$name = ( $i == 0 ) ? $no_yes[0] : $i;
		rawoutput('<option value="'.$i.'"'.($companion['abilities']['magic']==$i?' selected="selected"':'').'>'.$name.'</option>');
	}
	rawoutput('</select>');
	output('`^`iMax amount of damage caused per round`i`0');
	rawoutput('</td></tr><tr><td class="trdark" nowrap="nowrap">');
	output('Can Defend:');
	rawoutput('</td><td class="trdark"><select name="companion[abilities][defend]"><option value="0"'.($companion['abilities']['defend']==0?' selected="selected"':'').'>'.$no_yes[0].'</option><option value="1"'.($companion['abilities']['defend']==1?' selected="selected"':'').'>'.$no_yes[1].'</option></select></td></tr>');
	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('Can Fight:');
	rawoutput('</td><td class="trlight"><select name="companion[abilities][fight]"><option value="0"'.($companion['abilities']['fight']==0?' selected="selected"':'').'>'.$no_yes[0].'</option><option value="1"'.($companion['abilities']['fight']==1?' selected="selected"':'').'>'.$no_yes[1].'</option></select></td></tr>');

	rawoutput('<tr><td colspan="2" class="trhead">');
	output('Other Options');
	rawoutput('</td></tr><tr><td class="trdark" nowrap="nowrap">');
	output('Cannot Be Killed:');
	rawoutput('</td><td class="trdark"><input type="checkbox" name="companion[cannotdie]" value="1"'.($companion['cannotdie']==TRUE?' checked="checked"':'').'" /></td></tr>');
	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('Cannot Be Healed:');
	rawoutput('</td><td class="trlight"><input type="checkbox" name="companion[cannotbehealed]" value="1"'.($companion['cannotbehealed']==TRUE?' checked="checked"':'').'" /></td></tr>');
	rawoutput('<tr><td class="trdark" nowrap="nowrap">');
	output('Allow in Shades:');
	rawoutput('</td><td class="trdark"><input type="checkbox" name="companion[allowinshades]" value="1"'.($companion['allowinshades']==TRUE?' checked="checked"':'').'" /></td></tr>');
	rawoutput('<tr><td class="trlight" nowrap="nowrap">');
	output('Allow in PvP:');
	rawoutput('</td><td class="trlight"><input type="checkbox" name="companion[allowinpvp]" value="1"'.($companion['allowinpvp']==TRUE?' checked="checked"':'').'" /></td></tr>');
	rawoutput('<tr><td class="trdark" nowrap="nowrap">');
	output('Allow in Training:');
	rawoutput('</td><td class="trdark"><input type="checkbox" name="companion[allowintrain]" value="1"'.($companion['allowintrain']==TRUE?' checked="checked"':'').'" /></td></tr>');
	rawoutput('</table>');
	rawoutput('<input type="hidden" name="companion[companionactive]" value="'.htmlentities((int)$companion['companionactive'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[companionid]" value="'.htmlentities((int)$companion['companionid'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[companioncostdks]" value="'.htmlentities((int)$companion['companioncostdks'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[companioncostgems]" value="'.htmlentities((int)$companion['companioncostgems'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[companioncostgold]" value="'.htmlentities((int)$companion['companioncostgold'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[category]" value="'.htmlentities($companion['category'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[companionlocation]" value="'.htmlentities($companion['companionlocation'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[description]" value="'.htmlentities($companion['description'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[jointext]" value="'.htmlentities($companion['jointext'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[used]" value="'.htmlentities($companion['used'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[suspended]" value="'.htmlentities($companion['suspended'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
	rawoutput('<input type="hidden" name="companion[ignorelimit]" value="'.htmlentities($companion['ignorelimit'], ENT_COMPAT, getsetting('charset', 'ISO-8859-1')).'" />');
}
?>