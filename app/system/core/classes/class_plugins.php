<?php
//
//	file: includes/class_plugins
//	begin: 01/01/2006
//	$Author$
//	$Revision$
//	$Date$
//
//	description: simple STATIC plugin handling class

if (!defined('PLAY_MUSIC'))
{
	die('No peeksies!');
}

static class plugins
{
	// add $object to $point
	public function add ($point, $object, $file='')
	{
		global $plugs, $config;

		if (!empty($file))
		{
			$config->include($file);
		}

		$plugs[$point][$object] = new $object();

		return true;
	}

	// removes $object from $point
	public function remove ($point, $object)
	{
		global $plugs;

		if ($plugs[$point][$object])
		{
			unset($plugs[$point][$object]);
		}

		return true;
	}

	// runs $method on all plugs asociated with $point
	// it also has the optional $args for passing any arguments to the method
	public function apply ($point, $method, $args='')
	{
		global $plugs;

		if ($plugs[$point])
		{
			foreach ($plugs[$point] as $name => $object)
			{
				if(method_exists($object, $method))
				{
					$object->$method($args);
				}
			}
		}

		return true;
	}
}

$plugs = array();

/*
Usage:  In the root plugin file, first declare any plugins::add(__POINT__, $plugin)
and plugins::remove(__POINT__, $plugin) you may have.
The components will automatically call
plugins::apply(__POINT__, 'some_method', __EXTRA_ARGUMENTS__). This will then
call $plugin->some_method(__ANY_ARGUMENTS__).  You should not be editing the
core files at all!  Components should have plugin points where possible.  If one
does not, contact that author for inclusion.