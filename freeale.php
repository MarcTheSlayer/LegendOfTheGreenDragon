<?php
/**
	Bug Fix - MarcTheSlayer
	02/06/2011 - v20110602
	+ Added 'drinks-check' hook and code and removed blocknav.
	+ On uninstall, free ale is deactivated and not deleted.
*/
function freeale_getmoduleinfo(){
	$info = array(
		"name"=>"Free Ale",
		"version"=>"20110602",
		"author"=>"`%Anpera`& - Converted by `#Sixf00t4<br/>`&Modified by `@CortalUX`&.",
		"category"=>"Inn",
		"download"=>"http://dragonprime.net/index.php?module=Downloads;sa=dlview;id=387",
		"vertxtloc"=>"http://www.legendofsix.com/",
		"settings"=>array(
			"Free Ale - Settings,title",
			"maxales"=>"Maximum amount of free Ale?,range,1,50,1|1",
			"paidales"=>"Free Ales currently available?,int|0",
			"freeAleID"=>"ID of Free Ale drink?,hidden|0",// So that it'll work when we uninstall it.
			"alecharm"=>"Charm received for buying a round?,int|2",
			"minalecharm"=>"Minimum numbers of Ales a user needs to buy to gain charm?,int|5",
			"alebuyer"=>"Who last bought a round?,int|0",
		),
		"prefs"=>array(
			"Free Ale - Preferences,title",
			"gotfreeale"=>"Has this user had free Ale today?,enum,0,Nope,1,Paid for a Round,2,Drunk free Ale,3,Paid for a Round and drunk free Ale",
		),
		"requires"=>array(
			"drinks"=>"1.1|John J. Collins<br>Heavily modified by JT Traub, Part of the Core",
		),
	);
	return $info;
}

function freeale_install(){
	if (!is_module_active('freeale')){
		output("`n`c`b`QFree Ale Module - Installed`0`b`c");
		$sql="INSERT INTO " . db_prefix("drinks") . " VALUES (0, 'Free Ale', 0, 0, 2, 1, 0, 0, 33, 0, 0, 0, 5, 1, 1, 'You raise you glass to the warrior who bought the round of drinks and join everyone else in irish bar songs.', '`#Buzz', 8, 'You\'ve got a nice buzz going.', 'Your buzz fades.', '1.2', '0', '0', '0', '', '', '')";
		$result = db_query($sql);
	}else{
		output("`n`c`b`QFree Ale Module - Updated`0`b`c");
	}
	if (get_module_setting('freeAleID')==0) {
		$sql="SELECT drinkid FROM ".db_prefix("drinks")." WHERE name = 'Free Ale'";
		$result = db_query($sql);
		if (db_num_rows($result)>0) {
			$row = db_fetch_assoc($result);
			set_module_setting('freeAleID',$row['drinkid']);
		}
		output("`c`b`QFree Ale Module - Drink ID Stored`0`b`c");
	}
	module_addhook("ale");
	module_addhook("drinks-check");
	module_addhook("newday");
	module_addhook("header-runmodule");
	return true;
}

function freeale_uninstall(){
	db_query("UPDATE " . db_prefix('drinks') . " SET active = 0 WHERE drinkid = '" . get_module_setting('freeAleID') . "'");
	output("`n`c`b`QFree Ale Module - Uninstalled`0`b`c");
	return true;
}

function freeale_dohook($hookname,$args){
	switch($hookname){
		case "ale":
			global $session,$SCRIPT_NAME;
	//		blocknav("runmodule.php?module=drinks&act=buy&id=".get_module_setting('freeAleID'),true);
			if (strstr($SCRIPT_NAME, "inn")) {
				if ((get_module_setting("paidales"))<1 || (get_module_pref("gotfreeale"))>=2) {
					$alecost = $session['user']['level']*10;	
				} else {
					$alecost = 0;
				}
				if ((get_module_setting("paidales"))<1) {
					addnav("`#Pay for a Round","runmodule.php?module=freeale&op=form");
					$max = get_module_setting("maxales");
					output("`nThere are %s empty pint mugs sat in front of Cedrik.",$max);
				} else {
					$amt=get_module_setting("paidales");
					addnav("`#Free Ale","runmodule.php?module=freeale&op=freeale");
					$max = get_module_setting("maxales");
					if ($amt>1) {
						output("`n`@There are `^%s`@ freshly filled Ales pint mug standing in front of Cedrik...",$amt);
					} else {
						output("`n`@There is a freshly filled Ale pint mug standing in front of Cedrik...");
					}
					if ($amt<$max) {
						$max-=$amt;
						output("and `^%s`@ empty pint mugs...",$max);
					}
					if ((get_module_pref("gotfreeale"))>=2){
						blocknav("runmodule.php?module=freeale&op=freeale");
						output("but you've had your free Ale for today, so you pay for your drink.");
					}
					$sql = "SELECT name FROM ".db_prefix("accounts")." WHERE acctid='".get_module_setting('alebuyer')."' AND locked=0";
					$res = db_query_cached($sql,"freeale");
					if (db_num_rows($res)>0) {
						$row=db_fetch_assoc($res);
						$n=$row['name'];
					} else {
						$n = "Seth";
					}
					output("`nThe last round was paid for by `^%s`@.",$n);
					output_notl("`n`n");
				}
			}
		break;
		case 'drinks-check':
			if( $args['drinkid'] == get_module_setting('freeAleID') ) $args['allowdrink'] = FALSE;
		break;
		case "newday":
			set_module_pref("gotfreeale",0,"freeale");
		break;
		case "header-runmodule":
			if (httpget('module')=='drinks') {
				blocknav('runmodule.php?module=drinks&act=editor&op=del&drinkid='.get_module_setting("freeAleID"),true);
				if (httpget('drinkid')==get_module_setting("freeAleID")) {
					output("`c`b`\$DO NOT DELETE THIS DRINK!!`nIT WILL BREAK THE 'FREE AlE' MODULE!`nUNINSTALL THE MODULE TO DELETE IT!`nFEEL FREE TO CHANGE THE SETTINGS OF THE DRINK!`b`c`n");
				}
			}
		break;
	}
	return $args;
}

function freeale_run(){
	global $session;
	$op = httpget('op');
	page_header("Free Ale");
	$alecost = $session['user']['level']*10;
	switch ($op) {
		case "form":
			$maxales = get_module_setting("maxales");
			$amt=$maxales-get_module_setting("paidales");
			output("`@You're in a good mood and think about paying for a round of ale for everyone who's rotting in here.`n`nOne Ale will cost you `^%s gold`@.`n`n",$alecost);
			output("Cedrik asks how many Ales you want to pay for, while pointing to %s empty cups... `3\"`&I only want those cups filled, I don't want any Barflies!`3\"`@ Cedrik states.`n`n",$amt);
			rawoutput("<form action='runmodule.php?module=freeale&op=form-confirm' method='POST'><input name='amount' id='amount' size='4'>");
			rawoutput("<input type='submit' class='button' value='".translate_inline("Pay")."'></form>");
			rawoutput("<script language='javascript'>document.getElementById('amount').focus();</script>");
			addnav("","runmodule.php?module=freeale&op=form-confirm");
		break;
		case "freeale":
			set_module_setting("paidales",get_module_setting("paidales","freeale")-1,"freeale");
			set_module_pref("gotfreeale",get_module_pref("gotfreeale","freeale")+2,"freeale");
			unblocknav('runmodule.php?module=drinks&act=buy&id',true);
			redirect("runmodule.php?module=drinks&act=buy&id=".get_module_setting("freeAleID"));
		break;
		case "form-confirm":
			$amt = abs((int)httppost('amount'));
			$cost=$amt*$alecost;
			$playername=$session['user']['name'];
			output_notl("`@");
			if (!is_numeric($amt)||$amt<=0) {
				$amt=1;
				output("Thinking that %s is a silly number, you change your mind and decide upon one.`n",$amt);
			}
			if ($session['user']['gold']<$cost) {
				output("Right in time, before you commit yourself, you realize that you don't have enough gold with you.");
			} elseif (get_module_setting("paidales")>get_module_setting("maxales")){
				output("Damn! Someone was faster than you. Disappointed, you move towards the free Ale.");
			} elseif (get_module_pref('gotfreeale')==1||get_module_pref('gotfreeale')==3){
				output("Cedrik starts to clean the bar with a pretty dirty looking cloth, as he says, \"`%You've paid for a round today. I don't want any Barflies in my Inn. Okay?`@\"");
			} elseif ($amt+get_module_setting('paidales')>get_module_setting("maxales")) {
				 output("\"`%Don't boast with your money in here! Could be deadly...`@\"");
			} else {
				if ($amt==1) {
					$str = translate_inline("Ale");
					$word = translate_inline("was");
				} else {
					$str = translate_inline("%s Ales");
					$str = str_replace('%s',$amt,$str);
					$word = translate_inline("were");
				}
				output("You talk to Cedrik and push `^%s`0 gold towards him. He nods and screams out into the room, \"`%The next %s %s paid for by %s`%!!`@\".",$cost,$str,$word,$playername);
				output("A murmur goes through the crowd, and you are the %s of the hour.`n`n",($session['user']['sex']?translate_inline("Heroine"):translate_inline("Hero")));
				if ($amt>get_module_setting('minalecharm')&&get_module_setting('minalecharm')>0){
					output("`^You gain %s Charm!`0",get_module_setting('alecharm'));
					$session['user']['charm']+=get_module_setting('alecharm','freeale');
				}
				set_module_setting("paidales",get_module_setting('paidales')+$amt);
				$session['user']['gold']-=$cost;
				set_module_pref("gotfreeale",get_module_pref("gotfreeale","freeale")+1,"freeale");		 
				if ($amt==1) $str=translate_inline("an Ale");
				$sql = "INSERT INTO ".db_prefix("commentary")." (postdate,section,author,comment) VALUES (now(),'inn',".$session['user']['acctid'].",\": ".translate_inline("paid for")." `^$str`&!\")";
				db_query($sql) or die(db_error(LINK));
				addnews("$playername bought a round of drinks!");
				output_notl("`0");
				set_module_setting('alebuyer',$session['user']['acctid']);
				invalidatedatacache("freeale");
			}
		break;
	}
	addnav("Where to?");
	addnav("I?Return to the Inn","inn.php");
	addnav("B?Return to the Bar","inn.php?op=bartender");
	villagenav();
	page_footer();
}
?>