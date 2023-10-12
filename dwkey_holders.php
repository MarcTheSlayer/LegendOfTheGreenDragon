<?php
function dwkey_holders_getmoduleinfo()
{
	$info = array(
		"name"=>"Dwelling Key Holders",
		"description"=>"Allow dwelling keyholders see who else has a dwelling key.",
		"version"=>"0.0.1",
		"author"=>"`@MarcTheSlayer `2for `#C`&on`#t`&ess`#a",
		"category"=>"Dwellings",
		"download"=>"http://dragonprime.net/index.php?topic=10378.0",
		"requires"=>array(
			"dwellings"=>"20060105|By Sixf00t4, available on DragonPrime"
		)
	);
	return $info;
}

function dwkey_holders_install()
{
	output("`c`b`Q%s 'dwkey_holders' Module.`b`n`c", translate_inline(is_module_active('dwkey_holders')?'Updating':'Installing'));
	module_addhook_priority('dwellings-inside',90);
	return TRUE;
}

function dwkey_holders_uninstall()
{
	output("`n`c`b`Q'dwkey_holders' Module Uninstalled`0`b`c");
	return TRUE;
}

function dwkey_holders_dohook($hookname,$args)
{
	// We don't want our link showing up if another module has for whatever reason blocked this nav link.
	// Hook priority is set low for this reason, we want these modules to go first. :)
	if( !is_blocked('runmodule.php?module=dwellings&op=keys&subop=giveback&dwid='.$args['dwid']) )
	{
		addnav('Dwellings Extras');
		addnav('Key Holders','runmodule.php?module=dwkey_holders&dwid='.$args['dwid']);
	}

	return $args;
}

function dwkey_holders_run()
{
	global $session;

	page_header('Fellow Key Holders');

	output('`n`3You pick up the book showing the names of all the current key holders and take a look.`0`n`n');

	$dwid = httpget('dwid');

	$sql = "SELECT a.name
			FROM " . db_prefix('accounts') . " a, " . db_prefix('dwellingkeys') . " b
			WHERE b.dwid = '$dwid'
				AND a.acctid = b.keyowner
			ORDER BY b.keyid ASC";
	$result = db_query($sql);

	$kholder = translate_inline('Key Holder');
	rawoutput('<table border="0" cellpadding="2" cellspacing="1" bgcolor="#999999" align="center">');
	rawoutput('<tr class="trhead"><td align="center">#</td><td align="center">'.$kholder.'</td>');

	$i = 1;
	while( $row = db_fetch_assoc($result) )
	{
		rawoutput('<tr class="'.($i%2?'trdark':'trlight').'"><td align="center">'.$i.'</td><td>');
		output_notl('%s', $row['name']);
		rawoutput('</td></tr>');
		$i++;
	}

	rawoutput('</table>');

	$sql = "SELECT type
			FROM " . db_prefix('dwellings') . " 
			WHERE dwid = '$dwid'";
	$result = db_query($sql);
	$row = db_fetch_assoc($result);

	addnav('Return');
	addnav(array('Back to the %s',dwkey_holders_ucfirst(get_module_setting('dwname',$row['type']))),'runmodule.php?module=dwellings&op=enter&dwid='.$dwid);

	page_footer();
}

function dwkey_holders_ucfirst($name)
{
	$start = '';
	if( substr($name, 0, 1) == '`' )
	{
		$start = substr($name, 0, 2);
		$length = strlen($name);
		$name = substr($name, 2, ($length-2));
	}
	return $start . ucfirst($name);
}
?>