<?
IncludeModuleLangFile(__FILE__);

if(class_exists("payqr")) return;

class payqr extends CModule
{
    var $MODULE_ID = "payqr";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "Y";

    public function payqr()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");

        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        else
        {
            $this->MODULE_VERSION = PAYQR_VERSION;
            $this->MODULE_VERSION_DATE = PAYQR_VERSION_DATE;
        }

        $this->MODULE_NAME = GetMessage("PAYQR_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("PAYQR_MODULE_DESCRIPTION");
    }

    public function DoInstall()
    {
        $this->InstallFiles();
        $this->InstallDB();
        $this->InstallPayQRPaySystem();
        $GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage("PAYQR_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/payqr/install/step1.php");
    }

    public function InstallPayQRPaySystem()
    {
        global $DB;

        $SITE_ID = 'ru';

        $dbSite = CSite::GetByID($SITE_ID);

        if (!$dbSite->Fetch())
        {
            $by = 'sort';
            $order = 'asc';
            $dbSite = CSite::GetList($by, $order);
            $arSite = $dbSite->Fetch();
            $SITE_ID = $arSite['ID'];
        }

        if(CModule::IncludeModule("sale"))
        {
            $psID = 0;

            $db_ptype = CSalePaySystem::GetList($arOrder = Array("SORT"=>"ASC"), Array("LID"=> ($SITE_ID == 'ru'? 's1': $SITE_ID), "CURRENCY"=>"RUB", "ACTIVE"=>"Y", "NAME" => "PayQR"));

            if($psRes = $db_ptype->Fetch())
            {
                $psID = $psRes["ID"];
            }
            else
            {
                $description = "Платежная система PayQR, самый быстрый способ оплаты телефоном!";

                if(defined('LANG_CHARSET'))
                {
                    $description = mb_convert_encoding($description, LANG_CHARSET);
                }

                $arFields = array(
                    "LID"      => ($SITE_ID == 'ru'? 's1': $SITE_ID),
                    "CURRENCY" => "RUB",
                    "NAME"     => "PayQR",
                    "ACTIVE"   => "Y",
                    "DESCRIPTION" => $description
                );
                if(!($psID = CSalePaySystem::Add($arFields)))
                {
                    return false;
                }
            }
            if($DB->Query("SELECT * FROM b_payqr_items", true) && $psID)
            {
                $DB->Update("b_payqr_items", array("htmlvalue" => "'".$psID."'"), "WHERE name='bitrix_payqr_paysystem_id'", __LINE__, false, true );
            }
        }
    }

    public function UnInstallPayQRPaySystem()
    {
        $SITE_ID = 'ru';
        $dbSite = CSite::GetByID($SITE_ID);
        if (!$dbSite->Fetch())
        {
            $by = 'sort';
            $order = 'asc';
            $dbSite = CSite::GetList($by, $order);
            $arSite = $dbSite->Fetch();
            $SITE_ID = $arSite['ID'];
        }

        if(CModule::IncludeModule("sale"))
        {
            $db_ptype = CSalePaySystem::GetList($arOrder = Array("SORT"=>"ASC"), Array("LID"=>($SITE_ID == 'ru'? 's1': $SITE_ID), "CURRENCY"=>"RUB", "ACTIVE"=>"Y", "NAME" => "PayQR"));

            if($psRes = $db_ptype->Fetch())
            {
                CSalePaySystem::Delete($psRes["ID"]);
            }
        }
    }

    public function InstallDB()
    {
        global $DB, $APPLICATION;

        $this->errors = false;
        if(!$DB->Query("SELECT * FROM b_payqr_items", true))
            $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/payqr/install/db/".strtolower($DB->type)."/install.sql");

        if($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return false;
        }

        RegisterModule("payqr");

        return true;
    }

    public function InstallFiles()
    {
        if($_ENV['COMPUTERNAME']!='BX')
        {
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/payqr/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/payqr/install/components/payqr", $_SERVER["DOCUMENT_ROOT"]."/payqr", true, true);

            //Производим настройку конфигурации подключения
            require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/dbconn.php");

            if(is_file($_SERVER["DOCUMENT_ROOT"] . "/payqr/module/orm/PayqrModuleDbConfig.php") && is_writeable($_SERVER["DOCUMENT_ROOT"] . "/payqr/module/orm/PayqrModuleDbConfig.php"))
            {
                $fileConfig = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/payqr/module/orm/PayqrModuleDbConfig.php");

                if(strlen($fileConfig) > 0 && isset($DBHost, $DBLogin, $DBPassword, $DBName))
                {
                    $new_value = str_replace(array("#login#", "#password#", "#database#", "#host#", "#prefix#"), array($DBLogin, $DBPassword, $DBName, $DBHost, "b_"), $fileConfig);

                    file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/payqr/module/orm/PayqrModuleDbConfig.php", $new_value);
                }
            }
            //-->

            $SITE_ID = 'ru';
            $dbSite = CSite::GetByID($SITE_ID);
            if (!$dbSite->Fetch())
            {
                $by = 'sort';
                $order = 'asc';
                $dbSite = CSite::GetList($by, $order);
                $arSite = $dbSite->Fetch();
                $SITE_ID = $arSite['ID'];
            }

            $templates = array();
            $rsTemplates = CSite::GetTemplateList($SITE_ID);

            while($arTemplate = $rsTemplates->Fetch())
            {
                $templates[]  = $arTemplate;
            }

            //Находим шаблон сайта, который настраивает все CONDITION
            foreach($templates as $template)
            {
                //Наиболее подходящее условие
                if(empty($template["CONDITION"]) && !empty($template["TEMPLATE"]))
                {
                    //Копируем кастомзированную корзину
                    CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/payqr/install/components/sale.basket.basket", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".$template["TEMPLATE"]."/components/bitrix/sale.basket.basket", true, true);
                }
            }
            //
            //-->
        }
        return true;
    }

    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION, $step;
        $step = IntVal($step);
        $this->UnInstallDB(array("savedata" => $_REQUEST["savedata"]));
        $this->UnInstallFiles();
        $this->UnInstallPayQRPaySystem();
        $APPLICATION->IncludeAdminFile(GetMessage("PAYQR_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/payqr/install/unstep1.php");
    }

    function UnInstallDB($arParams = Array())
    {
        global $APPLICATION, $DB, $errors;

        $this->errors = false;

        if (!$arParams['savedata'])
            $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/payqr/install/db/".strtolower($DB->type)."/uninstall.sql");

        $arSQLErrors = Array();
        if(is_array($this->errors))
            $arSQLErrors = array_merge($arSQLErrors, $this->errors);

        if(!empty($arSQLErrors))
        {
            $this->errors = $arSQLErrors;
            $APPLICATION->ThrowException(implode("", $arSQLErrors));
            return false;
        }

        UnRegisterModule("payqr");

        return true;
    }

    function UnInstallFiles($arParams = array())
    {
        DeleteDirFilesEx("payqr");

        $SITE_ID = 'ru';
        $dbSite = CSite::GetByID($SITE_ID);
        if (!$dbSite->Fetch())
        {
            $by = 'sort';
            $order = 'asc';
            $dbSite = CSite::GetList($by, $order);
            $arSite = $dbSite->Fetch();
            $SITE_ID = $arSite['ID'];
        }

        $templates = array();
        $rsTemplates = CSite::GetTemplateList($SITE_ID);

        while($arTemplate = $rsTemplates->Fetch())
        {
            $templates[]  = $arTemplate;
        }

        //Находим шаблон сайта, который настраивает все CONDITION
        foreach($templates as $template)
        {
            //Наиболее подходящее условие
            if(empty($template["CONDITION"]) && !empty($template["TEMPLATE"]) && is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".$template["TEMPLATE"]."/components/bitrix/sale.basket.basket"))
            {
                //Копируем кастомзированную корзину
                $result = DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/".$template["TEMPLATE"]."/components/bitrix/sale.basket.basket");
            }
        }
        //
        //-->

        return true;
    }
}