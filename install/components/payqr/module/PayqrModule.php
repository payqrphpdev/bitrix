<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PayqrModule
 *
 * @author 1
 */
class PayqrModule 
{
    public static function getBaseUrl()
    {
        $url = "http://{$_SERVER["SERVER_NAME"]}/" . PayqrConfig::$baseUrl;
        return $url;
    }
    public static function redirect($url)
    {        
        $location = PayqrModule::getBaseUrl() . $url;
        header("Location: $location");
    }

        private $options;

    private function setOptions()
    {
        //Включаем кэш
        if(is_file($_SERVER["DOCUMENT_ROOT"] . "/payqr/cache/settings.json") && $settings = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/payqr/cache/settings.json"))
        {
            $settings = json_decode($settings);

            foreach($settings as $key => $item)
            {
                $this->options[$key] = $item;
            }
        }
        else
        {
            $db = PayqrModuleDb::getInstance();
//            $auth = new PayqrModuleAuth();
//            $user = $auth->getUser();
//            if($user)
//            {
//                $query = "select settings from ".PayqrModuleDb::getUserTable()." where user_id={$user->user_id}";
//            }
//            else
            {
                $query = "select settings from ".PayqrModuleDb::getUserTable()." limit 1";
            }
            $result = $db->query($query);

            if(!mb_check_encoding($result->settings, "utf-8"))
            {
                $result->settings = mb_convert_encoding($result->settings, "utf-8", "windows-1251");
            }

            if($settings = json_decode($result->settings))
            {
                foreach($settings as $key => $item)
                {
                    $this->options[$key] = $item;
                }
            }
        }
    }
    
    public function getOption($key)
    {
        if(!$this->options)
        {
            $this->setOptions();
        }
        if(isset($this->options[$key]))
        {
            return $this->options[$key];
        }
    }
}
