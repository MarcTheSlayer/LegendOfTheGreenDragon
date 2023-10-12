<?php
/**
	04/09/2010 - v1.0.0
	Based on the 'bloodbank' module by E Stevens, JT Traub, S Brown.
	Removed the gold side of the bank so that it's only just blood
	and made it so vampires can drink the donated blood for a buff.
*/
function blooddonations_getmoduleinfo()
{
	$info = array(
		"name"=>"Blood Donations",
		"description"=>"Donate blood for a buff. Vampires drink blood for a buff.",
		"version"=>"1.0.0",
		"author"=>"`@MarcTheSlayer",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1440",
		"settings"=>array(
			"Blood Settings,title",
				"bankname"=>"The name of the blood bank:,text|`4Ye Blood Bank",
				"bankloc"=>"Where does the blood bank appear:,location|".getsetting('villagename', LOCATION_FIELDS),
				"pintstotal"=>"How many pints are in the bank?,int|0",
				"perpage"=>"HoF names per page:,int|25",
		),
		"prefs"=>array(
			"Blood Prefs,title",
				"gavetoday"=>"Given blood today?,bool",
				"gavetotal"=>"Total amount of pints donated:,int",
				"dranktoday"=>"Drank blood today?,bool",
				"dranktotal"=>"Total amount of pints drunk:,int",
		)
	);
	return $info;
}

function blooddonations_install()
{
	output("`c`b`Q%s 'blooddonations' Module.`0`b`c`n", translate_inline(is_module_active('blooddonations')?'Updating':'Installing'));
	module_addhook('changesetting');
	module_addhook('newday');
	module_addhook('newday-runonce');
	module_addhook('village');
	module_addhook('footer-hof');
	return TRUE;
}

function blooddonations_uninstall()
{
	output("`4Un-Installing 'blooddonations' Module.`0`n");
	return TRUE;
}

function blooddonations_dohook($hookname,$args)
{
	switch( $hookname )
	{
		case 'changesetting':
			if( $args['setting'] == 'villagename' )
			{
				if( $args['old'] == get_module_setting('bankloc') )
				{
					set_module_setting('bankloc', $args['new']);
				}
			}
		break;

		case 'newday':
			clear_module_pref('gavetoday');
			clear_module_pref('dranktoday');
		break;

		case 'newday-runonce':
			if( get_module_setting('pintstotal') < 10 ) addnews('`$%s `&in %s is running short of `$blood`&!`0', get_module_setting('bankname'), get_module_setting('bankloc'), TRUE);
		break;

		case 'village':
			global $session;
			if( $session['user']['location'] == get_module_setting('bankloc') )
			{
				tlschema($args['schemas']['marketnav']);
				addnav($args['marketnav']);
				tlschema();
				addnav(array('B?%s`0',get_module_setting('bankname')),'runmodule.php?module=blooddonations');
			}
		break;

		case 'footer-hof':
			addnav('Warrior Rankings');
			addnav('`$Blood Donators`0','runmodule.php?module=blooddonations&op=hof&sop=gave');
			addnav('`$Blood Drinkers`0','runmodule.php?module=blooddonations&op=hof&sop=drank');
		break;
	}

	return $args;
}

function blooddonations_run()
{
	global $session;

	$op = httpget('op');

	switch( $op )
	{
		case 'give':
			page_header(full_sanitize(get_module_setting('bankname')));

			if( get_module_pref('gavetoday') == 1 )
			{
				output("`n`7You inform Vladimir that you'd like to give `\$blood `7again.");
				output("Vladimir smiles, but says he'd rather not have you faint in his bank.`n`n");
				output("Perhaps you'll give `\$blood `7another time.`0`n");
			}
			else
			{
				output("`n`7You inform Vladimir that you'd like to give `\$blood `7and he calls his assistant, who takes you into a side room and asks you to get comfortable on a bed.");
				output("`7Your apprehension eases as you realize it isn't nearly as bad as you expected.");
				output("`7Once the collection is complete, you are given some milk and a cookie.`n`n");
				output("`7You feel tired, but really good about yourself!`0`n");
				if( has_buff('bloodbank') )
				{
					$session['bufflist']['bloodbank']['rounds'] += 10;
				}
				else
				{
					apply_buff('bloodbank',array(
						"name"=>"`@Selfless Giving`0",
						"rounds"=>20,
						"defmod"=>1.2
						)
					);
				}
				$session['user']['hitpoints'] *= 0.9;
				increment_module_setting('pintstotal');
				set_module_pref('gavetoday',1);
				increment_module_pref('gavetotal');
			}
		break;

		case 'drink':
			page_header(full_sanitize(get_module_setting('bankname')));

			if( get_module_pref('dranktoday') == 1 )
			{
				output("`n`7You inform Vladimir that you'd like to drink some more `\$blood `7again.");
				output("Vladimir smiles, but says he'd rather not have you drunk in his bank and scaring off potential customers.`n`n");
				output("Perhaps you can come back tomorrow.`0`n");
			}
			elseif( get_module_setting('pintstotal') > 0 )
			{
				output("`n`7You inform Vladimir that you're after something to drink.");
				output("`7He looks around to make sure that nobody is looking and pulls a `\$bottle of blood `7out from under the counter and hands it to you. \"`&Freshly donated today.`7\" he smirks.`n`n");
				output("You can feel the warmth as you hold the bottle in your hand and sneak off to a dark corner to drink it.`n`n");
				output("`7You feel stronger and more healthy!`0`n");
				if( has_buff('bloodbank') )
				{
					$session['bufflist']['bloodbank']['rounds'] += 10;
				}
				else
				{
					apply_buff('bloodbank',array(
						"name"=>"`\$Blood Boost`0",
						"rounds"=>20,
						"atkmod"=>1.2
						)
					);
				}
				$session['user']['hitpoints'] *= 1.1;
				increment_module_setting('pintstotal', -1);
				set_module_pref('dranktoday',1);
			}
			else
			{
				output("`n`7Vladimir shakes his head, \"`&Didn't you see the sign, the bank is empty. Go feast on someone in the fields.`7\"`0`n");
			}
		break;

		case 'hof':
			page_header('Hall of Fame');
			$sop = httpget('sop');
			$sop = ( !empty($sop) ) ? $sop : 'gave';
			$perpage = get_module_setting('perpage');
			$pageoffset = (int)httpget('page');
			if( $pageoffset > 0 ) $pageoffset--;
			$pageoffset *= $perpage;
			$from = $pageoffset+1;
			$limit = "LIMIT $pageoffset, $perpage";
			$sql = "SELECT COUNT(userid) AS total
					FROM " . db_prefix('module_userprefs') . "
					WHERE modulename = 'blooddonations' 
						AND setting = '".$sop."total' 
						AND value+0 > 0";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			$total = $row['total'];
			$cond = ( $from + $perpage < $total ) ? $pageoffset + $perpage : $total;

			$sql = "SELECT a.name, b.value AS pints
					FROM " . db_prefix('accounts') . " a, " . db_prefix('module_userprefs') . " b 
					WHERE b.modulename = 'blooddonations' 
						AND b.setting = '".$sop."total'
						AND b.value+0 > 0
						AND a.acctid = b.userid
					ORDER BY (b.value+0) DESC, b.userid ASC 
					$limit";
			$result = db_query($sql);
			$count = db_num_rows($result);

			$rank = translate_inline('Rank');
			$name = translate_inline('Name');
			$pints = translate_inline('Pints');

			if( $sop == 'gave' ) output("`c`b`^Top `\$Blood `^Donators in the Land`b`c`0`n");
			else output("`c`b`^Top `\$Blood `^Drinkers in the Land`b`c`0`n");
			rawoutput('<table border="0" cellpadding="2" cellspacing="1" align="center" bgcolor="#999999">');
			rawoutput("<tr class=\"trhead\"><td>$rank</td><td>$name</td><td>$pints</td></tr>");

			if( $count > 0 )
			{
				$i = 1;
				while( $row = db_fetch_assoc($result) )
				{
					if( $row['name'] == $session['user']['name'] )
					{
						rawoutput('<tr class="trhilight"><td align="center">');
					}
					else
					{
						rawoutput('<tr class="'.($i%2?'trdark':'trlight').'"><td align="center">');
					}
					rawoutput("$i</td><td>");
					output_notl('`&%s`0', $row['name']);
					rawoutput('</td><td align="center">');
					output_notl('`@%s`0', $row['pints']);
					rawoutput('</td></tr>');
					$i++;
				}
			}
			else
			{
				rawoutput('<tr class="trlight"><td colspan="3">');
				if( $sop == 'gave' ) output('`&Nobody has given `$blood `&yet.`0');
				else  output('`&No Vampires have drunk `$blood `&yet.`0');
				rawoutput('</td></tr>');
			}

			rawoutput('</table>');

			if( $total > $perpage )
			{
				addnav('Pages');
				for( $p = 0; $p < $count && $cond; $p += $perpage )
				{
					addnav(array('Page %s (%s-%s)', ($p/$perpage+1), ($p+1), min($p+$perpage,$count)), 'runmodule.php?module=blooddonations&op=hof&page='.($p/$perpage+1));
				}
			}

			if( $sop == 'gave' ) addnav('`$Blood Drinkers`0','runmodule.php?module=blooddonations&op=hof&sop=drank');
			else addnav('`$Blood Donators`0','runmodule.php?module=blooddonations&op=hof&sop=gave');

			addnav('Back');
			addnav('Back to HoF','hof.php');
		break;

		default:
			page_header(full_sanitize(get_module_setting('bankname')));
			addnav('Options');
			if( $session['user']['race'] == 'Vampire' )
			{
				output("`7You casually stroll in to the bank through a darkened doorway and see a vampire standing behind a counter.`n`n");
				addnav('Drink Blood','runmodule.php?module=blooddonations&op=drink');
			}
			else
			{
				output("`7You cautiously enter the bank through a darkened doorway and see a man standing behind a counter that looks some what like a vampire.`n`n");
				addnav('Give Blood','runmodule.php?module=blooddonations&op=give');
			}
			$total = get_module_setting('pintstotal');
			if( empty($total) || $total < 0 ) $total = 0;
			if( $total == 1 ) output("A sign on the counter tells you that the bank is holding `$1 pint of blood");
			else output("A sign on the counter tells you that the bank is holding `$%s pints of blood", $total);
			if( $total < 10 ) output(' `7and that more is urgently needed to help save injured warriors.`n`n');
			else output('`7.`n`n');
			output("`7\"`&Greetings, %s, I am Vladimir`7\" he says with a smile. \"`&How may I assist you today?`7\"`0`n", translate_inline($session['user']['sex']?'Madam':'Sir'));
		break;
	}

	addnav('Leave');
	villagenav();
	page_footer();
}
?>