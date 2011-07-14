<?php

namespace DbConfig;

class DbConfig {

	public static $loaded_keys = array();
	
	public static $items = array();

	public static $table = null;


	private static function _get_by_key($key)
	{
		return \DB::select('value')->from(static::$table)->where('key', $key)->limit(1)->execute();
	}

	public static function load($key, $group = null, $reload = false)
	{
		if (in_array($key, static::$loaded_keys) and ! $reload)
		{
			return false;
		}
		
		if ( ! $config = static::_get_by_key($key)->current())
		{
			return false;
		}

		static::$items[$key] = json_decode($config['value'], true);

		if (is_array(static::$items[$key]))
		{
			static::$loaded_keys[] = $key;
			return true;
		}
		
		return false;
	}
	
	public static function save($key, $config)
	{
		if ( ! is_array($config))
		{
			return false;
		}
		
		$config = json_encode($config);

		if (count(static::_get_by_key($key)) > 0)
		{
			return \DB::update(static::$table)->value('value', $config)->where('key', $key)->execute();
		}
		else
		{
			return \DB::insert(static::$table)->set(array('key' => $key, 'value' => $config))->execute();
		}
	}
	
	public static function get($item, $default = null)
	{
		if (isset(static::$items[$item]))
		{
			return static::$items[$item];
		}
		
		if (strpos($item, '.') !== false)
		{
			$parts = explode('.', $item);

			switch (count($parts))
			{
				case 2:
					if (isset(static::$items[$parts[0]][$parts[1]]))
					{
						return static::$items[$parts[0]][$parts[1]];
					}
				break;

				case 3:
					if (isset(static::$items[$parts[0]][$parts[1]][$parts[2]]))
					{
						return static::$items[$parts[0]][$parts[1]][$parts[2]];
					}
				break;

				case 4:
					if (isset(static::$items[$parts[0]][$parts[1]][$parts[2]][$parts[3]]))
					{
						return static::$items[$parts[0]][$parts[1]][$parts[2]][$parts[3]];
					}
				break;

				default:
					$return = false;
					foreach ($parts as $part)
					{
						if ($return === false and isset(static::$items[$part]))
						{
							$return = static::$items[$part];
						}
						elseif (isset($return[$part]))
						{
							$return = $return[$part];
						}
						else
						{
							return $default;
						}
					}
					return $return;
				break;
			}
		}

		return $default;
	}
	
	public static function set($item, $value)
	{
		$parts = explode('.', $item);

		switch (count($parts))
		{
			case 1:
				static::$items[$parts[0]] = $value;
			break;

			case 2:
				static::$items[$parts[0]][$parts[1]] = $value;
			break;

			case 3:
				static::$items[$parts[0]][$parts[1]][$parts[2]] = $value;
			break;

			case 4:
				static::$items[$parts[0]][$parts[1]][$parts[2]][$parts[3]] = $value;
			break;

			default:
				$item =& static::$items;
				foreach ($parts as $part)
				{
					// if it's not an array it can't have a subvalue
					if ( ! is_array($item))
					{
						return false;
					}

					// if the part didn't exist yet: add it
					if ( ! isset($item[$part]))
					{
						$item[$part] = array();
					}

					$item =& $item[$part];
				}
				$item = $value;
			break;
		}

		return true;
	}

	public static function _init()
	{
		\Config::load('dbconfig');

		static::$table = \Config::get('dbconfig.db.table', 'config');
		
		$installed = \Config::get('dbconfig.db.installed', false);

		if ( ! $installed)
		{
			if ( ! static::_install_db())
			{
				throw new \Exception('Could not create configuration table.');
			}
			
			\Config::set('dbconfig.db', array('table' => static::$table, 'installed' => true));
			\Config::save('dbconfig', \Config::get('dbconfig'));
		}
	}

	private static function _install_db()
	{
		$rows = \DBUtil::create_table(static::$table, array(
			'id'    => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true),
			'key'   => array('constraint' => 30, 'type' => 'varchar', 'null' => false),
			'value' => array('type' => 'text', 'null' => false),
		), array('id'));
		
		return ($rows > 0);
	}
}