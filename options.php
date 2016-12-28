<?
global $MESS;
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/payqr/lib/Button.php");
$button = new Button();

$module_id = "payqr";
CModule::IncludeModule($module_id);

$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($MOD_RIGHT>="R"):

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_PAYQR_SETTINGS"),  "ICON" => "support_settings", "TITLE" => GetMessage("MAIN_TAB_MARKET_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_BUTTON_SETTINGS"), "ICON" => "support_settings", "TITLE" => GetMessage("MAIN_TAB_BUTTON_SET")),
	array("DIV" => "edit3", "TAB" => GetMessage("MAIN_TAB_QUERY_SETTINGS"),  "ICON" => "support_settings", "TITLE" => GetMessage("MAIN_TAB_BUTTON_SET")),
);

//сохраняем настройки
$button->save($_POST, $DB);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

    <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>">
<?
    $tabControl->BeginNextTab();
    $button->show_('payqr.gateway.schema.xml', $DB, 1);

    $tabControl->BeginNextTab();
    $button->show_('payqr.button.schema.xml', $DB, 2);

    $tabControl->BeginNextTab();
    $button->show_('payqr.query.schema.xml', $DB, 3);

    $tabControl->End();
?>
<?echo bitrix_sessid_post()?>
</form>
<?endif; //if($MOD_RIGHT>="R"):?>