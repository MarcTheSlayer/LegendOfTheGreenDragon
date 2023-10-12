<?php
	addnav('Options');

	$action = httpget('action');
	switch( $action )
	{
		case 'reset':
			output("`n`3This is your last chance. After this point you can't go back.`n`n");
			output("No prizes will be given out and nobody will get their entrance fee back.`n`n");
			output("`\$Are you REALLY sure you want to reset the Tournament?");

			addnav('Y?`$Yes, Reset Tournament`0',$from.'&op=reset&action=resetconfirm');
			addnav('N?`@No, Go Back`0',$from.'&op=reset');
		break;

		case 'resetconfirm':
			debuglog("The Tournament has been reset.");
			db_query("DELETE FROM " . db_prefix('module_userprefs') . " WHERE modulename = 'tournament' AND (setting = 'entry' OR setting = 'points' OR setting = 'allprefs')");
			set_module_setting('leader', 0);
			set_module_setting('status', 0);
			set_module_setting('start',date("Y-m-d H:i:s"));

			output("`n`@Tournament has been reset manually!!");
		break;

		case 'prizes':
			output("`n`3This is your last chance. After this point you can't go back.`n`n");
			output("Prizes will be awarded to the top 3 warriors and the Tournament will be reset.`n`n");
			output("`\$Are you REALLY sure you want to give out the prizes??");

			addnav('Y?`$Yes, Give Out Prizes`0',$from.'&op=reset&action=giveprizes');
			addnav('N?`@No, Go Back`0',$from.'&op=reset');
		break;

		case 'giveprizes':
			output("`n`c`b`&The Giveaways of Prizes of the Big Tourney of LoGD`b`c`n`n");

			include('modules/tournament/tournament_reset.php');

			if( $count == 0 )
			{
				output("`3No warriors had joined the Tournament, or nobody had any points, so no prizes were handed out!`0");
			}
			else
			{
				output("`\$`bPrizes have been handed out!!`b`n`n`3Go check out the news and make a MoTD post or something.");
			}
		break;

		case '':
			if( get_module_setting('status') == 2 )
			{
				output("`\$`n`4`bWARNING`b`0!!! `3You are about to reset the Tournament.`n`n");
				output("Are you 100% sure?`n`n");

				$sql = "SELECT userid
						FROM " . db_prefix('module_userprefs') . "
						WHERE modulename = 'tournament'
							AND setting = 'entry'
							AND value = 1";
				$result = db_query($sql);
				$count = db_num_rows($result);
				output("There are currently %s %s in the Tournament.", $count, translate_inline($count==1?'player':'players'));

				addnav('R?`$Reset Tournament`0',$from.'&op=reset&action=reset');
				addnav('G?`^Give Out Prizes`0',$from.'&op=reset&action=prizes');
			}
			else
			{
				output('`n`3No Tournament is currently running so the reset options are not available.');
			}
		break;
	}
?>