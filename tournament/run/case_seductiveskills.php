<?php
	switch( $sop )
	{
		case 'seduce':
				$tools = translate_inline(array('`^18th Century Codpiece','`!Ronald Reagan Mask','`$Red Lipstick','`qNerd Glasses','`&Handcuffs','`@Barry White Music','`$Stiletto Heels','`qLeather Toolbelt','`7Diver\'s Wetsuit','`7Furry Custume','`QFake Tan','`&Whipped Cream','`$Sexy Lingerie'));
				shuffle($tools);
				output("`n`3Seducing Sir Tristan wont be easy, you'll need to use every trick in the book, including your amazing `bseduction tool`b&#8482;!`n`n",TRUE);
				$skill = rand(1,10);
				switch( $skill )
				{
					case 1: case 2: case 3: case 4: case 5:
						output("`3You quickly get to work shaking your hair and biting your lip. You put the %s `3on, but end up tripping over your %s`3. ", $session['user']['armor'], $tools[0]);
						output("Sir Tristan laughs at your incompetence, `#Nice try, but you're not my type `3he says sarcastically.`n`nYou feel less charming and you spirits took a dive.");
						$session['user']['spirits']='-2';
						$session['user']['charm']-=5;
						addnews("`#%s `3put on the %s`3, but fell flat on their face whilst trying to seduce Sir Tristan.", $session['user']['name'], $tools[0]);
					break;

					case 6: case 7:
						output("You give a girly giggle and cheekily wink at Sir Tristan as you walk clumsily up him. He's not having any of it and turns away.`n`n");
						output("You get out the %s `3and he turns his head to look. Biting your lower lip you give a soft sigh as you bat your eyelashes which seem to do the trick. He charges you only gems. Success!`n`n", $tools[0]);
						output("This makes you feel more charming.");
						set_module_pref('entry', 1);
						$session['user']['gems']-=get_module_setting('efeegems');
						if( $session['user']['spirits'] < 1 ) $session['user']['spirits']=1;
						$session['user']['charm']+=2;
						addnews("`#%s `3seduced Sir Tristan using the %s `3and only had to pay gems!", $session['user']['name'], $tools[0]);
					break;

					case 8: case 9:
						output("Dancing slowing towards Sir Tristan with the %s `3in hand, you give a girly giggle and bite your lower lip. He seems smitten.`n`n", $tools[0]);
						output("You run your fingers through his hair and give him a cheeky wink. He goes weak at the knees and has to sit down, but you're able to get the fee costs down. Success!`n`n");
						output("You feel more charming.");
						set_module_pref('entry', 1);
						$session['user']['gold']-=get_module_setting('efeegold');
						if( $session['user']['spirits'] < 1 ) $session['user']['spirits']=1;
						$session['user']['charm']+=5;
						addnews("`#%s `3seduced Sir Tristan using the %s `3and only had to pay gold!", $session['user']['name'], $tools[0]);
					break;

					case 10:
						output("You swish your hair and bat your eyelashes as you seductively make your way towards Sir Tristan as you put on the %s`3. He can't take his eyes off of you.`n`n", $tools[0]);
						output("You bite your lip and give him a cheeky wink before giggling and whispering sweet nothings into his ear as your fingers run through his hair. Any thought of charging you a fee to enter is now gone. You're a total pro!`n`n");
						output("You feel high spirited and are more charming than before.");
						set_module_pref('entry', 1);
						$session['user']['spirits']=2;
						$session['user']['charm']+=10;
						addnews("`#%s put the %s `3on and seduced Sir Tristan to gain free entry into the Tournament!", $session['user']['name'], $tools[0]);
					break;
				}

				output("`n`n`3After all that you need some fresh air and exiting the office you head back to the village.`0`n");

				addnav('Leave');
				villagenav();
		break;

		case 'payfee':
			include('modules/tournament/run/case_enter.php');
			output("`n`#A good thing you didn't try seduction, you're not that attractive and Sir Tristan is so good looking he probably wouldn't be caught dead with you.`n`nYou feel less charming.`n`n");
			$session['user']['charm']-=5;
		break;

		case '':
			if( $session['user']['gems'] >= get_module_setting('efeegems') && $session['user']['gold'] >= get_module_setting('efeegold') )
			{
				output("`n`3The entrance fee seems a little expensive and you're an attractive person with skills in seduction, maybe you could seduce Sir Tristan into getting the fee down, or even in for free!!!`n`n");
				output("You consider your options.");

				addnav('Options');
				addnav('S?Seduce Sir Tristan',$from.'&op=seductiveskills&sop=seduce');
				addnav('J?Just Pay The Fee',$from.'&op=seductiveskills&sop=payfee');
			}
			else
			{
				output("`n`3For a split second you had thought about seducing Sir Tristan to get in for free. Now that would have been funny!`n");
			
				addnav('Enter Tournament');
				addnav('Y?`@Yes`0',$from.'&op=enter');
				addnav('N?`$No`0','village.php');
			}
		break;
	}
?>