<?php
/**
	16/08/09 - v0.0.3
	Idea for using the LOGINTIMEOUT setting from Talisman, code based on jerry's. :)
*/
function devnotimeout_getmoduleinfo()
{
	$info = array(
		"name"=>"Developers No Timeout Window",
		"description"=>"A popup window for developers so they do not timeout when testing.",
		"version"=>"0.0.3",
		"author"=>"`@MarcTheSlayer `2for JollyGG",
		"category"=>"Administrative",
		"override_forced_nav"=>TRUE,
		"download"=>"http://dragonprime.net/index.php?topic=10382.0"
	);
	return $info;
}

function devnotimeout_install()
{
	module_addhook('superuser');
	return TRUE;
}

function devnotimeout_uninstall()
{
	return TRUE;
}

function devnotimeout_dohook($hookname,$args)
{
	global $session;

	if( $session['user']['superuser'] & SU_DEVELOPER )
	{
		addnav('Developers');
		addnav('No Timeout Window','runmodule.php?module=devnotimeout',FALSE,TRUE,'600x400');
	}

	return $args;
}

function devnotimeout_run()
{
	popup_header('Developers No Timeout Window');

	output('`n`2This page will automatically refresh every %s minutes (%s seconds).`n`nSeconds: `^', round((getsetting('LOGINTIMEOUT',900)-20)/60, 2), (getsetting('LOGINTIMEOUT',900)-20));
	rawoutput('<script type="text/JavaScript">function timedRefresh(timeoutPeriod){setTimeout(\'location.reload(true);\',timeoutPeriod);}');
	rawoutput('var num=1;var t;function dis_num(){document.getElementById("dispnum").innerHTML=num;num=num+1;t=setTimeout("dis_num()",1000);}</script>');
	rawoutput('<body onload="JavaScript:timedRefresh('.(getsetting('LOGINTIMEOUT',900)-20).'000); dis_num();"><div id="dispnum"></div><br />');

	popup_footer();
}
?>