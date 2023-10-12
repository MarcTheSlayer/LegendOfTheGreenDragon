<?php
	if( $session['user']['alive'] && get_module_pref('medhunt','medcontest') )
	{
		$medallions = get_module_pref('medallion','medcontest');
		$medallion = '';
		if( get_module_pref('user_stat','medcontest') )
		{
			if( $medallions > 0 )
			{
				$medallion = $medallions;
			}
			else
			{
				$medallion = translate_inline('None');
			}
		}
		else
		{
			for( $i=0; $i<5; $i++ )
			{
				if( $medallions > $i )
				{
					$medallion .= '<img src="./images/' . get_module_setting('medimage') . '" width="16" height="16" title="" alt="' . color_sanitize($med) . ' image." />';				
				}
				else
				{
					$medallion .= '<img src="./images/med_clear.gif" width="16" height="16" title="" alt="Blank space holder image." />';
				}
			}
		}
		addcharstat('Inventory');
		addcharstat($meds, $medallion);
	}
?>