<?php
/**
 * FormaPlugin class
 * Interface for Forma Plugins 
 */

class FormaPlugin {
    static function getName(){
        $reflector = new ReflectionClass(static::class);
        $fn = $reflector->getFileName();
        $name=basename(dirname($fn));
        return $name;
    }
    public function addSetting($name, $type, $size, $value="", $sequence=0){
        $pg_adm=new PluginAdm();
        $plugin_info=$pg_adm->getPluginFromDB(self::getName(),'name');
        $query_insert_string="INSERT %adm_setting (param_name,param_value,value_type,max_size,pack,regroup,sequence) VALUES ";
        $query_insert_string.="('".$name."','".$value."','".$type."',".$size.",'".$plugin_info['name']."',".$plugin_info['regroup'].",".$sequence.")";
        sql_query($query_insert_string);
        return true;
    }
    public function addRequest($app, $name, $controller, $model){
        $pg_adm=new PluginAdm();
        $plugin_info=$pg_adm->getPluginFromDB(self::getName(),'name');
        $query_insert_string="INSERT %adm_requests (app, name, controller, model, plugin) VALUES ";
        $query_insert_string.="('".$app."','".$name."','".$controller."','".$model."',".$plugin_info['plugin_id'].")";
        sql_query($query_insert_string);
        return true;
    }
/*
    public function addCollapsedMenu($app, $default_name, $image, $module, $request){
        $pg_adm=new PluginAdm();
        $plugin_info=$pg_adm->getPluginFromDB(self::getName(),'name');
        $query_insert_menu_string="INSERT %".$app."_menu (name, image, sequence, collapse, plugin_id) VALUES ";
        $query_insert_menu_string.="('".$default_name."','".$image."',(SELECT MAX(sequence) FROM learning_menu T ) +1 ,'true',".$plugin_info['plugin_id'].")";
        $check=sql_query($query_insert_menu_string);

        $query_insert_menu_under_string="INSERT %".$app."_menu_under (idMenu, module_name, default_name, associated_token, sequence, mvc_path, plugin_id) VALUES ";
        $query_insert_menu_under_string.="(LAST_INSERT_ID(),'".$module."','".$default_name."','view',1,'".$request."',".$plugin_info['plugin_id'].")";
        $check=sql_query($query_insert_menu_under_string);

        return true;
    }

    public function addMenu($app, $default_name, $image, $menu_under_array) {
        $pg_adm=new PluginAdm();
        $plugin_info=$pg_adm->getPluginFromDB(self::getName(),'name');
        $query_insert_menu_string="INSERT %".$app."_menu (name, image, sequence, collapse, plugin_id) VALUES ";
        $query_insert_menu_string.="('".$default_name."','".$image."',(SELECT MAX(sequence) FROM learning_menu T ) +1 ,'false',".$plugin_info['plugin_id'].")";
        $check=sql_query($query_insert_menu_string);
        foreach ($menu_under_array)
    }
*/
}