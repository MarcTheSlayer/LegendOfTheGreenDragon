<?php
/**
	Modified by MarcTheSlayer

	07/03/09 - v1.0.2
	+ Didn't like how you could only see the superusers for one flag at a time.
	  Now you can see at a glance who all your superusers are and by clicking
	  their names, what superuser flags they have.

	14/07/09 - v1.0.3
	+ Added 2nd page that shows player's names under each flag that they have.

	08/11/09 - v1.0.4
	+ Added superuser count on nav link.
*/
function check_flags_getmoduleinfo()
{
	$info = array(
		"name"=>"Check Superuser Flags",
		"description"=>"Show all your superusers and what flags they have.",
		"version"=>"1.0.4",
		"author"=>"Chris Vorndran`2, modified by `@MarcTheSlayer",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?topic=9928.0",
	);
	return $info;
}

function check_flags_install()
{
	module_addhook('superuser');
	return TRUE;
}

function check_flags_uninstall()
{
	return TRUE;
}

function check_flags_dohook($hookname,$args)
{
	global $session;

	if( $session['user']['superuser'] & SU_MEGAUSER )
	{
		$sql = "SELECT acctid
				FROM " . db_prefix('accounts') . "
				WHERE superuser > 0";
		$result = db_query($sql);
		$count = db_num_rows($result);
		addnav('Actions');
		addnav(array('Check SU Flags (%s)',($count>0?$count:0)),'runmodule.php?module=check_flags');
	}

	return $args;
}

function check_flags_run()
{
	global $session;

	page_header('Check Superuser Flags');

	$op = httpget('op');

	$rows = array();
	$form = array();

	$sql = "SELECT acctid, name, superuser
			FROM " . db_prefix('accounts') . "
			WHERE superuser != 0
			ORDER BY acctid ASC";
	$result = db_query($sql);

	if( $op == 'flags' )
	{
		$megauser = $config = $users = $mounts = $creatures = $equipment = $riddles = $modules = $gamemaster = $petitions = $comments = $clans = $moderation = array();
		$warning = $motd = $donations = $paylog = $days = $developer = $translator = $debug = $phpnotice = $rawsql = $source = $grotto = $expire = array();
		while( $row = db_fetch_assoc($result) )
		{
			if( $row['superuser'] & SU_MEGAUSER ) 				$megauser[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_CONFIG ) 			$config[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_USERS ) 			$users[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_MOUNTS ) 			$mounts[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_CREATURES ) 		$creatures[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_EQUIPMENT ) 		$equipment[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_RIDDLES ) 			$riddles[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_MANAGE_MODULES ) 		$modules[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_IS_GAMEMASTER ) 			$gamemaster[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_PETITIONS ) 		$petitions[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_COMMENTS ) 			$comments[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_MODERATE_CLANS ) 		$clans[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_AUDIT_MODERATION ) 		$moderation[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_OVERRIDE_YOM_WARNING ) 	$warning[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_POST_MOTD ) 				$motd[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_DONATIONS ) 		$donations[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_EDIT_PAYLOG ) 			$paylog[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_INFINITE_DAYS ) 			$days[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_DEVELOPER ) 				$developer[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_IS_TRANSLATOR ) 			$translator[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_DEBUG_OUTPUT ) 			$debug[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_SHOW_PHPNOTICE ) 		$phpnotice[] 	= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_RAW_SQL ) 				$rawsql[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_VIEW_SOURCE ) 			$source[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_GIVE_GROTTO ) 			$grotto[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';
			if( $row['superuser'] & SU_NEVER_EXPIRE ) 			$expire[] 		= '<a href="user.php?op=edit&userid=' . $row['acctid'] . '">'.$row['name'].'</a>,note';

			addnav('','user.php?op=edit&userid=' . $row['acctid']);
		}

		$form[] = 'Megauser,title';
		$form = array_merge($form, $megauser);
		$form[] = 'Edit Config,title';
		$form = array_merge($form, $config);
		$form[] = 'Edit Users,title';
		$form = array_merge($form, $users);
		$form[] = 'Edit Mounts,title';
		$form = array_merge($form, $mounts);
		$form[] = 'Edit Creatures,title';
		$form = array_merge($form, $creatures);
		$form[] = 'Edit Equipment,title';
		$form = array_merge($form, $equipment);
		$form[] = 'Edit Riddles,title';
		$form = array_merge($form, $riddles);
		$form[] = 'Manage Modules,title';
		$form = array_merge($form, $modules);
		$form[] = 'Is Gamemaster,title';
		$form = array_merge($form, $gamemaster);
		$form[] = 'Edit Petitions,title';
		$form = array_merge($form, $petitions);
		$form[] = 'Edit Comments,title';
		$form = array_merge($form, $comments);
		$form[] = 'Moderate Clans,title';
		$form = array_merge($form, $clans);
		$form[] = 'Audit Moderation,title';
		$form = array_merge($form, $moderation);
		$form[] = 'Override YoM Warning,title';
		$form = array_merge($form, $warning);
		$form[] = 'Post MoTD,title';
		$form = array_merge($form, $motd);
		$form[] = 'Edit Donations,title';
		$form = array_merge($form, $donations);
		$form[] = 'Edit Paylog,title';
		$form = array_merge($form, $paylog);
		$form[] = 'Infinite Days,title';
		$form = array_merge($form, $days);
		$form[] = 'Developer,title';
		$form = array_merge($form, $developer);
		$form[] = 'Is Translator,title';
		$form = array_merge($form, $translator);
		$form[] = 'Debug Output,title';
		$form = array_merge($form, $debug);
		$form[] = 'Show PHP Notice,title';
		$form = array_merge($form, $phpnotice);
		$form[] = 'Raw SQL,title';
		$form = array_merge($form, $rawsql);
		$form[] = 'View Source,title';
		$form = array_merge($form, $source);
		$form[] = 'Give Grotto,title';
		$form = array_merge($form, $grotto);
		$form[] = 'Never Expire,title';
		$form = array_merge($form, $expire);

		addnav('Superusers');
		addnav('Show by Players','runmodule.php?module=check_flags');
	}
	else
	{
		while( $row = db_fetch_assoc($result) )
		{
			$rows['superuser'.$row['acctid']] = $row['superuser'];

			$form[] = appoencode($row['name']) . ',title';
			$form[] = '`&This is just an overall visual.,note';
			$form[] = '`&To change the flags of this person <a href="user.php?op=edit&userid=' . $row['acctid'] . '">click here</a>,note';
			addnav('','user.php?op=edit&userid=' . $row['acctid']);

			$form['superuser'.$row['acctid']] = 'Superuser Permissions,bitfield,'.
				($session['user']['superuser'] | SU_ANYONE_CAN_SET | ($session['user']['superuser'] & SU_MEGAUSER ? 0xFFFFFFFF : 0)).','.
				SU_MEGAUSER.        ',MEGA USER'.
				'<br/><br/><b>Editors</b>,'.
				SU_EDIT_CONFIG.     ',Edit Game Configurations,'.
				SU_EDIT_USERS.      ',Edit Users,'.
				SU_EDIT_MOUNTS.     ',Edit Mounts,'.
				SU_EDIT_CREATURES.  ',Edit Creatures & Taunts,'.
				SU_EDIT_EQUIPMENT.  ',Edit Armor & Weapons,'.
				SU_EDIT_RIDDLES.    ',Edit Riddles,'.
				SU_MANAGE_MODULES.  ',Manage Modules'.
				'<br/><br/><b>Customer Service</b>,'.
				SU_IS_GAMEMASTER.   ',Can post comments as gamemaster,'.
				SU_EDIT_PETITIONS.  ',Handle Petitions,'.
				SU_EDIT_COMMENTS.   ',Moderate Comments,'.
				SU_MODERATE_CLANS.  ',Moderate Clan Commentary,'.
				SU_AUDIT_MODERATION.',Audit Moderated Comments,'.
		        SU_OVERRIDE_YOM_WARNING.',Do NOT display YOM warning for this person,'.
				SU_POST_MOTD.       ',Post MoTD\'s'.
				'<br/><br/><b>Donations</b>,'.
				SU_EDIT_DONATIONS.  ',Manage Donations,'.
				SU_EDIT_PAYLOG.     ',Manage Payment Log'.
				'<br/><br/><b>Game Development</b>,'.
				SU_INFINITE_DAYS.   ',Infinite Days,'.
				SU_DEVELOPER.       ',Game Developer (super powers),'.
				SU_IS_TRANSLATOR.   ',Enable Translation Tool,'.
				SU_DEBUG_OUTPUT.    ',Debug Output,'.
				SU_SHOW_PHPNOTICE.  ',See PHP Notices in debug output,'.
				SU_RAW_SQL.         ',Execute Raw SQL,'.
				SU_VIEW_SOURCE.     ',View source code,'.
				SU_GIVE_GROTTO.     ',Grotto access (only if not granted implicitly in another permission),'.
				SU_NEVER_EXPIRE.    ',Account never expires';
		}

		addnav('Superusers');
		addnav('Show by Flags','runmodule.php?module=check_flags&op=flags');
	}

	require_once('lib/showform.php');
	showform($form,$rows,TRUE);

	require_once('lib/superusernav.php');
	superusernav();

	page_footer();
}
?>			