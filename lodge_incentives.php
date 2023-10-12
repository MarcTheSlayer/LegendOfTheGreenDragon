<?php
/**
	06/03/09 - v0.0.2
	+ Hooked into 'donator_point_messages' and added a textbox setting so you can change the message in the Lodge.
	+ Hooked into 'donation_adjustments' to alter the default 100 pts per $1. *Only for this module, others will probably still use 100*
	+ Added textbox for optional message on the incentives page.
	+ Added textbox for optional thank you message to person who donated.
*/
function lodge_incentives_getmoduleinfo()
{
	$info = array(
		"name"=>"Lodge Incentives",
		"description"=>"Display a list of incentives on the index page showing what you can buy with your donation points.",
		"version"=>"0.0.2", 
		"author"=>"`@MarcTheSlayer`0, idea from `!`bRolland's`b `0Incentives module.",
		"category"=>"Lodge",
		"download"=>"http://dragonprime.net/index.php?topic=9831.0",
		"allowanonymous"=>TRUE,
		"settings"=>array(
			"IMPORTANT - README,title",
			"`^This module allows you to change the default 100 points you get for a $1 donation.`n
			However; The default 100 value is hard coded in various hooks and although this module hooks into and alters the value; Any other modules that use these various hooks will use the default 100.,note",
			"Lodge Incentives,title",
			"points"=>"How many lodge points per dollar?,int|100",
			"lodgemsg"=>"Replace $1 -> 100pts message in Lodge:,textarea,30|`7For each $1 donated; the account which makes the donation will receive %s contributor points in the game.",
			"`^Note: The %s in above message will be replaced with the lodge points per dollar value.,note",
			"incentmsg"=>"Add additional text to the lodge incentives page:,textarea,30|",
			"thanksmsg"=>"Add a thank you message after somebody has donated:,textarea,30|Thank you %N for donating $%D. You have been awarded %P lodge points.",
			"`#The following codes are supported in the thank you message only (case matters):`n%N = Player's name.`n%P = Points awarded.`n%D = Donated amount.,note",
			"shownil"=>"Show items that cost no lodge points?,bool|0",
			"incentives"=>"Enter incentives here.,textarearesizeable,40|",
			"`^Note: Put each on a newline and format like so '`@&lt;points&gt;`$:`@&lt;what they get&gt;`^'.,note",
			"`^Note: This is a new module and so not many hook into it just yet.`nIf no modules are hooked in and the box above is empty; then 'Describe Points' will be displayed.,note",
			"reset"=>"Reset the above box with default values.,bool|1",
		)
	);
	return $info;
}

function lodge_incentives_install()
{
	output("`4%s 'lodge_incentives' Module.`n", translate_inline(is_module_active('lodge_incentives')?'Undating':'Installing'));

	module_addhook('index');
//	module_addhook('lodge_incentives');
	module_addhook('changesetting');
	module_addhook('donation_adjustments');
	module_addhook('donator_point_messages');

	lodge_incentives_reset();
	return TRUE;
}

function lodge_incentives_uninstall()
{
	output("`4Uninstalling 'lodge_incentives' Module.`n");
	return TRUE;
}

function lodge_incentives_reset()
{
	/**
		This function will be run on install or when the admin selects 'Yes' in the reset setting.
	*/
	include('modules/lodge_incentives/lodge_incentives_modules.php');
}

function lodge_incentives_dohook($hookname,$args)
{
	switch( $hookname )
	{
		case 'index':
			addnav('Other Info');
			addnav('Donation Incentives','runmodule.php?module=lodge_incentives');
		break;
	/**
		case 'lodge_incentives':
			$points = $args['points'];
			$points['10000'][] = '`#This is an example.';
			$args['points'] = $points;
		break;
	*/
		case 'changesetting':
		    if( $args['setting'] == 'reset' && get_module_setting('reset') == 1 )
		    {
				lodge_incentives_reset();
		    }
		break;

		case 'donation_adjustments':
			$donation = $args['amount'];
			$args['points'] = $donation * get_module_setting('points');

			$msg = get_module_setting('thanksmsg');
			if( $msg != '' )
			{
				$search = array('%N','%P','%D');
				$replace = array($session['user']['name'],$args['points'],$donation);
				$msg = str_replace($search, $replace, $msg);
				$args['messages'][] = $msg;
			}
		break;

		case 'donator_point_messages':
			$msg = get_module_setting('lodgemsg');
			if( $msg != '' )
			{
				$args['messages'] = array('default'=>sprintf($msg, get_module_setting('points')));
			}
		break;
	}
	return $args;
}

function lodge_incentives_run()
{
	page_header('Donation Incentives');

	$lodge_points = get_module_setting('points');

	$op = httpget('op');

	switch( $op )
	{
		case 'incentives';
			output('`@Donations are accepted in whole dollar increments only!.`n`n');
			output('`@Each item that can be purchased with donation points is on its own line.`n`n');

			$points = array();
			$points = modulehook('lodge_incentives', array('points'=>$points));
			$incentives = get_module_setting('incentives');

			if(	!empty($points['points']) || !empty($incentives) )
			{
				$spend = $points['points'];
				unset($points);

				$incentives = explode("\r\n",trim($incentives));
				$count = count($incentives);
				for( $i=0; $i<$count; $i++ )
				{
					list($key, $value) = explode(':', trim($incentives[$i]));
					$spend[$key][] = $value;
				}

				if( get_module_setting('shownil') != 1 )
				{
					unset($spend['0']);
					unset($spend['']);
				}

				ksort($spend);

				foreach( $spend as $key => $value )
				{
					rawoutput('<fieldset style="width:98%"><legend>');
					$cost = round($key/$lodge_points, 2);
					$parts = explode('.', $cost);
					$cost = ( strlen($parts[1]) != 2 && ($count = count($parts)) == 2 ) ? $cost . '0' : $cost;
					output('`^$%s - %s Points`0', $cost, $key);
					rawoutput('</legend><ul>');
					foreach( $value as $key2 )
					{
						rawoutput('<li>');
						output('%s`0', $key2);
						rawoutput('</li>');
					}
					rawoutput('</ul></fieldset><br /><br />');
				}
			}
			else
			{
				$args = modulehook('pointsdesc', array('format'=>'`#&#149;`7 %s`n', 'count'=>0));
				if( $args['count'] == 0 )
				{
					output("`#&#149;`7None -- Please talk to your admin about creating some.`n", true);
				}
			}

			addnav('Donations');
			addnav('D?Donating Details','runmodule.php?module=lodge_incentives');
		break;

		default:
			output('`2Although this is a free game to play, it survives by being given donations to cover server hosting costs. ');
			output('If you enjoy this game and can afford it, we gladly and graciously accept any amount to help towards its upkeep.`n`n');
			output('`2As an incentive to donate, donators will be given `#%s lodge points `2and access to the Hunter\'s Lodge for every `#$1 `2donated.', $lodge_points);
			output('This is an area reserved exclusively for them and where they can spend their points on various things.`n`n');
			output('Purchases from the Lodge can offer various advantages in the game and as time goes on, more advantages will likely reveal themselves.`n`n');

			$award = getsetting('refereraward', 25);
			if( !empty($award) )
			{
				$level = getsetting('referminlevel', 4);
				output('`2"`@But I don\'t have access to a PayPal account, or I otherwise can\'t donate to your very wonderful project!`2"`n');
				output('`2Not to worry as there\'s another way. For every person that you refer to our site and who makes it to `#level %s`2, you will receive `#%s points`2.', $level, $award);
				output('Just one referrer reaching `#level %s `2will grant you access to the Hunter\'s Lodge.`n`n', $level);
			}

			if( get_module_setting('incentmsg') )
			{
				output_notl('`2%s`0`n`n', get_module_setting('incentmsg'));
			}

			output('`@Donations are accepted in whole dollar increments only!.`n`n');

			addnav('Donations');
			addnav('I?Incentives Details','runmodule.php?module=lodge_incentives&op=incentives');
		break;
	}

	addnav('Return');
	addnav('H?HomePage','home.php');

	page_footer();
}
?>