<?php
	$op2 = httpget('op2');
	
	$armour = $session['user']['armor'];
	$male = shuffle(translate_inline(array('Y-fronts','boxers','briefs','cod piece','loincloth','long johns','skivvies','tighty whities','unmentionables')));
	$female = shuffle(translate_inline(array('knickers','panties','thong','g-string','bloomers','pantyhose','pantaloons','dainties','drawers','undies','unmentionables')));
	$underwear = ( $session['user']['sex'] == 1 ) ? $female[1] : $male[1];

	$maidens = TRUE;

	if( $spanks == 1 )
	{
		$spanks = rand(2,3);	
	}

	switch( $op2 )
	{
		case '1':
			output('`n`2You select %s `2who carries you back to her room.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('She takes off her granny panties and bends over your knees exposing her bottom.');
			}
			else
			{
				output('You take off your %s `2and slide down your %s. Abby tells you to bend over and expose your bottom which you do so without question.', $armour, $underwear);
			}
		break;

		case '2':
			output('`n`2You select %s `2and head upstairs to her room.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('She lifts up her skirt and bends over, exposing her bottom and a pair of G-strings.');
			}
			else
			{
				output('Your %s `2is quickly thrown aside as are your %s. You\'re even quicker to bend over exposing your bottom.', $armour, $underwear);
			}
		break;

		case '3':
			output('`n`2You select %s `2and go back to her room.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('She takes off her white cotton panties, bends over exposing her bottom and grabs her ankles.');
			}
			else
			{
				output('You slowly take off your %s `2and slide down your %s. You then bend over to expose your bottom.', $armour, $underwear);
			}
		break;

		case '4':
			output('`n`2You select %s `2and head upstairs to her room.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('She slowly removes her corset and slides her frilly knickers down her legs until she\'s fully bent over with her rosy bum staring you in the face.');
			}
			else
			{
				output('Your %s `2is quickly removed as are your %s, you then bend over exposing your bottom.', $armour, $underwear);
			}
		break;

		case '5':
			output('`n`2You select %s `2who motions for you to follow her.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('You follow her into her room and she reveals that she\'s not wearing any panties. She proves this by lifting her skirt and bending over.');
			}
			else
			{
				output('You take off your %s, slide down your %s and bend over exposing your bottom.', $armour, $underwear);
			}
		break;

		case '6':
			output('`n`2You select %s `2who picks you up and carries you back to his room.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('He grins at you as he removes his leather codpiece and bends over exposing his bottom.');
			}
			else
			{
				output('As coolly as possible you remove your %s`2, slide down your %s and bend over exposing your bottom.', $armour, $underwear);
			}
			$maidens = FALSE;
		break;

		case '7':
			output('`n`2You select %s `2who licks his fingers and waves you to follow.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('He rolls about on the bed until he\'s able to remove his stained undies, then he turns over and exposes his rear end.');
			}
			else
			{
				output('You take off your %s`2, slide down your %s and bend over exposing your bottom.', $armour, $underwear);
			}
			$maidens = FALSE;
		break;

		case '8':
			output('`n`2You select %s `2whos good looks dazzle you.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('His smooth movements removing his briefs impresses you, as does his rear when he sticks it in the air.');
			}
			else
			{
				output('You casually take off your %s `2and slide your %s down. Then you bend over exposing your bottom.', $armour, $underwear);
			}
			$maidens = FALSE;
		break;

		case '9':
			output('`n`2You select %s `2who points and winks at you.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('He smugly dances as he slips his posing pouch off, then bends over exposing his smooth bottom.');
			}
			else
			{
				output('You take off your %s `2 and slide down your %s as %s `2watches paddle in hand. You bend over exposing your bottom.', $armour, $underwear, $partners[$op2]);
			}
			$maidens = FALSE;
		break;

		case '10':
			output('`n`2You select %s `2who smiles you into a dream state.`n`n', $partners[$op2]);
			if( $spanks == 2 )
			{
				output('%s `2quickly slides his boxers down and kicks them across the room, he bends over exposing his bottom.', $partners[$op2]);
			}
			else
			{
				output('You remove your %s `2slowly, slide down your %s, then bend over exposing your bottom.', $armour, $underwear);
			}
			$maidens = FALSE;
		break;
	}

	$turns = rand(get_module_setting('minturns'), get_module_setting('maxturns'));
	$hitpoints = rand(get_module_setting('minhp'), get_module_setting('maxhp'));
	$charm = rand(get_module_setting('mincharm'), get_module_setting('maxcharm'));

	$rand = rand(1,8);
	if( $maidens == TRUE )
	{
		switch( $rand )
		{
			case 1:
					spanking_buff('good', $partners[$op2], $spanks);
			case 4:
				if( $spanks == 2 )
				{
					output('`n`n`2%s `2squeals with delight as you paddle her bottom!`n`n', $partners[$op2]);
					output('Your spanking has given her delight she has never known before.`n`n');
				}
				else
				{
					output('`n`n`2You squeal with delight as your bottom is spanked red by %s`2!`n`n', $partners[$op2]);
					output('The spanking has given you delight you have never known before.`n`n');
				}
				output('When it\'s all over %s `2hugs you then opens her purse and hands you a vial from inside.`n`n', $partners[$op2]);
				output('You drink down the vial and find you have %s more forest %s!', $turns, translate_inline($turns==1?'fight':'fights'));
				$session['user']['turns'] += $turns;
			break;

			case 2:
			case 5:
				if( $spanks == 2 )
				{
					output('`n`n`2%s `2shreaks with delight as you paddle her bottom.`n`n', $partners[$op2]);
				}
				else
				{
					output('`n`n`2You shreak with delight and wiggle your legs as %s `2paddles your bottom.`n`n', $partners[$op2]);
				}
				output('When it is all over, she hands you a vial which increases your overall strength.');
				$session['user']['hitpoints'] += $hitpoints;
			break;

			case 3:
				spanking_buff(FALSE, $partners[$op2], $spanks);
			case 6:
				if( $spanks == 2 )
				{
					output('`n`n`2You begin to very gently paddle her bottom.`n`n');
					output('%s `2squeals with delight with every strike of the paddle.`n`n', $partners[$op2]);
					output('When it\'s all over, you give a gentle kiss on each of her red cheeks.`n`n');
				}
				else
				{
					output('`n`n`2%s `2begins to very gently paddle your bottom.`n`n', $partners[$op2]);
					output('She squeals with delight with every strike of the paddle.`n`n');
					output('When it\'s all over, she gives you a gentle kiss on each of your red cheeks.`n`n');
				}
				output('Blushing a bit ...you smile at her with a certain amount of gained charm.');
				$session['user']['charm'] += $charm;
			break;

			case 7:
			case 8:
				spanking_buff('bad', $partners[$op2], $spanks);
				if( $spanks == 2 )
				{
					output('`n`n`2You begin to very gently paddle her bottom.`n`n');
					output('%s `2squeals with delight with every strike of the paddle.`n`n', $partners[$op2]);
					output('You start to quicken the speed as she begs you for more.`n`n');
					addnews('`2%s `2got `qsplinters `2from paddling the bottom of %s `2too hard!', $session['user']['name'], $partners[$op2]);
				}
				else
				{
					output('`n`n`2%s `2begins to very gently paddle your bottom.`n`n', $partners[$op2]);
					output('She squeals with delight with every strike of the paddle.`n`n');
					output('You tell her to spank harder and she complies.`n`n');
					addnews('`2%s `2got `qsplinters `2from having %s `2paddle %s bottom too hard!', $session['user']['name'], $partners[$op2], translate_inline($session['user']['sex']==1?'her':'his'));
				}
				output('`@THEN DISASTER!!!`n`n`2Both you and %s `2let out a yell as the paddle breaks and you both receive splinters.`n`n', $partners[$op2]);
				output('It takes you a while to pull the splinters out of both you and %s`2, not only that but you feel less charming and can\'t carry on spanking.', $partners[$op2]);
				set_module_pref('totaltoday',get_module_setting('spanksperday'));
				if( $session['user']['turns'] >= 2 ) $session['user']['turns'] -= 2;
				$session['user']['charm'] -= 5;			
				$session['user']['hitpoints'] *= 0.9;
				if( $session['user']['hitpoints'] < 1 ) $session['user']['hitpoints'] = 1;
			break;
		}
	}
	else
	{
		switch( $rand )
		{
			case 1:
				spanking_buff('good', $partners[$op2], $spanks);
			case 4:
				if( $spanks == 2 )
				{
					output('`n`n`2You paddle his bottom. %s `2tells you to paddle harder!`n`n', $partners[$op2]);
					output('When it is all over you say he has the finest looking bottom you have ever paddled!`n`n');
				}
				else
				{
					output('`n`n`2%s `2paddles your bottom hard and you enjoy it!`n`n', $partners[$op2]);
					output('When it is all over he says you have the finest looking bottom he has ever paddled!`n`n');
				}
				output('%s `2hands you a vial and thanks you for giving so much pleasure!`n`n', $partners[$op2]);
				output('You drink down the vial and find you have %s more forest %s!', $turns, translate_inline($turns==1?'fight':'fights'));
				$session['user']['turns'] += $turns;
			break;

			case 2:
			case 5:
				if( $spanks == 2 )
				{
					output('`n`n`2You swiftly paddle his bottom until it\'s red and glowing.`n`n');
				}
				else
				{
					output('`n`nHe swiftly paddles your bottom until it\'s red and glowing.`n`n');
				}
				output('When it is all over, %s `2smiles and hands you a vial which increases your overall strength.', $partners[$op2]);
				$session['user']['hitpoints'] += $hitpoints;
			break;

			case 3:
				spanking_buff(FALSE, $partners[$op2], $spanks);
			case 6:
				if( $spanks == 2 )
				{
					output('`n`n`2You gently paddle his very firm bottom.`n`n');
					output('%s `2squeals with delight like a little girl with every strike of the paddle.`n`n', $partners[$op2]);
					output('When it is all over, you give him a gentle kiss on each of his red cheeks.`n`n');
				}
				else
				{
					output('`n`n`2%s `2gently paddles your bottom.`n`n', $partners[$op2]);
					output('You squeal with delight with every strike of the paddle.`n`n');
					output('When it is all over, he gives you a gentle kiss on each on your red cheeks.`n`n');
				}
				output('Blushing a bit ...you smile at him with a certain amount of gained charm.`n`n');
				$session['user']['charm'] += $charm;
			break;

			case 7:
			case 8:
				spanking_buff('bad', $partners[$op2], $spanks);
				if( $spanks == 2 )
				{
					output('`n`n`2You very gently start to paddle his bottom.`n`n');
					output('%s `2moans softly with every strike of the paddle.`n`n', $partners[$op2]);
					output('You start to quicken the speed as he begs you for more.`n`n');
					addnews('`2%s `2got `qsplinters `2from paddling the bottom of %s `2too hard!', $session['user']['name'], $partners[$op2]);
				}
				else
				{
					output('`n`n`2%s `2begins to gently paddle your bottom.`n`n', $partners[$op2]);
					output('He gives a yell with every strike of the paddle.`n`n');
					output('You tell him to spank harder and he does.`n`n');
					addnews('`2%s `2got `qsplinters `2from having %s `2paddle %s bottom too hard!', $session['user']['name'], $partners[$op2], translate_inline($session['user']['sex']==1?'her':'his'));
				}
				output('`@THEN DISASTER!!!`n`n`2Both you and %s `2let out a yell as the paddle breaks and you both receive splinters.`n`n', $partners[$op2]);
				output('It takes you a while to pull the splinters out of both you and %s`2, not only that but you feel less charming and can\'t carry on spanking.', $partners[$op2]);
				set_module_pref('totaltoday',get_module_setting('spanksperday'));
				if( $session['user']['turns'] >= 2 ) $session['user']['turns'] -= 2;
				$session['user']['charm'] -= 5;			
				$session['user']['hitpoints'] *= 0.9;
				if( $session['user']['hitpoints'] < 1 ) $session['user']['hitpoints'] = 1;
			break;
		}
	}

	function spanking_buff($type, $partner, $spanks)
	{
		if( has_buff('houseofspanking') )
		{
			strip_buff('houseofspanking');
		}

		if( $type == 'good' )
		{
			apply_buff('houseofspanking',array(
				"name"=>"`QS`qpanking `QP`qaddle`0",
				"roundmsg"=>"`6Your earlier spanking session with " . $partner . " `6helps you!",
				"atkmod"=>1.08,
				"rounds"=>-1,
				"schema"=>"module-wraith")
			);
		}
		elseif( $type == 'bad' )
		{
			if( $spanks == 2 )
			{
				$name = '`3Sore `$Hand';
				$message = '`3Your sore hand from spanking ' . $partner . '`3, lessens your attack!';
			}
			else
			{
				$name = '`3Sore `$Bottom';
				$message = '`3' . $partner . ' `3spanked you too hard. Your sore bottom lessens your attack!';
			}

			apply_buff('houseofspanking',array(
				"name"=>"$name`0",
				"roundmsg"=>"$message`0",
				"atkmod"=>0.98,
				"rounds"=>-1,
				"schema"=>"module-wraith")
			);
		}
		else
		{
			apply_buff('houseofspanking',array(
				"name"=>"`LS`lpanking `LM`lemories`0",
				"roundmsg"=>"`#You can't wait to visit `7Wraith's House of Spanking `#again!",
				"rounds"=>-1,
				"schema"=>"module-wraith")
			);
		}
	}

	if( get_module_pref('totaltoday') >= get_module_setting('spanksperday') )
	{
		output('`n`n`2As you leave, `7Wraith `2thanks you for your custom and to return again tomorrow.`n`n');
	}
	else
	{
		output('`n`n`2As you go to leave, `7Wraith `2thanks you for your custom and asks if you\'d like another spanking session?`n`n');
		addnav('Again?');
		addnav('S?Sure, why Not','runmodule.php?module=wraiths&op=pay');	
	}

	addnav('Leave');
	addnav('E?Exit House','village.php');
?>