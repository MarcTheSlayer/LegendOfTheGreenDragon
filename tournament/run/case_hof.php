<?php
	$champions = @unserialize(get_module_setting('champions'));
	if( !is_array($champions) ) $champions = array();
	$count = count($champions[0]);
	if( $count > 0 )
	{
		output('`n`2These are the Champions for each Tournament that has taken place.`n`n');

		$ids = $champions[0];
		$scores = $champions[1];

		$sql = "SELECT acctid, name FROM " . db_prefix('accounts') . " WHERE acctid IN (".implode(',', $ids).")";
		$result = db_query($sql);
		$names = array();
		while( $row = db_fetch_assoc($result) ) $names[$row['acctid']] = $row['name'];

		$name = translate_inline('Name');
		$score = translate_inline('Score');
		rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999" align="center">');
		rawoutput('<tr class="trhead"><td align="center">#</td><td align="center">'.$name.'</td><td align="center">'.$score.'</td></tr>');

		for( $i=0; $i<$count; $i++ )
		{
			if( $names[$ids[$i]] == $session['user']['name'] )
			{
				rawoutput('<tr bgcolor="#007700">');
			}
			else
			{
				rawoutput('<tr class="'.($i%2?'trlight':'trdark').'">');
			}
			rawoutput('<td>'.($i + 1).'</td><td>');
			output_notl($names[$ids[$i]]);
			rawoutput('</td><td align="center">'.$scores[$i].'</td></tr>');
		}

		rawoutput('</table>');
	}
	else
	{
		output('`n`2There are currently no Champions. How about you join the Tournament and see if you can become one.');
	}

	addnav('Options');
	addnav('Back to HoF','hof.php');
?>