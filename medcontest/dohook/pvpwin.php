<?php
	//
	// The person sleeping in the fields has been defeated by their attacker.
	//
	if( get_module_pref('medhunt','medcontest',$session['user']['acctid']) && get_module_pref('medhunt','medcontest',$args['badguy']['acctid']) )
	{
		$amount1 = get_module_pref('medallion','medcontest',$session['user']['acctid']);
		$amount2 = get_module_pref('medallion','medcontest',$args['badguy']['acctid']);
		if( !empty($amount2) && $amount1 != 5 )
		{
			$diff = 5 - $amount1;
			if( $amount2 > $diff )
			{
				increment_module_pref('medallion',$diff,'medcontest',$session['user']['acctid']);
				increment_module_pref('medallion',-$diff,'medcontest',$args['badguy']['acctid']);
				$args['pvpmessageadd'] .= '`n' . $session['user']['name'] . ' `3took ' . $amount2 . ' of your ' . $meds . ' `3.`n`2';
				output('`n`#You take %s of %s `#%s`#.`0`n`n', $diff, $args['badguy']['creaturename'], $meds);
			}
			elseif( $amount2 <= $diff )
			{
				increment_module_pref('medallion',$amount2,'medcontest',$session['user']['acctid']);
				increment_module_pref('medallion',-$amount2,'medcontest',$args['badguy']['acctid']);
				$args['pvpmessageadd'] .= '`n' . $session['user']['creaturename'] . ' `3took all of your ' . $meds . ' `3.`n`2';
				output('`n`#You take all of their %s`#.`0`n`n', $meds);
			}
		}
	}
?>