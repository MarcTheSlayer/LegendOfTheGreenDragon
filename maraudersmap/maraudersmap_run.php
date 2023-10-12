<?php
	$op = httpget('op');
	switch( $op )
	{
		case 'lodge':
			page_header("Hunter's Lodge");

			$cost = get_module_setting('cost');
			$points = translate_inline(array('point','points'));
			$sop = httpget('sop');
			if( $sop == 'buy' )
			{
				output("`n`7J. C. Petersen turns to you. \"`&To purchase %s more uses for the `6%s `&while it is in your possession, will cost %s %s,`7\" he says.  \"`&Will this suit you?`7\"`n`n", get_module_setting('lodgeuses'), get_module_setting('mapname'), $cost, ($cost==1?$point[0]:$points[1]));
				addnav('Confirm Purchase');
				addnav('Yes','runmodule.php?module=maraudersmap&op=lodge&sop=confirm');
				addnav('No','lodge.php');
			}
			elseif( $sop == 'confirm' )
			{
				$pointsavailable = $session['user']['donation'] - $session['user']['donationspent'];
				if( $pointsavailable >= $cost )
				{
					$uses = get_module_setting('lodgeuses');
					output("`n`7J. C. Petersen takes the %s `7from you and very carefully redraws it with a quill and some magic ink before handing it back with a smile. \"`&There you go `7%s`&. Good for at least another %s uses.`7\"`0`n", get_module_setting('mapname'), $session['user']['name'], $uses);
					increment_module_setting('maxused', -$uses);
					$session['user']['donationspent'] += $cost;
				}
				else
				{
					output("`n`7J. C. Petersen looks down his nose at you. \"`&I'm sorry, but you do not have the %s %s required. Please return when you do and I'll be happy to do business with you.`7\"`0`n", $cost, ($cost==1?$point[0]:$points[1]));
				} 
				addnav('L?Return to the Lodge','lodge.php');
			}
		break;

		case 'editor':
			page_header('Tunnel Map Editor');
			$sop = httpget('sop');

			//
			// Setup examples tunnels.
			//
			if( $sop == 'examples' )
			{
				maraudersmap_setexamples();
				$sop = 'recreate';
			}

			//
			// Delete all tunnels.
			//
			if( $sop == 'delete')
			{
				output('`n`QDeleteing tunnels...please hold...`0`n');
				set_module_setting('allprefs',serialize(array()));
				unset($tunnels);
				output('`n`QFinished!`0`n');
				$sop = 'recreate';
			}

			//
			// Wipe then create hooks.
			//
			if( $sop == 'recreate' )
			{
				module_wipehooks();
				maraudersmap_sethooks();
				output('`n`QWiping current hooks...adding hooks...`0`n');
				output('`n`QFinished!`0`n');
			}

			//
			// Just for me testing tunnel in shades.
			//
			if( $sop == 'die' )
			{
				if( $session['user']['alive'] == 1 )
				{
					output("`n`QYou're now dead!`0`n");
					$session['user']['alive'] = 0;
					$session['user']['hitpoints'] = 0;
				}
				else
				{
					output("`n`QYou're now alive!`0`n");
					$session['user']['alive'] = 1;
					$session['user']['hitpoints'] = $session['user']['maxhitpoints'];
				}
			}

			$tunnels = maraudersmap_allprefs();
			$count = count($tunnels);

			if( httpget('subop') == 'save' )
			{
				$count++;
				$tunnels = array();
				$postdata = httpallpost();
				for( $i=1; $i<=$count; $i++ )
				{
					if( $postdata["del$i"] != 1 )
					{
						$tunnels[$i]['use'] = $postdata["use$i"];
						$tunnels[$i]['name1'] = $postdata["name1$i"];
						$tunnels[$i]['door1'] = $postdata["door1$i"];
						$tunnels[$i]['loc1'] = $postdata["loc1$i"];
						$tunnels[$i]['query1'] = $postdata["query1$i"];
						$tunnels[$i]['name2'] = $postdata["name2$i"];
						$tunnels[$i]['door2'] = $postdata["door2$i"];
						$tunnels[$i]['loc2'] = $postdata["loc2$i"];
						$tunnels[$i]['query2'] = $postdata["query2$i"];
					}
				}
				$tunnels2 = array();
				$i = 1;
				// This resets the keys so that there are no gaps. Gaps are bad, mmkay.
				foreach( $tunnels as $value )
				{
					$tunnels2[$i] = $value;
					$i++;
				}
				$tunnels = $tunnels2;
				$count = count($tunnels);
				set_module_setting('allprefs',serialize($tunnels));
				module_wipehooks();
				maraudersmap_sethooks(TRUE);
				output('`#Allprefs tunnel data updated. Module hooks changed to suit.`0`n');
			}

			$module_buildlist = maraudersmap_buildlist();
			$tunnel_data = $form_data = array();
			$form_data[] = "README,title";
			$form_data[] = "`i`^Each tunnel has 2 entrances with one leading to the other. At each end you'll have a link to the other. You can have more than one tunnel entrance in a location.`n`n
							`@`bName:`b `2You can leave this blank&#44; but I recommend you enter the name of the place where the entrance is located.`n`n
							`#`bModule:`b `3Select the module or core file where the entrance is to be found. To populate this menu see the module settings.`n`n
							`@`bLocated in:`b `2The village this module or core file is located in.`n`n
							`#`bQuery string:`b `3You can leave this blank&#44; but it can be used to take the player to a certain part of a module.`nUse the following format. e.g: `3`bop=first&sop=second`b`^`n`bDO NOT`b put a question mark (?) or an ampersand (&) at the beginning.`n`n
							`@`bRemember:`b `2Don't create tunnels to shades/graveyard as the player will just get bounced back to the village. Tunnels between shades/valhalla are ok. And don't whatever you do go creating tunnels to superuser pages either or the player will get severely punished and called a hacker.`0`i,note";
			foreach( $tunnels as $key => $value )
			{
				$tunnel = array(
					"del$key"=>0,
					"use$key"=>$value['use'],
					"name1$key"=>stripslashes($value['name1']),
					"door1$key"=>$value['door1'],
					"loc1$key"=>$value['loc1'],
					"query1$key"=>$value['query1'],
					"name2$key"=>stripslashes($value['name2']),
					"door2$key"=>$value['door2'],
					"loc2$key"=>$value['loc2'],
					"query2$key"=>$value['query2'],
				);

				$form = array(
					"Tunnel $key,title",
						"del$key"=>"Delete this Tunnel?,bool",
						"use$key"=>"Use this Tunnel?,bool",
						"`i`#Entrance One`0`i,note",
						"name1$key"=>"Name:,string,69",
						"door1$key"=>"Module:,enum$module_buildlist",
						"loc1$key"=>"Located in:,location",
						"query1$key"=>"Query string:,string,69",
						"`i`#Entrance Two`0`i,note",
						"name2$key"=>"Name:,string,69",
						"door2$key"=>"Module:,enum$module_buildlist",
						"loc2$key"=>"Located in:,location",
						"query2$key"=>"Query string:,string,69",
				);

				$tunnel_data = array_merge($tunnel_data, $tunnel);
				$form_data = array_merge($form_data, $form);
			}

			$count++;

			// Add a set of empty input boxes for an additional tunnel.
			$tunnel = array(
				"del$count"=>1,
				"use$count"=>0,
				"name1$count"=>'',
				"door1$count"=>'',
				"loc1$count"=>getsetting('villagename', LOCATION_FIELDS),
				"query1$count"=>'',
				"name2$count"=>'',
				"door2$count"=>'',
				"loc2$count"=>getsetting('villagename', LOCATION_FIELDS),
				"query2$count"=>'',
			);

			$form = array(
				"Add a Tunnel,title",
					"del$count"=>"Add this Tunnel?,enum,1,No,0,Yes",
					"use$count"=>"Use this Tunnel?,bool",
					"`i`#Entrance One`0`i,note",
					"name1$count"=>"Name:,string,69",
					"door1$count"=>"Module:,enum$module_buildlist",
					"loc1$count"=>"Located in:,location",
					"query1$count"=>"Query string:,string,69",
					"`i`#Entrance Two`0`i,note",
					"name2$count"=>"Name:,string,69",
					"door2$count"=>"Module:,enum$module_buildlist",
					"loc2$count"=>"Located in:,location",
					"query2$count"=>"Query string:,string,69",
			);

			$tunnel_data = array_merge($tunnel_data, $tunnel);
			$form_data = array_merge($form_data, $form);

			require_once('lib/showform.php');
			rawoutput('<form action="runmodule.php?module=maraudersmap&op=editor&subop=save" method="POST">');
			addnav('','runmodule.php?module=maraudersmap&op=editor&subop=save');
			showform($form_data,$tunnel_data,TRUE);
			$submit = translate_inline('Save');
			rawoutput('<input type="submit" class="button" value="'.$submit.'" /></form>');

			if( $session['user']['superuser'] & SU_DEVELOPER )
			{
				addnav('Testing Dev Links');
				addnav('Recreate Tunnel Hooks','runmodule.php?module=maraudersmap&op=editor&sop=recreate');
				addnav('Delete Tunnels','runmodule.php?module=maraudersmap&op=editor&sop=delete');
				addnav('Example Tunnels','runmodule.php?module=maraudersmap&op=editor&sop=examples');
				addnav('Killself','runmodule.php?module=maraudersmap&op=editor&sop=die');
			}
			if( $session['user']['superuser'] & SU_MANAGE_MODULES ) addnav('Module Settings','configuration.php?op=modulesettings&module=maraudersmap');
			addnav('Return');
			addnav('The Grotto','superuser.php');
		break;

		case 'quitevent':
			page_header('Event Quitter');
			$session['user']['specialinc'] = '';
			output('`n`^You quit the event... It\'s good to be a SU developer.`0`n');
			require_once('lib/villagenav.php');
			villagenav();
		break;

		case 'nopvp':
			page_header('No PVP');
			output("`n`^The `&%s `^is too good for the likes of you. Maybe if you were participating in PvP...`0`n", get_module_setting('mapname'));
			require_once('lib/villagenav.php');
			villagenav();
		break;

		case 'nopvpyet':
			page_header('No PVP');
			output("`n`^Ah youngster, it would be a crime to gave you the `&%s `^as everyone would hunt you down and kill you while you're still in diapers.`0`n", get_module_setting('mapname'));
			require_once('lib/villagenav.php');
			villagenav();
		break;

		case 'shades':
			$sop = httpget('sop');
			if( $sop == 'continue' )
			{
				page_header('The Tunnel');
         		output('`n`2The ground starts to get cooler and as time passes you spot a white speck in the distance that you gather is daylight and feel a cool breeze passing over you. The white speck gets bigger and bigger and soon you reach the end.`n`n');
				if( (get_module_setting('shadeuses') - get_module_setting('shadeused') ) == 1 )
				{
					output('An old sack catches your eye and you find a bottle of life restorer potion inside and a note which reads,');
				}
				else
				{
					output('An old sack catches your eye and you find a few bottles of life restorer potion inside and a note which reads,');
				}
				output('`n`n`6"`^It\'s not a good idea for the dead to be walking amongst the living as %s `^will spot you easily and punish you. Drink one of these life restorer potions to restore your life. Regards, %s`6"`n`n', getsetting('deathoverlord','`$Ramius'), 'MarcTheSlayer');
				output('`2You empty a bottle into your mouth and feel a warm tingling sensation all over. You check that nobody is around and tuck the %s `2back into your pocket and exit the tunnel into the daylight.`0`n', get_module_setting('mapname'));
				$session['user']['alive'] = 1;
				$session['user']['hitpoints'] = $session['user']['maxhitpoints'];
				$session['user']['location'] = getsetting('villagename', LOCATION_FIELDS);
				increment_module_setting('shadeused');
				addnav('E?Exit Tunnel','village.php');
			}
			else
			{
				page_header('The Mausoleum');
				output('`n`4You pull the %s `4from your pocket and look for a hidden tunnel out of shades, and sure enough you find an entrance behind a nearby marble statue.', get_module_setting('mapname'));
				if( get_module_setting('shadeused') < get_module_setting('shadeuses') || get_module_setting('shadeuses') == 0 )
				{
					if( $session['user']['gems'] >= 5 )
					{
						output('You give %s `45 of your shiniest gems and while he\'s cooing over them you slip into the tunnel unnoticed and close the entrance behind you.`n`n', getsetting('deathoverlord','`$Ramius'));
						$session['user']['gems'] -= 5;
					}
					else
					{
						output('When %s `4isn\'t looking, you sneak into the tunnel and close the entrance behind you.`n`n', getsetting('deathoverlord','`$Ramius'));
					}
					output('As you scramble along the dark tunnel ever upwards peering at the map, you keep looking over your shoulder, paranoid that %s `4might be coming for you.`n`n', getsetting('deathoverlord','`$Ramius'));
					addnav('C?Continue','runmodule.php?module=maraudersmap&op=shades&sop=continue');
				}
				else
				{
					output('`n`nYou then realise that you used up the last of the life restorer potions on your previous escape.`n`n');
					output('%s `$laughs at you, "`$It\'s no use looking for ways to escape. Nobody gets out of here alive.`4"`n`n', getsetting('deathoverlord','`$Ramius'));
					output('You smirk to yourself knowing otherwise and leave.`0`n');
					addnav('Places');
					addnav('S?Land of the Shades','shades.php');
					addnav('G?Return to the Graveyard','graveyard.php');
				}
			}
		break;

		default:
			// I was thinking about having a setting to bypass this part, the link would go directly to the
			// other end, but then I wouldn't be able to increment the uses or set the player's location. :-/
			require_once('lib/sanitize.php');
			page_header(full_sanitize(get_module_setting('mapname')));
			$tunnels = maraudersmap_allprefs();
			$i = httpget('sop'); // Tunnel array key.
			$m = httpget('top'); // Door number, either 1 or 2.

			// Check to see if it's a core file. Just the important game ones listed here.
			$corefiles = array('armor','bank','bio','clan','forest','gardens','graveyard','gypsy','healer','hof','inn','list','lodge','mercenarycamp','pvp','rock','shades','superuser','stables','train','village','weapons');
			addnav(full_sanitize(get_module_setting('mapname')));

			// Work out if unlimited, or have enough uses still.
			if( get_module_setting('maxuses') == 0 || get_module_setting('maxused') < get_module_setting('maxuses') )
			{
				$turns = TRUE;
			}
			elseif( get_module_setting('maxused') >= get_module_setting('maxuses') )
			{
				$turns = FALSE;
				$m = ( $m == 1 ) ? 2 : 1;
			}

			if( !empty($tunnels[$i]["query$m"]) )
			{
				$link = ( in_array($tunnels[$i]["door$m"], $corefiles) ) ? $tunnels[$i]["door$m"].'.php?'.$tunnels[$i]["query$m"] : 'runmodule.php?module='.$tunnels[$i]["door$m"].'&'.$tunnels[$i]["query$m"];
			}
			else
			{
				$link = ( in_array($tunnels[$i]["door$m"], $corefiles) ) ? $tunnels[$i]["door$m"].'.php' : 'runmodule.php?module='.$tunnels[$i]["door$m"];
			}
			$place_name = ( !empty($tunnels[$i]["name$m"]) ) ? stripslashes($tunnels[$i]["name$m"]) : translate_inline('the other location');
			$session['user']['location'] = $tunnels[$i]["loc$m"];

			if( !isset($link) )
			{
				$place_name = '';
				$session['user']['location'] = getsetting('villagename', LOCATION_FIELDS);
				addnav('E?Exit Tunnel','village.php');
			}

			if( $turns == TRUE )
			{
				$hidden = translate_inline(array('in the corner','behind a pillar','in the wall panelling','in the `&bathroom`3 under the `&sink','labelled with a `@"Secret Entrance Here" `3sign','behind a wall painting','behind a large `7marble statue','under a rug on the floor','opened with the phrase `@"Open sesame seeds"','revealed with the spell `#"Alohomora"'));
				output('`n`3You pull out the `&%s `3and look for the secret tunnel to %s`3, and sure enough you find a hidden entrance that\'s %s`3.`n`n', get_module_setting('mapname'), $place_name, $hidden[array_rand($hidden)]);
				output('Checking that nobody is watching, you sneak through the entrance, silently closing it behind you, and make your way quickly along the tunnel.`n`n');
				output('You reach the end soon enough and check the `&%s `3to see if the coast is clear before exiting the tunnel.`0`n`n', get_module_setting('mapname'));
				if( get_module_setting('maxuses') > 0 )
				{
					$percent = ceil((get_module_setting('maxused')/get_module_setting('maxuses'))*100);
					if( $percent < 25 ) output('`3As you tuck it away in your pocket, you notice how the ink is still `b`#vibrantly rich`3`b in colour.`0`n');
					elseif( $percent < 50 ) output('`3As you tuck it away in your pocket, you notice how the ink is `b`#still rich`3`b in colour.`0`n');
					elseif( $percent < 75 ) output('`3As you tuck it away in your pocket, you notice how the ink is `b`#starting to fade`3`b and it\'s difficult to make parts out.`0`n');
					elseif( $percent < 100 ) output('`3As you tuck it away in your pocket, you notice how the ink is very faded and `b`#badly needs to be re-inked`3`b before it becomes unreadable altogether.`0`n');
					increment_module_setting('maxused');
				}
				addnav('E?Exit Tunnel',$link);
			}
			else
			{
				if( get_module_setting('lodgeuses') > 0 )
				{
					$qop = httpget('qop');
					if( $qop == 'throwyes' )
					{
						output('`n`3You take one last look at the now blank parchment and throw it away. Your Lodge points are better spent on other things.`0`n');
						addnews('`&%s `^threw the `&%s `^away in %s!', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
						maraudersmap_changeowner();
						debuglog("threw the ".get_module_setting('mapname')."`0 away in {$session['user']['location']}.");
						addnav('C?Continue',$link);
					}
					elseif( $qop == 'throwno' )
					{
						output('`n`3You can\'t believe for a second that you\'d even considered throwing this amazing thing away and plan to visit the Hunter\'s Lodge the next time you pass it.`0`n');
						addnav('C?Continue',$link);
					}
					else
					{
						output('`n`3You pull out the `&%s `3and to look for the secret entrance, but the ink has faded too much and you can no longer make anything out.`n`n', get_module_setting('mapname'));
						output('`3You remember reading the small print that mentioned getting it re-inked at the Hunter\'s Lodge.`0`n');
						addnav('Throw it Away?');
						addnav('Y?Yes',"runmodule.php?module=maraudersmap&op=$op&sop=$i&top=".httpget('top')."&qop=throwyes");
						addnav('N?No',"runmodule.php?module=maraudersmap&op=$op&sop=$i&top=".httpget('top')."&qop=throwno");
					}
				}
				else
				{
					output('`n`3You pull out the `&%s `3and to look for the secret entrance, but the ink has faded too much and you can no longer make anything out.`n`n', get_module_setting('mapname'));
					output('`3You curse your bad luck and throw the `&%s `3away. It\'s no use to anybody in this state.`0`n', get_module_setting('mapname'));
					addnews('`&%s `^threw the `&%s `^away in %s!', $session['user']['name'], get_module_setting('mapname'), $session['user']['location']);
					maraudersmap_changeowner();
					debuglog("threw the ".get_module_setting('mapname')."`0 away in {$session['user']['location']}.");
					addnav('M?Mischief Managed',$link);
				}
			}
			rawoutput('<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><span style="font-size:0.7em;"');
			output('`7"Messrs. Moony, Wormtail, Padfoot, Prongs, and %s`nPurveyors of Aids to Magical Mischief-Makers are proud to present`nTHE %s"`0`0`n', 'MarcTheSlayer', strtoupper(full_sanitize(get_module_setting('mapname'))));
		break;
	}

	page_footer();
?>