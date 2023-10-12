<?php
/**
	Modified by MarcTheSlayer

	19/05/09 - v0.0.1
	+ You now take not only the weapon name, but also the value and damage.
	+ Taken from person sleeping in the fields who then get left your weapon.
	+ YoM gets sent to offline person informing them what happened.
	+ Changed name from "Random Doppleganger" (randop.php).
*/
function randomencounter_getmoduleinfo()
{
	$info = array(
		"name"=>"Random Encounter",
		"description"=>"Random encounter with somebody sleeping in the fields. You swap weapons in a scuffle.",
		"version"=>"0.0.1",
		"author"=>"Chris Vorndran`2, modified by `@MarcTheSlayer",
		"category"=>"Forest Specials",
		"download"=>"http://dragonprime.net/index.php?topic=10121.0",
	);

	return $info;
}

function randomencounter_install()
{
	output("`c`b`Q%s 'randomencounter' Module.`0`b`c`n", translate_inline(is_module_active('randomencounter')?'Updating':'Installing'));
	module_addeventhook('forest','return 100;');
	return TRUE;
}

function randomencounter_uninstall()
{
	output("`c`b`QUn-Installing 'randomencounter' Module.`0`b`c`n");
	return TRUE;
}

function randomencounter_runevent($type)
{
	global $session;

	$op = httpget('op');
	$from = 'forest.php';

	if( $op == '' || $op == 'search' )
	{
		$session['user']['specialinc'] = 'module:randomencounter';

		output("`@Walking down a narrow path of the forest, you notice a rustling noise in the bushes. ");
		output("You have heard tales of a Demon that has bene haunting the woods lately. ");
		output("It has also been sucking out the souls of those that are evil.`n`n");
		output("Will you be the Hero?");

		addnav('Be a Hero', $from.'?op=hero');
		addnav('Wuss Out', $from.'?op=leave');
	}
	elseif( $op == 'hero' )
	{
		$sql = "SELECT acctid, name, sex, attack, weapon, weaponvalue, weapondmg
				FROM " . db_prefix('accounts') . "
				WHERE loggedin = 0 
					AND location = '" . $session['user']['location'] . "'
				ORDER BY RAND() LIMIT 1";
		$result = db_query($sql);
		if( $row = db_fetch_assoc($result) )
		{
			$name = $row['name'];
			$sex = $row['sex'];
			$weapon = $row['weapon'];
			$weapondmg = $row['weapondmg'];
			$weaponvalue = $row['weaponvalue'];
			$attack = ($row['attack'] - $row['weapondmg']) + $session['user']['weapondmg'];

			db_query("UPDATE " . db_prefix('accounts') . " SET attack = '$attack', weapon = '" . $session['user']['weapon'] . "', weaponvalue = '" . $session['user']['weaponvalue'] . "', weapondmg = '" . $session['user']['weapondmg'] . "' WHERE acctid = '" . $row['acctid'] . "'");

			require_once('lib/systemmail.php');
			$subject = translate_mail("`&Witness Report!`0");
			$message = translate_mail(array('`&You were disturbed by %s `&while you were trying to sleep in the fields outside %s.`n`nIn the scuffle you dropped your %s `&and %s `&made off with it cackling madly.`n`n', $session['user']['name'], $session['user']['location'], $weapon, $session['user']['name']));
			systemmail($row['acctid'], $subject, $message);
		}
		else
		{
			$name = '`&S`7imple `&S`7imon`0';
			$sex = 0;
			$weapon = '`QR`qusty `QW`qood-`QC`qhopping `QA`qxe`0';
			$weapondmg = 12;
			$weaponvalue = 6840;
		}

		$male = translate_inline(array('He','his'));
		$female = translate_inline(array('She','her'));

		output("`@You puff out your chest and hustle over to the bush. ");
		output("You poke your `2%s`@ in there, and then hear a loud yelp. ", $session['user']['weapon']);
		output("Out from the bushes, springs `3%s`@.`n`n", $name);
		output("%s rubs %s sore bottom and then charges at you. ", ($sex==1?$female[0]:$male[0]), ($sex==1?$female[1]:$male[1]));
		output("`3%s `@hits you head on and drops %s `2%s`@.`n`n", $name, ($sex==1?$female[1]:$male[1]), $weapon);
		output("It looks a better weapon, but you're not sure, you quickly pick the `2%s `@up and run off into the woods, cackling madly.", $weapon);

		$session['user']['weapon'] = $weapon;
		$session['user']['attack'] -= $session['user']['weapondmg'];
		$session['user']['weapondmg'] = $weapondmg;
		$session['user']['attack'] += $session['user']['weapondmg'];
		$session['user']['weaponvalue'] = $weaponvalue;

		addnews("%s `3encountered %s `3in the forest and made off with %s `#%s`3.",$session['user']['name'], $name, ($sex==1?$female[1]:$male[1]), $weapon);
		$session['user']['specialinc'] = '';
	}
	elseif( $op == 'leave' )
	{
		output("`@You wander away, thinking better than to mess with the Demon of the Woods.`n`n");
		output("Strangely, you feel wiser.`n");
		output("You gain One Experience Point!");

		$session['user']['experience']++;
		$session['user']['specialinc'] = '';
	}
}
?>