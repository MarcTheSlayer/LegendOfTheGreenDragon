<?php
/**
	21/08/10 - v0.0.1b
	06/09/10 - v0.0.2b
	+ Fixed a location problem where open dwellings appeared in all locations.
	12/09/10 - v0.0.3
	+ Fixed a bug, had used $dwid instead of $arg['dwid'].
	20/09/10 - v0.0.4
	+ Added mute option so non keyholders can only view commentary, they can't post. Idea from Contessa. :)
	+ Fixed logout bug. Link no longer shows for non keyholders.
*/
function dwopenhouse_getmoduleinfo()
{
	$info = array(
		"name"=>"Dwelling Open House Policy",
		"description"=>"Allow dwelling owner to open their house to anyone, key or no key.",
		"author"=>"`@MarcTheSlayer`2, idea from `#C`&on`#t`&ess`#a`0",
		"version"=>"0.0.4b",
		"category"=>"Dwellings",
		"download"=>"http://dragonprime.net/index.php?topic=11202.0",
		"requires"=>array(
			"dwellings"=>"1.0|Dwellings Project Team,http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=162",
		),
		"settings"=>array(
			"Open House Policy,title",
				"cost"=>"Cost to buy this at the Lodge:,int|200",
				"`^If cost is zero (0) then any delling type anywhere can be open.,note",
				"show"=>"Show open dwellings even when policy no longer allowed?,bool|1",
				"`^ie: If a dwelling type had it&#44; but then was taken away. The owner if they had bought the policy would still have an open house.,note",
		),
		"prefs-city"=>array(
			"Open House Policy,title",
				"allowopen"=>"Allow open dwellings in this location?,bool|1",
		),
		"prefs-dwellingtypes"=>array(
			"Open House Policy,title",
				"canopen"=>"Can these dwelling types be opened?,bool|1",
		),
		"prefs-dwellings"=>array(
			"Open House Policy,title",
				"paid"=>"Has this dwelling paid to be open?,bool",
				"open"=>"Is this dwelling currently open?,bool",
				"mute"=>"Is commentary here muted for non key holders?,bool|1",
		),
	);
	return $info;
}

function dwopenhouse_install()
{
	output("`c`b`Q%s 'dwopenhouse' Module.`b`n`c", translate_inline(is_module_active('dwopenhouse')?'Updating':'Installing'));
	module_addhook_priority('dwellings',85);
	module_addhook('dwellings-inside');
	module_addhook('dwellings-list-interact');
	module_addhook('dwellings-manage');
    module_addhook('dwellings-management');
    module_addhook('insertcomment');
	module_addhook('lodge');
	module_addhook('lodge_incentives');
	module_addhook('pointsdesc');
	return TRUE;
}

function dwopenhouse_uninstall()
{
	output("`n`c`b`Q'dwopenhouse' Module Uninstalled`0`b`c");
	return TRUE;
}

function dwopenhouse_dohook($hookname,$args)
{
	global $session;

	switch( $hookname )
	{
		case 'dwellings':
			if( get_module_objpref('city', $args['cityid'], 'allowopen', 'dwopenhouse') == 1 || get_module_setting('show') == 1 )
			{
				$accounts = db_prefix('accounts');
				$dwellings = db_prefix('dwellings');
				$objprefs = db_prefix('module_objprefs');
				$sql = "SELECT a.name, d.dwid, d.ownerid, d.name AS dname, d.type, d.windowpeer
						FROM $dwellings d INNER JOIN $objprefs o
						ON d.dwid = o.objid JOIN $objprefs ob
						ON d.dwid = ob.objid JOIN $accounts a
						ON d.ownerid = a.acctid
						WHERE o.modulename = 'dwopenhouse'
							AND ob.modulename = 'dwopenhouse'
							AND o.objtype = 'dwellings'
							AND ob.objtype = 'dwellings'
							AND o.setting = 'paid'
							AND ob.setting = 'open'
							AND o.value = 1
							AND ob.value = 1
							AND d.ownerid != '{$session['user']['acctid']}'
							AND a.location = d.location";
				$result = db_query($sql);

				$tname = translate_inline('Name');
				$towner = translate_inline('Owner');
				$tdesc = translate_inline('Description');
				$ttype = translate_inline('Type');
				$enter = translate_inline('Open');
				$unnamed = translate_inline('Unnamed');
				$none = translate_inline('None');
				output("`n`n`cThe following dwellings here are open to all:`c`n");
				rawoutput('<table border="0" cellpadding="3" cellspacing="0" align="center">');
				rawoutput('<tr class="trhead"><td style="width:100px">'.$tname.'</td><td style="width:100px">'.$towner.'</td><td style="width:300px" align="center">'.$tdesc.'</td><td>'.$ttype.'</td><td align="center" style="width:75px">&nbsp;</td></tr>'); 

				if( db_num_rows($result) > 0 )
				{
					$i = 0;
					while( $row = db_fetch_assoc($result) )
					{
					 	$dname = ( empty($row['dname']) ) ? $unnamed : appoencode(stripslashes($row['dname']));
					 	$name = appoencode($row['name']);
					 	$cname = appoencode(get_module_setting('dwname',$row['type']));
				 		rawoutput('<tr class="'.($i%2?'trlight':'trdark').'"><td>'.$dname.'</td><td>'.$name.'</td><td>'.appoencode(stripslashes($row['windowpeer'])).'</td><td>'.$cname.'</td>');
						rawoutput('<td>[ <a href="runmodule.php?module=dwellings&op=enter&dwid='.$row['dwid'].'">'.$enter.'</a> ]</td></tr>');
				 		addnav('','runmodule.php?module=dwellings&op=enter&dwid='.$row['dwid']);
				 		$i++;
				 	}
				}
				else
				{
					rawoutput('<tr class="trlight"><td align="center" colspan="5">'.$none.'</td></tr>');
				}

			  	rawoutput('</table>');
			}
			addnav('Dwellings Open Policy','runmodule.php?module=dwopenhouse&op=policy');
		break;

		case 'dwellings-inside':
			if( $session['user']['acctid'] != $args['owner'] && (get_module_objpref('dwellings', $args['dwid'], 'paid', 'dwopenhouse') == 1 || get_module_setting('cost') == 0) && get_module_objpref('dwellings', $args['dwid'], 'open', 'dwopenhouse') == 1 )
			{
				blocknav('runmodule.php?module=dwellings&op=manage&dwid='.$args['dwid']);
				$sql = "SELECT keyid
						FROM " . db_prefix('dwellingkeys') . "
						WHERE dwid = '{$args['dwid']}'
							AND keyowner = '{$session['user']['acctid']}'";
				$result = db_query($sql);
				if( !$row = db_fetch_assoc($result) )
				{
					blocknav('runmodule.php?module=dwellings&op=coffers&dwid='.$args['dwid']);
					blocknav('runmodule.php?module=dwellings&op=keys&subop=giveback&dwid='.$args['dwid']);
					blocknav('runmodule.php?module=dwellings&op=logout&dwid='.$args['dwid'].'&type='.$args['type']);
				}
			}
		break;

		case 'dwellings-list-interact':
			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
			if( get_module_objpref('city', $cityid, 'allowopen', 'dwopenhouse') == 1 || get_module_setting('show') == 1 )
			{
				if( $session['user']['location'] == $args['location'] && $args['status'] == 1 )
				{
					$typeid = get_module_setting('typeid',$args['type']);
					if( get_module_objpref('dwellingtypes', $typeid, 'canopen', 'dwopenhouse') == 1 || get_module_setting('show') == 1 )
					{
						if( (get_module_objpref('dwellings', $args['dwid'], 'paid', 'dwopenhouse') == 1 || get_module_setting('cost') == 0) && get_module_objpref('dwellings', $args['dwid'], 'open', 'dwopenhouse') == 1 )
						{
							rawoutput('<a href="runmodule.php?module=dwellings&op=enter&dwid='.$args['dwid'].'">'.translate_inline('Open House').'</a><br />');
							addnav('','runmodule.php?module=dwellings&op=enter&dwid='.$args['dwid']);
						}
					}
				}
			}
		break;

		case 'dwellings-manage':
			if( get_module_objpref('dwellings', $args['dwid'], 'paid', 'dwopenhouse') == 1 || get_module_setting('cost') == 0 )
			{
				addnav('Management');
				addnav('Open House Policy','runmodule.php?module=dwopenhouse&op=open&type='.$args['type'].'&dwid='.$args['dwid']);
			}
		break;

        case 'dwellings-management';
			$policy = translate_inline('Open House Policy');
			$header = translate_inline("Dwelling's Open House Policy");
			rawoutput('<tr><td colspan="2" class="trhead" style="text-align:center;">'.$header.'</td></tr>');
			rawoutput('<tr height="30px" class="trlight"><td colspan="2">'.$policy.':');
			if( get_module_objpref('dwellings', $args['dwid'], 'paid', 'dwopenhouse') == 1 || get_module_setting('cost') == 0 )
			{
				if( get_module_objpref('dwellings', $args['dwid'], 'open', 'dwopenhouse') == 1 )
				{
					output('This dwelling is currently open to all with keys or not.');
				}
				else
				{
					output('This dwelling is currently closed to all except those with keys.');
				}
			}
			else
			{
				require_once('modules/cityprefs/lib.php');
				$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
				if( get_module_objpref('city', $cityid, 'allowopen', 'dwopenhouse') == 1 )
				{
					$typeid = get_module_setting('typeid',$args['type']);
					if( get_module_objpref('dwellingtypes', $typeid, 'canopen', 'dwopenhouse') == 1 )
					{
						rawoutput('[<a href="runmodule.php?module=dwopenhouse&op=lodge&sop=buy&dwid='.$args['dwid'].'">');
						output('Purchase open house policy from the lodge.');
						rawoutput('</a>]');
						addnav('','runmodule.php?module=dwopenhouse&op=lodge&sop=buy&dwid='.$args['dwid']);
					}
					else
					{
						output('This dwelling type cannot have an open house policy.');
					}
				}
				else
				{
					output('Dwellings in %s cannot have an open house policy.', $session['user']['location']);
				}
			}
			rawoutput('</td></tr>');
        break;

		case 'insertcomment':
			$dwid = strstr($args['section'], 'dwellings-');
			if( $dwid == FALSE ) break;
			$dwid = str_replace('dwellings-', '', $dwid); 
			if( get_module_objpref('dwellings', $dwid, 'open', 'dwopenhouse') == 1 && get_module_objpref('dwellings', $dwid, 'mute', 'dwopenhouse') == 1 )
			{
				$sql = "SELECT ownerid
						FROM " . db_prefix('dwellings') . "
						WHERE dwid = '$dwid'";
				$result = db_query($sql);
				if( $row = db_fetch_assoc($result) )
				{
					if( $row['ownerid'] == $session['user']['acctid'] ) break;
				}
				$sql = "SELECT keyowner
						FROM " . db_prefix('dwellingkeys') . "
						WHERE dwid = '$dwid'
							AND keyowner = '".$session['user']['acctid']."'";
				$result = db_query($sql);
				if( $row = db_fetch_assoc($result) ) break;

				$args['mute'] = TRUE;
				$args['mutemsg'] .= translate_inline('`n`@The owner has disallowed non keyholders from posting.`0`n`n');
			}
		break;

		case 'lodge':
			$cost = get_module_setting('cost');
			if( $cost > 0 )
			{
				$points = translate_inline(array('point','points'));
				addnav('Use Points');
				addnav(array('Open House Policy (%s %s)', $cost, ($cost==1?$point[0]:$points[1])),'runmodule.php?module=dwopenhouse&op=lodge&sop=pick');
			}
		break;

		case 'lodge_incentives':
			$cost = get_module_setting('cost');
			if( $cost > 0 )
			{
				$points = $args['points'];
				$points[$cost][] = translate("`#An open door policy for your dwelling (Anyone can enter as no keys required).");
				$args['points'] = $points;
			}
		break;

		case 'pointsdesc':
			$cost = get_module_setting('cost');
			if( $cost > 0 )
			{
				$args['count']++;
				$str = translate("An open door policy for your dwelling (Anyone can enter) costs %s %s.");
				$points = translate_inline($cost==1?'point':'points');
				$str = sprintf($str, $cost, $points);
				output($args['format'], $str, TRUE);
			}
		break;
	}

	return $args;
}

function dwopenhouse_run()
{
	global $session;

	$dwid = httpget('dwid');

	$op = httpget('op');
	if( $op == 'lodge' )
	{
		$cost = get_module_setting('cost');
		$points = translate_inline(array('point','points'));

		page_header("Hunter's Lodge");
		$sop = httpget('sop');
		if( $sop == 'buy' )
		{
			output("`n`7J. C. Petersen turns to you. \"`&To make your dwelling open to every one so that no keys are required requires a special policy, which will cost %s %s,`7\" he says.  \"`&Will this suit you?`7\"`n`n", $cost, ($cost==1?$point[0]:$points[1]));

			addnav('Confirm Purchase');
			addnav('Yes','runmodule.php?module=dwopenhouse&op=lodge&sop=confirm&dwid='.$dwid);
			if( httpget('top') == 1 ) addnav('No','lodge.php');
			else addnav('No','runmodule.php?module=dwellings&op=manage&dwid='.$dwid);
		}
		elseif( $sop == 'confirm' )
		{
			global $session;

			$pointsavailable = $session['user']['donation'] - $session['user']['donationspent'];
			if( $pointsavailable >= $cost )
			{
				output("`n`7J. C. Petersen writes out a YoM to the person in charge and sends it off. \"`&There you go `7%s`&. Your dwelling is now able to become open should you wish it.`7\"`0`n", $session['user']['name']);
				set_module_objpref('dwellings',$dwid,'paid',1,'dwopenhouse');
				$session['user']['donationspent'] += $cost;
			}
			else
			{
				output("`n`7J. C. Petersen looks down his nose at you. \"`&I'm sorry, but you do not have the %s %s required. Please return when you do and I'll be happy to do business with you.`7\"`0`n", $cost, ($cost==1?$point[0]:$points[1]));
			} 
			addnav('Dwelling Management','runmodule.php?module=dwellings&op=manage&dwid='.$dwid);
		}
		elseif( $sop == 'pick' )
		{
			$sql = "SELECT dwid, name, type, location
					FROM " . db_prefix('dwellings') . "
					WHERE ownerid = '{$session['user']['acctid']}'";
			$result = db_query($sql);

			$count = db_num_rows($result);
			if( $count == 0 )
			{
				output("`n`7J. C. Petersen pulls out a book and flicks through the pages. \"`&Ah, it would seem that you don't have any dwellings. This is a requirement for this policy. Please return when you have at least one and I'll be happy to do business with you.`7\"`0`n");
				addnav('The Lodge','lodge.php');
			}
			elseif( $count == 1 )
			{
				$row = db_fetch_assoc($result);
				require_once('modules/cityprefs/lib.php');
				$cityid = get_cityprefs_cityid('cityname',$row['location']);
				if( get_module_objpref('city', $cityid, 'allowopen', 'dwopenhouse') == 1 )
				{
					$typeid = get_module_setting('typeid',$row['type']);
					if( get_module_objpref('dwellingtypes', $typeid, 'canopen', 'dwopenhouse') == 1  )
					{
						$paid = get_module_objpref('dwellings', $row['dwid'], 'paid', 'dwopenhouse');
						$tname = translate_inline('Name');
						$tlocation = translate_inline('Location');
						$ttype = translate_inline('Type');
						$buy = translate_inline('Buy Policy');
						$unnamed = translate_inline('Unnamed');
					 	$name = ( empty($row['name']) ) ? $unnamed : appoencode($row['name']);
					 	$cname = appoencode(get_module_setting('dwname',$row['type']));
						rawoutput('<table border="0" cellpadding="3" cellspacing="0" align="center">');
						rawoutput('<tr class="trhead"><td align="center">'.$tname.'</td><td align="center">'.$ttype.'</td><td align="center">'.$tlocation.'</td><td align="center" style="width:75px">&nbsp;</td></tr>'); 
				 		rawoutput('<tr class="trlight"><td>'.$name.'</td><td>'.$cname.'</td><td>'.$row['location'].'</td><td>');
				 		if( $paid == 1 )
				 		{
							output('- Bought -');
			  				rawoutput('</td></tr></table>');
							output("`n`n`7J. C. Petersen pulls out a book and flicks through the pages. \"`&Ah, it would seem that you only have one dwelling and you have already bought a policy for it.`7\"`0`n");
							addnav('The Lodge','lodge.php');
				 		}
				 		else
				 		{
							rawoutput('[ <a href="runmodule.php?module=dwopenhouse&op=lodge&sop=buy&top=1&dwid='.$row['dwid'].'">'.$buy.'</a> ]');
				 			addnav('','runmodule.php?module=dwopenhouse&op=lodge&sop=buy&top=1&dwid='.$row['dwid']);
			  				rawoutput('</td></tr></table>');
							output("`n`n`7J. C. Petersen pulls out a book and flicks through the pages. \"`&Ah, it would seem that you only have one dwelling and that its a type the policy supports. Do you wish to select this dwelling?`7\"`0`n");
			 	 			addnav('Selection');
			  				addnav('Yes','runmodule.php?module=dwopenhouse&op=lodge&sop=buy&top=1&dwid='.$row['dwid']);
			  				addnav('No','lodge.php');
				 		}
					}
					else
					{
						output("`n`7J. C. Petersen pulls out a book and flicks through the pages. \"`&Ah, it would seem that you only have one dwelling, and that it's a type the policy doesn't support. Please return later and I'll be happy to do business with you.`7\"`0`n");
						addnav('The Lodge','lodge.php');
					}
				}
				else
				{
					output("`n`7J. C. Petersen pulls out a book and flicks through the pages. \"`&Ah, it would seem that you only have one dwelling, and that it's in a location the policy doesn't cover. Please return later and I'll be happy to do business with you.`7\"`0`n");
					addnav('The Lodge','lodge.php');
				}
			}
			else
			{
				require_once('modules/cityprefs/lib.php');

				$tname = translate_inline('Name');
				$tlocation = translate_inline('Location');
				$ttype = translate_inline('Type');
				$buy = translate_inline('Get Policy');
				$unnamed = translate_inline('Unnamed');
				rawoutput('<table border="0" cellpadding="3" cellspacing="0" align="center">');
				rawoutput('<tr class="trhead"><td align="center">'.$tname.'</td><td align="center">'.$ttype.'</td><td align="center">'.$tlocation.'</td><td align="center" style="width:75px">&nbsp;</td></tr>'); 

			  	addnav('Selection');
				$i = $j = 0;
				while( $row = db_fetch_assoc($result) )
				{
					$paid = get_module_objpref('dwellings', $row['dwid'], 'paid', 'dwopenhouse');
					$name = ( empty($row['name']) ) ? $unnamed : appoencode($row['name']);
					$cname = appoencode(get_module_setting('dwname',$row['type']));
				 	rawoutput('<tr class="'.($j%2?'trlight':'trdark').'"><td>'.$name.'</td><td>'.$cname.'</td><td>'.$row['location'].'</td><td>');

					$cityid = get_cityprefs_cityid('cityname',$row['location']);
					if( get_module_objpref('city', $cityid, 'allowopen', 'dwopenhouse') == 1 )
					{
						$typeid = get_module_setting('typeid',$row['type']);
						if( get_module_objpref('dwellingtypes', $typeid, 'canopen', 'dwopenhouse') == 1  )
						{
							if( $paid == 1 )
							{
								output('- Bought -');
							}
							else
							{
								rawoutput('[ <a href="runmodule.php?module=dwopenhouse&op=lodge&sop=buy&top=1&dwid='.$row['dwid'].'">'.$buy.'</a> ]');
					 			addnav('','runmodule.php?module=dwopenhouse&op=lodge&sop=buy&top=1&dwid='.$row['dwid']);
			  					addnav(array('%s`0',$row['name']),'runmodule.php?module=dwopenhouse&op=lodge&sop=buy&top=1&dwid='.$row['dwid']);
					 			$i++;
					 		}
					 	}
					 	else
					 	{
							if( $paid == 1 ) output('- Bought -');
					 		else output('- None -');
					 	}
					}
					else
					{
						if( $paid == 1 ) output('- Bought -');
					 	else output('- None -');
					}
					rawoutput('</td></tr>');
					$j++;
				}

			  	rawoutput('</table>');
			  	if( $i == 0 )
			  	{
					output("`n`n`7J. C. Petersen pulls out a book and flicks through the pages. \"`&Ah, it would seem that none of your dwellings are supported by the policy. Please return later and I'll be happy to do business with you.`7\"`0`n");
				}
				else
				{
					output("`n`n`7J. C. Petersen pulls out a book and flicks through the pages. \"`&Ah, it would seem that %s of your dwellings can have a policy purchased for them. Which one would you like the policy for?`7\"`0`n", $i);
				}
				addnav('The Lodge','lodge.php');
			}
		}
	}
	elseif( $op == 'open' )
	{
		page_header('Change Open House Policy');

		$type = httpget('type');
		$dwname = get_module_setting('dwname',$type);
		$open = get_module_objpref('dwellings', $dwid, 'open', 'dwopenhouse');
		$mute = get_module_objpref('dwellings', $dwid, 'mute', 'dwopenhouse');
		if( httpget('subop') == 'save' )
		{
			$open = httppost('open');
			$mute = httppost('mute');
			debuglog("changed their open house setting to $open and non keyholders posting to $mute.");
			set_module_objpref('dwellings', $dwid, 'open', $open, 'dwopenhouse');
			set_module_objpref('dwellings', $dwid, 'mute', $mute, 'dwopenhouse');
			output('`n`#You have changed your open house policy. Your dwelling is now %s`# to all.`n', translate_inline($open==1?'`@open':'`$closed'));
			if( $mute == 1 ) output('`n`#Non keyholders will not be able to post anything.`0`n');
			else output('`n`#Everyone will be able to post here.`0`n');
		}

		require_once('lib/showform.php');

		$form = array(
			"Open House Policy,title",
			"open"=>"Open your dwelling to all?,bool",
			"mute"=>"Mute non keyholders?,bool",
		);

		$data = array('open'=>$open,'mute'=>$mute);

		rawoutput('<form action="runmodule.php?module=dwopenhouse&op=open&subop=save&type='.$type.'&dwid='.$dwid.'" method="POST">');
		addnav('','runmodule.php?module=dwopenhouse&op=open&subop=save&type='.$type.'&dwid='.$dwid);
		showform($form,$data);
		rawoutput('</form>');

		addnav('Leave');
		addnav('Back to Management','runmodule.php?module=dwellings&op=manage&dwid='.$dwid);
		addnav(array('Back to the %s',$dwname),'runmodule.php?module=dwellings&op=enter&dwid='.$dwid);
	}
	elseif( $op == 'policy' )
	{
		page_header('Open House Policy');
		output('`n`2You wonder what this open house policy is about and so wander over to a nearby half built farmhouse and ask one of the workers there.`n`n');
		output('"`3Well you see,`2" says one, "`3The open house policy allows you to open your house to anyone and everyone without the need for keys.`2" "`3However,`2" says another, "`3only the owner and key holders will be able to access the dwelling\'s coffers.`2"`n`n');
		output('A 3rd working appears and chimes in, "`3Depending on where you are, not all locations will allow all dwelling types and not all locations and dwelling types will allow an open house policy.`2"`n`n');
		output('"`3Here,`2" he says, "`3Look at these building instructions to find out where and what types do.`2"`0`n`n');

		$sql = "SELECT cityid, cityname
				FROM " . db_prefix('cityprefs');
		$result = db_query($sql);

		$tlocation = translate_inline('Location');
		$tpolicy = translate_inline('Policy');
		$yesno = translate_inline(array('Yes','No'));

		rawoutput('<table border="0" cellpadding="3" cellspacing="1" align="center">');
		rawoutput('<tr class="trhead"><td align="center">'.$tlocation.'</td><td align="center">'.$tpolicy.'</td></tr>'); 

		$i = 0;
		while( $row = db_fetch_assoc($result) )
		{
			$policy = ( get_module_objpref('city', $row['cityid'], 'allowopen', 'dwopenhouse') == 1 ) ? $yesno[0] : $yesno[1];
			if( $session['user']['superuser'] & SU_MEGAUSER )
			{
				$policy = '<a href="runmodule.php?module=cityprefs&op=editmodule&cityid='.$row['cityid'].'&mdule=dwopenhouse">'.$policy.'</a>';
				addnav('','runmodule.php?module=cityprefs&op=editmodule&cityid='.$row['cityid'].'&mdule=dwopenhouse');
			}
			rawoutput('<tr class="'.($i%2?'trlight':'trdark').'"><td align="center">'.$row['cityname'].'</td><td align="center">'.$policy.'</td></tr>');
			$i++;
		}

		rawoutput('</table><br /><br />');

		$sql = "SELECT typeid, module
				FROM " . db_prefix('dwellingtypes');
		$result = db_query($sql);

		$ttype = translate_inline('Type');
		$tpolicy = translate_inline('Policy');
		$yesno = translate_inline(array('Yes','No'));

		rawoutput('<table border="0" cellpadding="3" cellspacing="1" align="center">');
		rawoutput('<tr class="trhead"><td align="center">'.$ttype.'</td><td align="center">'.$tpolicy.'</td></tr>'); 

		$i = 0;
		while( $row = db_fetch_assoc($result) )
		{
			$policy = ( get_module_objpref('dwellingtypes', $row['typeid'], 'canopen', 'dwopenhouse') == 1 ) ? $yesno[0] : $yesno[1];
			$cname = appoencode(dwopenhouse_ucfirst(get_module_setting('dwname',$row['module'])));
			if( $session['user']['superuser'] & SU_MEGAUSER )
			{
				$policy = '<a href="runmodule.php?module=dwellingseditor&op=typeeditmodule&typeid='.$row['typeid'].'&mdule=dwopenhouse">'.$policy.'</a>';
				addnav('','runmodule.php?module=dwellingseditor&op=typeeditmodule&typeid='.$row['typeid'].'&mdule=dwopenhouse');
			}
			rawoutput('<tr class="'.($i%2?'trlight':'trdark').'"><td align="center">'.$cname.'</td><td align="center">'.$policy.'</td></tr>');
			$i++;
		}

		rawoutput('</table><br /><br />');

		if( $session['user']['superuser'] & SU_MEGAUSER )
		{
			output('MEGAUSER message: click on yes/no to get taken to the settings page for that location/type. Sadly no quick way back.');
		}

		addnav('Return to Hamlet','runmodule.php?module=dwellings');
	}
	else
	{
		addnav('Return to Hamlet','runmodule.php?module=dwellings');
	}

	page_footer();
}

function dwopenhouse_ucfirst($name)
{
	$start = '';
	if( substr($name, 0, 1) == '`' )
	{
		$start = substr($name, 0, 2);
		$length = strlen($name);
		$name = substr($name, 2, ($length-2));
	}
	return $start . ucfirst($name);
}
?>