<?php
/**************
Name: Hidden Chest
Author: Eth - ethstavern(at)gmail(dot)com 
Version: 1.0
Release Date: 01-11-2006
About: Player finds a hidden chest in the woods, in which they can
	   then use it to store gold and gems inbetween dk's. They can
	   also forget it's location inbetween dk's as well. Heh.
translation compatible.
*****************/
/**
	Modified by MarcTheSlayer
	23/02/10 - v1.0.1
	+ No limit as to how many chests can be found.
	+ Possible to take somebody elses chest in the forest event, but only if you don't already have one.
	+ If you lose a chest on a dk, it's possible to find it again on another dk if somebody hasn't already.
	+ Changed existing code around and added to it.
*/
function hiddenchest_getmoduleinfo()
{
	$info = array(
		"name"=>"Hidden Chest",
		"version"=>"1.0.1",
		"author"=>"Eth`2, modified by `@MarcTheSlayer",
		"category"=>"Forest Specials",
		"download"=>"http://dragonprime.net/index.php?topic=10927.0",
		"settings"=>array(
			"Hidden Chest - Main Settings,title",
		   		"chestchance"=>"Chance of finding a chest:,range,0,100,5|25",
		   		"ownedchance"=>"Chance of somebody finding a used chest. (0 for no chance):,range,0,100,5|25",
				"dklose"=>"Chance of losing chest upon a dk. (0 for no chance):,range,0,100,5|25",
				"dkfind"=>"Chance of finding your lost chest upon a dk. (0 for no chance):,range,0,100,5|25",
				"dkempty"=>"Empty the chest when it is lost?,bool",
				"dkreq"=>"DK's needed to find a chest:,int|10",
				"goldlimit"=>"Maximum gold the chest can hold:,int|10000",
				"gemslimit"=>"Maximum gems the chest can hold:,int|10",
		),
		"prefs"=>array(
		 	"Hidden Chest - User Settings,title",
		 		"hiddenchest"=>"Has a hidden chest?,enum,0,No,1,Yes,2,Lost,3,Taken",
		 		"hiddengold"=>"Gold in chest?,int",
		 		"hiddengems"=>"Gems in chest?,int",
		)
	);
	return $info;
}

function hiddenchest_install()
{
	output("`c`b`Q%s 'hiddenchest' Module.`b`n`c", translate_inline(is_module_active('hiddenchest')?'Updating':'Installing'));
	module_addeventhook('forest', 'require_once(\'modules/hiddenchest.php\'); return hiddenchest_chance();');
	module_addhook('dragonkill');
	module_addhook('forest');
	module_addhook('newday');
	return TRUE;
}

function hiddenchest_uninstall()
{
	output("`n`c`b`Q'hiddenchest' Module Uninstalled`0`b`c");
	return TRUE;
}

function hiddenchest_chance()
{
	// Requires modulename in function calls as they're being 'run' inside php's eval().
	global $session;
	$chance = get_module_setting('chestchance','hiddenchest');
	if( $session['user']['dragonkills'] < get_module_setting('dkreq','hiddenchest') ) return 0;
	elseif( get_module_pref('hiddenchest','hiddenchest',$session['user']['acctid']) == 1 ) return 0;
	return $chance;
}

function hiddenchest_dohook($hookname,$args)
{
	switch( $hookname )
	{
		case 'dragonkill':
			if( get_module_pref('hiddenchest') == 1 && mt_rand(1,100) < get_module_setting('dklose') && get_module_setting('dklose') > 0 )
			{
				output("`n`n`2You remember for a moment having a chest hidden safely away in the forest, but for the life of you, you can't remember how to get to it anymore. ");
				if( get_module_setting('dkempty') )
				{
					output("For that matter, you don't even remember what you had stored in it.");
					clear_module_pref('hiddenchest');
					clear_module_pref('hiddengold');
					clear_module_pref('hiddengems');
				}
				else
				{
					set_module_pref('hiddenchest',2);
				}
				rawoutput('<br />');
			}
			elseif( get_module_pref('hiddenchest') == 2 && mt_rand(1,100) < get_module_setting('dkfind') && get_module_setting('dkfind') > 0 )
			{
				output('`n`2You see a familiar bush and markings on a nearby tree. Suddenly you remember that this is where your chest is hidden.`n');
				set_module_pref('hiddenchest',1);
			}
		break;

		case 'forest':
			if( get_module_pref('hiddenchest') == 1 )
			{
				addnav('`^Seek Out Chest','runmodule.php?module=hiddenchest&op=seek');
			}
			elseif( get_module_pref('hiddenchest') == 3 )
			{
				addnav('`^Seek Out Chest','runmodule.php?module=hiddenchest&op=taken');
			}
		break;

		case 'newday':
			if( get_module_pref('hiddenchest') == 1 )
			{
				output("`n`qYou anxiously hope that the valuables you have stored in the hidden chest are still okay.`n");
			}
		break;
	}

	return $args;
}

function hiddenchest_runevent($type)
{
	global $session;
	$session['user']['specialinc'] = 'module:hiddenchest';
	switch( $type )
	{	
		case 'forest':	
			$op = httpget('op');
			if( $op == '' || $op == 'search' )
			{
				output("`n`2Far off the beaten path, you decide to take a moment to catch your breath. ");
				output("Taking a seat upon a rock, you glance over to the side and spy an old weathered chest tucked away in the bushes!`n`n");

				$empty_chest = FALSE;
				$gem = translate_inline(array('gem','gems'));
				if( get_module_setting('ownedchance') > 0 && mt_rand(1,100) < get_module_setting('ownedchance') )
				{
					// You find someone elses chest. =)
					$sql = "SELECT setting, userid, value
							FROM " . db_prefix('module_userprefs') . "
							WHERE modulename = 'hiddenchest'";
					$result = db_query($sql);
					$chest_array = array();
					while( $row = db_fetch_assoc($result) )
					{
						// Get everyone with a chest and put into an array.
						$chest_array[$row['userid']][$row['setting']] = $row['value'];
					}
					foreach( $chest_array as $userid => $setting )
					{
						if( $setting == 'hiddenchest' )
						{
							// We want to get rid of any 0s or 3s.
							if( $chest_array[$userid]['hiddenchest'] != 1 || $chest_array[$userid]['hiddenchest'] != 2 ) unset($chest_array[$userid]);
						}
					}
					if( ($count = count($chest_array)) > 0 )
					{
						// If 1 or more chests then randomly pick one.
						$rand = mt_rand(0,$count-1); // $keys array starts at 0 so take one away.
						$keys = array_keys($chest_array);
						if( !isset($chest_array[$keys[$rand]]['hiddengold']) ) $chest_array[$keys[$rand]]['hiddengold'] = 0;
						if( !isset($chest_array[$keys[$rand]]['hiddengems']) ) $chest_array[$keys[$rand]]['hiddengems'] = 0;
						if( !empty($chest_array[$keys[$rand]]['hiddengold']) ) set_module_pref('hiddengold',$chest_array[$keys[$rand]]['hiddengold']);
						if( !empty($chest_array[$keys[$rand]]['hiddengems']) ) set_module_pref('hiddengems',$chest_array[$keys[$rand]]['hiddengems']);
						if( $chest_array[$keys[$rand]]['hiddengold'] == 0 && $chest_array[$keys[$rand]]['hiddengems'] == 0 )
						{
							$empty_chest = TRUE;
						}
						elseif( $chest_array[$keys[$rand]]['hiddengold'] > 0 && $chest_array[$keys[$rand]]['hiddengems'] > 0 )
						{
							output('Dragging it out and prying it open, you discover to your amazement that it contains gold and gems. ');
							output('You count it out and find that it totals `^%s gold `2and `% %s %s`2!`n`n', $chest_array[$keys[$rand]]['hiddengold'], $chest_array[$keys[$rand]]['hiddengems'], ($chest_array[$keys[$rand]]['hiddengems']==1?$gem[0]:$gem[1]));
							output('You decide to leave the treasure in the chest, but to hide it to a new location should the previous owner come looking for it. You\'re fairly certain you could find your way back each time.');
							addnews('`q%s `Qfound a chest in the forest that contained `^%s gold `Qand `% %s %s`Q!', $session['user']['name'], $chest_array[$keys[$rand]]['hiddengold'], $chest_array[$keys[$rand]]['hiddengems'], ($chest_array[$keys[$rand]]['hiddengems']==1?$gem[0]:$gem[1]));
						}
						elseif( $chest_array[$keys[$rand]]['hiddengold'] > 0 )
						{
							output('Dragging it out and prying it open, you discover to your amazement that it contains gold. ');
							output('You count it out and find that it\'s `^%s gold `2in total!`n`n', $chest_array[$keys[$rand]]['hiddengold']);
							output('You decide to leave the gold in the chest, but to hide it to a new location should the previous owner come looking for it. You\'re fairly certain you could find your way back each time.');
							addnews('`q%s `Qfound a chest in the forest that contained `^%s gold`Q!', $session['user']['name'], $chest_array[$keys[$rand]]['hiddengold']);
						}
						elseif( $chest_array[$keys[$rand]]['hiddengems'] > 0 )
						{
							output('Dragging it out and prying it open, you discover to your amazement that it contains `% %s %s`2!`n`n', $chest_array[$keys[$rand]]['hiddengems'], ($chest_array[$keys[$rand]]['hiddengems']==1?$gem[0]:$gem[1]));
							output('You decide to leave the treasure in the chest, but to hide it to a new location should the previous owner come looking for it. You\'re fairly certain you could find your way back each time.');
							addnews('`q%s `Qfound a chest in the forest that contained `% %s %s`Q!', $session['user']['name'], $chest_array[$keys[$rand]]['hiddengems'], ($chest_array[$keys[$rand]]['hiddengems']==1?$gem[0]:$gem[1]));
						}

						// Reset previous owner's prefs.
						if( $chest_array[$keys[$rand]]['hiddenchest'] == 1 ) set_module_pref('hiddenchest',3,'hiddenchest',$keys[$rand]);
						elseif( $chest_array[$keys[$rand]]['hiddenchest'] == 2 ) clear_module_pref('hiddenchest','hiddenchest',$keys[$rand]);
						clear_module_pref('hiddengold','hiddenchest',$keys[$rand]);
						clear_module_pref('hiddengems','hiddenchest',$keys[$rand]);
					}
					else
					{
						$empty_chest = TRUE;
					}
				}
				else
				{
					$empty_chest = TRUE;
				}

				if( $empty_chest == TRUE )
				{
					// You find an empty chest.
					output("Dragging it out and prying it open, you discover to your disappointment that it's empty. ");
					output("However, you think, you could store your own gold and gems here safely. ");
					output("You're fairly certain too that you could find your way back here each time as well.`n`n");
					addnews('`q%s `Qfound an empty chest in the forest.', $session['user']['name']);
					clear_module_pref('hiddengold');
					clear_module_pref('hiddengems');
				}

				set_module_pref('hiddenchest',1);
				addnav('Return to Forest','forest.php');
			}
		break;
	}	
	$session['user']['specialinc'] = '';
}

function hiddenchest_run()
{
	global $session;

	page_header('Hidden Chest');	

	$op = httpget('op');
	$sop = httpget('sop');
	$gold = abs((int)httppost('hiddengold'));
	$gems = abs((int)httppost('hiddengems'));
	if( $gold == '' ) $gold = 0;
	if( $gems == '' ) $gems = 0;

	$from = 'runmodule.php?module=hiddenchest&';
	$gem = translate_inline(array('gem','gems'));

	switch( $op )
	{
		case 'seek':			
			output("`n`2You go to the place where you hid the chest and kneeling down before it, you ponder as to what you should do.`n`n");
			addnav('Choice');
			addnav('Deposit',$from.'op=deposit');
			addnav('Withdraw',$from.'op=withdraw');
		break;

		case 'deposit':
			$goldlimit = get_module_setting('goldlimit') - get_module_pref('hiddengold');
			$gemslimit = get_module_setting('gemslimit') - get_module_pref('hiddengems');

			if( $sop == 'submit' )
			{
				if( $gold > $session['user']['gold'] )
				{
					output("`n`2You don't have that much `^gold `2onhand!");
				}
				elseif( $gold > $goldlimit )
				{
					output("`n`2The chest can't store that much `^gold`2!");
				}
				elseif( $gold == 0 )
				{
					output("`n`2No `^gold `2deposited!");
				}
				else
				{
					output("`n`^%s gold `2deposited in chest.", $gold);
					$session['user']['gold'] -= $gold;
					increment_module_pref('hiddengold',$gold);
				}

				if( $gems > $session['user']['gems'] )
				{
					output("`n`2You don't have that many `%gems `2onhand!");
				}
				elseif( $gems > $gemslimit )
				{
					output("`n`2The chest can't store that many `%gems`2!");
				}
				elseif( $gems == 0 )
				{
					output("`n`2No `%gems `2deposited!");
				}
				else
				{
					output("`n`% %s gems `2deposited in chest.", $gems);
					$session['user']['gems'] -= $gems;
					increment_module_pref('hiddengems',$gems);
				}
			}
			else
			{
				require_once('lib/showform.php');
				output("`n`2You currently have `^%s gold `2and `% %s %s `2stored.`n", (get_module_pref('hiddengold')>0?get_module_pref('hiddengold'):0), (get_module_pref('hiddengems')>0?get_module_pref('hiddengems'):0), (get_module_pref('hiddengems')==1?$gem[0]:$gem[1]));
				output("There's space left to store `^%s gold `2and `% %s %s`2.`n`n", $goldlimit, $gemslimit, ($gemslimit==1?$gem[0]:$gem[1]));

				if( $goldlimit > 0 )
				{
					$data['hiddengold'] = '';
					$form['hiddengold'] = 'Gold to store,int';
				}
				if( $gemslimit > 0 )
				{
					$data['hiddengems'] = '';
					$form['hiddengems'] = 'Gems to store,int';
				}
				if( $goldlimit > 0 || $gemslimit > 0 )
				{
					output("How much would you like to leave in the chest?`n`n");
					rawoutput('<form action="'.$from.'op=deposit&sop=submit" method="POST">');
					addnav('', $from."op=deposit&sop=submit");
					showform($form,$data,TRUE);
					rawoutput('<input type="submit" value=" '.translate_inline('Store').' " /></form>');
				}
			}
			addnav('Go Back',$from.'op=seek');
		break;

		case 'withdraw':
			if( $sop == 'submit' )
			{
				if( $gold > get_module_pref('hiddengold') )
				{
					output("`n`2You don't have that much `^gold `2stored in the chest!");
				}
				else
				{
					output("`n`^%s gold `2taken from the chest.", $gold);
					$session['user']['gold'] += $gold;
					increment_module_pref('hiddengold',-$gold);
				}

				if( $gems > get_module_pref('hiddengems') )
				{
					output("`n`2You don't have that many `%gems `2stored in the chest!");
				}
				else
				{
					output("`n`% %s %s `2taken from the chest.`n", $gems, ($gemslimit==1?$gem[0]:$gem[1]));
					$session['user']['gems'] += $gems;
					increment_module_pref('hiddengems',-$gems);
				}
			}
			else
			{
				require_once('lib/showform.php');
				output("`n`2You currently have `^%s gold `2and `% %s %s `2stored.`n", (get_module_pref('hiddengold')>0?get_module_pref('hiddengold'):0), (get_module_pref('hiddengems')>0?get_module_pref('hiddengems'):0), (get_module_pref('hiddengems')==1?$gem[0]:$gem[1]));

				if( get_module_pref('hiddengold') > 0 )
				{
					$data['hiddengold'] = '';
					$form['hiddengold'] = 'Gold to take,int';
				}
				if( get_module_pref('hiddengems') > 0 )
				{
					$data['hiddengems'] = '';
					$form['hiddengems'] = 'Gems to take,int';
				}
				if( get_module_pref('hiddengold') > 0 || get_module_pref('hiddengems') > 0 )
				{
					output("How much would you like to take from the chest?`n`n");
					rawoutput('<form action="'.$from.'op=withdraw&sop=submit" method="POST">');
					addnav('', $from.'op=withdraw&sop=submit');
					showform($form,$data,TRUE);
					rawoutput('<input type="submit" value=" '.translate_inline('Take').' " /></form>');
				}
			}
			addnav('Go Back',$from.'op=seek');
		break;

		case 'taken':
			output('`n`2You go to the place where you hid your chest, but it\'s not there anymore. You curse the person who took it and yourself for thinking it was a good idea to store your gold and gems in it.');
			clear_module_pref('hiddenchest');
		break;
	}

	addnav('Return');
	addnav('To the Forest','forest.php');

	page_footer();
}
?>