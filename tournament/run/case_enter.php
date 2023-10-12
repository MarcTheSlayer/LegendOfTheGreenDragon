<?php
	$efeegold = get_module_setting('efeegold');
	$efeegems = get_module_setting('efeegems');

	if( $session['user']['gems'] >= $efeegems && $session['user']['gold'] >= $efeegold )
	{
		output("`n`3Sir Tristan breaks into a toothy grin.`n`n`#Excellent! You have chosen to enter the Tournament. You must face a challenge at each level of your training. So as not to forget it's best to visit right after you've been to Blusprings.`n`n");
		output("For each challenge, you will receive a number of points so make sure to do the challenge at each level of your training to maximise your points. Whoever attains the highest score at the end of the tournament collects the prizes.`n`n");
		output("`#`bGood Luck to Ye!`b`n`n");
		$session['user']['gems'] -= $efeegems;
		$session['user']['gold'] -= $efeegold;
		set_module_pref('entry', 1);

		$sql = "SELECT userid
				FROM " . db_prefix('module_userprefs') . "
				WHERE modulename = 'tournament'
					AND setting = 'entry'
					AND value = 1";
		$result = db_query($sql);
		$count = db_num_rows($result);
		if( get_module_setting('status') == 1 )
		{
			if( $count >= get_module_setting('minimum') )
			{
				set_module_setting('status',2);
				set_module_setting('start',date("Y-m-d H:i:s")); // 2013-05-11 00:00:00
			}
		}

		if( get_module_setting('status') == 2 )
		{
			addnav('Tournament');
			addnav('E?`^Enter the Arena`0',$from.'&op=main');
		}
		else output('`3The Tournament is still waiting for more warriors, check back later.`n`n');
	}
	else
	{
		output("`3Sir Tristan glares at you. `#What do ye think I run here? A charity? Ye do not have enough gold and gems for the Entrance Fee.`n");
		output("Get out of my office, you dishonourable swine!`n`n");
		output("`bDon't waste my time until you can afford the paltry sum required to enter!`b");
	}

	addnav('Leave');
	villagenav();
?>