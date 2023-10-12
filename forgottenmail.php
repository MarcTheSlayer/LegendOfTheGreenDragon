<?php

//1.1 and 1.2 Changes in the URL-Link in the recieved mail
//1.3 Code Cleanup, added download-link

/**
	Modified by MarcTheSlayer

	21/02/09 - v1.3.1
	+ Cleaned up the code.
	+ Added some settings.
*/

function forgottenmail_getmoduleinfo()
{
	$info = array(
		"name"=>"Forgotten Password Recovery by Mail Input",
		"version"=>"1.3.1",
		"author"=>"SexyCook, modified by `@MarcTheSlayer",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?topic=4808",
		"allowanonymous"=>TRUE,
		"settings"=>array(
			"Forgotten Password Settings,title",
			"sitename"=>"Enter your LotGD sitename.,string,30|LotGD",
			"siteemail"=>"Enter the site's email address.,string,30|",
			"`^Note: Leave empty if you wish to use the default email in game settings.,note"
		)
	);
	return $info;
}
	
function forgottenmail_install()
{
	output("`c`b`Q%s 'forgottenmail' Module.`b`n`c", translate_inline(is_module_active('forgottenmail')?'Updating':'Installing'));
  	module_addhook('index');
	return TRUE;
}
	
function forgottenmail_uninstall()
{
	output("`n`c`b`Q'forgottenmail' Module Uninstalled`0`b`c");
	return TRUE;
}

function forgottenmail_dohook($hookname,$args)
{
	blocknav('create.php?op=forgot');
	addnav('Forgotten Password');
	addnav('Enter Name','create.php?op=forgot&dummy=1');				  
	addnav('Enter Mail','runmodule.php?module=forgottenmail');

	return $args;
}  	
  	
function forgottenmail_run()
{
	page_header('Forgotten Password');

 	addnav('Login','index.php');

	$charname = httppost('charname');
	if( !empty($charname) )
	{
		$sql = "SELECT acctid, login, emailaddress, emailvalidation, password
				FROM " . db_prefix('accounts') . "
				WHERE emailaddress = '$charname'";
		$result = db_query($sql);
		if( $row = db_fetch_assoc($result) )
		{
			if( trim($row['emailaddress']) != '' )
			{
				if( empty($row['emailvalidation']) )
				{
					$row['emailvalidation'] = substr("x".md5(date("Y-m-d H:i:s").$row['password']),0,32);
					$sql = "UPDATE " . db_prefix('accounts') . "
							SET emailvalidation = '{$row['emailvalidation']}'
							WHERE emailaddress = '{$row['emailaddress']}'";
					db_query($sql);
				}

				$repurl = str_replace('runmodule','create',$_SERVER['SCRIPT_NAME']);  	

				$subj = translate_mail(array('%s Account Verification', get_module_setting('sitename')), $row['acctid']);
				$msg = translate_mail(array("Someone from %s requested a forgotten password link for your account. If this was you, then here is your"
						." link, you may click it to log into your account and change your password from your preferences page in the village square.\n\n"
						."If you didn't request this email, then don't sweat it, you're the one who is receiving this email, not them."
						."\n\n  http://%s?op=val&id=%s\n\nThanks for playing!",
						$_SERVER['REMOTE_ADDR'], ($_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] == 80?'':':' . $_SERVER['SERVER_PORT']) . $repurl), $row['emailvalidation']), $row['acctid']);

				$siteemail = get_module_setting('siteemail');
				$from = ( !empty($siteemail) ) ? $siteemail : getsetting('gameadminemail','postmaster@localhost.com');
					
				mail($row['emailaddress'],$subj,$msg,"From: $from");

				output("`n`#We sent a new validation email to the address on file for that account.`n");
				output("You may use the validation email to log in and change your password.");
			}
			else
			{
				output("`n`#We're sorry, but that account does not have an email address associated with it, and so we cannot help you with your forgotten password.`n`n");
				output("Use the `@Petition for Help `#link to request help with resolving your problem.");
		 	}
		}
		else
		{
			output("`n`#Could not locate a character with that email.`n`n");
			output("Look at the List Warriors page off the login page to make sure that the character hasn't expired and been deleted.");
		}
	}
	else
	{
		rawoutput('<form action="runmodule.php?module=forgottenmail" method="POST">');
		output('`n`#Enter the email address that you used for the account that you\'re trying to recover the password for.`n`nEnter your email: ');
		$send = translate_inline('Email me my Password');
		rawoutput('<input type="text" name="charname" value="" /><br /><input type="submit" value="' . $send . '" class="button" /></form>');
	}

	page_footer();
}	
?>