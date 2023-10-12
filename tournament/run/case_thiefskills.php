<?php
	$into = translate_inline("`n`3You quickly set to work on the lock and have it open in a jiffy.`n`n");
	$skill = rand(0,15);
	switch( $skill )
	{
		case 0: case 1: case 2: case 3:
			output("`n`3You quickly set to work on the lock, but it's too complicated and you just can't crack it.`n`n");
			output("You hear footsteps approaching and think it best to leave for now and head back to the village.`n");
			$session['user']['spirits']='-2';
		break;

		case 4: case 5: case 6: case 7:
			$gold = rand(100,700);
			output_notl($intro);
			output("`3You hear somebody fast approaching and grab what you can. You're able to stuff `%1 gem `3and `^%s gold `3into your pockets!", $gold);
			$session['user']['gems']++;
			$session['user']['gold']+=$gold;
			$session['user']['spirits']='-1';
			addnews("`3The Tournament chest was robbed. The main suspect is `#%s`3!", $session['user']['name']);
		break;

		case 8: case 9: case 10: case 11:
			$gold = rand(700,2500);
			output_notl($intro);
			output("`3You hear somebody approaching and grab what you can. You're able to stuff `%3 gems `3and `^%s gold `3into your pockets!", $gold);
			$session['user']['gems']+=3;
			$session['user']['gold']+=$gold;
			$session['user']['spirits']=0;
			addnews("`3The Tournament chest was robbed. One of the possible suspects is `#%s`3!", $session['user']['name']);
		break;

		case 12: case 13: case 14:
			$gold = rand(5000,12000);
			output_notl($intro);
			output("`3You hear somebody in the distance, but this wont take long. You're able to stuff `%5 gems `3and `^%s gold `3into your pockets!", $gold);
			$session['user']['gems']+=5;
			$session['user']['gold']+=$gold;
			$session['user']['spirits']=1;
			addnews("`3The Tournament chest was robbed. `#%s `3is not even a suspect!", $session['user']['name']);
		break;

		case 15;
			$gold = rand(13000,30000);
			output_notl($intro);
			output("`3There's not a sound outside which tells you that there's plenty of time. You're able to stuff `%10 gems `3and `^%s gold `3into your pockets!`n`n", $gold);
			output("`^The shiney gems and gold make you feel more charming and boost your spirits!");
			$session['user']['gems']+=10;
			$session['user']['gold']+=$gold;
			$session['user']['spirits']=2;
			$session['user']['charm']+=10;
			addnews("`3The Tournament chest was robbed. Nobody saw or heard a thing. The thief was a pro!");
		break;
	}

	output("`n`n`3Exiting the office quickly and quietly you head back to the village.`0`n");

	addnav('Leave');
	villagenav();
?>