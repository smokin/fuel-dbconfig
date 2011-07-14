<?php

/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

/**
 * FuelPHP DbConfig Package
 *
 * @author     Frank Bardon Jr.
 * @version    1.0
 * @package    Fuel
 * @subpackage DbConfig
 */

Autoloader::add_core_namespace('DbConfig');
//Autoloader::add_namespace('DbConfig');


Autoloader::add_classes(array(
	'DbConfig\\DbConfig'             => __DIR__.'/classes/dbconfig.php',
));


/* End of file bootstrap.php */