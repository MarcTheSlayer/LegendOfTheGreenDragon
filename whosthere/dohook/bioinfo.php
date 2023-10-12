<?php
	if( (get_module_setting('hide') == 1) && $session['user']['superuser'] & SU_EDIT_USERS )
	{
		$id = $args['acctid'];
		$op = httpget('hide');
		if( $op == 'Hide' )
		{
			set_module_pref('hidden',1,'whosthere',$id);
			output("`n`\$This player is now hidden from Who's Here list and shown as offline!`0`n");
		}
		elseif( $op == 'Unhide' )
		{
			clear_module_pref('hidden','whosthere',$id);
			output("`n`\$This player can now be seen again!`0`n");
		}

		$hidden = get_module_pref('hidden','whosthere',$id);
		addnav('Hide Player');
		if( $hidden )
		{
			if( $op != 'Unhide' ) addnav('Unhide Them',"bio.php?char=$id&ret=".rawurlencode(httpget('ret'))."&hide=Unhide");
		}
		else
		{
			addnav('Hide Them',"bio.php?char=$id&ret=".rawurlencode(httpget('ret'))."&hide=Hide");
		}
	}
?>