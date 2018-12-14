<?php
class IDC_Modules {
	public static $PHP_EXTENSION = '.php';

	public function load_modules() {
		// Getting the list of modules available
		$modules = $this->get_available_modules();

		foreach ($modules as $module) {
			if ($module->is_active) {
				$this->load_module($module);
			}
		}
	}

	public function get_available_modules() {
		// Getting modules available with the key we have from the server, but rightnow
		// making it ourselves
		$modules = get_option('idc_modules');

		$charity = (object) array(
			"is_active" => false,
			"class" => ""
		);
		$modules['charity'] = $charity;

		return $modules;
	}

	public function load_module($module) {
		// Loading the class file of the module
		require_once dirname(__FILE__) .'/' . $module->class . self::$PHP_EXTENSION;
	}
}
$idc_modules = new IDC_Modules();
$idc_modules->load_modules();