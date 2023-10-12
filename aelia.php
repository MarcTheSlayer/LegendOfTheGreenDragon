<?php
/**
	Modified by MarcTheSlayer
	01/07/09 - v1.3
	+ 1 Bug fix, added locations setting and small alterations.

	10/07/09 - v1.4
	+ Added a points rate ratio so lodge points could be increased.
*/
function aelia_getmoduleinfo()
{
	$info = array(
		"name"=>"Aelia's Gem Shoppe",
		"description"=>"A shoppe that sells donation points for gems.",
		"author"=>"Aelia, minor update by Iori`2, modified by `@MarcTheSlayer",
		"version"=>"1.4",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/index.php?topic=4833.0",
		"settings"=>array(
			"aelialoc"=>"What is the Gem Shoppe's location?,location|".getsetting('villagename', LOCATION_FIELDS),
			"allowed"=>"Amount of site points allowed to be bought per DK,int|200",
			"ratio"=>"What's the Ratio?,enum,0,Points:1 Gem,1,Gems:1 Point|1",
			"rate2"=>"Number of Points,range,1,50,1|2",
			"rate"=>"Number of Gems,range,1,50,1|2",
		),
		"prefs"=>array(
			"thisdk"=>"Site points bought this DK:,int|0",
			"totalbought"=>"Total site points bought from the shoppe:,int|0",
		),
	);
	return $info;
}

function aelia_install()
{
	module_addhook('village');
	module_addhook('dragonkill');
	return TRUE;
}

function aelia_uninstall()
{
	return TRUE;
}

function aelia_dohook($hookname, $args)
{
	if( $hookname == 'village' )
	{
		global $session;
		if( $session['user']['location'] == get_module_setting('aelialoc') )
		{
			tlschema($args['schemas']['marketnav']);
			addnav($args["marketnav"]);
			tlschema();
			addnav("A?Aelia's Gem Shoppe","runmodule.php?module=aelia");
		}
	}
	else
	{
		clear_module_pref('thisdk');
	}

	return $args;
}

function aelia_run()
{
	global $session;

	page_header("Aelia's Gem Shoppe");

	$allowed = get_module_setting('allowed');
	$thisdk = get_module_pref('thisdk');
	$ratio = get_module_setting('ratio');
	$rate = get_module_setting('rate');
	$rate2 = get_module_setting('rate2');

	$op = httpget('op');
	if( $op == 'pay' )
	{
		$amt = abs((int)httpget('amt'));
		if( $ratio == 1 )
		{
			$needed = $rate * $amt;
		}
		else
		{
			$needed = $amt / $rate2;
		}

		if( $session['user']['gems'] < $needed )
		{
			output("`n`2Aelia looks at you sternly and says, \"`7You don't have enough gems!`2\"");
		}
		elseif( $thisdk >= $allowed )
		{
			output("`n`2Aelia looks at you sternly and says, \"`7You've bought enough already, don't come back until after you've slain the Dragon!`2\"");
		}
		else
		{
			$session['user']['gems'] -= $needed;
			$session['user']['donation'] += $amt;
			debuglog("spent $needed gems to buy $amt donation points");
			$thisdk += $amt;
			increment_module_pref('thisdk', $amt);
			increment_module_pref('totalbought', $amt);

			output("`n`2You spend `% %s %s `2and buy `&%s donation %s`2!`n`n", $needed, translate_inline($needed==1?'gem':'gems'), $amt, translate_inline($amt==1?'point':'points'));
			output("So far this DK you have purchased `&%s donation %s`2. You're allowed to purchase `7%s `2more.", $thisdk, translate_inline($thisdk==1?'point':'points'), ($allowed-$thisdk));
		}
	}
	else
	{
		if( $ratio == 1 )
		{
			output("`2This is Aelia's Gem Shoppe. Here, you can exchange gems for donation points at a rate of `% %s gems `2to `&1 donation point`2.`n`n", $rate);
		}
		else
		{
			output("`2This is Aelia's Gem Shoppe. Here, you can exchange gems for donation points at a rate of `%1 gem `2to `&%s donation points`2.`n`n", $rate2);
		}
		output("You can purchase a maximum of `7%s donation points `2every Dragonkill, and you have already purchased `7%s %s `2this DK.`n", $allowed, $thisdk, translate_inline($thisdk==1?'point':'points'));
	}

	addnav('Options');
	if( $ratio == 1 )
	{	// How many gems per donation point.
		addnav(array("`2Pay`R %s `5Gems`0",$rate), "runmodule.php?module=aelia&op=pay&amt=1");
		if( (10 * $rate) <= $session['user']['gems'] && ($thisdk + 10) <= $allowed )
		{
			addnav(array("`2Pay`R %s `5Gems`0",(10 * $rate)), "runmodule.php?module=aelia&op=pay&amt=10");
			if( (25 * $rate) <= $session['user']['gems'] && ($thisdk + 25) <= $allowed )
			{
				addnav(array("`2Pay`R %s `5Gems`0",(25 * $rate)), "runmodule.php?module=aelia&op=pay&amt=25");
				if( (50 * $rate) <= $session['user']['gems'] && ($thisdk + 50) <= $allowed )
				{
					addnav(array("`2Pay`R %s `5Gems`0",(50 * $rate)), "runmodule.php?module=aelia&op=pay&amt=50");
					if( (100 * $rate) <= $session['user']['gems'] && ($thisdk + 100) <= $allowed )
					{
						addnav(array("`2Pay`R %s `5Gems`0",(100 * $rate)), "runmodule.php?module=aelia&op=pay&amt=100");
					}
				}
			}
		}
	}
	else
	{	// How many donation points per gem.
		$points = $rate2 * 1;
		addnav("`2Pay`R 1 `5Gem`0", "runmodule.php?module=aelia&op=pay&amt=$points");
		$points = $rate2 * 10;
		if( 10 <= $session['user']['gems'] && ($thisdk + $points) <= $allowed )
		{
			addnav("`2Pay`R 10 `5Gems`0", "runmodule.php?module=aelia&op=pay&amt=$points");
			$points = $rate2 * 25;
			if( 25 <= $session['user']['gems'] && ($thisdk + $points) <= $allowed )
			{
				addnav("`2Pay`R 25 `5Gems`0", "runmodule.php?module=aelia&op=pay&amt=$points");
				$points = $rate2 * 50;
				if( 50 <= $session['user']['gems'] && ($thisdk + $points) <= $allowed )
				{
					addnav("`2Pay`R 50 `5Gems`0", "runmodule.php?module=aelia&op=pay&amt=$points");
					$points = $rate2 * 100;
					if( 100 <= $session['user']['gems'] && ($thisdk + $points) <= $allowed )
					{
						addnav("`2Pay`R 100 `5Gems`0", "runmodule.php?module=aelia&op=pay&amt=$points");
					}
				}
			}
		}
	}

	addnav('Leave');
	require_once('lib/villagenav.php');
	villagenav();

	page_footer();
}
?>