<?php
	$action = httpget('action');
	switch( $action )
	{
		case 'general':
			output("`c`b`!Tournament's General Score`b`c`n`n");

			$sql = "SELECT userid, name, value
					FROM " . db_prefix('module_userprefs') . "
					LEFT JOIN " . db_prefix('accounts') . "
						ON (acctid = userid)
					WHERE modulename = 'tournament'
						AND setting = 'points'
						AND value <> ''
					ORDER BY value + 0 DESC";
			$result = db_query($sql);

			$name = translate_inline('Name');
			$score = translate_inline('Score');
			$trials = translate_inline('N° of Trials');
			rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999" align="center">');
			rawoutput('<tr class="trhead"><td align="center">#</td><td align="center">'.$name.'</td><td align="center">'.$score.'</td><td align="center" nowrap="nowrap">'.$trials.'</td></tr>');

			if( db_num_rows($result) == 0 )
			{
				rawoutput('<tr class="trlight"><td colspan="4" align="center">');
				output("`&Nobody has performed any trials yet.`0");
				rawoutput('</td></tr>');
			}
			else
			{
				$i=0;
				while( $row = db_fetch_assoc($result) )
				{
					$row['allprefs'] = @unserialize(get_module_pref('allprefs', 'tournament', $row['userid']));
					$trials = count($row['allprefs']);
					if( $trials >= 1 )
					{
						if( $row['name'] == $session['user']['name'] )
						{
							rawoutput('<tr bgcolor="#007700">');
						}
						else
						{
							rawoutput('<tr class="'.($i%2?'trlight':'trdark').'">');
						}
						rawoutput('<td>'.($i + 1).'</td><td>');
						output_notl($row['name']);
						rawoutput('</td><td align="right">'.$row['value'].'</td><td align="center">'.$trials.'</td></tr>');
				 	}
				 	$i++;
				}
			}
			rawoutput('</table>');
		break;

		case 'scorelevel':
			$level = httpget('level');

			addnav('Choose Level');
			for( $i=1; $i<=15; $i++ )
			{
				$code = ( $level == $i ) ? '`@' : '`$';
				addnav(array('%sLevel %s`0', $code, $i),$from.'&op=scores&action=scorelevel&level='.$i);
			}

			if( !$level )
			{
				output("`c`b`!Level Scores`b`c`n`n");
				output('`6Please select one of the levels to see those scores.');
			}
			else
			{
				$sql = "SELECT userid, name, value
						FROM " . db_prefix('module_userprefs') . "
						LEFT JOIN " . db_prefix('accounts') . "
							ON (acctid = userid)
						WHERE modulename = 'tournament'
							AND setting = 'points'
							AND value <> ''
						ORDER BY value + 0 DESC";
				$result = db_query($sql);
				$k=1;
				$arr = array();
				while( $row = db_fetch_assoc($result) )
				{
					$row['allprefs'] = @unserialize(get_module_pref('allprefs', 'tournament', $row['userid']));
					if( isset($row['allprefs'][$level]) && $row['allprefs'][$level] > 0 )
					{
						$arr[$k]['score'] = $row['allprefs'][$level];
						$arr[$k]['name'] = $row['name'];
						$k++;
					}
				}
				arsort($arr);
				reset($arr);

				output("`c`b`!Level %s Score`b`c`n`n", $level);

				$name = translate_inline('Name');
				$score = translate_inline('Score');
				rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999" align="center">');
				rawoutput('<tr class="trhead"><td align="center">#</td><td align="center">'.$name.'</td><td align="center">'.$score.'</td></tr>');

				if( count($arr) > 0 )
				{
					foreach( $arr as $key => $row )
					{
						if( $arr[$key]['name'] == $session['user']['name'] )
						{
							rawoutput('<tr bgcolor="#007700">');
						}
						else
						{
							rawoutput('<tr class="'.(($key+1)%2?'trlight':'trdark').'">');
						}
						rawoutput("<td>$key</td><td>");
						output_notl($arr[$key]['name']);
						rawoutput('</td><td align="right">'.$arr[$key]['score'].'</td></tr>');
					}
				}
				else
				{
					rawoutput('<tr class="trlight"><td colspan="3">');
					output("`&Nobody has played this level yet`0");
					rawoutput('</td></tr>');
				}
				rawoutput('</table>');
			}
		break;

		case 'level':
			output("`c`b`!Levels Completed by %s`b`c`n`n", $session['user']['name']);

			$level = translate_inline('Level');
			$score = translate_inline('Score');
			rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999" align="center">');
			rawoutput('<tr class="trhead"><td align="center">'.$level.'</td><td align="center">'.$score.'</td></tr>');

			$allprefs = @unserialize(get_module_pref('allprefs'));
			if( !is_array($allprefs) ) $allprefs = array();
			if( count($allprefs) > 0 )
			{
				$total = $i = 0;
				foreach( $allprefs as $level => $score )
				{
					rawoutput('<tr class="'.($i%2?'trlight':'trdark').'"><td align="center">');
					output_notl('`@%s', $level);
					rawoutput('</td><td align="right">');
					output_notl('`^%s', $score);
					rawoutput('</td></tr>');
					$i++;
					$total += $score;
				}
				rawoutput('<tr bgcolor="#005599"><td colspan="2">');
				output('`$`bTotal: `^%s`b', $total);
				rawoutput('</td></tr>');
			}
			else
			{
				rawoutput('<tr class="trlight"><td colspan="2">');
				output("`&You haven't done any trials yet.`0");
				rawoutput('</td></tr>');
			}
			rawoutput('</table>');
		break;

		case '':
			output("`c`b`@The Tournament Scoreboard`0`b`c`n");

			output("`6Welcome to the `@Tournament Scoreboard`6. This is where you can view the different kinds of scores. ");
			output("View the general overall score which lists from highest to the lowest. View the overall score per level which lists the highest to the lowest per level ");
			output("total points and the number of trial played till now, or you can choose to view the ");
			output("classification for every single trial played, where you can see who's best scored in that trial. ");
			output("Choose a Classification`n");
		break;
	}

	addnav('Tournament');
	addnav('O?Office`0',$from);
	addnav('Scores');
	addnav('G?`#General Score`0',$from.'&op=scores&action=general');
	addnav('L?`%Score Per Level`0',$from.'&op=scores&action=scorelevel');
	addnav('Y?`^Your Score/Level`0',$from.'&op=scores&action=level');

	addnav('Leave');
	villagenav();
?>