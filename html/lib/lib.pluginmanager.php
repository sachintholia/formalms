<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

class PluginManager {
	protected static $plugin_list = array();
    public $category;
	/**
	 * Class constructor, this is a static class, don't call this
	 */
	public function __construct($category) {
        $this->category=$category;
    }

    public function load_lib(){
        include(_lib_."/plugins/".$this->category."/loader.php");
    }

    private static function get_all_plugins($onlyActive = false){
        if (empty(self::$plugin_list)) {
            require_once _adm_ . '/models/PluginmanagerAdm.php';
            $PluginmanagerAdm = new PluginmanagerAdm();
            self::$plugin_list = $PluginmanagerAdm->getPlugins($onlyActive);
        }
        return self::$plugin_list;
    }

    public function run_plugin($plugin,$method,$parameter=array()){
        $category=$this->category;
        if ($category!="") {
            if (self::is_plugin_active($plugin)){
                $this->load_lib();
                if(self::include_plugin_file($plugin, 'Plugin.php')) {
                    if(self::include_plugin_file($plugin, $category . '.php')) {
                        $namespace_class = "Plugin\\" . $plugin. "\\" . $category;
                        if (method_exists($namespace_class, $method)) {
                            return call_user_func_array(array($namespace_class, $method), $parameter);
                        }
                    }
                }
            }
        }
        return false;
    }

    public function run($method,$parameter=array()){
        $return=array();
        $category=$this->category;
        if ($category!="") {
            $plugin_list = self::get_all_plugins(true);
            $this->load_lib();
            foreach ($plugin_list as $class_name) {
                if (self::is_plugin_active($class_name['name'])){
                    if(self::include_plugin_file($class_name['name'], 'Plugin.php')) {
                        if(self::include_plugin_file($class_name['name'], $category . '.php')) {
                            $namespace_class = "Plugin\\" . $class_name['name'] . "\\" . $category;
                            if (method_exists($namespace_class, $method)) {
                                $return[] = call_user_func_array(array($namespace_class, $method), $parameter);
                            }
                        }
                    }
                }
            }
            return $return;
        }
        return false;
    }

    public function get_plugin($plugin,$parameter=array()){
        $category=$this->category;
        if ($category!="") {
            if (self::is_plugin_active($plugin)) {
                $this->load_lib();
                if(self::include_plugin_file($plugin, 'Plugin.php')) {
                    if(self::include_plugin_file($plugin, $category . '.php')) {
                        $namespace_class = "Plugin\\" . $plugin . "\\" . $category;
                        return new $namespace_class($parameter);
                    }
                }
            }
        }
        return false;
    }
    
    private static function is_plugin_active($plugin) {
        
        return in_array($plugin, array_column(self::get_all_plugins(true), 'name'));
    }
    
    private static function include_plugin_file($plugin, $file) {
        
        $path = _plugins_ . '/' . $plugin . '/' . $file;
        if(file_exists($path)) {
            include_once($path);
            return true;
        } else {
            return false;
        }
    }
    
    private static function get_plugin_by_request($mvc_app,$mvc_name) {

        $query =  " SELECT p.name, r.controller, r.model "
                . " FROM %adm_requests r"
                . " INNER JOIN %adm_plugin p"
                . "     ON r.plugin = p.plugin_id"
                . " WHERE 1 = 1"
                . "     AND r.app = '$mvc_app'"
                . "     AND r.name = '$mvc_name'"
                . "     AND p.active = 1"; // TODO: valutare se usare invece funzione is_plugin_active"
        
        $r = sql_query($query);        
        list($plugin,$controller,$model) = sql_fetch_row($r);
        return array($plugin,$controller,$model);
    }
    
    public static function get_feature($mvc_app, $mvc_name) {
        list($plugin,$controller,$model) = self::get_plugin_by_request($mvc_app,$mvc_name);
        if(!isset($plugin) || !isset($controller) || !isset($model)) {
            return false;
        }
        
        if(!self::include_plugin_file($plugin, 'Plugin.php')) {
            return false;
        }
                
        switch(strtolower($mvc_app)) {
            case 'adm':
                $path_controller = _folder_adm_.'/controllers/';
                $path_model = _folder_adm_.'/models/';
                break;
            case 'alms':
                $path_controller = _folder_lms_.'/admin/controllers/';
                $path_model = _folder_lms_.'/admin/models/';
                break;
            case 'lms':
            case 'lobj':
                $path_controller = _folder_lms_.'/controllers/';
                $path_model = _folder_lms_.'/models/';
                break;
            default: return false;
        }

        if(!self::include_plugin_file($plugin, 'features/' . $path_controller . $controller . '.php')) {
            return false;
        }

        self::include_plugin_file($plugin, 'features/' . $path_model . $model . '.php');
        return new $controller($mvc_name);
    }
}

class PluginManagerException extends Exception {}

