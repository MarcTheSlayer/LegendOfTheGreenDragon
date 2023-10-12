<?php
	if( $session['user']['alive'] && get_module_pref('medhunt','medcontest') && get_module_pref('medfind','medcontest') > 0 )
	{
		global $SCRIPT_NAME;

		$scriptname_array = array('mail.php','motd.php','petition.php','superuser.php','user.php',get_module_pref('lastloc','medcontest'),get_module_pref('seclastloc','medcontest'));

		if( !in_array($SCRIPT_NAME, $scriptname_array) )
		{
			if( e_rand(1,100) > ((100 - get_module_setting('medallionmax','medcontest')) + get_module_pref('medfind','medcontest')) )
			{
				require_once('lib/output.php');
				$found = translate_inline('You Found a');
				$end = substr($med, -2);
				$med = ( $end == '`0' ) ? $med : $med . '`0'; 
				if( get_module_pref('medallion','medcontest') < 5 )
				{
					rawoutput('<center><b><span class="colDkRed"><big><big><big>'.$found.' '.appoencode($med).'<span class="colDkRed">!</span></big></big></big></span></b></center>');
					if( get_module_pref('medallion','medcontest') == 4 )
					{
						$end = substr($meds, -2);
						$meds = ( $end == '`0' ) ? $meds : $meds . '`0'; 
						$limit = translate_inline('You\'re now carrying 5');
						rawoutput('<center><b><span class="colDkRed"><big>'.$limit.' '.appoencode($meds).'<span class="colDkRed">!</span></big></span></b></center>');
					}
					increment_module_pref('medallion',1,'medcontest');
				}
				else
				{
					$limit = translate_inline('Too bad you\'re already carrying your limit!');
					rawoutput('<center><b><span class="colDkRed"><big><big><big><big>'.$found.' '.appoencode($med).'</big></big></big></big></span></b></center>');
					rawoutput('<center><b><span class="colDkRed"><big>'.$limit.'</big></span></b></center>');
				}
				increment_module_pref('medfind',-1,'medcontest');
				set_module_pref('seclastloc',get_module_pref('lastloc','medcontest'),'medcontest');
				set_module_pref('lastloc',$SCRIPT_NAME,'medcontest');
			}
		}
	}
?>