<?php
	$id = httpget('id');
	$sql = "SELECT name FROM " . db_prefix('accounts') . " WHERE acctid = '$id'";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);

	output("`c`b`!Tournament Levels Completed by %s`b`c`n`n", $row['name']);

	$level = translate_inline('Level');
	$score = translate_inline('Score');

	$allprefs = @unserialize(get_module_pref('allprefs','tournament',$id));
	if( !empty($allprefs) )
	{
		rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999" align="center">');
		rawoutput('<tr class="trhead"><td align="center">'.$level.'</td><td align="center">'.$score.'</td></tr>');
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
		rawoutput('<tr bgcolor="#005599"><td align="center">');
		output('`$`bTotal`b');
		rawoutput('</td><td align="right">');
		output_notl('`^`b%s`b', $total);
		rawoutput('</td></tr></table>');
	}
	else
	{
		if( get_module_setting('status') > 0 ) output('`n`#%s `3has yet to take part in the current Tournament.', $row['name']);
		else output('`n`#The Tournament is currently not available. See Sir Tristan in his office for more details.');
	}

	addnav('Back');
	addnav("Player's Bio",'bio.php?char='.$id);
?>