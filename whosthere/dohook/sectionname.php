<?php
	global $_SERVER, $SCRIPT_NAME;

	$id = $session['user']['acctid'];
	$section = ( isset($args['section']) ) ? $args['section'] : 'nochat';
	set_module_pref('playerloc',$section,'whosthere',$id);
	$section_names = explode("\r\n",get_module_setting('list'));

	if( in_array($section, $section_names) )
	{
		if( $section == 'shades' )
		{
			// Set location to shades.
			if( $session['user']['location'] != get_module_setting('shade_name') )
			{
				// Store village name so they return to the village they died in.
				set_module_pref('deadloc',$session['user']['location'],'whosthere',$id);
				$session['user']['location'] = get_module_setting('shade_name');
			}					
		}

		if( $section == 'valhalla' )
		{
			// Set location to valhalla.
			if( $session['user']['location'] != get_module_setting('valhalla_name') )
			{
				set_module_pref('deadloc',$session['user']['location'],'whosthere',$id);
				$session['user']['location'] = get_module_setting('valhalla_name');
			}					
		}

		$clan = ( $SCRIPT_NAME == 'clan.php' ) ? "a.clanid = '" . $session['user']['clanid'] . "' AND" : '';

		$sql = "SELECT a.acctid, a.name, a.login
			FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
			WHERE $clan a.loggedin = 1
			AND b.modulename = 'whosthere'
			AND b.setting = 'playerloc'
			AND b.value = '" . $section . "'
			AND a.acctid = b.userid
			AND a.acctid <> '" . $id . "'
			AND a.laston > '" . date("Y-m-d H:i:s",strtotime("-".getsetting("LOGINTIMEOUT",300) . " seconds"))."'";
		$result = db_query($sql);

		output("`n`@Who Else is here:`n");
		if( db_num_rows($result) )
		{
			$count1 = 0;
			$count2 = 0;
			$whos_there = array();
			while( $row = db_fetch_assoc($result) )
			{
				// Put the names of the people in the same location into an array.
				$whos_there[$row['acctid']]['name'] = $row['name'];
				$whos_there[$row['acctid']]['login'] = $row['login'];
				$whos_there[$row['acctid']]['hidden'] = 0;
				$count1++;
			}
			db_free_result($result);

			if( get_module_setting('hide') == 1 )
			{
				$sqlh = "SELECT userid
					FROM " . db_prefix('module_userprefs') . "
					WHERE modulename = 'whosthere'
					AND setting = 'hidden'
					AND value > 0";
				$resulth = db_query($sqlh);
				while( $rowh = db_fetch_assoc($resulth) )
				{
					// Get list of people who are hidden and mark them in the array as such.
					if( array_key_exists($rowh['userid'], $whos_there) )
					{
						$whos_there[$rowh['userid']]['hidden'] = 1;
						$count2++;
					}
				}
				db_free_result($resulth);
			}

			// Hidden players aren't hidden from superusers so count remains the same, else take away hidden players count.
			$count = ( $session['user']['superuser'] > 0 ) ? $count1 : $count1 - $count2;
			$pre = '';
			$and = translate_inline('and');
			$i = 1;
			foreach( $whos_there as $key => $value )
			{
				if( $value['hidden'] == 1 && $SCRIPT_NAME != 'superuser.php' )
				{
					if( $session['user']['superuser'] > 0 )
					{
						$pre .= '<a href="bio.php?char=' . rawurlencode($value['login']) . '&ret=' . URLEncode($_SERVER['REQUEST_URI']) . '"><span>' . $value['name'] . '<i>`$-hidden`0</i></span></a>';
						addnav('',"bio.php?char=".rawurlencode($value['login'])."&ret=".URLEncode($_SERVER['REQUEST_URI']));
					}
				}
				else
				{
					$pre .= '<a href="bio.php?char=' . rawurlencode($value['login']) . '&ret=' . URLEncode($_SERVER['REQUEST_URI']) . '"><span>' . $value['name'] . '</span></a>';
					addnav('',"bio.php?char=".rawurlencode($value['login'])."&ret=".URLEncode($_SERVER['REQUEST_URI']));
				}

				if( $count >= 3 && $i != ($count-1) && $i != $count )
				{
					$pre .= '`0, ';
				}
				if( $i == ($count-1) )
				{
					$pre .= ' `0' . $and . ' ';
				}
				$i++;
			}
			output('`7%s`7.',$pre,TRUE);
			unset($whos_there);
		}
		else
		{
			output("`2No one.");
		}
		output("`n`2-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-`0`n");
	}
?>