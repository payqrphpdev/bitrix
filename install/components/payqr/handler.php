<?php

/**
 * Скрипт принимает и обрабатывает уведомления от PayQR
 */
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");
CModule::IncludeModule("subscribe");

global $USER, $DB;

require_once __DIR__ . "/PayqrConfig.php"; // подключаем основной класс

try
{
    $receiver = new PayqrReceiver();
    $receiver->handle();
}
catch (PayqrExeption $e)
{
    PayqrLog::log($e->response);
}
