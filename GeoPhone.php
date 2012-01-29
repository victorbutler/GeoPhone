<?php
/**
 * This is the main offline phone number geocoding library
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright 2012 Victor Butler
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package   GeoPhone
 * @author    Victor Butler <victorbutler@gmail.com>
 * @copyright 2012 Victor Butler
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 * @link      https://github.com/victorbutler/GeoPhone
 */

/**
 * Geocoding of US phone numbers based on libphonenumber
 * http://code.google.com/p/libphonenumber/source/browse/trunk/resources/geocoding/en/1.txt
 * Source uses the Apache License, Version 2.0
 * Make sure your cache directory is writable if you want to regenerate storage objects
 * Usage: $location = GeoPhone::find('+14089961010');
 * Benchmarks:
 * 		Storage Generation (no search) 0.08574104309082 seconds
 *		Search (no generation) 0.063844919204712 seconds
 *		Storage + Search 0.14285802841187 seconds
 */

class GeoPhone {
	/**
	 * Find a location by given phone number
	 * @param string  Phone number
	 * @return mixed  Can return string or null
	 */
	public static function find($phone) {
		$phone = preg_replace('/[^\d]/', '', $phone);
		$test = $phone;
		$storage = GeoPhoneStorage::factory();
		do {
			$result = $storage->test($test);
			$test = substr($test, 0, -1);
		} while ($result === null && strlen($test) > 0);
		return $result;
	}
}

/**
 * Storage of Patterns and Locations
 * Includes a tester which will attempt to match a pattern to the location
 * Usage: $mystorage = GeoPhoneStorage::factory();
 */
class GeoPhoneStorage {
	/**
	 * @var array  Storage for phone number patterns
	 */
	protected $patterns = array();

	/**
	 * @var array  Storage of locations
	 */
	protected $locations = array();
	
	/**
	 * Creates a new instance of the geocoding storage
	 * @param array  Phone number patterns
	 * @param array  Physical locations
	 * @return void
	 */
	public function __construct($patterns, $locations) {
		$this->patterns = $patterns;
		$this->locations = $locations;
	}

	/**
	 * Test a given pattern with local storage
	 * @param string  Search for this parameter
	 * @return void
	 */
	public function test($pattern) {
		$pattern_result = array_search($pattern, $this->patterns);
		if ($pattern_result === false) {
			return null;
		}
		return $this->locations[$pattern_result];
	}

	/**
	 * @var GeoPhoneStorage
	 */
	static protected $_instance;

	/**
	 * Create the storage object using (libphonenumber)[http://libphonenumber.googlecode.com/svn/trunk/resources/geocoding/en/1.txt]
	 * Creates a cache (optional) of the generated pattern/location key value object
	 * To avoid unnecessary fetching, download the libphonenumber file (above) to cache/1.txt
	 * @param boolean  Force creation
	 * @param boolean  Cache fetched and generated files
	 * @return GeoPhoneStorage
	 * @throws GeoPhone_Exception
	 */
	public static function factory($force = false, $cache_files = true) {
		if (!is_file('cache/GeoPhoneStorage.inc') || $force === true) {
			$pattern_locations = ((!is_file('cache/1.txt') || $force === true) ? file_get_contents('http://libphonenumber.googlecode.com/svn/trunk/resources/geocoding/en/1.txt') : file_get_contents('cache/1.txt'));
			preg_match_all('/(\d+)\|([^\r\n]+)/', $pattern_locations, $matches);
			$patterns = $matches[1];
			$locations = $matches[2];
			self::$_instance = new GeoPhoneStorage($patterns, $locations);
			if ($cache_files === true) {
				if (is_writable('cache')) {
					file_put_contents('cache/1.txt', $pattern_locations);
					file_put_contents('cache/GeoPhoneStorage.inc', serialize(self::$_instance));
				} else {
					throw GeoPhone_Exception('Cache directory is not writable');
				}
			}
		} elseif (is_file('cache/GeoPhoneStorage.inc') && !isset(self::$_instance)) {
			self::$_instance = unserialize(file_get_contents('cache/GeoPhoneStorage.inc'));
		}
		return self::$_instance;
	}
}

class GeoPhone_Exception extends Exception {}
