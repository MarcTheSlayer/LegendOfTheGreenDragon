<?php
// Wrongful Wraiths by Robert Riochas
// http://maddrio.com
// For LotGD version 1.x

/*
	Modified by MarcTheSlayer

	22/02/09 - v1.2.1
	+ Rewrote the code and put each section in its own file.
	+ Added player prefs so they can choose to spank or be spanked by either sex.
	+ Changed the output text, slightly more riskier.
	+ Added good/neutral/bad buffs.
	24/02/09 - v1.2.2
	+ Added setting so admin can override player's choice and make it male -> female and female -> male.
	+ Added addnews when player gets bad buff.
	28/03/09 - v1.2.3
	+ Minor code improvements and tweaks and the odd bug fix.
	05/09/10 - v1.2.4
	+ The bad outcome no longer uses the turns/charm/hp gain settings to punish you.
	29/05/2011 - v1.2.5
	+ Altered village hook.
	25/06/2017 - v1.2.6
	+ Added a reset gender option if 'allowchoice' is enabled.
	+ Added more underwear names for randomness.
*/
function wraiths_getmoduleinfo()
{
	$info = array(
		"name"=>"Wrongful Wraiths",
		"description"=>"Village shop that allows players to get good/bad things.",
		"version"=>"1.2.6",
		"author"=>"`2Robert, modified by `@MarcTheSlayer",
		"category"=>"Village",
		"download"=>"http://dragonprime.net/index.php?topic=9874.0",
		"settings"=>array(
			"Wraith's House of Spanking - Settings,title",
			"inallloc"=>"Show House in all locations?,bool|0",
			"wraithloc"=>"Where does the House appear if not in all?,location|".getsetting('villagename', LOCATION_FIELDS),
			"spanksperday"=>"Visits to shop allowed each day:,int|1",
			"spankcost"=>"Cost in gold:,range,10,1000,10|100",
			"House Rewards,title",
			"minturns"=>"Minimum turns to give:,range,1,25,1|1",
			"maxturns"=>"Maximum turns to give:,range,1,50,1|2",
			"minhp"=>"Minimum hp to give:,range,1,100,1|10",
			"maxhp"=>"Maximum hp to give:,range,1,200,1|100",
			"mincharm"=>"Minimum charm to give:,range,1,5,1|1",
			"maxcharm"=>"Maximum charm to give:,range,1,10,1|2",
			"Sexual Choice,title",
			"allowchoice"=>"Allow player to choose which sex to spank/be spanked?,bool|1",
			"`^Note: If 'NO'; then male players spank females and female players get spanked by males.,note"
		),
		"prefs"=>array(
			"totaltoday"=>"How many spankings did they buy today?,int|",
			"totaldk"=>"Total times the player used this shop.,int|",
			"gender"=>"Spanking sexual preference.,enum,0,Not Set,1,Both,2,Men,3,Women|",
			"spanks"=>"Prefers to spank or be spanked.,enum,0,Not Set,1,Either,2,Spank,3,Spanked|"
		)
	);
	return $info;
}

function wraiths_install()
{
	output("`c`b`Q%s 'wraiths' Module.`b`n`c", translate_inline(is_module_active('wraiths')?'Updating':'Installing'));
	module_addhook('changesetting');
	if( get_module_setting('inallloc','wraiths') == 1 ) module_addhook('village');
	else module_addhook('village-'.get_module_setting('wraithloc','wraiths'));
	module_addhook('newday');
	return TRUE;
}

function wraiths_uninstall()
{
	output("`n`c`b`Q'wraiths' Module Uninstalled`0`b`c");
	return TRUE;
}

function wraiths_dohook($hookname,$args)
{
	global $session;

	$location = ( get_module_setting('inallloc','wraiths') == 1 ) ? '' : '-'.$session['user']['location'];

	switch( $hookname )
	{
		case 'changesetting':
			if( $args['setting'] == 'villagename' )
			{
				if( $args['old'] == get_module_setting('wraithloc') )
				{
					set_module_setting('wraithloc', $args['new']);
					if( get_module_setting('inallloc','wraiths') == 0 )
					{
						module_drophook('village-'.$args['old']);
						module_addhook('village-'.$args['new']);
					}
				}
			}
			if( $args['module'] == 'wraiths' )
			{
				if( $args['setting'] == 'wraithloc' )
				{
					if( get_module_setting('inallloc','wraiths') == 0 )
					{
						module_drophook('village-'.$args['old']);
						module_addhook('village-'.$args['new']);
					}
				}

				if( $args['setting'] == 'inallloc' )
				{
					if( $args['new'] == 1 )
					{
						module_drophook('village-'.get_module_setting('wraithloc'));
						module_addhook('village');
					}
					else
					{
						module_drophook('village');
						module_addhook('village-'.get_module_setting('wraithloc'));
					}
				}
			}
	    break;

		case "village$location":
			tlschema($args['schemas']['marketnav']);
			addnav($args['marketnav']);
			tlschema();
			addnav('Wrongful Wraiths','runmodule.php?module=wraiths');
		break;

		case 'newday':
			clear_module_pref('totaltoday');
		break;
	}

	return $args;
}
function wraiths_run()
{
	global $session;

	if( isset(httpget('reset')) && httpget('reset') == 'yes' )
	{
		set_module_pref('gender', 0);
	}

	if( get_module_setting('inallloc') == 1 )
	{
		page_header("Wraith's {$session['user']['location']} House of Spanking");
	}
	else
	{
		page_header("Wraith's House of Spanking");
	}

	// Names are here for easy colourising. I was tempted to change the title names so hotlink letters would spell out 'S P A N K' or something. Heh. :)
//	$partners = array(1=>'Amazon Abby','Cheerful Chrissy','Dainty Debbie','Full-figured Flo','Sultry Susan','Bearded Brad','Fat Frank','Handsome Hank','Marvelous Marvin','Smiling Sam');
	$partners = array(1=>'`qA`Qm`qa`Qz`qo`Qn `qA`Qb`qb`Qy`0','`VC`vh`Ve`ve`Vr`vf`Vu`vl `VC`vh`Vr`vi`Vs`vs`Vy`0','`%D`5a`%i`5n`%t`5y `%D`5e`%b`5b`%i`5e`0','`^F`6u`^l`6l`^-`6f`^i`6g`^u`6r`^e`6d `^F`6l`^o`0','`4S`$u`4l`$t`4r`$y `4S`$u`4s`$a`4n`0','`)Bearded `7Brad`0','`yFat `YFrank`0','`MH`ma`Mn`md`Ms`mo`Mm`me `MHank`0','`#M`3arvelous `#M`3arvin`0','`LSmiling `lSam`0');

	if( get_module_setting('allowchoice') == 1 )
	{
		$gender = get_module_pref('gender');
		$spanks = get_module_pref('spanks');
	}
	else
	{
		$gender = ( $session['user']['sex'] == 1 ) ? 2 : 3;
		$spanks = ( $session['user']['sex'] == 1 ) ? 3 : 2;
	}
	if( empty($gender) )
	{
		$op = 'gender';
	}
	elseif( empty($spanks) )
	{
		$op = 'spanks';
	}
	else
	{
		$op = httpget('op');
	}

	require("modules/wraiths/run/case_$op.php");

	if( $session['user']['superuser'] & SU_MANAGE_MODULES )
	{
		addnav('Superuser');
		addnav('Module Settings','configuration.php?op=modulesettings&module=wraiths');
	}

	page_footer();
}
?>