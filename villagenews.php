<?php
/**
	Modified by MarcTheSlayer
	22/05/2011 - v1.1.0
	+ Removed repeated code.
	+ Only hooks needed are installed.
*/
function villagenews_getmoduleinfo()
{
	$info = array(
		"name"=>"Village News",
		"version"=>"1.1.0",
		"author"=>"`#Lonny Luberts, `&modified by Oliver Brendel `2and `@MarcTheSlayer",
		"category"=>"PQcomp",
		"download"=>"",
		"settings"=>array(
			"Village News Module Settings,title",
			"showhome"=>"Show news on Home Page,enum,0,No,1,Above Login,2,Below Login",
			"newslines"=>"Number of news lines to display:,range,1,10,1|5",
		),
		"prefs"=>array(
			"Village News,title",
				"user_villnews"=>"Display Latest News in the Village,bool|1",
		),
	);
	return $info;
}

function villagenews_install()
{
	if( !is_module_active('villagenews') ) output("`4Installing Village News Module.`n");
	else output("`4Updating Village News Module.`n");

	villagenews_sethooks(get_module_setting('showhome'));
	return true;
}

function villagenews_sethooks($hook)
{
	if( $hook == 1 ) module_addhook('index');
	elseif( $hook == 2 ) module_addhook('footer-home');

	module_addhook('village-desc');
	module_addhook('changesetting');

	return TRUE;
}

function villagenews_uninstall()
{
	output("`4Un-Installing Village News Module.`n");
	return TRUE;
}

function villagenews_dohook($hookname,$args)
{
	switch($hookname)
	{
		case 'village-desc':
			if( get_module_pref('user_villnews') == 0 ) break; 
		case 'index':
		case 'footer-home':
			tlschema('news');
			output("`n`2`c`bLatest News`b`c");
			output("`2`c`c");
			$sql = "SELECT newstext, arguments
					FROM " . db_prefix('news') . "
					ORDER BY newsid DESC LIMIT " . get_module_setting('newslines');
			$result = db_query($sql);
			$i=0;
			while( $row = db_fetch_assoc($result) )
			{
				if( $row['arguments'] > '' )
				{
					$arguments = array();
					$base_arguments = unserialize($row['arguments']);
					array_push($arguments,$row['newstext']);
					while( list($key,$val) = each($base_arguments) )
					{
						array_push($arguments,$val);
					}
					$newnews = call_user_func_array('sprintf_translate',$arguments);
				}
				else
				{
					$newnews = $row['newstext'];
				}
				output("`c %s `c", $newnews);
				if( $i <> get_module_setting('newslines') ) output("`2`c`c");
				$i++;
			}
			output("`n");
			tlschema('user');
		break;

		case 'changesetting':
			if( $args['module'] == 'villagenews' && $args['setting'] == 'showhome' )
			{
				module_wipehooks();
				villagenews_sethooks($args['new']);
			}
		break;
	}

	return $args;
}

function villagenews_run()
{
}
?>