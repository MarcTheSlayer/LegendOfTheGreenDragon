<?php
/**
	Author - MarcTheSlayer
	v0.0.1 - 25/05/2009
	v0.0.2 - 11/08/2010
	File - sigsstats.php
	Name - Signature Server Stats Image
	Description - A script generated image containing the game server's stats.

	The Legend of the Green Dragon is a browser based role
	playing game that's based on Seth Able's Legend of the
	Red Dragon. You can download the latest official version
	of LotGD at DragonPrime.net

	Part of the 'sigsstats.php' module for LotGD.
	**This file goes in the /root/modules/sigsstats/ directory!!**

	The module file goes in the /root/modules/ directory and must
	be installed and the 'Refresh' option used at least once.

	The two files have the same name so don't get them mixed up.
	This file is separate from the module/game so as to not put
	more load on the server. As such, any alterations you may want
	will have to be done by hand.
*/
require_once('./../../dbconnect.php');
$link = @mysql_connect($DB_HOST, $DB_USER, $DB_PASS);

unset($DB_HOST);
unset($DB_USER);
unset($DB_PASS);

if( $link !== FALSE && mysql_select_db($DB_NAME) )
{
	// Get the settings that the module part built and saved.
	$result = mysql_query("SELECT value FROM ".$DB_PREFIX."module_settings WHERE modulename = 'sigsstats' AND setting = 'allprefs'");
	$row = mysql_fetch_assoc($result);
	$settings = @unserialize($row['value']);

	if( isset($settings['fontface']) && !empty($settings['fontface']) ) $settings['fontface'] = './../.'.$settings['fontface'];

	// First thing to do is check for hotlinking.
	if( isset($settings['siteallowed']) && !empty($settings['siteallowed']) )
	{
		$host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
		if( $_SERVER['HTTP_HOST'] != $host )
		{
			$sites = explode(',', $settings['siteallowed']);
			$sites = str_replace('http://', '', $sites);
			if( !in_array($host, $sites) )
			{
				if( isset($settings['hotlinking']) && !empty($settings['hotlinking']) )
				{	// No hotlinking image.
					$sign = @imagecreate(468, 60);
					imagefilledrectangle($sign, 0, 0, 468, 60, imagecolorallocate($sign, 255, 255, 255));
					imagettftext($sign, 9, 0, 5, 12, imagecolorallocate($sign, 0, 0, 0), $settings['fontface'], $settings['siteurl']);
					imagettftext($sign, 19, 0, 5, 50, imagecolorallocate($sign, 0, 0, 0), $settings['fontface'], (isset($settings['hotlinkmsg']) && !empty($settings['hotlinkmsg'])?$settings['hotlinkmsg']:'No Hotlinking Please'));
					header('Content-type: image/gif');
					imagegif($sign);
					imagedestroy($sign);
				}
				mysql_close($link);
				exit;
			}
		}
	}

	// $settings was not an array. Create a basic one.
	if( !is_array($settings) ) $settings = array('modulecount'=>0,'racecount'=>0,'specialtycount'=>0,'villagecount'=>0,'travelcount'=>0,'pvpcount'=>0,'turnscount'=>0,'gamedays'=>0,'sitename'=>'DragonPrime.net','siteurl'=>'http://dragonprime.net/','installer_version'=>'0.0.0','bannertype'=>0);

	if( isset($settings['outputsave']) && !empty($settings['outputsave']) )
	{	// Yes save image.
		if( isset($settings['outputpath']) && !empty($settings['outputpath']) )
		{	// There is a path.
			$settings['outputpath'] = './../.'.$settings['outputpath'];
			if( @is_dir($settings['outputpath']) )
			{	// It is a directory.
				if( isset($settings['outputname']) && !empty($settings['outputname']) )
				{	// There is a filename.
					$filename = $settings['outputpath'] . $settings['outputname'];
					if( @is_file($filename) )
					{	// The file exists.
						$refreshtime = ( !empty($settings['bannerrefresh2']) ) ? $settings['bannerrefresh2'] : $settings['bannerrefresh1'];
						$age = strtotime("+$refreshtime", filemtime($filename));
						if( $age >= time() )
						{	// The file is not old enough to be replaced.
							// Use previously saved image.
							$file = strtolower($settings['outputname']);
							$ext = ( ($pos = strrpos($file, '.')) !== FALSE ) ? substr($file, $pos+1) : '';
							switch( $ext )
							{
								case 'jpg':	case 'jpeg': header('Content-type: image/jpeg'); break;
								case 'png': header('Content-type: image/png'); break;
								case 'gif': header('Content-type: image/gif'); break;
								default: ''; break;
							}
							@readfile($filename);
							mysql_close($link);
							exit;
						}
						// else{ The file is too old so replace it.
					}
					// else{ The file doesn't exist, but $filename is no longer NULL so the file will be created.
				}
			}
		}
	}

	$result = mysql_query("SELECT setting, value FROM ".$DB_PREFIX."settings WHERE setting = 'gameoffsetseconds' OR setting = 'game_epoch' OR setting = 'daysperday' OR setting = 'installer_version'");
	if( mysql_num_rows($result) > 0 )
	{
		while( $row = mysql_fetch_assoc($result) )
		{
			$settings[$row['setting']] = $row['value'];
		}
	}
	else
	{
		// None of the game settings were returned so add defaults.
		array_merge($settings, array('gameoffsetseconds'=>0,'game_epoch'=>gmdate("Y-m-d 00:00:00 O",strtotime("-30 days")),'daysperday'=>4,'installer_version'=>'0.0.0'));
	}

	$intime = strtotime('now');
	$intime -= ( isset($settings['gameoffsetseconds']) ) ? $settings['gameoffsetseconds'] : 0;
	$epoch = strtotime($settings['game_epoch']);
	$now = strtotime(gmdate("Y-m-d H:i:s O",$intime));
	$logd_timestamp = ($now - $epoch) * $settings['daysperday'];
	$tomorrow = strtotime(gmdate("Y-m-d H:i:s O", $logd_timestamp)." + 1 day");
	$tomorrow = strtotime(gmdate("Y-m-d 00:00:00 O", $tomorrow));
	$secstotomorrow = $tomorrow - $logd_timestamp;
	$realsecstotomorrow = $secstotomorrow / $settings['daysperday'];
	$settings['nextnewday'] = date("H\\h i\\m s\\s",strtotime(date('Y-m-d 00:00:00') . "+ $realsecstotomorrow seconds"));

	$result = mysql_query("SELECT count(acctid) AS c FROM ".$DB_PREFIX."accounts WHERE locked = 0");
	$row = mysql_fetch_assoc($result);
	$settings['playercount'] = $row['c'];

	$result = mysql_query("SELECT count(acctid) AS c FROM ".$DB_PREFIX."accounts WHERE loggedin = 1");
	$row = mysql_fetch_assoc($result);
	$settings['playersonline'] = $row['c'];

	$result = mysql_query("SELECT sum(dragonkills) AS c FROM ".$DB_PREFIX."accounts WHERE locked = 0");
	$row = mysql_fetch_assoc($result);
	$settings['dragonskilled'] = $row['c'];

	// Check banner exists and extension is correct.
	$bgimage = TRUE;
	if( isset($settings['banneruse']) && !empty($settings['banneruse']) )
	{
		$settings['bannerpath'] = './../.'.$settings['bannerpath'];
		if( @is_file($settings['bannerpath']) )
		{
			$path = strtolower(basename($settings['bannerpath']));
			$ext = ( ($pos = strrpos($path, '.')) !== FALSE ) ? substr($path, $pos+1) : '';
			$sign = '';
			switch( $ext )
			{
				case 'jpg': case 'jpeg': $sign = @imagecreatefromjpeg($settings['bannerpath']); break;
				case 'png': $sign = @imagecreatefrompng($settings['bannerpath']); break;
				case 'gif': $sign = @imagecreatefromgif($settings['bannerpath']); break;
			}
			if( !empty($sign) && isset($settings['alpha']) && !empty($settings['alpha']) )
			{
				$alpha = imagecolorallocatealpha($sign, $settings['alphared'], $settings['alphagreen'], $settings['alphablue'], $settings['alphatrans']);
				imagefilledrectangle($sign, 1, 1, 467, 59, $alpha); // Apply transparent layer to image.
			}
			$bgimage = ( !empty($sign) ) ? FALSE : TRUE;
		}
	}

	if( $bgimage == TRUE )
	{	// No banner image so create a default.
		$sign = @imagecreatetruecolor(468, 60);
		imagefilledrectangle($sign, 0, 0, 468, 60, imagecolorallocate($sign, $settings['bannerred'], $settings['bannergreen'], $settings['bannerblue']));
	}

	$white = imagecolorallocate($sign, 255, 255, 255); // Text colour.
	$yellow = imagecolorallocate($sign, 255, 255, 0);
	$green = imagecolorallocate($sign, 0, 255, 0);
	$cyan = imagecolorallocate($sign, 0, 255, 255);
	$fontface = $settings['fontface'];
	// Top row.
	imagettftext($sign, 9, 0, 5, 12, $green, $fontface, $settings['sitename']);
	imagettftext($sign, 8, 0, 337, 12, $green, $fontface, $settings['installer_version']);
	// Left column.
	imagettftext($sign, 7, 0, 5, 23, $yellow, $fontface, 'Players:');
	imagettftext($sign, 7, 0, 5, 34, $yellow, $fontface, 'Game Days:');
	imagettftext($sign, 7, 0, 5, 56, $yellow, $fontface, 'Day Duration:');
	imagettftext($sign, 7, 0, 5, 45, $yellow, $fontface, 'Next Game Day:');

	imagettftext($sign, 7, 0, 90, 23, $white, $fontface, $settings['playercount']);
	imagettftext($sign, 7, 0, 90, 34, $white, $fontface, $settings['daysperday']);
	imagettftext($sign, 7, 0, 90, 56, $white, $fontface, (24/$settings['daysperday']).' Hours');
	imagettftext($sign, 7, 0, 90, 45, $white, $fontface, $settings['nextnewday']);

	imagettftext($sign, 7, 0, 105, 23, $cyan, $fontface, '(online: '.$settings['playersonline'].')');
	// Middle column.
	imagettftext($sign, 7, 0, 200, 23, $yellow, $fontface, 'PVPs Per Day:');
	imagettftext($sign, 7, 0, 200, 34, $yellow, $fontface, 'Turns Per Day:');
	imagettftext($sign, 7, 0, 200, 45, $yellow, $fontface, 'Travel Per Day:');
	imagettftext($sign, 7, 0, 200, 56, $yellow, $fontface, 'Dragons Killed:');

	imagettftext($sign, 7, 0, 280, 23, $white, $fontface, $settings['pvpcount']);
	imagettftext($sign, 7, 0, 280, 34, $white, $fontface, $settings['turnscount']);
	imagettftext($sign, 7, 0, 280, 45, $white, $fontface, $settings['travelcount']);
	imagettftext($sign, 7, 0, 280, 56, $white, $fontface, $settings['dragonskilled']);
	// Right column.
	imagettftext($sign, 7, 0, 350, 23, $yellow, $fontface, 'Modules Installed:');
	imagettftext($sign, 7, 0, 350, 34, $yellow, $fontface, 'Races Available:');
	imagettftext($sign, 7, 0, 350, 45, $yellow, $fontface, 'Villages Available:');
	imagettftext($sign, 7, 0, 350, 56, $yellow, $fontface, 'Specialties Available:');

	imagettftext($sign, 7, 0, 448, 23, $white, $fontface, $settings['modulecount']);
	imagettftext($sign, 7, 0, 448, 34, $white, $fontface, $settings['racecount']);
	imagettftext($sign, 7, 0, 448, 45, $white, $fontface, $settings['villagecount']);
	imagettftext($sign, 7, 0, 448, 56, $white, $fontface, $settings['specialtycount']);

	switch( $settings['bannertype'] )
	{
		// image###() can't save to file *and* output to the browser so needs to be called twice when you want to do both.
		case 1:
			header('Content-type: image/jpeg');
			if( !empty($filename) ) imagejpeg($sign, $filename, $settings['typejpeg']);
			imagejpeg($sign, NULL, $settings['typejpeg']);
		break;
		case 2:
			header('Content-type: image/png');
			if( !empty($filename) ) imagepng($sign, $filename, $settings['typepng']);
			imagepng($sign, NULL, $settings['typepng']);
		break;
		default:
			header('Content-type: image/gif');
			if( !empty($filename) ) imagegif($sign, $filename); // No quality control for gifs.
			imagegif($sign);
		break;
	}
	imagedestroy($sign);
	mysql_close($link);
}
else
{
	// Output a basic image that doesn't require a true type font.
	$sign = @imagecreate(468, 60);
	$bg = imagecolorallocate($sign, 255, 255, 255);
	$black = imagecolorallocate($sign,0,0,0);
	$string = "MySQL Error: It's gone and borked!";
	$len = strlen($string);
	for( $i=0; $i<$len; $i++ )
	{
	    $xpos = $i*imagefontwidth(5);
	    imagechar($sign,5,$xpos,40,$string,$black);
	    $string = substr($string,1);    
	}
	header('Content-type: image/gif');
	imagegif($sign);
	imagedestroy($sign);
}

exit;
/**
The Legend of Six - Server Stats				Version: 1.1.1 Dragonprime Edition
Players: 		48	(online: 4)	PVPs per day: 	4		Modules Installed: 		350
Game Days: 		4				Turns per day: 	10		Races Available: 		30
Day Duration:	8 Hours			Travel per day: 10		Villages: 				6
Next Day: 		04h 25m 03s		Dragons Killed: 400		Specialties Available: 	10

*/
?>