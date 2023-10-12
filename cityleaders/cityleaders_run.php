<?php
	global $session;

	$sop = httpget('sop');

	require_once('modules/cityprefs/lib.php');
	$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);

	$cityhall = stripslashes(get_module_objpref('city',$cityid,'cityhall'));
	$leader = get_module_objpref('city',$cityid,'leader');
	$citizens = stripslashes(get_module_objpref('city',$cityid,'citizens'));
	$status = get_module_objpref('city',$cityid,'status');
	$title = stripslashes(get_module_objpref('city',$cityid,'title'));
	
	if( $op == 'council' ) page_header(array('%s Discussion',$cityhall));
	elseif( $op == 'uk' ) page_header('The United Kingdoms');
	else page_header($cityhall);

	$submit = translate_inline('Submit');

	switch( $op )
	{
		case 'chambers':
			addnav('Change');
			addnav(array('%s`0 Description',$cityhall),'runmodule.php?module=cityleaders&op=chambersedit&sop=header&var=cheader');
			addnav(array('%s`0 Name',$cityhall),'runmodule.php?module=cityleaders&op=chambersedit&sop=change&var=cityhall');
			addnav(array('%s`0 Title',$title),'runmodule.php?module=cityleaders&op=chambersedit&sop=change&var=title');
			addnav('City Square Message','runmodule.php?module=cityleaders&op=chambersedit&sop=header&var=header');
			addnav('Citizen Name','runmodule.php?module=cityleaders&op=chambersedit&sop=change&var=citizens');
			addnav('Citizens');
			if( get_module_objpref('city',$cityid,'newcits') ) addnav('Deny all applications','runmodule.php?module=cityleaders&op=chambersedit&sop=appl&var=0');
			else addnav('Allow new applications','runmodule.php?module=cityleaders&op=chambersedit&sop=appl&var=1');

			if( is_module_active('dwellings') )
			{
				addnav('Dwellings');
				if( get_module_objpref('city',$cityid,'allowbuy') ) addnav('Deny non citizen building','runmodule.php?module=cityleaders&op=chambersedit&sop=building&var=0');
				else addnav('Allow non citizen building','runmodule.php?module=cityleaders&op=chambersedit&sop=building&var=1');
				if( get_module_objpref('city',$cityid,'allowsale') ) addnav('Deny non citizen buying','runmodule.php?module=cityleaders&op=chambersedit&sop=buying&var=0');
				else addnav('Allow non citizen buying','runmodule.php?module=cityleaders&op=chambersedit&sop=buy&var=1');
			}

			modulehook('cityleaders-leader');

			addnav('Other');
			addnav('The U.K.','runmodule.php?module=cityleaders&op=uk');
			if( $status == 1 )
			{
				addnav('`$Quit!`0');
				addnav(array('Resign as %s`0',$title),'runmodule.php?module=cityleaders&op=resign');
			}
		break;

		case 'chambersedit':
			$var = httpget('var');
			switch( $sop )
			{
				case 'header':
					require_once('lib/showform.php');
					require_once('lib/nltoappon.php');
					$old = stripslashes(get_module_objpref('city',$cityid,$var));
					$new = trim(strip_tags(stripslashes(httppost('new'))));
					if( strlen($new) >= 255 ) output('`n`$The message was longer than 255 characters, but not anymore. `^=)`0`n');
					$new = substr($new, 0, 255);
					if( !empty($new) )
					{
						set_module_objpref('city',$cityid,$var,$new);
						output("`n`3The message is now:`0`n`n%s`0", nltoappon($new));
					}
					else
					{
						$new = $old;
						output("`n`3The message is currently:`0`n`n%s`0", nltoappon($new));
					}
					$text1 = translate_inline('City Message');
					$text2 = translate_inline('Description');
					$text3 = ( $var == 'header' ) ? $text1 : $text2;
					$data = array('new'=>$new);
					$form = array("$text3,title","new"=>"Change $text3:,textarea,40","`^The text length cannot exceed 255 characters. If it does then only the first 255 characters will be saved. Also don't post anything that breaks the rules or messes up the village page.,note");
					rawoutput('<form action="runmodule.php?module=cityleaders&op=chambersedit&sop=header&var='.$var.'" method="POST" autocomplete="FALSE">');
					addnav('','runmodule.php?module=cityleaders&op=chambersedit&sop=header&var='.$var);
					showform($form,$data);
					rawoutput('</form>');
				break;

				case 'appl':
					if( $var == 1 )
					{
						output('`n`3New applications for citizenship are now enabled.`n');
					}
					else
					{
						output('`n`3New applicants for citizenship are now disabled.`n');
					}
					set_module_objpref('city',$cityid,'newcits',$var);				
				break;

				case 'building':
					if( $var == 1 )
					{
						output('`n`3Non citizens are now allowed to establish a dwelling in this city.`n');
					}
					else
					{
						output('`n`3Non citizens are *NOT* allowed to establish a dwelling in this city now.`n');
					}
					set_module_objpref('city',$cityid,'allowbuy',$var);				
				break;

				case 'buying':
					if( $var == 1 )
					{
						output('`n`3Non citizens are now allowed to buy a dwelling in this city.`n');
					}
					else
					{
						output('`n`3Non citizens are *NOT* allowed to buy a dwelling in this city now.`n');
					}
					set_module_objpref('city',$cityid,'allowsale',$var);				
				break;

				case 'change':
					if( $var == 'title' && get_module_objpref('city',$cityid,'usetitle') == 1 )
					{
						addnav('Option');
						if( $session['user']['title'] == $title ) addnav('Take Title','runmodule.php?module=cityleaders&op=chambersedit&sop=ttitle&var=remove');
						else addnav('Take Title','runmodule.php?module=cityleaders&op=chambersedit&sop=ttitle&var=take');
					}
					require_once('lib/showform.php');
					$old = stripslashes(get_module_objpref('city',$cityid,$var));
					$new = trim(strip_tags(stripslashes(httppost('new'))));
					if( !empty($new) )
					{
						set_module_objpref('city',$cityid,$var,$new);
						output("`n`#`b%s`b `3will now be called `#`b%s`b`3.", $old, $new);
					}
					else
					{
						output("`n`3Currently called `#`b%s`b`3.", $old);
						$new = $old;
					}
					$data = array("new"=>$new);
					$form = array(ucfirst($var).",title","new"=>"Change $var:,string,30","`^Don't abuse this or it'll be taken away!,note");
					rawoutput('<form action="runmodule.php?module=cityleaders&op=chambersedit&sop=change&var='.$var.'" method="POST">');
					addnav('','runmodule.php?module=cityleaders&op=chambersedit&sop=change&var='.$var);
					showform($form,$data);
					rawoutput('</form>');
				break;

				case 'ttitle':
					if( $var == 'remove' )
					{
						cityleaders_leadertitle();
						output('`nAfter carefully consideration, you decide to return to your normal title.`n`n');
					}
					else
					{
						cityleaders_leadertitle($title);
						output('`nAfter carefully creating a title that doesn\'t break any rules and reflects your position as leader of %s. You give yourself the title.`n`n', $session['user']['location']);
					}
					output('You\'re now known as %s`0.', $session['user']['name']);
				break;
			}

			addnav('Private');
			addnav(array('%s Chambers', $title),'runmodule.php?module=cityleaders&op=chambers');
		break;
		
		case 'uk':
			$from = httpget('from');
			if( $from == 'grotto' )
			{
				addnav('Grotto');
				addnav('Back to the Grotto','superuser.php');
				blocknav('runmodule.php?module=cityleaders');
			}
			else
			{
				addnav('Private');
				addnav(array('%s Chambers', $title),'runmodule.php?module=cityleaders&op=chambers');
			}

			output("`n`6You have entered a large auditorium of sorts. Large paintings of doves with branches in their beaks decorate the walls.");
			output(" There are lots of other leaders from all around the kingdom here. Discussions have already begun about diplomacy, kingdom peace, exchange rates, and about global farmboy poverty.`0`n`n");

			require_once('lib/commentary.php');
			addcommentary();
			commentdisplay('','The-UK','Many leaders express their ideas here',15,'diplomatically says');
		break;

		case 'apply':
			switch( $sop )
			{
				case '2':
					$homecity = get_module_pref('homecity','cities');
					$cityid2 = get_cityprefs_cityid('location',$homecity);
					$leader2 = get_module_objpref('city',$cityid2,'leader');
					if( $leader2 == $session['user']['acctid'] )
					{
						$citizens2 = stripslashes(get_module_objpref('city',$cityid2,'citizens'));
						$title2 = stripslashes(get_module_objpref('city',$cityid2,'title'));
						if( get_module_objpref('city',$cityid2,'usetitle') == 1 ) cityleaders_leadertitle();
						set_module_objpref('city',$cityid2,'leader',0);
						set_module_objpref('city',$cityid2,'status',2);
						set_module_objpref('city',$cityid2,'votes',0);
						set_module_objpref('city',$cityid2,'date',date('Y-m-d H:i:s'));
						output('`n`3You abandon your job, your title and your home city of %s.`n', $homecity);
						addnews('`^The %s `^of %s has abandoned their leadership. `&%s `^couldn\'t take the pressure!. In other news, the %s `^of %s are having elections!!!', $title2, $homecity, $session['user']['name'], $citizens2, $homecity, TRUE);
					}
					output('`n`3%s welcomes you. Your allegiance to %s will remain even after you slay the Dragon or become another race.`0`n', $session['user']['location'], $session['user']['location']);
					addnews("%s has pledged allegiance to %s! They have abandoned their citizenship to %s!", $session['user']['name'], $session['user']['location'], $homecity);
					set_module_pref('homecity',$session['user']['location'],'cities');
					set_module_setting("newest-{$session['user']['location']}",$session['user']['acctid'],'cities');
					db_query("DELETE FROM " . db_prefix('module_userprefs') . " WHERE modulename = 'cityleaders' AND userid = '{$session['user']['acctid']}'");
					set_module_pref('newhome',$session['user']['location']);
					invalidatedatacache("cityleaders-banners-city-$cityid");
				break;

				default:
					output("`n`3If you chose to call %s home, you will be abandoning your current allegiance to %s.", $session['user']['location'], get_module_pref('homecity','cities'));
					addnav('Choices');
					addnav(array('Pledge allegiance to %s',$session['user']['location']),'runmodule.php?module=cityleaders&op=apply&sop=2');
					addnav('No Thanks','runmodule.php?module=cityleaders');
				break;
			}
		break;

		case 'election':
			switch( $sop )
			{
				case 'votedata':
					output('`nYou go down to the basement to look for the boxes that contain the votes from the last election.`n`n');
					$votedata = @unserialize(get_module_objpref('city',$cityid,'votedata'));
					if( is_array($votedata) && !empty($votedata) )
					{
						output('You find the boxes and the old voting sheets that show the names of those that were running in the last election and how many votes they each got. There\'s a note that says that in the event of a tie, the person with the most Dragon killing experience shall win.`n`n');
						$name = translate_inline('Name');
						$votes = translate_inline('Votes');
						$i = 0;
						rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999" align="center">');
						rawoutput('<tr class="trhead"><td>'.$name.'</td><td align="center">'.$votes.'</td></tr>');
						foreach( $votedata as $key => $value )
						{
							rawoutput('<tr class="' . ($i%2?'trdark':'trlight') . '"><td>');
							output_notl('`&%s`0', $votedata[$key]['name']);
							rawoutput('</td><td align="center">'.$votedata[$key]['votes'].'</td></tr>');
							$i++;
						}
						rawoutput('</table>');
					}
					else
					{
						output('Try as you might, you just can\'t find them. Something smells off, maybe it\'s time for a revolt?`n');
					}
				break;

				case 'ask':
					if( get_module_pref('revolt') == 1 )
					{
						output("`n`3I know it only takes one to lead a revolution, but your desire for a revolt has already been noted.");
					}
					else
					{
						output("`n`3If you the majority of the population in this city call for an election, the current leader will be ousted and a new leader can be elected.`n`n");
						output("Are you sure you want to contribute to the revolt?`0`n");
						addnav('Options');
						addnav('Yes','runmodule.php?module=cityleaders&op=election&sop=yes');
						addnav('No','runmodule.php?module=cityleaders');
					}
				break;

				case 'yes':
					$votes = get_module_objpref('city',$cityid,'votes') + 1;
					set_module_objpref('city',$cityid,'votes',$votes);
					set_module_pref('revolt',1);
					$sql = "SELECT value
							FROM " . db_prefix('module_userprefs') . "
							WHERE modulename = 'cities'
								AND setting = 'homecity'
								AND value = '" . $session['user']['location'] . "'";
					$result = db_query($sql);
					$count = db_num_rows($result);
					$revolt_percentage = round(($votes/$count)*100);
					$anarchy = get_module_setting('anarchy');
					if( $revolt_percentage >= $anarchy )
					{
						set_module_objpref('city',$cityid,'status',2);
						set_module_objpref('city',$cityid,'votes',0);
						output("`n`3You have begun the revolt! New potential leaders may now enter their interest in being elected!");
					}
					else
					{
						$person_percent = round(100/$count);
						$j = 1;
						for( $i=1; $i<$count; $i++ )
						{
							if( ($person_percent*$i) >= $anarchy ) break;
							$j++;
						}
						$j = $j - $votes;
						if( $j == 1 )
						{
							output("`n`3Your request to have a new election has been noted. 1 more vote is still needed though.");
						}
						else
						{
							output("`n`3Your request to have a new election has been noted. %s more votes are still needed though.", $j);
						}
					}
				break;

				case 'run':
					$gold = get_module_setting('goldcost');
					$gems = get_module_setting('gemscost');
					if( $session['user']['gold'] < $gold || $session['user']['gems'] < $gems )
					{
						output("`n`3You will need `^%s gold `3and `$%s %s `3in order to run for office.`0",$gold, $gems, translate_inline($gems==1?'gem':'gems'));
					}
					else
					{
						$session['user']['gold'] -= $gold;
						$session['user']['gems'] -= $gems;
						set_module_pref('run',1);
						output("`n`3Your interest in running for leader has been noted. If you would like to edit you banner, you can do so with the option to your left.");
						addnav('Options');
						addnav('Edit your Banner','runmodule.php?module=cityleaders&op=election&sop=banner');
					}
				break;

				case 'banner':
					require_once('lib/showform.php');
					require_once('lib/nltoappon.php');
					$old = stripslashes(get_module_pref('banner'));
					$new = trim(strip_tags(stripslashes(httppost('new'))));
					if( strlen($new) >= 255 ) output('`n`$Your banner was longer than 255 characters, but not anymore. `^=)`0`n');
					$new = substr($new, 0, 255);
					if( !empty($new) )
					{
						set_module_pref('banner',$new);
						output("`n`3Your banner now reads:`0`n`n");
						output_notl('<table cellpadding="1" cellspacing="0" style="border: 1px solid #7F3D1A" align="center"><tr><td width="90%%">%s</td></tr></table>', nltoappon($new), TRUE);
						invalidatedatacache("cityleaders-banners-city-$cityid");
					}
					elseif( !empty($old) )
					{
						$new = $old;
						output("`n`3Your banner currently reads:`0`n`n");
						output_notl('<table cellpadding="1" cellspacing="0" style="border: 1px solid #7F3D1A" align="center"><tr><td width="90%%">%s</td></tr></table>', nltoappon($new), TRUE);
					}
					else
					{
						$new = $old;
						output("`n`3Your banner is currently blank.`0");
					}

					$data = array("new"=>$new);
					$form = array("Your Banner,title","new"=>"Change Banner:,textarea,40","`^The text length cannot exceed 255 characters. If it does then only the first 255 characters will be saved. Also don't post anything that breaks the rules or messes up the village page.,note");
					rawoutput('<form action="runmodule.php?module=cityleaders&op=election&sop=banner" method="POST">');
					addnav('','runmodule.php?module=cityleaders&op=election&sop=banner');
					showform($form,$data);
					rawoutput('</form>');
				break;

				case 'vote':
					output("`n`3Outside the voting booths, you can see all the candidates banners, trying to get that last drop of political propaganda.`n`n");
					output("You read all what they have to say and try and decide who best to vote for...`n`n");
	
					$sql = "SELECT a.acctid, a.name
							FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
							WHERE b.modulename = 'cityleaders'
								AND b.setting = 'run'
								AND b.value = 1
								AND a.acctid = b.userid";
					$result = db_query($sql);
					addnav('Vote For...');
					while( $row = db_fetch_assoc($result) )
					{
						if( get_module_pref('homecity','cities',$row['acctid']) == $session['user']['location'] )
						{
							addnav(array('%s',$row['name']),'runmodule.php?module=cityleaders&op=election&sop=vote2&who='.$row['acctid']);
							output("`3%s - `#\"`@%s`#\"`n`n", $row['name'], get_module_pref('banner','cityleaders',$row['acctid']));
						}
					}
				break;

				case 'vote2';
					$who = httpget('who');
					increment_module_pref('votes',1,'cityleaders',$who);
					set_module_pref('voted',1);
					output("`n`3Your vote has been entered. Now you must wait until the voting period is over and a new leader is elected!");
				break;
			}
		break;

		case 'resign':
			output('`nYou write a letter of resignation and hand it in on your way out the door with your belongings in a box and leaving everything else behind.`0`n');
			if( get_module_objpref('city',$cityid,'usetitle') == 1 ) cityleaders_leadertitle();
			set_module_objpref('city',$cityid,'leader',0);
			set_module_objpref('city',$cityid,'status',2);
			set_module_objpref('city',$cityid,'votes',0);
			set_module_objpref('city',$cityid,'date',date('Y-m-d H:i:s'));
			addnews('`^The %s `^of %s has resigned. `&%s `^couldn\'t take the pressure!. In other news, the %s `^of %s are having elections!!!', $title, $session['user']['location'], $session['user']['name'], $citizens, $session['user']['location']);
		break;

		case 'list':
			output('`n`c`b`^The %s `^of %s`0`b`c`n`n', $citizens, $session['user']['location']);
			
			$sql = "SELECT count(userid) AS c
					FROM " . db_prefix('module_userprefs') . "
					WHERE modulename = 'cities'
						AND setting = 'homecity'
						AND value = '" . $session['user']['location'] . "'";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			$totalplayers = $row['c'];

			if( $totalplayers > 0 )
			{
				$playersperpage = 25;
				$page = httpget('page');
				$pageoffset = (int)$page;
				if( $pageoffset > 0 ) $pageoffset--;
				$pageoffset *= $playersperpage;
				$from = $pageoffset+1;
				$to = min($pageoffset+$playersperpage,$totalplayers);

				$limit = " LIMIT $pageoffset,$playersperpage ";

				addnav('Pages');
				for( $i=0; $i<$totalplayers; $i+=$playersperpage )
				{
					$pnum = $i/$playersperpage+1;
					if( $page == $pnum )
					{
						addnav(array(" ?`b`#Page %s`0 (%s-%s)`b", $pnum, $i+1, min($i+$playersperpage,$totalplayers)), "runmodule.php?module=cityleaders&op=list&page=$pnum");
					}
					else
					{
						addnav(array(" ?Page %s (%s-%s)", $pnum, $i+1, min($i+$playersperpage,$totalplayers)), "runmodule.php?module=cityleaders&op=list&page=$pnum");
					}
				}

				$sql = "SELECT a.name, a.acctid, a.login
						FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b
						WHERE b.modulename = 'cities'
							AND b.setting = 'homecity'
							AND b.value = '" . $session['user']['location'] . "'
							AND a.acctid = b.userid
						ORDER BY login ASC $limit";
				$result = db_query($sql);

				rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999" align="center">');
				rawoutput('<tr class="trhead"><td>' . translate_inline('Name') . '</td></tr>');
				$i = 0;
				while( $row = db_fetch_assoc($result) )
				{
					rawoutput('<tr class="' . ($i%2?'trdark':'trlight') . '"><td><a href="bio.php?char=' . $row['acctid'] . '&ret=' . URLEncode($_SERVER['REQUEST_URI']) . '">');
					addnav('','bio.php?char=' . $row['acctid'] . '&ret=' . URLEncode($_SERVER['REQUEST_URI']));
					output_notl('`&%s`0', $row['name']);
					rawoutput('</a>');
					rawoutput('</td></tr>');
					$i++;
				}
				rawoutput('</table>');
			}
			else
			{
				output('No warriors currently call this place home.`0`n');
			}
		break;

		case 'council':
			require_once('lib/commentary.php');
			addcommentary();
			commentdisplay('','cityhall-'.$cityid,'Many people express their ideas here',15,'says');
		break;

		default:
			$cheader = stripslashes(get_module_objpref('city',$cityid,'cheader'));
			if( $cheader == '' || $leader <= 0 )
			{
				output_notl('`n`cYou enter the city hall of %s, where nothing really seems to be going on at the moment.`c`n`0', $session['user']['location']);
			}
			else
			{
				output_notl('`n`c%s`c`n`0', $cheader);
			}

			$sql = "SELECT name
					FROM " . db_prefix('accounts') . "
					WHERE acctid = '$leader'";
			$result = db_query($sql);
			if( $row = db_fetch_assoc($result) )
			{
				output("`n`cA painting of %s`0 hangs on the wall.`c`n", $row['name']);
			}
			else
			{
				output('`n`cA painting hangs on the wall of nobody in particular.`c`n');
			}

			if( $session['user']['acctid'] == $leader )
			{
				addnav('Private');
				addnav(array('%s Chambers', $title),'runmodule.php?module=cityleaders&op=chambers');
			}

			addnav('Options');
			addnav('Center Council','runmodule.php?module=cityleaders&op=council');
			addnav(array('List of %s',$citizens),'runmodule.php?module=cityleaders&op=list');

			$start = strtotime(get_module_objpref('city',$cityid,'date'));
			$length = 'clength';
			if( $status == 3 ) $length = 'vlength';
			if( $status == 1 && get_module_setting('term') ) $length = 'tlength';
			$end = strtotime(get_module_setting($length), $start);
			$tl = cityleaders_timeleft(time(), $end);

			$homecity = get_module_pref('homecity','cities');
			if( $homecity != $session['user']['location'] && get_module_objpref('city',$cityid,'newcits') == 1 )
			{
				addnav('Apply for Citizenship','runmodule.php?module=cityleaders&op=apply');
			}
			if( $status == 1 && $homecity == $session['user']['location'] )
			{
				if( get_module_setting('term') )
				{
					output("`nThere will be a new election in %s.`n", $tl);
				}
				addnav('Ask for an election','runmodule.php?module=cityleaders&op=election&sop=ask');
				addnav('View last poll data','runmodule.php?module=cityleaders&op=election&sop=votedata');
			}
			elseif( $status == 2 )
			{
				if( $homecity == $session['user']['location'] )
				{
					if( get_module_pref('run') )
					{
						addnav('Edit your Banner','runmodule.php?module=cityleaders&op=election&sop=banner');
					}
					else
					{
						$gold = get_module_setting('goldcost');
						$gems = get_module_setting('gemscost');
						output('`nTo run for leader costs `^%s gold`0 and `% %s %s`0.`n', $gold, $gems, translate_inline($gems==1?'gem':'gems'));
						addnav('Run for Leader','runmodule.php?module=cityleaders&op=election&sop=run');
					}
				}
				output("`nThe Voting booths will open in %s.`n", $tl);
			}
			elseif( $status == 3 && $homecity == $session['user']['location'] )
			{
				if( get_module_pref('voted') == 0 )
				{
					output("`nYou have %s left to vote.`n", $tl);
					addnav('Vote','runmodule.php?module=cityleaders&op=election&sop=vote');
				}
				else
				{
					output("`nThankyou for voting.`n");
				}
			}

			modulehook('cityleaders',array('leader'=>$leader,'cityid'=>$cityid,'status'=>$status));
			
			cityleaders_debug();
		break;
		//
		// Developer stuff.
		//
		case 'status':
			if( $status == 3 )
			{
				$status = 1;
				cityleaders_inaugurate($cityid);
			}
			else
			{
				$status++;
				set_module_objpref('city',$cityid,'status',$status);
			}
			set_module_objpref('city',$cityid,'date',date('Y-m-d H:i:s'));
		break;

		case 'rigvotes':
			for( $i=1; $i<=10; $i++ )
			{
				set_module_pref('voted',1,'cityleaders',$i);
				set_module_pref('votes',rand(0,10),'cityleaders',$i);
				set_module_pref('run',1,'cityleaders',$i);
				set_module_pref('banner','Banner '.$i,'cityleaders',$i);
				set_module_pref('homecity',$session['user']['location'],'cities',$i);
			}
		break;
	}

	addnav('Navigation');
	if( $op != '' ) addnav(array('Back to %s',$cityhall),'runmodule.php?module=cityleaders');
	villagenav();

	if( $session['user']['superuser'] & SU_DEVELOPER )
	{
	//	addnav('Developer');
	//	if( $status == 1 ) addnav('Start Revolt','runmodule.php?module=cityleaders&op=status');
	//	if( $status == 2 ) addnav('Start Voting','runmodule.php?module=cityleaders&op=status');
	//	if( $status == 3 ) addnav('End Election','runmodule.php?module=cityleaders&op=status');
	//	addnav('Rig Votes','runmodule.php?module=cityleaders&op=rigvotes');
	}
	if( $session['user']['superuser'] & SU_EDIT_USERS )
	{
		addnav('Superuser');
		addnav('Y?Edit City Settings','runmodule.php?module=cityprefs&op=editmodule&cityid='.$cityid.'&mdule=cityleaders');
	}

	page_footer();
?>