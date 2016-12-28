<?php

class Button
{
    private $ShowInPlace = array("cart"/*, "product", "category"*/);

	private $buttonXmlStructure = array();

	/**
	 * @var string таблица в базе данных
	 */
	private $table = 'b_payqr_items';

	/**
     * Выводит контент модуля
     * @param $structure_file
     * @param $DB
     * @param $tab
     */
    public function show_($structure_file, $DB, $tab = 1)
	{
        $xml_structure = $this->getStructure($structure_file);

        //инициализируем общие настройки кнопки
        $html = "";

        $payqrButtonSettings = $this->getSettings($DB);

        if(in_array($tab, array(1,3)))
        {
            foreach($xml_structure as $row)
            {
                if(isset($row->field) && !$this->buttonStructure($row))
                {
                    $settings[(string)$row->field[0]["value"]] = $payqrButtonSettings[(string)$row->field[0]["value"]];

                    $html.= $this->generateHtml($row, $settings);

                    unset($settings);
                }
            }
        }
        else
        {
            //инициализиурем параметры кнопки в соответствии с местом отображения
            foreach($this->ShowInPlace as $place)
            {
                foreach($xml_structure as $row)
                {
                    if(isset($row->field) && $this->buttonStructure($row))
                    {
                        $settings[$place . "_" . (string)$row->field[0]["value"]] = $payqrButtonSettings[$place . "_" . (string)$row->field[0]["value"]];

                        $html.= $this->generateHtml($row, $settings, $place);

                        unset($settings);
                    }
                }
            }
        }

        $html.= "<tr><td colspan='2'><input type='submit'></td>";

        echo $html;
	}

	/**
     * @param $xmlRow
     * @param $settings
     * @param bool $place - Для какого места (карточка товара, корзина, категория товара) будет настраиваться настройка
     * @return string
     */
    private function generateHtml($xmlRow, $settings, $place = false)
    {
        $html = "";
        
        $button_option = $xmlRow->field;

        $html .= "<tr class=''>";
            $html .= "<td width='30%'>";
            //$html .= iconv("utf-8", "windows-1251", $button_option[4]['value']);
            $html .= mb_convert_encoding($button_option[4]['value'], SITE_CHARSET, "utf-8");// ("utf-8", "windows-1251", $button_option[4]['value']);
            $html .= "</td>";

            $html .= "<td width='70%'>";

            $fieldName = (string)($place ? $place . "_" . $button_option[0]['value'] : $button_option[0]['value']);

            switch ($button_option[1]['value'])
            {
                case 'text':
                    $html .= "<input type='text' name='".$fieldName."' value='" . (isset($settings[$fieldName])? $settings[$fieldName] : $button_option[2]['value']) ."' ".
                                                            ($button_option[5]['value'] == "0" ? "disabled='disabled'" : "") . ">";
                    break;
                case 'select':
                    $select = json_decode($button_option[3]['value'], true);

                    $html .= "<select name='".$fieldName."' ". ($button_option[5]['value'] == "0"? "disabled='disabled'" : "") .">";

                    foreach($select as $key => $element) {

						$html .= "<option value='";

                    	if(isset($settings[$fieldName]) && !empty($settings[$fieldName]) && /*$button_option[2]['value']*/ $key == $settings[$fieldName])
                    	{
                    		$html .= $key . "' selected";
                    	}
                    	else 
                    	{
                    		$html .= $key. "' ";
                    	}

                    	$html .= ">" . iconv("utf-8", "windows-1251", $element) . "</option>";

                    }
                    $html .= "</select>";
                    break;
            }

            $html .= "</td>";
        $html .= "</tr>";
        
        return $html;
    }


    /**
     * Получение структуры кнопки
     * @param $structure_file
     * @return array|SimpleXMLElement
     */
    private function getStructure($structure_file)
    {
            $path = __DIR__ . '/';

            if(file_exists( $path . $structure_file))
            {
                $xmlObject = new SimpleXMLElement(file_get_contents($path . $structure_file));

                return $xmlObject ? $xmlObject : array();
            }
    }

    /**
     * Проверяет, является поле структурой кнопки
     * @param $xmlRow
     * @return bool
     */
    private function buttonStructure($xmlRow)
    {
        $button_option = $xmlRow->field;

        $fieldName = $button_option[0]['value'];

        if(strpos($fieldName, "button") !== false)
        {
            $this->buttonXmlStructure[] = $xmlRow;

            return true;
        }
        return false;
    }

    /**
     * @param $post
     * @param $DB
     */
    public function save($post, $DB)
    {
        $cache = array();

        foreach($post as $key => $value)
        {
            try {
                $DB->Update($this->table, array("htmlvalue" => "'".$DB->ForSql($value)."'"), "WHERE name ='".$key."'", __LINE__, false, true);

                //Приводим к нужно кодировке
                $cache[$key] = mb_convert_encoding($value, "utf-8", "windows-1251");
            }
            catch(Exception $e)
            {

            }
        }

        //Инициализируем таблицы модуля
        if(isset($post["merchant_id"]))
        {
            $DB->Update("b_payqr_user", array("merch_id" => "'".$DB->ForSql($post["merchant_id"])."'", "settings" => "'".$DB->ForSql(json_encode($cache, JSON_UNESCAPED_UNICODE))."'"), "WHERE user_id=1", __LINE__, false, true );
        }
        //

        //Производим пересчет в cache-файле
        $cache_path = $_SERVER["DOCUMENT_ROOT"] . "/payqr/cache";

        if(is_dir($cache_path) && is_writeable($cache_path))
        {
            if(file_put_contents($cache_path . "/" . "settings.json", json_encode($cache, JSON_UNESCAPED_UNICODE)) === false)
            {
                echo "Ошибка создания cache-файла, при работе платежной системы могут возникать задержки, для этого убедитесь, что директория: '" . $cache_path. "' - доступна для перезаписи !";
            }
        }

    }

    /**
     * @param $DB
     * @return array
     */
    public function getSettings($DB)
    {
        $payqr_settings = array();

        $strSQL = "SELECT * FROM " . $this->table;

        $res = $DB->Query($strSQL, false, __LINE__);

        while($arElement = $res->GetNext())
        {
            $payqr_settings[$arElement["name"]] = $arElement["htmlvalue"];
        }

        return $payqr_settings;
    }
}