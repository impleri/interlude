<?php
/**
 * Example extension
 *
 * @author Christopher Roussel <christopher@impleri.net>
 * @version $Id$
 * @package Interlude-Example
 * @filesource
 */

if (!defined('PLAY_MUSIC')) {
	die('Play it from the top, Sammie.');
}

/* This extension pack is meant to provide developers a working guide as to how extensions interface with Interlude. Most of the files in this directory are loaded automatically by il when needed. I will provide detailed descriptions of the usage of each file as they relate to integration with the structures of il. They are meant to be self-contained within their directory, as this one is, in order to make upgrades and removals easier. */

/* This file provides basic package information. It will be loaded during the installation process and when the extensions table is rebuilt. */

$ext = new ilExtension();
$ext->name = 'example'; // Use only alphanumerics, dashes (-), and/or underscores (_). This name must be unique to the extenstion. Best to use the same name as the directory name.
$ext->version = 100; // Must be an integer. Choose your schema wisely. I prefer to simply remove the periods from the 1.0.0 schema so that 159 = 1.5.9 and 1040 = 10.4.0.
$ext->provides = array( // the elements this package provides as NAME
	'libExampleBlog', // I highly recommend keeping a separate library when possible, so other packages can access data this ext manages without needing to hack files or write another library
	'example-blog', // a regular package
	'_CONTENT', // this is a meta package indicating that this extension can provide the primary content section
	'_BLOG'); // Another meta package, this time indicating that this extension provides a blog-like page
$ext->depends = array( // the packages this one _requires_ to operate
	'interlude', // this one only requires Interlude to exist
	'libFoo' => '40', // can include a minimum version (in this case, 40 or 0.4.0 or 0.40 or 0.0.40, depending on schema)
	'bar-blog' => '>90', // can also use basic comparison symbols to indicate greater than/less than and/or equivalence
	'bar-blog' => '<=99', // can also be used multiple times to indicate a range of acceptable versions
	'good-template' => 'eq750'); // can even require a specific version or use textual comparison
	// The comparison symbols il will understand are: <=. lte, <. lt, ==, =, eq, >, gt, >=, gte (default is >=/gte)
$ext->add();