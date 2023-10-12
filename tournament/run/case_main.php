<?php
	$points_total = get_module_pref('points');
	$level = $session['user']['level'];

	rawoutput('<span style="font-size:larger">');
	output("`b`c`#THE TOURNAMENT`b`c`n`n");
	rawoutput('</span>');

	addnav('Tournament');
	addnav('S?`@View Scores`0',$from.'&op=scores');

	if( $level > 15 )
	{
		output("`3Sir Tristan informs you that you are too powerful for the tournament. There is no opponent that would be a match for you. You can participate in the tournament up to level 15.");
		output("`n`n`^Your current tournament score is `^%s Points.", number_format($points_total));
	}
	else
	{
		$allprefs = @unserialize(get_module_pref('allprefs'));
		if( !is_array($allprefs) ) $allprefs = array();

		if( isset($allprefs[$level]) && $allprefs[$level] > 0 )
		{
			output("`3Sir Tristan informs you that your challenge for this level has been completed.`n`n`^`bCome back when you have more experience!!`b`");
		}
		else
		{
			$points = e_rand(2,100); // Random points scored this level.
			$remainder = 100-$points;
			$points_total += $points;
			set_module_pref('points', $points_total); // Save the points now.
			$points1 = intval($points / 10); // This is for the switch() cases.
			if( $points1 == 0 ) $points1 = 1;

			$allprefs[$level] = $points; // Save points awarded this level to array.
			ksort($allprefs);
			set_module_pref('allprefs', serialize($allprefs));

			output("`3Sir Tristan's faithful assistant, Belanthros prepares you for the `^Level %s Challenge.`n`n", $level);

			switch( $level )
			{
				case 1:
					output("`@Do not underestimate this level, for each is as challenging as the next!! `nBut enough idle chatter, let's get this show on the road!`n`n ");
					output("This challenge is called `%`bDwarf Tossing`b`@. The further ye throw them, the more points ye earn!`n ");
					output("Have at 'em!!!`n`n");

					for( $i=0; $i<$points; $i++ )
					{
						output_notl("`% +");
					}

					switch( $points1 )
					{
						case 1:
							output("`n`n`@That must be the worst throw I have seen.`n`n");
							output("`6`bYou have earned a lame %s points!!`b",$points);
						break;
	
						case 2: case 3: case 4: case 5:
							output("`n`n`@Not bad, but I've seen better.`n`n");
							output("`6`bYou have earned a paltry %s points!!`b",$points);
						break;
	
						case 6: case 7: case 8: case 9:
							output("`n`n`@Nice throw, Warrior. My compliments to ye.`n`n");
							output("`6`bYou have earned a respectable %s points!!`b",$points);
						break;
	
						case 10:
							output("`n`n`@Remarkable! You've bested even `!MightyE`@ with a perfect toss!!!`n`n");
							output("`6`bYou have earned the maximum %s points!!`b",$points);
						break;
					}
				break;

				case 2:
					output("`@This challenge will push you to the extremes of your physical endurance.  Do not think this will be simple. ");
					output("You must run. Aye, run. Ye must run as far as you possibly can! You will receive 1 point for each kilometre you run.`n`n");
					output("Now get those feet moving!!!`n`n");
	
					for( $i=0; $i<$points; $i++ )
					{
						output_notl("`% + +");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`n`@Such a miserable performance!!! Ye consider yourself worthy of this tournament? HA!`n`n");
							output("`6`bYou have run barely %s kilometres!!`nYou accumulate a measily %s points!!`b",$points,$points);
						break;
	
						case 2: case 3: case 4: case 5:
							output("`n`n`@Ye have failed to impress me, I know ye can do better then this.`n`n");
							output("`6`bYou ran the moderate distance of %s kilometers!!`nYou merit only %s points!!`b",$points,$points);
						break;
	
						case 6: case 7: case 8: case 9:
							output("`n`n`@Mhhh, not bad at all, gratz.`n`n");
							output("`6`bYou travelled coast to coast, covering %s kilometers!!`nI shall award ye %s well deserved points!!`b",$points,$points);
						break;
	
						case 10:
							output("`n`n`@Most impressive!!!  Should I call ye Forrest Gump, perchance?`n`n");
							output("`6`bYou realized the goal of running the full %s kilometers of the Challenge!!`nYou shall receive a perfect score of %s points!!`b",$points,$points);
						break;
					}
				break;
	
				case 3:
					output("`@This challenge is not physically demanding, rather it is a test of your skill at arms and accuracy.`n");
					output("Ye must fire five bolts from this crossbow, hitting the target 100 yards away.`n");
					output("A Bullseye counts for 20 points. The best possible score is 100 points.`n");
					output("Can ye match the perfect score set by our sharpshooter, `bFarmboy Saruman`b??`n`n");
	
					for( $z=1; $z<6; $z++ )
					{
						output("`!Shot # %s ",$z);
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`% + +");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`@Mark yer target next time, not the cat!!! I've seen goblins shoot better then that!`n");
							output("Only one struck near the right target!`n`n");
							output("`6`bYou have scored only %s points!!`b",$points);
						break;
	
						case 2: case 3: case 4: case 5:
							output("`n`@Well, ye've shot 3 arrows in the right direction, but I know ye can do better.`n`n");
							output("`6`bYou receive %s points!!`b",$points);
						break;
	
						case 6: case 7: case 8: case 9:
							output("`n`@Mhhh, not bad at all, compadre.`n`@It looks like one of your 4 accurate shots found the Bullseye.`n`n");
							output("`6`bI shall give ye %s points!!`b",$points);
						break;
	
						case 10:
							output("`n`@Bravissimo! A perfect score!!! Are you related to Robin Hood?`n`n");
							output("`6`bYou shall receive %s points!!!`b",$points);
						break;
					}
				break;
	
				case 4:
					output("`@Mastery of the long bow is a difficult goal to achieve, but ye had best have it if you hope to win this challenge. ");
					output("`nYour accuracy will be judged on how expertly you fire five arrows into yonder target.");
					output("`nYou will receive points for each strike, with the BullsEye worth 20 points. ");
					output("`nGo now, and show me that you are as skilled as `^Aris!!`n`n");
	
					for( $z=1; $z<6; $z++ )
					{
						output("`!Shot # %s ",$z);
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`% + +");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`@What are ye waiting for?  You may begin!!!`nWait...you've already shot? Pathetic!!!`n`n");
							output("`6`bThese meager %s points will do little for ye!!`b",$points);
						break;
	
						case 2: case 3: case 4: case 5:
							output("`n`@Hardly a noteworthy effort, my friend, but I have seen much worse.`n`n");
							output("`6`bBe thankful for the %s points you've received!!`b",$points);
						break;
	
						case 6: case 7: case 8: case 9:
							output("`n`@Splendid shooting, amigo!!! `n`n");
							output("`6`bIncluding that bullseye, I count %s points for you!!`b",$points);
						break;
	
						case 10:
							output("`n`@Ye have truly shown the skill of `#Aris`@!! Are ye sure your name is really %s??`n",$session['user']['name']);
							output("`6`bFive arrows, five Bullseyes!!! %s points for you!!`b",$points);
						break;
					}
				break;
	
				case 5:
					output("`@You are doing well to make it this far, my friend.  You have worked hard and deserve some refreshment.`n ");
					output("Have a seat...right over there.  Make yourself comfortable.  Would ye care for some ale?`n");
					output("Let me get ye some of Cedrick's finest.`n`nNow...ye didn't think it would be THIS easy, did ye?  ");
					output("Of course ye didn't - ye know me better by now.`nThis is a competition, one which now tests your constitution!  ");
					output("How much of this ale can ye stomach???`nWell, I'll tell ye now ... ye get 5 points for each mug ye down.  ");
					output("Can ye match Cedrick's own record of 20??`n`n`c");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`6 + +");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`c`n`@Hey!!! Whatsa matter with you?  Got a hole in your lip?  More ale went on the table than your mouth.`n`n");
							output("`6`bI can only let ye have %s points for that pathetic performance.`b",$points);
						break;
	
						case 2: case 3: case 4: case 5:
							output("`c`n`@Nice try, but hardly a worthwhile effort, young friend. That would be but a warmup for Cedrick!`n`n");
							output("`6`bYe drank enough mugs to earn %s points!!`b",$points);
						break;
	
						case 6: case 7: case 8: case 9:
							output("`c`n`@My compliments on a valiant effort, although ye are certainly no competition for Cedrick. `n`n");
							output("`6`bEighteen mugs, minus the spillage, adds %s points to your score!!`b",$points);
						break;
	
						case 10:
							output("`c`n`@Amazing!  You must be the %s of Cedrick! `n",$session['user']['sex']?translate_inline("sister"):translate_inline("brother"));
							output("`6`bYe drank all 20 mugs, and nary spilled a drop! Ye truly deserve these %s points!!`b",$points);
						break;
					}
				break;
	
				case 6:
					output("`@Ye're fast nearing the halfway mark, my friend. It is now that champions begin to separate themselves ");
					output("from the 'also rans', and show their true mettle. ");
					output("Ye must have worked up an appetite after the runs and beer drinking, aye?");
					output("Cedrik suggested some `b`7Smoked Herrings`&`b`@ might now hit the spot.`n`n");
					output("The one who is able to scarf the most herrings will earn points and the title of ");
					output("`\$Smoked Herring Scarfer of the Year`@. `nThe champion of our village is `b`#Luke`b`@, ");
					output("who managed to wolf down 99 smoked herring.`n`n");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`7 + +");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`@Ye should have said ye were allergic to seafood!!!`n`n");
							output("`6`bYou have eaten only %s herrings!!`b",$points);
						break;
	
						case 2: case 3: case 4: case 5:
							output("`n`@Bah!  I've seen better lately...did the ale fill ye?`n`n");
							output("`6`bYou have eaten %s smoked herring!!`b",$points);
						break;
	
						case 6: case 7: case 8: case 9:
							output("`n`@Mhhh, you could give even Luke a run for his money, my compliments.`n`n");
							output("`@I see you have left only %s herrings on the table.`n",$remainder);
							output("`6`bYou have earned %s points!!`b",$points);
						break;
	
						case 10:
							output("`n`@Wow, we have a new champion in the village!! You destroyed `#Luke's`@ record!!`n`n");
							output("`@You have scarfed all 100 herring, mastering this challenge.`n`n");
							output("`6`bYou have earned %s points!!`b",$points);
						break;
					}
				break;
	
				case 7:
					output("`@Ye've now reached the halfway point in our competition, and are about to meet my favorite challenge.");
					output("With the herring and ale of the last two trials your stomach must be churning. ");
					output("Do you already feel the pressure of the gas trying to escape?  Well don't let it out yet!!`n");
					output("In this challenge, whoever produces the `7`blongest belch`b `@will receive the highest score. ");
					output("But be careful that ALL ye do is belch - I don't wish to see the ale or herring again!!!`n`n");
					output(" The one and only champion of this trial is our quiet fellow-citizen `!`bHutgard`b`@ with 99 seconds.");
					output("Hmmm, that dull rumble I hear tells me I'd better stop talking and let ye start belching!!!`n`n");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`6 * *");
						}
						output_notl("`n");
					}
					switch( $points1 )
					{
						case 1:
							output("`n`@You call that a belch??  That wasn't even a hiccup, let alone a burp!!!`n`n");
							output("`6`bYour so called belch lasted only %s seconds!!`b",$points);
						break;
	
						case 2: case 3: case 4: case 5:
							output("`n`@A good start, but no follow through!`n`n");
							output("`6`bYou belched for %s seconds!!!`b",$points);
						break;
	
						case 6: case 7: case 8: case 9:
							output("`n`@Saaayyyy...not bad...not bad at all!  Ye knocked out 30 farmies with that one!!!.`n`n");
							output("`6`bYour belch earns ye a total of %s points!!!`b",$points);
						break;
	
						case 10:
							output("`n`@I would not have thought it possible, but ye've obliterated all traces of `#Hutgard's`@ record!!`n");
							output("`@A full barrack of troops is unconscious, ye've broken every window in the village and neighboring ");
							output("villages have reported minor earth tremors!!!`n`n");
							output("`6`bYour efforts warrant %s points!!!`b",$points);
						break;
					}
				break;
	
				case 8:
					output("`@Ye still walk amongst the living, `b%s`b`@? Unexpected, but tis grand to see! ",$session['user']['name']);
					output("Sadly, we are not done with ye yet.`n Between the herring, beer and belches of the last few challenges, ");
					output("how would ye feel about a good rest?  From the look on your face, I suspect you are eager for a good sleep!`n");
					output("Well...I hope we've tired ye enough that ye may win this challenge by sleeping the longest of any competitor. ");
					output("Ye shall earn one point for each hour of sleep. ");
					output("Your goal should be to beat our friend `!`bDankor's`b`@ record of 99 hours of uninterrupted sleep!!`n`n");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`7 z z");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`@Terrible, TERRIBLE!!! This is one occasion where time spent sleeping would not be wasted time.`n`n");
							output("`6`bSadly, you slept for only %s hours!!`b",$points);
						break;
	
						case 2: case 3: case 4: case 5:
							output("`n`@Well, I don't think ye need to worry about anyone calling ye 'Sleepy Head'.`n`n");
							output("`6`bYe slept for %s hours!!`b",$points);
						break;
	
						case 6: case 7: case 8: case 9:
							output("`n`@Mhhh, nice, really!  Ye could consider yerself kin to Rumplestiltskin!`n`n");
							output("`6`bYour nap lasted for %s hours!!`b",$points);
						break;
	
						case 10:
							output("`n`^Amazing, a new champion in the village!! ");
							output("Your laziness has surpassed even `#Dankor's`@ long sleep with `b100 hours`b!!`n");
							output("`@Your sleeping rivals that of `#Sleeping Beauty`@ ... ");
							output("although I fear ye not be quite as beautiful as she was rumoured to be!!!`n`n");
							output("`6`bYou have earned % points!!!`b",$points);
						break;
					}
				break;
	
				case 9:
					output("`@This next challenge is directly related to the last one; in fact, you completed it at the same time ");
					output("you were sleeping in the previous challenge. Surprised?  Well, it's quite simple really. Ye see, ");
					output("we measured the decibel level of your snoring, and awarded you one point per decibel.`n");
					output("The loudest snoring in memory was that of `b`!CMT`b`@, at an amazing 99 decibels - louder than the dying ");
					output("dragon's roar!`nNow let me check to see if ye've done better than that...`n`n ");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`7 Z Z");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`@Noooooo, that is one of the worst performances I've never heard!!!`n`n");
							output("`6`bYou have reached only %s decibels!!!`b",$points);
							output("`n`n`@Don't you know that blowing your nose before going to bed makes it harder to get good `isound`i?");
						break;
	
						case 2: case 3: case 4: case 5:
							output("`n`@What can I say, you managed to wake your %s from slumber beside you, but made little impact beyond that.`n`n", $session['user']['sex']?translate_inline("husband"):translate_inline("wife"));
							output("`6`bYou peaked at %s decibels!!!`b",$points);
						break;
	
						case 6: case 7: case 8: case 9:
							output("`n`@Mhhh, not that bad, really. You woke the whole neighbourhood with your snoring!`n`n");
							output("`6`bA measure of %s decibels earns ye %s points!!`b",$points,$points);
						break;
	
						case 10:
							output("`n`@My goodness! We have a new champion - You smashed the old record held by `#CMT`@ with `b100 decibels`b!!`n");
							output("`@We could use your snoring instead of explosives to demolish enemy fortresses!!!!`n`n");
							output("`6`bYou have earned %s points!!!`b",$points);
						break;
					}
				break;
	
	
				case 10:
					output("`@H-Hey, `b%s`b`@!!! I've got something you need, right here - 100 rubber bands.`n`n", $session['user']['name']);
					output("Now, ye might be wondering why ye might need 100 rubber bands? We-e-ell, I'm glad ye asked!`n");
					output("As you well know, the Garden Gnomes don't keep the forest as tidy as they should, ");
					output("and allow water to stagnate, thus allowing the proliferation of mosquitoes.`n`n");
					output("Your challenge is to wipe out as many mosquitoes as ye can with those rubber bands. ");
					output("A perfect score will see ye crowned as `#King of Rubber Bands'`@ in place of `b`#OberonGloin`b`@, ");
					output("the current 'King'.`n`n");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`6 o o");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`@Noooooo, ye're a terrible shot with those bands, and the Mosquitoes thank you!!!`n`n");
							output("`6`bYou have scored only %s points!!`b",$points);
							output("`n`n`@As ye seem to be on the mosquito's side, tell me...are ye `\$Nosferatu's`@ relative ???");
						break;
	
						case 2: case 3: case 4:
							output("`n`@Ye seem rather fond of those mosquitoes, judging by your low kill ratio. ");
							output("Don't tell me...is your favorite drink a `\$Bloody Mary`@?`n`n");
							output("`6`bYou have earned only %s points!!`b",$points);
						break;
	
						case 5: case 6: case 7: case 8: case 9:
							output("`n`@Mhhh, you're a sharpshooter with those elastics! The mosquitoes are learning to stay away from ye.`n`n");
							output("`6`bWith %s dead mosquitoes, you earn %s points!!`b",$points,$points);
						break;
	
						case 10:
							output("`n`@Well now, I think I shall dub thee `3Rubber Hood`@ in recognition of your outstanding aim!! ");
							output("You have beat the record of `#OberonGloin`@ with `b100 hits`b!!");
							output("`@The adjacent villages are ready to contract you to assassinate all the mosquitoes in their shires!!!`n`n");
							output("`6`bYou have earned %s points!!",$point);
						break;
					}
				break;
	
				case 11:
					output("`@My good friend `3`bMerick`b`@ has asked of favour of me, and I think it would make for a great challenge. ");
					output("Only `3`bMerick`b`@ can truly tame a centaur, but a strong warrior should be able to ride one for some time. ");
					output(" You will have to ride this powerful beast for as long as ye can, earning a point for each second of your ");
					output("flight. Our champion is `bPoker`b`@, with 99 seconds on it's back. Can ye beat him?? `n`n");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`% <^>");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`n`@You better stick to cleaning the stables!!!  ");
							output("You stuck to that centaur like `6`bGrog`b`@ does to soap!`n`n");
							output("`6`bYour ride only lasted %s seconds!!`b",$points);
						break;
	
						case 2: case 3: case 4:
							output("`n`n`@Forget about that career as a wrangler, your ride only lasted %s seconds. `n",$points);
							output("`6`bAnyhow you have earned %s points!!`b",$points);
						break;
	
						case 5: case 6: case 7: case 8: case 9:
							output("`n`n`@Outstanding ride, my friend ... ye must have been riding centaurs ");
							output("since ye were knee high to a goblin!!`n`n");
							output("`6`bYou kept hold on his back for %s seconds!!!`b",$points);
						break;
	
						case 10:
							output("`n`n`@Wow, not even `!Merick`@ stayed as long as you just did!!! ");
							output("You replace `#Poker`@ as the village champion!!");
							output("`nNow...do ye think ye could tame my wild canary?`n`n");
							output("`6`bYou receive `b100 points!!");
						break;
					}
				break;
	
				case 12:
					output("`@This next challenge is a test of your agility and dexterity.  ");
					output("I have 100 of my favorite friends, the sprytes, who will enter a room with ye.");
					output("  Your task is to catch as many of them as ye are able, but ye must be careful not to injure them.");
					output("Will you be able to catch as many as did our champion, `%`bKhendra`b`@, who holds the record with 99?`n`n");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl("`& * * * *");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`@Need glasses?  Couldn't see them? ");
							output("Or are those mitts you call hands too clumsy to catch such graceful creatures?`n`n");
							output("`6`bYour pathetic score nets you only %s points!!`b",$points);
						break;
	
						case 2: case 3: case 4:
							output("`n`@Well, you caught %s sprytes.  ",$points);
							output("I guess it could have been worse, but it should have been much better.`n`n");
							output("`6`bYou have earned %s points!!`b",$points);
						break;
	
						case 5: case 6: case 7: case 8: case 9:
							output("`n`@Mhhh, excellent. A a little more effort, and maybe you may have been able to break the old record.`n`n");
							output("`6`bYou caught %s Sprytes!!!`b",$points);
						break;
	
						case 10:
							output("`n`@Bravo!!! You surpassed `#Khendra's`@ old record of 99!!!`n`n");
							output("`6`bYe captured `b%s Sprytes`b. A legendary performance!!`b",$points);
						break;
					}
				break;
	
				case 13:
					output("`@I wonder, as I look at ye...how do ye like your eggs? Sunnyside up?...Poached?...Scrambled maybe? ");
					output("But wait! Don't tell me...I'll let ye show me. Here is a bucket of 100 fresh eggs. ");
					output("Ye must toss them, one at a time, into that large frying pan 20 feet away. ");
					output("Ye shall receive one point for each egg that lands in the pan unbroken. Careful now!!!`n`n");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl(" `&(`^0`&) `&(`^X`&)");
						}
						output_notl("`n");
					}
	
					switch( $points1 )
					{
						case 1:
							output("`n`@Remind me not to send you for eggs!! I guess you must like yours scrambled and crunchy.`n`n");
							output("`6`bYou broke all but %s eggs!!!`b",$points);
						break;
	
						case 2: case 3: case 4:
							output("`n`@If ye help anybody cook a steak and eggs breakfast, ");
							output("I'd recommend you cook the steak and leave the eggs to somebody more skilled. ");
							output("You broke %s eggs.`n`n",$remainder);
							output("`6`bYour egg throwing earned you %s points!!!`b",$points);
						break;
	
						case 5: case 6: case 7: case 8: case 9:
							output("`n`@Congratulations, except for the %s broken eggs, you were perfect. ",$remainder);
							output("Ok, not quite perfect, but not too bad, either.`n`n");
							output("`6`bYou landed %s unbroken eggs!!!`b",$points);
						break;
	
						case 10:
							output("`n`@Well done!!! `#Nulla`@ should take some lesson from ye - ");
							output("your perfect score unseated him as village champion!!!`n`n");
							output("`6`bYour reward is `b%s points!!`b",$points);
						break;
					}
				break;
	
				case 14:
					output("`@You will now face one of the most difficult challenges of the tournament, that of sword twirling.  ");
					output("I see your arms are strong, as is your desire to win.  Ye are to take this greatsword, and singlehandedly ");
					output("throw it in the air, allowing it to rotate one full revolution before catching it by it's handle.  ");
					output("Ye shall receive one point for each successful catch.`n`n");
	
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl(" `^-{`7--- `s`s`s`s`s`s   ---`^}-`s`s`s`s`s`s");
						}
						output_notl("`n");
					}
					switch( $points1 )
					{
						case 1:
							output("`n`@Your arms would not be so scarred if ye listened to my instructions - ");
							output("catch by the handle, not by the blade.`n`n");
							output("`6`bYou get credit for just catches!!`b",$points);
						break;
	
						case 2: case 3: case 4:
							output("`n`@Ye're sure not any %s, ", ($session['user']['sex']==1?translate_inline('Xena'):translate_inline('Conan')));
							output("but ye do get the job done out in the forest. I'd suggest ye stick to the normal methods of swordhandling.`n`n");
							output("`6`bYou earn %s points for your catches!!`b",$points);
						break;
	
						case 5: case 6: case 7: case 8: case 9:
							output("`n`@A good effort, I think ye're our local `#%s`@!!`n`n", ($session['user']['sex']==1?translate_inline('Xena the Warrior Princess'):translate_inline('Conan the Barbarian')));
							output("`6`bYou completed %s sword catches!!`b",$points);
						break;
	
						case 10:
							output("`n`@Outstanding! Ye beat `bMightyE`b`@'s old record of 99 catches!!!`n`n");
							output("`6`bYou have earned %s points!!!`b",$points);
						break;
					}
				break;
	
				case 15:
					output("`@My friend, you have arrived at the most challenging portion of this competition. ");
					output("I know not that ye have the strength and endurance to survive this trial alive, ");
					output("yet ye have come this far and it would be unfair to stop ye now. ");
					output("We have managed to acquire, just for this competition, a rare `\$Red Dragon`@. ");
					output("All ye need to do is withstand the blast of his flames for as long as ye can.`n");
					output("Good luck to ye.`n`n");
					for( $z=0; $z<5; $z++ )
					{
						for( $i=0; $i<e_rand(0,$points1); $i++ )
						{
							output_notl(" `4w`$ Y `4w`$ Y ");
						}
						output_notl("`n");
					}
					switch( $points1 )
					{
						case 1:
							output("`n`@Couldn't stand the heat?  Get out of the frying pan!!! `n`n");
							output("`6`bYour pathetic effort earned you a mere %s points!!`b",$points);
						break;
	
						case 2: case 3: case 4:
							output("`n`@Hmmmm...I see some 3rd degree burns on ye...what are ye? A fool? ");
							output("Ye shouldn't push your luck so.  Maybe next time you'll wear some heat resistant clothing!`n`n");
							output("`6`bYou survived %s seconds in the company of that `\$Red Dragon`6!!`b",$points);
						break;
	
						case 5: case 6: case 7: case 8: case 9:
							output("`n`@I swear ye must be at home in the desert, and firewalk for fun.  ");
							output("The `\$Red Dragon's `@flame barely singed ye, but ye were still ");
							output("%s seconds short of beating `bExcalibur`b's`@ record.`n`n",$remainder);
							output("`6`bYou gain %s points!!!`b",$points);
						break;
	
						case 10:
							output("`n`@Amazing!!! Ye have beaten `2`bExcalibur's`b `@record!!! ");
							output("Ye spent `b%s seconds`b`@ with the `\$Red Dragon's `@flames ",$points);
							output("trying to turn ye into a walking torch, and you don't even have a hair out of place!!!!`n`n");
							output("`6`bYou have earned %s points!!!`b",$points);
						break;
					}
				break;
			}
	
			if( $points1 == 10 )
			{
				output("`n`n`^Ye also receive `%`b1 gem`b`^ as a bonus!!!");
				$session['user']['gems']++;
			}
	
			// If the person with the most points is not the current player then we have a new leader. 
			$sql = "SELECT userid, value
					FROM " . db_prefix('module_userprefs') . "
					WHERE modulename = 'tournament'
						AND setting = 'points'
						AND value <> ''
					ORDER BY value + 0 DESC LIMIT 1";
			$result = db_query($sql);
			$row = db_fetch_assoc($result);
			if( $row['userid'] != get_module_setting('leader') )
			{
				set_module_setting('leader', $row['userid']);
				if( $row['userid'] == $session['user']['acctid'] ) output("`n`n`#You're now the Tournament Leader!");
			}
		}
	}

	output("`n`n`^Your current tournament score is `^%s Points.", number_format($points_total));

	addnav('Leave');
	villagenav();
?>