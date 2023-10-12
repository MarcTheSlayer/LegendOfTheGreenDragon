<?php
/**
	Modified by MarcTheSlayer
	08/09/10 - v1.0.0
	+ Fixed a time bug where voting time kept resetting.
	+ Fixed the allegiance bug and a voting bug I found.
	+ Changed forms to showform().
	+ A deleted leader's account will start an election.
	+ A load of other stuff as well. :)
	09/10/10 - v1.0.1
	+ Fixed a bug in the _inaugurate function. One wrong argument and one missing. Found by WickedWizard.
	20/11/10 - v1.0.2
	+ Fixed a bug that stopped the revolt percentage value to turn out wrong when people voted to revolt.
	+ Fixed a bug with the inaugurate function SQL query.
	+ Leaders can change city description for RP purposes.
	17/05/11 - v1.0.3
	+ Fixed 2 small bugs with revolt code.
	23/05/2012 - v1.0.4
	+ Fixed another couple of bugs.
	+ Added FAQ.
	+ Added dwelling code. City Leaders can stop non citizens from building or buying a dwelling.
*/
function cityleaders_getmoduleinfo()
{
	$info = array(
		"name"=>"City Leaders",
		"description"=>"Allows players to nominate a village leader",
		"version"=>"1.0.4",
		"author"=>"<a href='http://www.joshuadhall.com'>Sixf00t4</a> - idea from Sneakabout`2, modified by `@MarcTheSlayer",
		"category"=>"Cities",
		"download"=>"http://dragonprime.net/index.php?topic=11245.0",
		"override_forced_nav"=>TRUE,
		"requires"=>array(
			"cityprefs"=>"20051101|By <a href='http://www.joshuadhall.com'>Sixf00t4</a>, available on DragonPrime",
		), 
		"settings"=>array(
			"City Election,title",
				"anarchy"=>"What percent need to call for an election?,range,1,100,1|40",
				"clength"=>"How long is the campaigning length?,datelength|1 week",
				"vlength"=>"How long is the voting stage?,datelength|2 days",
				"term"=>"Use terms? (leader will need to be voted out otherwise),bool",			
				"tlength"=>"How long is a term?,datelength|1 month",
				"gemscost"=>"How many gems does it cost to run?,int|5",			
				"goldcost"=>"How much gold does it cost to run?,int|5000",
		),
		"prefs-city"=>array(
			"City Election,title",
				"on"=>"Allow leaders for this city?,bool|1",
				"leader"=>"Who is the city leader?,int",
				"newcits"=>"Are players allowed to apply for new citizenship?,bool|1",
				"cityhall"=>"What is the name of the city hall?,string,50|City Hall",
				"title"=>"What is the title of the leader?,string,50|Mayor",
				"usetitle"=>"Leaders can use their titles?,bool",
				"`^If you allow this then be prepared for players abusing this and giving themselves new titles every 5 minutes until you spank them. :),note",
				"citizens"=>"What are citizens called?,string,50|citizens",
				"header"=>"What is the placard notice?,textarea,30|I'll get around to changing this when I figure out how.",
				"cheader"=>"What is the header for the city hall?,textarea,30|You enter the city hall where nothing really seems to be going on at the moment.",
				"allowbuy"=>"Allow non citizens to build dwellings here?,bool|1",
				"allowsale"=>"Allow non citizens to buy dwellings here?,bool|1",
			"Election Stuff,title",
				"votes"=>"How many votes for a new election?,int",
				"date"=>"Last time city had a revolt:,string,30|2010-01-01 00:00:00",
				"status"=>"What is the status of this village?,enum,1,Leader elected,2,Taking runners,3,Voting|2",
				"votedata"=>"The last voting data:,viewonly",
		),
		"prefs"=>array(
			"City Election,title",
				"voted"=>"Did this person vote this election?,bool",
				"revolt"=>"Person has asked for an election?,bool",
				"votes"=>"How many votes have they received?,int",
				"run"=>"Is this person running in the election?,bool",
				"banner"=>"What is this person's banner?,textarea,30",
				"newhome"=>"New city allegiance:,text",
		),
	);		
	return $info;
}

function cityleaders_install()
{
	if( is_module_active('cityleaders') )
	{
		output("`c`b`QUpdating 'cityleaders' Module.`b`n`c");
	}
	else
	{
		output("`c`b`QInstalling 'cityleaders' Module.`b`n`c");
		$sql = "SELECT cityid
				FROM " . db_prefix('cityprefs');
		$result = db_query($sql);
		for( $i=1; $i<=db_num_rows($result); $i++ )
		{
			set_module_objpref('city',$row['cityid'],'date',date('Y-m-d H:i:s'));
		}
	}

	module_addhook_priority('newday',95);
	module_addhook('validateprefs');
	module_addhook('dwellings');
	module_addhook('dwellings-forsale-check');
	module_addhook_priority('delete_character',90);
	module_addhook('bioinfo');
	module_addhook('faq-toc');
	module_addhook('superuser');
	module_addhook('village');
	module_addhook_priority('villagetext',70);
	return TRUE;
}

function cityleaders_uninstall()
{
	output("`n`c`b`Q'cityleaders' Module Uninstalled`0`b`c");
	return TRUE;
}

function cityleaders_dohook($hookname,$args)
{
	global $session;

	switch( $hookname )
	{
		case 'footer-newday':
			// Each race file checks on a newday that the player's city matches their race city.
			// Not good if you've changed allegiance to another city. No way to stop it so only
			// other way is to set it back again.
			$newhome = get_module_pref('newhome');
			if( $newhome != '' ) set_module_pref('homecity',$newhome,'cities',$session['user']['acctid']);
		break;

		case 'validateprefs':
			if( httpget('module') == 'cityleaders' )
			{	// If a SU edits the player's prefs then invalidate banner cache just incase.
				$userid = httpget('userid');
				if( $userid )
				{
					require_once('modules/cityprefs/lib.php');
					$cityid = get_cityprefs_cityid('cityname',get_module_pref('homecity','cities',$userid));
					invalidatedatacache("cityleaders-banners-city-$cityid");
				}
			}
		break;

		case 'dwellings':
		case 'dwellings-forsale-check':
			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
			if( get_module_pref('homecity','cities',$session['user']['acctid']) == $session['user']['location'] ) break;
			// Can non citizens build or buy dwellings in this city?
			if( $hookname == 'dwellings' )
			{
				if( get_module_objpref('city',$cityid,'allowbuy') == 0 )
				{
					$args['allowbuy'] = FALSE;
					output("`^Only the citizens of %s are allowed to establish a dwelling here.`n`n", $session['user']['location']);
				}
			}
			if( $hookname == 'dwellings-forsale-check' )
			{
				if( get_module_objpref('city',$cityid,'allowsale') == 0 )
				{
					$args['nosale']++;
					output("`nOnly the citizens of %s are allowed to buy dwellings here.", $session['user']['location']);
				}
			}
		break;

		case 'delete_character':
			if( $args['dodel'] == FALSE ) break;
			require_once('modules/cityprefs/lib.php');
			$homecity = get_module_pref('homecity','cities',$args['acctid']);
			$cityid = get_cityprefs_cityid('location',$homecity);
			$leader = get_module_objpref('city',$cityid,'leader');
			if( $leader == $args['acctid'] )
			{
				$citizens = stripslashes(get_module_objpref('city',$cityid,'citizens'));
				$title = stripslashes(get_module_objpref('city',$cityid,'title'));
				set_module_objpref('city',$cityid,'leader',0);
				set_module_objpref('city',$cityid,'status',2);
				set_module_objpref('city',$cityid,'votes',0);
				set_module_objpref('city',$cityid,'header','');
				if( $args['deltype'] == 1 ) addnews('`^The %s `^of %s have revolted in a big way and killed off their %s`^. In other news, the %s `^of %s are having elections!!!', $citizens, $homecity, $title, $citizens, $homecity, TRUE);
				elseif( $args['deltype'] == 2 ) addnews('`^The %s `^of %s has decided that the pressures of leadership were too great. `&%s `^was last seen dancing naked through the gardens with flowers in their hair. In other news, the %s `^of %s are having elections!!!', $title, $homecity, $citizens, $homecity, TRUE);
				elseif( $args['deltype'] == 4 ) addnews('`^The %s `^of %s has commited suicide. In other news, the %s `^of %s are having elections!!!', $title, $homecity, $citizens, $homecity, TRUE);
				else addnews('`^The %s `^of %s are having elections!!!', $citizens, $homecity, TRUE);
			}
		break;

		case 'bioinfo':
			output('`^Allegiance to: `@%s`0`n', get_module_pref('homecity','cities',$args['acctid']));
		break;

		case 'faq-toc':
			$t = translate_inline("`@Frequently Asked Questions on City Leaders`0");
			output_notl("&#149;<a href='runmodule.php?module=cityleaders&op=faq'>$t</a><br />", TRUE);
		break;

		case 'superuser':
			addnav('Other');
			addnav('The U.K.','runmodule.php?module=cityleaders&op=uk&from=grotto');
		break;

		case 'village':
			require_once('modules/cityprefs/lib.php');
			require_once('lib/nltoappon.php');
			$newhome = get_module_pref('newhome'); // See comments in footer-newday.
			if( $newhome != '' ) set_module_pref('homecity',$newhome,'cities',$session['user']['acctid']);
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
			if( get_module_objpref('city',$cityid,'on') == 0 ) break; // If no leaders are allowed here then break.
			$status = get_module_objpref('city',$cityid,'status');
			$length = 'clength';
			if( $status == 3 ) $length = 'vlength';
			if( $status == 1 && get_module_setting('term') ) $length = 'tlength';
			$start = strtotime(get_module_objpref('city',$cityid,'date'));
			$end = strtotime(get_module_setting($length), $start);
			if( $status == 1 )
			{
				$msg = get_module_objpref('city',$cityid,'header');
				if( $msg != '' )
				{
					output("`n`n`7A nearby placard shows the latest notice from the %s`0:`n", stripslashes(get_module_objpref('city',$cityid,'title')));
					$style = $title = $onclick = '';
					if( $session['user']['superuser'] & SU_EDIT_USERS )
					{
						addnav('','runmodule.php?module=cityprefs&op=editmodule&cityid='.$cityid.'&mdule=cityleaders');
						$title = translate_inline('Click to edit this placard.');
						$style = 'cursor:hand';
						$onclick = ' onclick="window.location=\'runmodule.php?module=cityprefs&op=editmodule&cityid='.$cityid.'&mdule=cityleaders\'"';
					}
					output_notl('<table cellpadding="1" cellspacing="0" style="border: 1px solid #7F3D1A;%s" align="center" title="%s"%s><tr><td width="90%%">%s</td></tr></table><br />', $style, $title, $onclick, nltoappon(stripslashes($msg)), TRUE);
				}
				if( $end < time() )
				{
					if( get_module_setting('term') == 1 )
					{
						set_module_objpref('city',$cityid,'status',2);
						set_module_objpref('city',$cityid,'date',date('Y-m-d H:i:s'));
					}
				}
			}
			else
			{
				if( $end < time() )
				{
					if( $status == 2 )
					{
						set_module_objpref('city',$cityid,'status',3);
					}
					elseif( $status == 3 )
					{
						cityleaders_inaugurate($cityid);
					}
					set_module_objpref('city',$cityid,'date',date('Y-m-d H:i:s'));
				}
				output("`n`b`\$The village is in revolt! Everyone is talking about who is most fit to be the next leader!`0`n`b");

				$accounts = db_prefix('accounts');
				$userprefs = db_prefix('module_userprefs');
				$sql = "SELECT a.acctid, a.name, c.value
						FROM $accounts a
						INNER JOIN $userprefs b
							ON a.acctid = b.userid AND b.modulename = 'cityleaders' AND b.setting = 'run' AND b.value = 1
						INNER JOIN $userprefs c
							ON b.userid = c.userid AND c.modulename = 'cityleaders' AND c.setting = 'banner' AND c.value != ''
						INNER JOIN $userprefs d
							ON b.userid = d.userid AND d.modulename = 'cities' AND d.setting = 'homecity' AND d.value = '{$session['user']['location']}'";
				$result = db_query_cached($sql,"cityleaders-banners-city-$cityid",86400);
				if( db_num_rows($result) > 0 )
				{
					require_once('lib/sanitize.php');
					$votefor = translate_inline('Vote for');
					$editthis = translate_inline('Click to edit this message from');
					while( $row = db_fetch_assoc($result) )
					{
						$title = $votefor.' '.full_sanitize($row['name']);
						$style = $onclick = '';
						if( $session['user']['superuser'] & SU_EDIT_USERS )
						{
							addnav('','user.php?op=edit&subop=module&userid='.$row['acctid'].'&module=cityleaders');
							$title = $editthis.' '.full_sanitize($row['name']);
							$style = 'cursor:hand';
							$onclick = ' onclick="window.location=\'user.php?op=edit&subop=module&userid='.$row['acctid'].'&module=cityleaders\'"';
						}
						output_notl('<table cellpadding="1" cellspacing="0" style="border: 1px solid #7F3D1A;%s" align="center" width="90%%" title="%s"%s><tr><td width="90%%">%s</td></tr></table><br />', $style, $title, $onclick, nltoappon(stripslashes($row['value'])), TRUE);
					}
				}
			}
			$tl = cityleaders_timeleft(time(), $end);
			if( $status == 3 )
			{
				output("`n`6You have %s left to vote.`0`n",$tl);
			}
			elseif( $status == 2 )
			{
				output("`n`6The Voting booths will open in %s.`0`n",$tl);
			}
			elseif( $status == 1 && get_module_setting('term') )
			{
				output("`n`6There will be a new election in %s.`0`n",$tl);
			}
			tlschema($args['schemas']['tavernnav']);
			addnav($args['tavernnav']);
			tlschema();
			addnav(array('%s`0',translate_inline(stripslashes(get_module_objpref('city',$cityid,'cityhall')))),'runmodule.php?module=cityleaders');
		break;

		case 'villagetext':
			require_once('modules/cityprefs/lib.php');
			$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
			if( get_module_objpref('city',$cityid,'on') == 0 ) break; // If no leaders are allowed here then break.
			if( get_module_objpref('city',$cityid,'status') > 1 )
			{
				$args['talk'] = sprintf_translate('`n`&Nearby some %s `&talk about the election:`n', stripslashes(get_module_objpref('city',$cityid,'citizens')));
				$args['schemas']['talk'] = 'module-cityleaders';
			}
		break;
	}

	return $args;
}

function cityleaders_run()
{
	$op = httpget('op');
	if( $op == 'faq' )
	{
		cityleaders_faq();
	}
	else require_once('modules/cityleaders/cityleaders_run.php');
}

function cityleaders_faq()
{
	tlschema('faq');
	popup_header('City Leader Questions');
	$c = translate_inline('Return to Contents');
	rawoutput("<a href='petition.php?op=faq'>$c</a><hr>");

	output("`n`n`c`bQuestions about City Leaders`b`c`n");
	output("`^1. What are City Leaders?`n");
	output("`@City Leaders are players that ran and were elected the Mayor of their home city.`n");
	output("`^2. How do I become a City Leader of my home city?`n");
	output("`@Wait until there's an election and then sign up as a candidate in the Town Hall. You'll get to post a banner in the city saying what you will do for the people and if the people like you then hopefully you'll get the most votes and win. If you can't wait for an election then you can start a revolt.`n");
	output("`^3. How do I become a City Leader of another City?`n");
	output("`@You can't become a leader of a city that doesn't have your allegiance. By default, you home city is based on your race. To change this you must go to the Town Hall of the city you wish to be your new home and pledge your allegiance to it if the option is available. Once this is done you can now take part in its elections.`n");
	output("`^4. How do I start a revolt?`n");
	output("`@Go to your Town Hall and ask for an election. If you the majority of the population in the city do the same then the current leader will be ousted and a new election process started.`n");
	output("`^5. I'm the City Leader, how do I change my title and stuff?`n");
	output("`@In your chambers, but this depends on whether the staff allow them to be altered. Any abuse will be severely punished.`n");

	rawoutput("<hr><a href='petition.php?op=faq'>$c</a>");
	popup_footer();
}

function cityleaders_inaugurate($cityid)
{
	global $session;
	require_once('modules/cityprefs/lib.php');
	require_once('lib/systemmail.php');

	$accounts = db_prefix('accounts');
	$userprefs = db_prefix('module_userprefs');

	$sql = "SELECT a.name, u1.userid, u1.value AS votes
			FROM $accounts a
			INNER JOIN $userprefs u1
				ON a.acctid = u1.userid AND u1.modulename = 'cityleaders' AND u1.setting = 'votes' AND u1.value != ''
			INNER JOIN $userprefs u2
				ON a.acctid = u2.userid AND u2.modulename = 'cities' AND u2.setting = 'homecity' AND u2.value = '{$session['user']['location']}'
			ORDER BY u1.value+0, a.dragonkills DESC"; // Decending order so the last person will be the one with the most votes.
	$result = db_query($sql);
	$count = db_num_rows($result);
	$votedata = $data = array();
	if( $count > 0 )
	{	// People voted.
		$i = 1;
		while( $row = db_fetch_assoc($result) )
		{
			$data[$i]['name'] = $row['name'];
			$data[$i]['votes'] = $row['votes'];
			array_unshift($votedata, $data[$i]);
			if( $i == $count )
			{	// The last row will be the player with the most votes. They're the new leader.
				set_module_objpref('city',$cityid,'leader',$row['userid']);
				$subject = translate_inline("`2You have been elected City Leader!`0");
				$message = translate_inline(array("`6Congratulations `@%s`6,`n`nYou have been elected the new Leader of %s!`n`n", $row['name'], $session['user']['location']));
				systemmail($row['userid'],$subject,$message);
				addnews("`n`^%s `&has been elected as the new Leader of %s!`0`n", $row['name'], $session['user']['location'], TRUE);
				set_module_objpref('city',$cityid,'status',1);
			}
			$i++;
		}
		set_module_objpref('city',$cityid,'votedata',serialize($votedata));
	}
	else
	{	// Nobody voted.
		$leader = translate_inline(get_module_objpref('city',$cityid,'leader'));
		if( $leader > 0 )
		{	//Existing leader stays in office.
			$sql = "SELECT name
					FROM " . db_prefix('accounts') . "
					WHERE acctid = '$leader'";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			$title = translate_inline(get_module_objpref('city',$cityid,'title'));
			$subject = translate_inline("`2You remain in Office!`0");
			$message = translate_inline(array("Congratulations %s,`n`nYou have fought off any competitors that stood against you and remain in office as the %s of %s!", $row['name'], $title, $session['user']['location']));
			systemmail($leader,$subject,$message);
			addnews("`n`^%s `&remains the elected %s of %s!`0`n", $row['name'], $title, $session['user']['location'], TRUE);
			set_module_objpref('city',$cityid,'status',1);
		}
		else
		{
			addnews("`n`^No leader was elected in %s. Looks like they'll have to revolt again!`0`n", $session['user']['location'], TRUE);
			set_module_objpref('city',$cityid,'status',2); // Taking runners.
		}
		set_module_objpref('city',$cityid,'votedata','');
	}

	$sql = "SELECT userid
			FROM $userprefs
			WHERE modulename = 'cities'
				AND setting = 'homecity'
				AND value = '{$session['user']['location']}'";
	$result = db_query($sql);
	while( $row = db_fetch_assoc($result) )
	{	// Delete all the player prefs for this location.
		clear_module_pref('voted','cityleaders',$row['userid']);
		clear_module_pref('revolt','cityleaders',$row['userid']);
		clear_module_pref('votes','cityleaders',$row['userid']);
		clear_module_pref('run','cityleaders',$row['userid']);
		clear_module_pref('banner','cityleaders',$row['userid']);
	}
	invalidatedatacache("cityleaders-banners-city-$cityid");
}

function cityleaders_timeleft($start,$end)
{
	$parts = translate_inline(array('day','days','hour','hours','minute','minutes','second','seconds'));
	$x = abs($end - $start);
	$d = (int)($x/86400);
	$x = $x % 86400;
	$h = (int)($x/3600);
	$x = $x % 3600;
	$m = (int)($x/60);
	$x = $x % 60;
	$s = (int)($x);
	if( $d > 0 )
	{
		$o = "$d ".($d>1?$parts[1]:$parts[0]).($h>0?", $h ".($h>1?$parts[3]:$parts[2]):'');
	}
	elseif( $h > 0 )
	{
		$o = "$h ".($h>1?$parts[3]:$parts[2]).($m>0?", $m ".($m>1?$parts[5]:$parts[4]):'');
	}
	elseif( $m > 0 )
	{
		$o = "$m ".($m>1?$parts[5]:$parts[4]).($s>0?", $s ".($s>1?$parts[7]:$parts[6]):'');
	}
	else
	{
		$o = "$s ".($s>0?$parts[7]:$parts[6]);		
	}
	return $o;
}

function cityleaders_leadertitle($title = FALSE)
{
	global $session;
	require_once('lib/titles.php');
	require_once('lib/names.php');
	$newtitle = ( $title == FALSE ) ? get_dk_title($session['user']['dragonkills'], $session['user']['sex']) : $title;
	$newname = change_player_title($newtitle);
	$session['user']['title'] = $newtitle;
	$session['user']['name'] = $newname;
}

function cityleaders_debug()
{
	global $session;
	if( $session['user']['superuser'] & SU_DEVELOPER )
	{
		debug("Location: {$session['user']['location']}");
		$sql = "SELECT value
				FROM " . db_prefix('module_userprefs') . "
				WHERE modulename = 'cities'
					AND setting = 'homecity'
					AND value = '" . $session['user']['location'] . "'";
		$result = db_query($sql);
		$count = db_num_rows($result);
		debug("Citizen Count: $count");
		require_once('modules/cityprefs/lib.php');
		$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
		$votes = get_module_objpref('city',$cityid,'votes');
		debug("Revolt Count: $votes");
		debug("Anarchy Percentage: ".get_module_setting('anarchy'));
		debug("Revolt Percentage: ".round(($votes/$count)*100));
		debug("Citizen Percent: ".round(100/$count));
	}
}
?>