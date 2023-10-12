<?php
/**
	Modified by MarcTheSlayer
	24/02/2010 - v1.0.0
	+ Setting to turn off taxing, but people can still donate.
	+ Setting to fix the max tax rate. Stops leaders going crazy and selecting 100.
	+ Only the gold in the bank gets taxed, not gold onhand.
	+ Each leader can select to tax the weapon/armour purchases in their city, the tax for which goes into the city fund.
	+ If alignment module is active then for every 5k gold or 1 gem you get 1 point.
	14/09/2010 - v1.0.1
	+ Had a couple of 'out' instead of 'output'. Thanks WickedWizard. :)
	+ Changed 'footer-newday' to just 'newday' as taxes were being taken on the select race/specialty and pages in between. Thanks Calen. :)
	23/05/2013 - v1.0.2
	+ Some small tweaks.
	+ Added a wage option for leaders. Gets paid out of the city coffers.
*/
function cityfunds_getmoduleinfo()
{
	$info = array(
		"name"=>"City Funds",
		"description"=>"Allows players to donate to their home city. City Leaders can set a tax on gold.",
		"version"=>"1.0.2",
		"author"=>"<a href='http://www.joshuadhall.com'>Sixf00t4</a>`2, modified by `@MarcTheSlayer",
		"category"=>"Cities",
		"download"=>"http://dragonprime.net/index.php?topic=11245.0",
		"requires"=>array(
			"cityleaders"=>"1.0.4|By <a href='http://www.joshuadhall.com'>Sixf00t4</a>`2, modified by `@MarcTheSlayer",
		),
		"settings"=>array(
			"Tax Settings,title",
				"allowtax"=>"Allow leaders to set a tax?,bool|1",
				"maxtax"=>"What is the maximum tax rate allowed?,range,0,100,1|15",
				"`^The leaders wont be able to set their tax rate above this.,note",
				"stipend"=>"Leaders get paid a gold wage from the coffers:,int",
				"`^Note: Only if the coffers contain gold. Gets paid every newday.,note",
		),
		"prefs-city"=>array(
			"City Funds Preferences,title",
				"tax"=>"The tax rate (percent):,range,0,100,1",
				"weapon"=>"Tax weapon purchases?,bool",
				"armour"=>"Tax armour purchases?,bool",
				"gold"=>"Gold this city has:,int",
				"gems"=>"Gems this city has:,int",
		),
	);
	return $info;
}

function cityfunds_install()
{
	output("`c`b`Q%s 'cityfunds' Module.`b`n`c", translate_inline(is_module_active('cityfunds')?'Updating':'Installing'));
	module_addhook_priority('newday',100); // Wait for 'cityleader' to fix location if it's changed then we can get tax from it.
	module_addhook('cityleaders');
	module_addhook('cityleaders-leader');
	module_addhook('footer-hof');
	module_addhook('changesetting');
	module_addhook_priority('weaponstext',90);
	module_addhook_priority('armortext',90);
	module_addhook_priority('modify-weapon',90);
	module_addhook_priority('modify-armor',90);
	return TRUE;
}

function cityfunds_uninstall()
{
	output("`n`c`b`Q'cityfunds' Module Uninstalled`0`b`c");
	return TRUE;
}

function cityfunds_dohook($hookname,$args)
{
	global $session;
	switch( $hookname )
	{
		case 'newday':
			require_once('modules/cityprefs/lib.php');
			$homecity = get_module_pref('homecity','cities');
			$cityid = get_cityprefs_cityid('location',$homecity);
			if( get_module_setting('allowtax') == 1 )
			{
				$tax = get_module_objpref('city',$cityid,'tax');
				if( $tax > 0 )
				{
					$amt = round(($session['user']['goldinbank'])*0.01*$tax);
					$bank = $session['user']['goldinbank'];
					if( $bank >= $amt )
					{
						$session['user']['goldinbank'] -= $amt;
					}
					else
					{
						$amt = $session['user']['goldinbank'];
						$session['user']['goldinbank'] = 0;
					}
					if( $amt > 0 )
					{
						increment_module_objpref('city',$cityid,'gold',$amt);
						$leader = get_module_objpref('city',$cityid,'title','cityleaders');
						output("`n`&The %s `&of %s has set the tax at `% %s percent `&of your gold in the bank.`n", $leader, $homecity, $tax);
						output("You gladly pay `^%s gold `&to the advancement and protection of your home city.", $amt);
						if( $session['user']['location'] != $homecity ) output(" Even though you're currently in %s.", $session['user']['location']);
						output_notl('`0`n');
					}
				}
			}
			$stipend = get_module_setting('stipend');
			$leader = get_module_objpref('city',$cityid,'leader','cityleaders');
			if( $stipend > 0 && $leader == $session['user']['acctid'] )
			{
				$gold = get_module_objpref('city',$cityid,'gold');
				if( $gold >= $stipend )
				{
					increment_module_objpref('city',$cityid,'gold',-$stipend);
					$session['user']['goldinbank']+=$stipend;
					output("`n`6As the City Leader of %s, you were paid a stipend of `^%s gold`6. It's good to be the %s`6.`0`n", $homecity, $stipend, stripslashes(get_module_objpref('city',$cityid,'title')));
				}
				elseif( $gold > 0 && $gold < $stipend )
				{
					increment_module_objpref('city',$cityid,'gold',-$gold);
					$session['user']['goldinbank']+=$gold;
					output("`n`6As the City Leader of %s, you were due a stipend of `^%s gold`6. Sadly you only got `^%s gold `6as there wasn't enough in the city coffers.`0`n", $homecity, $stipend, $gold);
				}
				else
				{
					output("`n`6As the City Leader of %s, you were due a stipend of `^%s gold`6. Sadly there was no gold in the city coffers to pay you.`0`n", $homecity, $stipend);
				}
			}
		break;

		case 'cityleaders':
			if( get_module_setting('allowtax') == 1 )
			{
				require_once('modules/cityprefs/lib.php');
				$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
				$leader = stripslashes(get_module_objpref('city',$cityid,'title','cityleaders'));
				$tax = get_module_objpref('city',$cityid,'tax');
				if( $tax > 0 )
				{
					output('`n`6The %s `6of %s has set the tax at %s percent.`0`n', $leader, $session['user']['location'], $tax);
				}
				else
				{
					output('`n`6The %s `6has decreed that there shall be no tax in %s.`0`n', $leader, $session['user']['location']);
				}
			}
			addnav('Options');
			addnav('Donate Funds','runmodule.php?module=cityfunds&op=donate');
		break;

		case 'cityleaders-leader':
			addnav('Other');
			addnav('City Funds','runmodule.php?module=cityfunds&op=funds');
			if( get_module_setting('allowtax') == 1 ) addnav('City Taxes','runmodule.php?module=cityfunds&op=tax');
		break;

		case 'footer-hof':
			addnav('Cities');
			addnav('Richest Cities','runmodule.php?module=cityfunds&op=hof&sop=gold');
		break;

		case 'changesetting':
			if( $args['module'] == 'cityfunds' && $args['setting'] == 'maxtax' )
			{
				if( $args['old'] > $args['new'] )
				{
					// If the new max tax is changed so that it's lower, go through
					// each city and change their tax if it's above this. :)
					$sql = "SELECT objid, value
							FROM " . db_prefix('module_objprefs') . "
							WHERE modulename = 'cityfunds'
								AND objtype = 'city'
								AND setting = 'tax'";
					$result = db_query($sql);
					while( $row = db_fetch_assoc($result) )
					{
						if( $row['value'] > $args['new'] )
						{
							set_module_objpref('city',$row['objid'],'tax',$args['new']);
						}
					}
				}
			}
		break;

		case 'weaponstext':
		case 'armortext':
			if( get_module_setting('allowtax') == 1 )
			{
				require_once('modules/cityprefs/lib.php');
				$cityid = get_cityprefs_cityid('location',$session['user']['location']); // Get the cityid for the city player is in so correct tax is used.
				$tax = get_module_objpref('city',$cityid,'tax');
				if( $tax > 0 )
				{
					if( $hookname == 'weaponstext' ) $args['desc'][] = translate_inline(array("`n`n`^A sign on the wall says that all weapons are taxed at %s percent.`0`n`n", $tax));
					else $args['desc'][] = translate_inline(array("`^A sign on the wall says that all armour is taxed at %s percent.`0`n`n", $tax));
				}
			}
		break;

		case 'modify-weapon':
		case 'modify-armor':
			if( get_module_setting('allowtax') == 1 )
			{
				require_once('modules/cityprefs/lib.php');
				$cityid = get_cityprefs_cityid('location',$session['user']['location']); // Get the cityid for the city player is in so correct tax is used.
				$tax = get_module_objpref('city',$cityid,'tax');
				if( $tax > 0 )
				{
					$amt = round($args['value']*0.01*$tax);
					$args['value'] += $amt;
					$op = httpget('op');
					if( $op == 'buy' )
					{
						require_once('lib/commentary.php');
						$name = ( $hookname == 'modify-weapon' ) ? $args['weaponname'] : $args['armorname'];
						$message = sprintf_translate('::purchased a %s`&. The tax was `^%s gold`&.', $name, $amt);
						injectrawcomment("cityfunds-$cityid", $session['user']['acctid'], $message);
						increment_module_objpref('city',$cityid,'gold',$amt);
						debug("{$session['user']['location']} city fund increased by $amt gold due to tax.");
						invalidatedatacache('cityfunds-hof');
					}
				}
			}
		break;
	}
	return $args;
}

function cityfunds_run()
{
	global $session;

	$op = httpget('op');

	if( $op == 'hof' ) page_header('Hall of Fame');
	else page_header('City Funds');

	require_once('modules/cityprefs/lib.php');
	$cityid = get_cityprefs_cityid('cityname',$session['user']['location']);
	$leader = get_module_objpref('city',$cityid,'title','cityleaders');
	$citizens = get_module_objpref('city',$cityid,'citizens','cityleaders');
	$cityhall = get_module_objpref('city',$cityid,'cityhall','cityleaders');
	$gem = translate_inline(array('gem','gems'));

	if( $op == 'funds' )
	{
		$gems = get_module_objpref('city',$cityid,'gems');
		$gold = get_module_objpref('city',$cityid,'gold');
		output('`n`3Here you can see who donated to the betterment of your city and manage your city funds.`n');
		output('The city currently has `^%s gold`3 and `% %s %s`3 available.`n`n', ($gold?$gold:0), $gems, ($gems==1?$gem[0]:$gem[1]));
		require_once('lib/commentary.php');
		addcommentary();
		commentdisplay('','cityfunds-'.$cityid,'A log of donations is kept here',25,'says');
		addnav('Manage');

		modulehook('cityfunds');

		addnav('Private');
		addnav(array('%s Chambers', $leader),'runmodule.php?module=cityleaders&op=chambers');
	}
	elseif( $op == 'tax' )
	{
		require_once('lib/showform.php');
		output('`n`3What do you want to set the tax rate to? Bear in mind that if you tax the people too much then they will revolt. The maximum you can set it to is %s percent.', get_module_setting('maxtax'));
		$data['rate'] = get_module_objpref('city',$cityid,'tax');
		$data['weapon'] = get_module_objpref('city',$cityid,'weapon');
		$data['armour'] = get_module_objpref('city',$cityid,'armour');
		$form[] = 'Tax Settings,title';
		$form['rate'] = 'Tax Rate:,range,0,'.get_module_setting('maxtax').',1';
		$form[] = '`^The purchases will be taxed using the rate above.,note';
		$form['weapon'] = 'Tax weapon purchases?,bool';
		$form['armour'] = 'Tax armour purchases?,bool';
		rawoutput('<form action="runmodule.php?module=cityfunds&op=tax2" method="POST">');
		addnav('', 'runmodule.php?module=cityfunds&op=tax2');
		$taxform = modulehook('citytaxform',array('form'=>$form,'data'=>$data)); // Modulehook here to add more fields.
		showform($taxform['form'],$taxform['data'],TRUE);
		rawoutput('<input type="submit" value=" '.translate_inline('Set Tax').' " class="button" /></form>');
		addnav('Private');
		addnav(array('%s Chambers', $leader),'runmodule.php?module=cityleaders&op=chambers');
	}
	elseif( $op == 'tax2' )
	{
		$rate = httppost('rate');
		$weapon = httppost('weapon');
		$armour = httppost('armour');
		output("`n`3The tax rate has been set to `b%s percent`b of the gold in the bank.`n`n", $rate);
		set_module_objpref('city',$cityid,'tax',$rate);
		if( $weapon == 1 )
		{
			output('Weapon purchases will now be taxed at the rate above.`n');
			set_module_objpref('city',$cityid,'weapon',1);
		}
		else
		{
			output('Weapon purchases will not be taxed.`n');
			set_module_objpref('city',$cityid,'weapon',0);
		}
		if( $armour == 1 )
		{
			output('Armour purchases will now be taxed at the rate above.`n');
			set_module_objpref('city',$cityid,'armour',1);
		}
		else
		{
			output('Armour purchases will not be taxed.`n');
			set_module_objpref('city',$cityid,'armour',0);
		}
		modulehook('citytaxpost'); // Modulehook here to get submitted data.
		addnav('Private');
		addnav(array('%s Chambers', $leader),'runmodule.php?module=cityleaders&op=chambers');
	}
	elseif( $op == 'donate' )
	{
		require_once('lib/showform.php');
		output('`n`3Cities need gold and gems to maintain the city walls, army, economy, and the %s`3\'s fairy dust addiction. ', $leader);
		output('Would you like to donate something to our City coffers?');
		$data['gold'] = '';
		$form['gold'] = 'Donate Gold:,int';
		$data['gems'] = '';
		$form['gems'] = 'Donate Gems:,int';
		rawoutput('<form action="runmodule.php?module=cityfunds&op=donation" method="POST">');
		addnav('', 'runmodule.php?module=cityfunds&op=donation');
		showform($form,$data,TRUE);
		rawoutput('<input type="submit" value=" '.translate_inline('Donate').' " class="button" /></form>');
	}
	elseif( $op == 'donation' )
	{
		require_once('lib/commentary.php');
		$gold = abs((int)httppost('gold'));
		$gems = abs((int)httppost('gems'));
		if( $gold == '' ) $gold = 0;
		if( $gems == '' ) $gems = 0;

		if( $session['user']['gold'] < $gold )
		{
			output("`n`3You don't have enough gold to make that donation!");
		}
		elseif( $gold > 0 )
		{
			$session['user']['gold'] -= $gold;
			increment_module_objpref('city',$cityid,'gold',$gold);
			$message = sprintf_translate('::donated `^%s gold`&.', $gold);
			injectrawcomment("cityfunds-$cityid", $session['user']['acctid'], $message);
			debuglog("donated $gold gold to {$session['user']['location']}.");
		}

		if( $session['user']['gems'] < $gems )
		{
			output("`n`3You don't have enough gems to make that donation!");
		}
		elseif( $gems > 0 )
		{
			$session['user']['gems'] -= $gems;
			increment_module_objpref('city',$cityid,'gems',$gems);
			$message = sprintf_translate('::donated `% %s %s`&.', $gems, ($gems==1?$gem[0]:$gem[1]));
			injectrawcomment("cityfunds-$cityid", $session['user']['acctid'], $message);
			debuglog("donated $gems gems to {$session['user']['location']}.");
		}

		if( $gold > 0 || $gems > 0 )
		{
			output("`n`3The %s and the %s of %s thank you for your kind donation.`n", $leader, $citizens, $session['user']['location']);
			$homecity = get_module_pref('homecity','cities',$session['user']['acctid']);
			if( $homecity != $session['user']['location'] )
			{	// Donated to a village that wasn't their home.
				addnews("`&%s `7was seen donating to %s when %s is their home!", $session['user']['name'], $session['user']['location'], $homecity);
			}
			invalidatedatacache('cityfunds-hof');
		}

		if( is_module_active('alignment') )
		{
			$points = floor($gold/5000); // For every 5000 gold donated player gets 1 alignment point.
			if( $points > 0 )
			{
				output('`n`3You feel much better with yourself!');
				increment_module_pref('alignment',$points,'alignment');
				debug("$points alignment points for $gold gold.");
				debuglog("donated`^ $gold gold`0 to {$session['user']['location']} and got $points alignment points.");
			}
			$points = floor($gems/1); // For every 1 gem donated player gets 1 alignment point.
			if( $points > 0 )
			{
				output('`n`3Yes indeed. You feel much better.');
				increment_module_pref('alignment',$points,'alignment');
				debug("$points alignment points for $gems gems.");
				debuglog("donated`% $gems gems`0 to {$session['user']['location']} and got $points alignment points.");
			}
		}
	}
	elseif( $op == 'hof' )
	{
		$sop = httpget('sop');
		$order = ( $sop == 'gold' ) ? 'b.value' : 'a.value';
		$objprefs = db_prefix('module_objprefs');
		$sql = "SELECT a.objid, a.value AS gems, b.value AS gold
				FROM $objprefs a INNER JOIN $objprefs b
				ON a.objid = b.objid
				WHERE a.modulename = 'cityfunds'
					AND b.modulename = 'cityfunds'
					AND a.objtype = 'city'
					AND b.objtype = 'city'
					AND a.setting = 'gems'
					AND b.setting = 'gold'
				ORDER BY $order+0 DESC";
		$result = db_query_cached($sql,'cityfunds-hof',86400);
		$rank = translate_inline('Rank');
		$city = translate_inline('City');
		$gold = translate_inline('Gold');
		$gems = translate_inline('Gems');
		rawoutput('<table border="0" cellpadding="2" cellspacing="1" align="center" bgcolor="#999999">');
		rawoutput('<tr class="trhead"><td>'.$rank.'</td><td align="center">'.$city.'</td><td align="center"><a href="runmodule.php?module=cityfunds&op=hof&sop=gold">'.$gold.'</a></td><td align="center"><a href="runmodule.php?module=cityfunds&op=hof&sop=gems">'.$gems.'</a></td></tr>');
		addnav('','runmodule.php?module=cityfunds&op=hof&sop=gold');
		addnav('','runmodule.php?module=cityfunds&op=hof&sop=gems');
		$i = 1;
		while( $row = db_fetch_assoc($result) )
		{
			rawoutput('<tr class="'.($i%2?'trdark':'trlight').'"><td>');
			$cityn = get_cityprefs_cityname('cityid',$row['objid']);
			rawoutput($i.'</td><td align="center">'.$cityn.'</td><td align="center">');
			output_notl('`^%s', ($row['gold']?$row['gold']:0));
			rawoutput('</td><td align="center">');
			output_notl('`% %s', ($row['gems']?$row['gems']:0));
			rawoutput('</td></tr>');
			$i++;
		}
		rawoutput('</table>');
		addnav('Other');
		addnav('Back to HoF','hof.php');
	}

	addnav('Navigation');
	if( $op != 'hof' ) addnav(array('Return to %s',$cityhall),'runmodule.php?module=cityleaders');
	villagenav();

	page_footer();
}
?>