<?php  defined('BASEPATH') or exit('No direct script access allowed');
/*
	Copyright (c) 2011 Lonnie Ezell

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

class Settings_lib
{
	protected $ci;

	/**
	 * Settings cache
	 *
	 * @var	array
	 */
	private static $cache = array();

	/**
	 * The Settings Construct
	 */
	public function __construct()
	{

		$this->ci =& get_instance();
		$this->ci->load->model('settings/settings_model');

		$this->find_all();
	}

	/**
	 * Getter
	 *
	 * Gets the setting value requested
	 *
	 * @param	string	$name
	 */
	public function __get($name)
	{
		return self::get($name);
	}

	/**
	 * Setter
	 *
	 * Sets the setting value requested
	 *
	 * @param	string	$name
	 * @param	string	$value
	 * @return	bool
	 */
	public function __set($name, $value)
	{
		return self::set($name, $value);
	}

	/**
	 * Gets a setting.
	 *
	 * @param	string	$name
	 * @return	bool
	 */
	public static function item($name)
	{
		$ci =& get_instance();

		if(isset(self::$cache[$name]))
		{
			return self::$cache[$name];
		}

		$setting = $ci->settings_model->find_by('name', $name);

		// Setting doesn't exist, maybe it's a config option
		$value = $setting ? $setting->value : config_item($name);

		// Store it for later
		self::$cache[$name] = $value;

		return $value;
	}

	/**
	 * Set
	 *
	 * Sets a config item
	 *
	 * @param string $name   Name of the setting
	 * @param string $value  Value of the setting
	 * @param string $module Name of the module
	 *
	 * @return bool
	 */
	public static function set($name, $value, $module='core')
	{
		$ci =& get_instance();

		if (isset(self::$cache[$name]))
		{
			$setting = $ci->settings_model->update_where('name', $name, array('value' => $value));
		}
		else
		{
			// insert
			$data = array(
				'name'   => $name,
				'value'  => $value,
				'module' => $module,
			);

			$setting = $ci->settings_model->insert($data);
		}

		self::$cache[$name] = $value;

		return TRUE;
	}

	/**
	 * Delete config item
	 *
	 * @param string $name   Name of the setting
	 * @param string $module Name of the module
	 *
	 * @return bool
	 */
	public static function delete($name, $module='core')
	{
		$ci =& get_instance();

		if (isset(self::$cache[$name]))
		{
			$data = array(
				'name'   => $name,
				'module' => $module,
			);

			if ($ci->settings_model->delete_where($data))
			{
				unset(self::$cache[$name]);

				return TRUE;
			}
		}

		return FALSE;
	}


	/**
	 * All
	 *
	 * Gets all the settings
	 *
	 * @return	array
	 */
	public function find_all()
	{
		if(self::$cache)
		{
			return self::$cache;
		}

		$settings = $this->ci->settings_model->find_all();

		foreach($settings as $setting)
		{
			self::$cache[$setting->name] = $setting->value;
		}

		return self::$cache;
	}


	/**
	 * Find By
	 *
	 * Gets setting for specific search criteria. For multiple matches, see
	 * find_all_by.
	 *
	 * @param   $field  Setting column name
	 * @param   $value  Value ot match
	 *
	 * @return	array
	 */
	public function find_by($field=null, $value=null)
	{

		$settings = $this->ci->settings_model->find_by($field, $value);

		foreach($settings as $setting)
		{
			self::$cache[$setting['name']] = $setting['value'];
		}

		return $settings;
	}


	/**
	 * Find All By
	 *
	 * Gets all the settings based on search criteria. For a single setting
	 * match, see find_by
	 *
	 * @see		find_by
	 *
	 * @param   $field  Setting column name
	 * @param   $value  Value ot match
	 *
	 * @return	array
	 */
	public function find_all_by($field=null, $value=null)
	{

		$settings = $this->ci->settings_model->find_all_by($field, $value);

		if (is_array($settings) && count($settings))
		{
			foreach($settings as $key => $value)
			{
				self::$cache[$key] = $value;
			}
		}

		return $settings;
	}
}

/* End of file Settings.php */
