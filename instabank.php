<?php
/**
* Version:      0.8
* Updated Date:	 February 04, 2005
* Author:       Kevin Hatfield - Arune http://www.dragonprime.net
* LOGD VER:     Module for 0.9.8
*
*v20060303  Added line to state how much is in bank after deposit.
*/
/*
	Modified by MarcTheSlayer - 18/09/08

	v20080918 - Cleaned formatting, rewrote code, removed un-needed code, fixed a couple of bugs, rewrote parts of the text and added forest().
*/
function instabank_getmoduleinfo()
{
	$info = array(
		"name"=>"Instant Banking",
		"version"=>"20080918",
		"category"=>"Forest",
		"author"=>"Kevin Hatfield - Arune (options by sixf00t4), modified by `@MarcTheSlayer.",
		"description"=>"Adds links to the forest area allowing you to bank your gold without leaving.",
		"download"=>"http://dragonprime.net"
	);
	return $info;
}

function instabank_install()
{
	module_addhook('forest');
	return true;
}

function instabank_uninstall()
{
	output('Uninstalling this module.`n');
	return true;
}

function instabank_dohook($hookname, $args)
{
	global $session;

	switch($hookname)
	{
		case 'forest':
			if( $session['user']['gold'] > 0 )
			{
				addnav('Eagle Banking');
				addnav('All Gold','runmodule.php?module=instabank');
				if( $session['user']['gold'] > 100 )	addnav('Keep 100 gold','runmodule.php?module=instabank&d=100');
				if( $session['user']['gold'] > 500 )    addnav('Keep 500 gold','runmodule.php?module=instabank&d=500');
				if( $session['user']['gold'] > 1000 )    addnav('Keep 1000 gold','runmodule.php?module=instabank&d=1000');
			}
		break;
	}
	return $args;
}

function instabank_run()
{
	global $session;

	$d = httpget('d');

	page_header('Eagle Banking');
	output('`^`c`bInstant Banking`b`c`6');

	if( $d )
	{
		$deposit = $session['user']['gold'] - $d;
		$session['user']['goldinbank'] += $deposit;
		$session['user']['gold'] = $d;
	}
	else
	{
		// Deposit all gold.
		$deposit = $session['user']['gold'];
		$session['user']['goldinbank'] += $session['user']['gold'];
		$session['user']['gold'] = 0;
	}

	output("You take an empty pouch from your back pocket and put %s of your gold into it. You whistle loudly and suddenly a giant eagle swoops down from the clouds and takes the pouch from your hand. It flies off towards %s and after a short while it returns to you carrying a now empty pouch and a note from the bank.",translate_inline($d?'some':'all'),$session['user']['location']);
	output("`n`n`^\"You deposited `&%s gold `^into your account",$deposit);

	if( $session['user']['goldinbank'] < 0 )
	{
		output(", but you're still in debt by `&%s gold`^.\"",$session['user']['goldinbank']);
	}
	else
	{
		output(" and you now have `&%s gold `^in the bank.\"",$session['user']['goldinbank']);
	}

	require_once('lib/forest.php');
	forest(TRUE);
	page_footer();
}
?>