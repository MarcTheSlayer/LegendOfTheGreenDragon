<?php
function petition_antispam_getmoduleinfo()
{
	$info = array(
		"name"=>"Petition Antispam",
		"description"=>"Stop spam petitions from people not logged in.",
		"version"=>"0.0.1",
		"author"=>"`@MarcTheSlayer",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=1449",
		"settings"=>array(
			"Settings,title",
				"length"=>"Length of antispam code string:,int|20",
				"playerspam"=>"Players must also enter the antispam code?,bool|0",
				"spamfile"=>"Check petition text against spam words file:,text",
				"`2eg: './modules/spamwords.txt' or './spamdictionary.txt',note",
				"`^Search Google for files containing spam words/phrases or enter your own below.,note",
				"spamwords"=>"Enter words/phrases to block separated by a comma:,textarearesizeable,30",
		),
	);
	return $info;
}

function petition_antispam_install()
{
	output("`c`b`Q%s 'petition_antispam' Module.`b`n`c", translate_inline(is_module_active('petition_antispam')?'Updating':'Installing'));
	module_addhook('addpetition');
	module_addhook('petitionform');
	return TRUE;
}

function petition_antispam_uninstall()
{
	output("`n`c`b`Q'petition_antispam' Module Uninstalled`0`b`c");
	return TRUE;
}

function petition_antispam_dohook($hookname,$args)
{
	global $session;

	if( $session['user']['loggedin'] && get_module_setting('playerspam','petition_antispam') == 0 ) return $args;

	switch( $hookname )
	{
		case 'addpetition':
			// To try and stop spammers simply clicking the back button on their browser and submitting again and again.
			// Save the code again so it can be compared with the next petition's code.
			if( isset($session['user']['prefs']['module-petition_antispam']['second']) && $session['user']['prefs']['module-petition_antispam']['second'] == $session['user']['prefs']['module-petition_antispam']['first'] )
			{
				$args['cancelpetition'] = TRUE;
				$args['cancelreason'] = translate_inline('That code has been used before. This petition has been cancelled.');
				break;
			}
			$session['user']['prefs']['module-petition_antispam']['second'] = $session['user']['prefs']['module-petition_antispam']['first'];
			// Has the code been entered correctly?
			if( $session['user']['prefs']['module-petition_antispam']['first'] != $args['codelength'] )
			{
				$args['cancelpetition'] = TRUE;
				$args['cancelreason'] = translate_inline('You failed to enter the code string correctly. This petition has been cancelled.');
				break;
			}
			$spam = FALSE;
			// Does the petition contain any admin entered spam words?
			$words = get_module_setting('spamwords','petition_antispam');
			if( $words != '' )
			{
				$words = explode(",",$words);
				$spam = petition_antispam_searchstring($words, $args);
				if( $spam )
				{
					$args['cancelpetition'] = TRUE;
					$args['cancelreason'] = sprintf_translate('`#A word/phrase in this petition was found to be spam related`n`@`b%s`b`n`#This petition has been cancelled.', $spam);
					break;
				}
			}
			// Does the petition contain any spam words that are inside the file?
			$spamfile = get_module_setting('spamfile','petition_antispam');
			if( file_exists($spamfile) )
			{
				$words = file($spamfile);
				$words = join("",$words);
				$words = explode("\n",$words);
				$spam = petition_antispam_searchstring($words, $args);
				if( $spam )
				{
					$args['cancelpetition'] = TRUE;
					$args['cancelreason'] = sprintf_translate('`#A word/phrase in this petition was found to be spam related`n`@`b%s`b`n`#This petition has been cancelled.', $spam);
					break;
				}
			}
		break;

		case 'petitionform':
			unset($session['user']['prefs']['module-petition_antispam']);
			$length = get_module_setting('length','petition_antispam');
			$string = petition_antispam_randomstring($length);
			output('`n`7Please verify that you\'re not a bot by copying and pasting the following code string into the box below.`n`b`&%s`0`b`n', $string);
			rawoutput('<input type="text" name="codelength" value="" size="'.$length.'" maxlength="'.$length.'" /><br />');
			$session['user']['prefs']['module-petition_antispam']['first'] = $string;
		break;
	}

	return $args;
}

function petition_antispam_searchstring($words, $args)
{
	$count = count($words);
	for( $i=0; $i<$count; $i++ )
	{
		if( ($found = strstr($args['description'], $words[$i])) !== FALSE )
		{
			return $words[$i];
		}
	}
	return FALSE;
}

function petition_antispam_randomstring($length = 20)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $strlen = strlen($characters);
    $randomstring = '';
    for( $i=0; $i<$length; $i++ ) $randomstring .= $characters[rand(0, $strlen - 1)];
    return $randomstring;
}
?>