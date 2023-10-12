<?php
function bbcodes_getmoduleinfo()
{
	$info = array(
		"name"=>"BBCodes",
		"description"=>"Allow BBCodes in commentary.",
		"version"=>"0.0.1",
		"author"=>"`@MarcTheSlayer`2, for `#C`&on`#t`&ess`#a`0",
		"category"=>"Exclusive",
		"settings"=>array(
			"README - BBCodes to Use,title",
			"There are no settings; just some notes.`n`n
			All superusers who can edit users or comments or post MOTDs or those you have given permission to; can use these bbcodes.`n`n
			BBcodes are as follows:`n`^[b]`0bold`^[/b]`0`n`^[u]`0underline`^[/u]`0`n`^[o]`0overline`^[/o]`0`n`^[i]`0italic`^[/i]`0`n`^[s]`0strikeout`^[/s]`0`n`^[f]`0flash`^[/f]`0`n
			`^[url]`0http://www.domain.com`^[/url]`0 or`n
			`^[url=`0http://www.domain.com`^]`0Link Text`^[/url]`0`n`n,note"
		),
		"prefs"=>array(
			"allow"=>"Can this player use BBcodes?,bool|"
		)
	);
	return $info;
}

function bbcodes_install()
{
	output("`c`b`Q%s 'bbcodes' Module.`b`n`c", translate_inline(is_module_active('bbcodes')?'Updating':'Installing'));
	module_addhook('commentary');
	module_addhook('viewcommentary');
	return TRUE;
}

function bbcodes_uninstall()
{
	output("`n`c`b`Q'bbcodes' Module Uninstalled`0`b`c");
	return TRUE;
}

function bbcodes_dohook($hookname,$args)
{
	global $session;

	switch( $hookname )
	{
		case 'commentary':
			if( !($session['user']['superuser'] & SU_GIVES_YOM_WARNING) && get_module_pref('allow') != 1 )
			{
				$commentline = $args['commentline'];
				$search = array('[url=','[url]','[/url]','[b]','[/b]','[u]','[/u]','[o]','[/o]','[i]','[/i]','[s]','[/s]','[f]','[/f]',']');
				$commentline = str_replace($search, '', $commentline);
				$args['commentline'] = $commentline;
			}
		break;

		case 'viewcommentary':
			// The following code (with added extras) was taken from...
			//
			// bbcode.php
			// (C) 2001 The phpBB Group
			// support@phpbb.com
			//
			$patterns = array();
			$replacements = array();
			// matches a [url]xxxx://www.phpbb.com[/url] code.
			$patterns[] = "#\[url\]((.*?))\[/url\]#is";
			$replacements[] = '<a href="\\1" target="_blank">\\1</a>';
			// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
			$patterns[] = "#\[url\]((.*?))\[/url\]#is";
			$replacements[] = '<a href="\\1" target="_blank">\\1</a>';
			// [url=xxxx://www.phpbb.com]phpBB[/url] code..
			$patterns[] = "#\[url=([^\[]+?)\](.*?)\[/url\]#is";
			$replacements[] = '<a href="\\1" target="_blank">\\2</a>';
			// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
			$patterns[] = "#\[url=([^\[]+?)\](.*?)\[/url\]#is";
			$replacements[] = '<a href="\\1" target="_blank">\\2</a>';
			// [b] and [/b] for bolding text.
			$patterns[] = "#\[b\](.*?)\[/b\]#si";
			$replacements[] = '<span style="font-weight: bold">\\1</span>';
			// [u] and [/u] for underlining text.
			$patterns[] = "#\[u\](.*?)\[/u\]#si";
			$replacements[] = '<span style="text-decoration: underline">\\1</span>';
			// [o] and [/o] for overlining text.
			$patterns[] = "#\[o\](.*?)\[/o\]#si";
			$replacements[] = '<span style="text-decoration: overline">\\1</span>';
			// [i] and [/i] for italicizing text.
			$patterns[] = "#\[i\](.*?)\[/i\]#si";
			$replacements[] = '<span style="font-style: italic">\\1</span>';
			// [s] and [/s] for line-through text, strikeout.
			$patterns[] = "#\[s\](.*?)\[/s\]#si";
			$replacements[] = '<span style="text-decoration: line-through">\\1</span>';
			// [f] and [/f] for flashing text, blick.
			$patterns[] = "#\[f\](.*?)\[/f\]#si";
			$replacements[] = '<span style="text-decoration: blink">\\1</span>';
			$commentline = $args['commentline'];
			$text = preg_replace($patterns, $replacements, $commentline);
			$args['commentline'] = $text;
		break;
	}

	return $args;
}
?>