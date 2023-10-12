<?php
	//
	// The person sleeping in the fields has defeated their attacker.
	//
	if( get_module_pref('medhunt','medcontest',$session['user']['acctid']) && get_module_pref('medhunt','medcontest',$args['badguy']['acctid']) )
	{
		$amount1 = get_module_pref('medallion','medcontest',$session['user']['acctid']);
		$amount2 = get_module_pref('medallion','medcontest',$args['badguy']['acctid']);
		if( !empty($amount1) && $amount2 != 5 )
		{
			$diff = 5 - $amount2;
			if( $amount1 > $diff )
			{
				increment_module_pref('medallion',-$diff,'medcontest',$session['user']['acctid']);
				increment_module_pref('medallion',$diff,'medcontest',$args['badguy']['acctid']);
				$args['pvpmessageadd'] .= '`n' . $session['user']['name'] . ' `3was carrying ' . $amount1 . ' ' . $meds . ' `3on their person, but you could only take ' . $diff . '.`n`2';
				output('`n`#You lost %s of your %s `#to %s`#.`0`n`n', $diff, $meds, $args['badguy']['creaturename']);
			}
			elseif( $amount1 <= $diff )
			{
				increment_module_pref('medallion',-$amount1,'medcontest',$session['user']['acctid']);
				increment_module_pref('medallion',$amount1,'medcontest',$args['badguy']['acctid']);
				$args['pvpmessageadd'] .= '`n' . $session['user']['name'] . ' `3was carrying ' . $amount1 . ' ' . $meds . ' `3on their person and you got all of them.`n`2';
				output('`n`#You lost all of your %s `#to %s`#.`0`n`n', $meds, $args['badguy']['creaturename']);
			}
		}
	}
?>