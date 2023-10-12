<?php
/**
	v0.0.1 - 25/05/2009
	+ Basic settings, very raw. Just wanted to see it working.
	v0.0.2 - 11/08/2010
	+ Loads more settings and more code in the script that generates the image.
*/
function sigsstats_getmoduleinfo()
{
	$info = array(
		"name"=>"Signature Server Stats Image",
		"description"=>"A script generated image containing the server's stats.",
		"version"=>"0.0.2",
		"author"=>"`@MarcTheSlayer",
		"category"=>"Administrative",
		"download"=>"http://dragonprime.net/index.php?topic=11182.0",
		"settings"=>array(
			"Banner Stats,title",
				"`@If you edit any of the settings then *you must* refresh the data!`nBut do so after you've saved the changes otherwise the refresh wont work.,note",
				"sitename"=>"Website name:,string|The Legend of Six",
				"siteurl"=>"Website url:,string|www.legendofsix.com",
				"siteallowed"=>"Websites allowed to call banner:,text|dragonprime.net",
				"`^Separate each website with a comma. Leave empty to allow any website to call your banner.,note",
				"hotlinking"=>"Display hotlink image to websites not allowed?,bool",
				"hotlinkmsg"=>"Short message to appear on hotlink images:,string|No Hotlinking Allowed",
				"allprefs"=>"Allpref data:,viewonly",
				"`^If the above allpref data is empty then refresh the data.,note",
				"reset"=>"Refresh data:,bool|",
				"`^Refresh data by selecting `bYes`b above and clicking Save.,note",
				"To see the banner image `b<a href=\"./modules/sigsstats/sigsstats.php\" target=\"_blank\">Click Here</a>`b.,note",
			"Banner Background,title",
				"banneruse"=>"Use a background image?,bool|",
				"bannerpath"=>"Path to background image:,text|./images/banner.png",
				"`^Eg: `b./images/banner.png`b Must be either a `bJPG`b or a `bPNG`b or a `bGIF`b file.`nSize must be 468x60.,note",
				"alpha"=>"Apply a semi transparent layer over image?,bool|1",
				"alphatrans"=>"0 = opaque and 127 = 100% transparent:,range,0,127,1|80",
				"`^Select how transparent you want the layer to be.,note",
				"alphared"=>"Red layer parameter:,range,0,255,1|0",
				"alphagreen"=>"Green layer parameter:,range,0,255,1|0",
				"alphablue"=>"Blue layer parameter:,range,0,255,1|0",
				"`^The <a href=\"http://www.w3schools.com/css/css_colors.asp\" target=\"_blank\">RGB colour</a> of the layer.,note",
				"bannerred"=>"Red background parameter:,range,0,255,1|0",
				"bannergreen"=>"Green background parameter:,range,0,255,1|0",
				"bannerblue"=>"Blue background parameter:,range,0,255,1|0",
				"`^The RGB colour of the background if no background image is entered.,note",
				"fontface"=>"Path to True Type Font:,text|./images/arial.ttf",
				"`^Eg: `b./images/arial.ttf`b A True Type Font is required. Please make sure that you've uploaded one.,note",
			"Banner Output File,title",
				"`@Banner will be 468x60 in size.,note",
				"bannertype"=>"Select file format:,enum,0,GIF,1,JPEG,2,PNG|0",
				"typejpeg"=>"Compression for JPEG (100=best):,range,1,100,1|85",
				"typepng"=>"Compression for PNG (0=best):,range,0,9,1|2",
				"`^GIF format has no compression.,note",
				"outputsave"=>"Save banner as a file?,bool",
				"outputpath"=>"Location to save banner image:,text|./images/",
				"`^Eg: `b./images/`b Location must be writable. ie: chmod 0777.,note",
				"outputname"=>"Filename for banner image:,text|statsbanner_468x60.jpg",
				"`^Eg: `bstatsbanner_468x60.jpg`b Make sure the extension matches the output format above.,note",
				"bannerrefresh1"=>"Update the banner image once every:,datelength|1 hour",
				"bannerrefresh2"=>"Update less than an hour?,enum,0,No,1 minute,1 minute,5 minutes,5 minutes,10 minutes,10 minutes,20 minutes,20 minutes,30 minutes,30 minutes,40 minutes,40 minutes,50 minutes,50 minutes|0",
				"`^Ignore this if the image doesn't get saved.,note",
			"Readme,title",
				"`^`iTo get the image to appear all you need to do is link to the script pretending that it's an image.`n`n
				`Q&lt;a href=\"`@http://domain.com`Q\"&gt;&lt;img src=\"`@http://domain.com/modules/sigsstats/sigsstats.php`Q\"&gt;&lt;/a&gt;`n`n
				`^or if you're using bbcode.`n`n`Q[url=`@http://domain.com`Q][img]`@http://domain.com/modules/sigsstats/sigsstats.php`Q[/img][/url]`n`n
				`^You may find that some forums will block the image from showing because the file doesn't end in a image extension. (gif/png/jpg) In these cases the only solution possible works only on servers running Apache.`n`n
				Included in the download is a file called `Q.htaccess `^Open this in an editor and remove a `Q# `^from one of the `QAddType `^lines then save the file. Then rename the sigsstats.php extension to match the line you uncommented.`n`n
				`QAddType application/x-httpd-php .png`n
				`^Rename script to sigsstats.`Q`bpng`b`n`n
				`^Then just link to the new file. Simple.,note",
		),
	);
	return $info;
}

function sigsstats_install()
{
	output("`c`b`Q%s 'sigsstats' Module.`b`n`c", translate_inline(is_module_active('sigsstats')?'Updating':'Installing'));
	module_addhook('changesetting');
	return TRUE;
}

function sigsstats_uninstall()
{
	output("`n`c`b`Q'sigsstats' Module Uninstalled`0`b`c");
	return TRUE;
}

function sigsstats_calc()
{
	$settings = array();

	$sql = "SELECT count(active) as c FROM " . db_prefix('modules') . " WHERE active = 1";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);
	$settings['modulecount'] = $row['c'];

	$race_names = modulehook('racenames');
	$settings['racecount'] = count($race_names);

	$specialty_names = modulehook('specialtynames',array(''=>'None'));
	$settings['specialtycount'] = count($specialty_names);

    $vloc = array();
	$vloc = modulehook('validlocation', $vloc);
	$settings['villagecount'] = count($vloc) + 1;

	$settings['travelcount'] = get_module_setting('allowance','cities');

	$settings['pvpcount'] = getsetting('pvpday',4);
	$settings['turnscount'] = getsetting('turns',4);
	$settings['gamedays'] = getsetting('daysperday',4);

	$settings = array_merge($settings, get_all_module_settings());
	unset($settings['allprefs'],$settings['reset'],$settings['showFormTabIndex'],$settings['0']);

	set_module_setting('reset',0);
	set_module_setting('allprefs',serialize($settings));
}

function sigsstats_dohook($hookname,$args)
{
	if( $args['module'] == 'sigsstats' )
	{
		if( $args['setting'] == 'reset' && $args['new'] == 1 ) sigsstats_calc();
		if( $args['setting'] == 'outputpath' && $args['new'] != '' )
		{
			$path = $args['new'];
			if( substr($path,-1) != "/" && substr($path,-1) != "\\" ) $path = $path.'/';
			if( is_dir($path) )
			{
				if( substr(sprintf('%o', fileperms($path)), -4) == '0777' )
				{
					output("`@Success: `b%s`b exists and is writeable!`0`n", $path);
				}
				else
				{
					output("`$ Warning: `b%s`b exists, but `@is not`$ writeable!`0`n", $path);
				}
				$args['new'] = $path;
			}
			else
			{
				output("`$ Warning: `b%s`b does not exist. No images can be saved there!!!`0`n", $path);
			}
		}
		if( $args['setting'] == 'outputname' && $args['new'] != '' )
		{
			$extensions = array('jpg','jpeg','png','gif');
			$path = strtolower(basename($args['new']));
			$ext = ( ($pos = strrpos($path, '.')) !== FALSE ) ? substr($path, $pos+1) : '';
			if( !in_array($ext, $extensions) )
			{
				output("`$ Warning: The filename for the banner has a wrong extension (%s). It must be either JPG/JPEG, or PNG, or GIF. Also make sure that it matches the output format.`0`n", $ext);
			}
		}
	}

	return $args;
}

function sigsstats_run()
{
}
?>