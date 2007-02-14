<?php
//Compiler test

$input='<img src="{I_TEST}" border="0" alt="{L_TEST}" />';

class template
{
	var $_tpldata;
	
	function template ()
	{
		$_tpldata = array();
	}
	
	function add_vars ($vars)
	{
		if(!is_array($vars))
		{
			return;
		}
		while(list($key, $val) = @each($vars))
		{
			$this->_tpldata['vars'][$key] = $val;
		}
	}
	function pparse ($input)
	{
		return eval('?>' . compile_tags($input) . '<?php ');
		/*eval(' ?>' . $this->compiled_code[$handle] . '<?php '); */
	}
}
class tpl_compiler
{
	function tpl_compiler ()
	{
	
	}
	
	
}

function compile_tags (&$block)
	{
		// change template varrefs into PHP varrefs
		$varrefs = array();

		// This one will handle varrefs WITH namespaces
		preg_match_all('#\{(([a-z0-9\-_]+?\.)+?)([a-z0-9\-_]+?)\}#is', $block, $varrefs);

		$count_varrefs_1 = count($varrefs[1]);
		for ($j = 0; $j < $count_varrefs_1; $j++)
		{
			$namespace = $varrefs[1][$j];
			$varname = $varrefs[3][$j];
			$new = $this->generate_block_varref($namespace, $varname);

			$block = str_replace($varrefs[0][$j], $new, $block);
		}

		// This will handle the remaining root-level varrefs
		$block = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "<?php echo \$this->_tpldata['vars']['\\1']; ?>", $block);

		return $block;
	}
$template = new template();
$template->add_vars(array(
	'I_TEST' => 'image.gif',
	'L_TEST' => 'Test Image',
));
echo "<html><head><title>Test Page</title></head><body>";
echo $template->pparse($input);
echo "</body></html>";
?>
<!-- BEGIN online -->
EQUALS
<?
	$_online_count = (isset($this->_tpldata['stats'][$_stats_i]['online'])) ? sizeof($this->_tpldata['stats'][$_stats_i]['online']) : 0;
	if ($_online_count) 
	{
		for ($_online_i = 0; $_online_i < $_online_count; $_online_i++)
		{
?>
<!-- END stats -->
EQUALS
<?php 
		}
	}
?>

{L_VIEWONLINE}
EQUALS
<?php echo $this->_tpldata['.'][0]['L_VIEWONLINE']; ?>

{stats.online.U_VIEW_PROFILE}
EQUALS
<?php echo $this->_tpldata['stats'][$_stats_i]['online'][$_online_i]['U_VIEW_PROFILE']; ?>