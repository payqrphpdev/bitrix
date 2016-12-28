<?php

/**
 * Класс для создания заказа
 */
class PayqrOrder 
{
    private $invoice;

    public function __construct(PayqrInvoice &$invoice)
    {
        $this->invoice = $invoice;
    }

    public function createOrder()
    {
        global $DB;

        if (!CModule::IncludeModule("iblock") || !CModule::IncludeModule("sale") || !CModule::IncludeModule("catalog"))
        {
            return false;
        }

        if(($request = $this->getOrderbyInvoice()) && !empty($request))
        {
            PayqrLog::log("Получили результат для функционала ");

            PayqrLog::log(print_r($request, true));

            if(isset($request["order_id"]))
            {
                return $request["order_id"];
            }

            $iteration = 0;

            while(true)
            {
                PayqrLog::log("Ждем пока битрикс сформирует заказ: " . $iteration);

                if($iteration > 5)
                {
                    break;
                }

                sleep(1);

                if($request = $this->getOrderbyInvoice())
                {
                    if(isset($request["order_id"]))
                    {
                        PayqrLog::log("Битрикс сформировал  заказ: " . $request["order_id"]);

                        return $request["order_id"];
                    }
                }
                $iteration++;
            }

            return false;
        }

        PayqrLog::log("Событие не найдено - это новое событие");

        /**
         * Получаем пользовательские данные:
         * @var USER_ID
         * @var FUSER_ID
         */
        $user_data = json_decode($this->invoice->getUserdata());

        $USER_ID = $FUSER_ID = null;

        if(isset($user_data[0]->custom, $user_data[0]->custom->FUSER_ID))
        {
            $FUSER_ID  = $user_data[0]->custom->FUSER_ID;
        }
        if(isset($user_data[0]->custom, $user_data[0]->custom->USER_ID))
        {
            $USER_ID = $user_data[0]->custom->USER_ID;
        }

        $arAddress = $arFieldsOrder = array();

        /**
         * Информация по покупателю
         */
        $customer = $this->invoice->getCustomer();

        if(isset($customer->firstName))
            $arFieldsOrder[1] = $this->convertDataFromPayQR2Bitrix($customer->firstName);

        if(isset($customer->lastName))
            $arFieldsOrder[1] .= ' ' . $this->convertDataFromPayQR2Bitrix($customer->lastName);

        if(isset($customer->email))
            $arFieldsOrder[2] = $this->convertDataFromPayQR2Bitrix($customer->email);

        if(isset($customer->phone))
            $arFieldsOrder[3] = $this->convertDataFromPayQR2Bitrix($customer->phone);

        /**
         * Информация по доставке
         */
        $delivery = $this->invoice->getDelivery();

        if (isset($delivery->street) && !empty($delivery->street))
            $arAddress[] = 'ул. ' . $delivery->street;

        if (isset($delivery->house) && !empty($delivery->house))
            $arAddress[] = 'д. ' .  $delivery->house;

        if (isset($delivery->unit) && !empty($delivery->unit))
            $arAddress[] = 'корп. ' . $delivery->unit;

        if (isset($delivery->building) && !empty($delivery->building))
            $arAddress[] = 'стр. ' . $delivery->building;

        if (isset($delivery->flat) && !empty($delivery->flat))
            $arAddress[] = 'кв. ' . $delivery->flat;

        if (isset($delivery->hallway) && !empty($delivery->hallway))
            $arAddress[] = 'подъезд ' . $delivery->hallway;

        if (isset($delivery->floor) && !empty($delivery->floor))
            $arAddress[] = 'этаж ' . $delivery->floor;

        if (isset($delivery->intercom) && !empty($delivery->intercom))
            $arAddress[] = 'домофон ' . $delivery->intercom;

        if (isset($delivery->city))
            $arFieldsOrder[5] = $this->convertDataFromPayQR2Bitrix($delivery->city);

        $arFieldsOrder[7] .= implode(', ', $arAddress);
        $arFieldsOrder[7] = $this->convertDataFromPayQR2Bitrix($arFieldsOrder[7]);

        /**
         * Создаем пользователя
         */
        if(empty($USER_ID))
        {
            /*
             * Без email-пользователя не сможем создать аккаунт в системе Битрикс
             */
            if(!isset($customer->email) || empty($customer->email))
            {
                PayqrLog::log("Пользователь не ввел свой email, прекращаем работу");

                return false;
            }

            /*
             * Проверим, существует ли такой пользователь в системе и
             * в случае его наличия получаем его USER_ID
             */
            $filter = array("EMAIL" => $customer->email);
            $rsUser = CUser::GetList(($by="id"), ($order="desc"), $filter);
            $usrInfo = $rsUser->GetNext();

            if(isset($usrInfo["ID"]) && !empty($usrInfo["ID"]))
            {
                $USER_ID = $usrInfo["ID"];

                PayqrLog::log("Нашли по email  пользователя: " . $USER_ID);
            }
            else
            {
                $USER = new CUser;

                //пароль пользователя
                $pass = substr(md5(date("Y-m-d h:i:s")), 0, 8);

                $arAuthResult = $USER->Register(
                    $customer->email,
                    isset($customer->firstName)? $this->convertDataFromPayQR2Bitrix($customer->firstName) : $customer->email,
                    isset($customer->lastName)? $this->convertDataFromPayQR2Bitrix($customer->lastName) : $customer->email,
                    $pass,
                    $pass,
                    $customer->email,
                    SITE_ID,
                    "",
                    0);

                if ($arAuthResult != False && $arAuthResult["TYPE"] == "ERROR")
                {
                    return false;
                }
                else
                {
                    CUser::SendUserInfo($USER->GetID(), SITE_ID, GetMessage("INFO_REQ"), true);

                    $mess = 'Вы совершили покупку на сайте http://' . $_SERVER['SERVER_NAME'] . ' Для доступа к сайту используйте следующие учетные данные: </br> Логин: ' .$customer->email. ' Пароль : ' .  $pass;

                    PayqrLog::log($mess);

                    if( mail($customer->email, "Регистрационные данные" , $mess) )
                    {
                        PayqrLog::log("Письмо с регистрационными данными успешно отправлено!");
                    }
                    else
                    {
                        PayqrLog::log("Не получилось отправить письмо с регистрационными данными!");
                    }

                    $USER_ID = $USER->GetID();
                }
                PayqrLog::log("Получили идентификатор нового пользователя: " . $USER_ID);
            }
        }

        /**
         * Получаем корзину этого пользователя из интернет-сайта
         */
        $dbBasketItems = CSaleBasket::GetList(
            array("NAME" => "ASC","ID" => "ASC"),
            array("FUSER_ID" => $FUSER_ID, "LID" => SITE_ID, "ORDER_ID" => "NULL"),
            false,
            false,
            array("ID", "PRODUCT_ID", "PRODUCT_PRICE_ID", "NAME", "QUANTITY", "CAN_BUY", "PRICE"
        ));


        $arBasketItems = array();

        while ($arBasketItem = $dbBasketItems->Fetch())
        {
            if(mb_check_encoding($arBasketItem["NAME"], "windows-1251"))
            {
                $arBasketItem["NAME"] = mb_convert_encoding($arBasketItem["NAME"], "utf-8", "windows-1251");
            }
            $arBasketItems[] = $arBasketItem;
        }

        /**
         * Производим сравнение корзины и тем, что присылается в объекте cart(объект).
         * В случае, если имеется отличие корзины и объекта, корзину оставляем без изменений,
         * а производим покупку по данным из объекта, связываем корзину с заказом и далее
         * восстанавливаем корзину.
         */
        $arCartItems = $this->invoice->getCart();

        if(count($arCartItems) == 0)
        {
            PayqrLog::log("Корзина пустая! ");
            return false;
        }

        PayqrLog::log("1) " . count($arCartItems) . " 2) " . count($arBasketItems));

        $isBasketEqualCart = count($arCartItems) == count($arBasketItems) ? true : false;

        PayqrLog::log("PayQR-корзина");
        PayqrLog::log(print_r($arCartItems, true));
        PayqrLog::log("Битрикс-корзина");
        PayqrLog::log(print_r($arBasketItems, true));

        if($isBasketEqualCart)
        {
            foreach($arBasketItems as $bItems)
            {
                $isBItemEqual = false;

                foreach($arCartItems as $cItems)
                {
                    if($cItems->article == $bItems["PRODUCT_ID"] && $cItems->quantity == $bItems["QUANTITY"])
                    {
                        $isBItemEqual = true;

                        break;
                    }
                }

                if(!$isBItemEqual)
                {
                    $isBasketEqualCart = false;
                    break;
                }
            }
        }

        if(!$isBasketEqualCart)
        {
            PayqrLog::log("Корзины PayQR&Bitrix не равны!");

            //Очищаем корзину и заполняем ее товарами полученными от PayQR
            CSaleBasket::DeleteAll($FUSER_ID);

            foreach($arCartItems as $cItems)
            {
                $PRICE_TYPE_ID = 1;

                //Получаем информацию по товару
                $rsPrice = CPrice::GetList(array(), array('PRODUCT_ID' => $cItems->article, 'CATALOG_GROUP_ID' => $PRICE_TYPE_ID));

                if(!($arPrice = $rsPrice->Fetch()))
                {
                    return false;
                }

                //Преобразуем кодировку
                $iCharSetProduct = $cItems->name;

                $iCharSetProduct = mb_convert_encoding($cItems->name, SITE_CHARSET, "utf-8");

                $arFields = array(
                    "PRODUCT_ID"       => $cItems->article,
                    "PRODUCT_PRICE_ID" => $PRICE_TYPE_ID,
                    "CURRENCY"         => "RUB",
                    "PRICE"            => $arPrice["PRICE"],
                    "QUANTITY"         => $cItems->quantity,
                    "LID"              => SITE_ID,
                    "NAME"             => $iCharSetProduct,
                    "FUSER_ID"         => $FUSER_ID
                );
                CSaleBasket::Add($arFields);
            }
        }
        if($isBasketEqualCart)
        {
            PayqrLog::log("Корзины PayQR&Bitrix равны!");

            $orderId = $this->bitrixCreateOrder($USER_ID, $FUSER_ID, $arFieldsOrder, $arBasketItems);

            if(!$orderId)
            {
                return false;
            }
            return $orderId;
        }

        return false;
    }

    /**
     * @param $USER_ID
     * @param $FUSER_ID
     * @param array $arFieldsOrder
     * @param $arBasketItems
     * @return bool|int
     */
    private function bitrixCreateOrder($USER_ID, $FUSER_ID, $arFieldsOrder = array(), $arBasketItems)
    {
        global $DB;

        $orderId = 0;

        $delivery = $this->invoice->getDelivery();

        //Актуализируем сумму заказа
        $basketAmount = $basketDiscountAmount = 0;

        foreach($arBasketItems as $bItems)
        {
            $basketAmount += $bItems["QUANTITY"] * $bItems["PRICE"];
        }

        if($basketAmount == 0)
        {
            PayqrLog::log("Сумма заказа оказалась = 0!");

            return false;
        }

        $arFields = array(
            "LID" => SITE_ID,
            "PERSON_TYPE_ID" => 1,
            "PAYED" => "N",
            "CANCELED" => "N",
            "STATUS_ID" => "N",
            "PRICE" => $basketAmount,
            "CURRENCY" => 'RUB',
            "USER_ID" => intval($USER_ID),
            "USER_DESCRIPTION" => isset($delivery->comment)? $delivery->comment : ""
        );

        $module = new PayqrModule();

        //Получаем идентификатор платежной системы
        if($PayQRID = $module->getOption("bitrix_payqr_paysystem_id"))
        {
            $arFields["PAY_SYSTEM_ID"] = intval($PayQRID);
        }

        // Добавляем заказ в учетную систему
        $orderId = CSaleOrder::Add($arFields);

        if ($orderId == false)
        {
            PayqrLog::log("Ошибка создания заказа!");

            return false;
        }

        if (count($arFieldsOrder) > 0)
        {
            foreach ($arFieldsOrder as $id => $value)
            {
                //$value = substr($value, 2);
                if ($id > 0 && !empty($value) && $arOrderProps = CSaleOrderProps::GetByID($id))
                {
                    CSaleOrderPropsValue::Add(array(
                        "ORDER_ID" => $orderId,
                        "ORDER_PROPS_ID" => $id,
                        "NAME" => $arOrderProps['NAME'],
                        "CODE" => $arOrderProps['CODE'],
                        "VALUE" => $value
                    )) ;
                }
            }
        }

        CSaleBasket::OrderBasket($orderId, $FUSER_ID, SITE_ID);

        foreach($arBasketItems as $bItems)
        {
            $arDiscounts = CCatalogDiscount::GetDiscountByProduct($bItems["PRODUCT_ID"],CUser::GetUserGroup($USER_ID),"N");

            if(isset($arDiscounts[0]))
            {
                $DB->StartTransaction();
                $err_mess = $strError = "";
                $arFields = array(
                    "MODULE_ID" => "'".$arDiscounts[0]["MODULE_ID"]."'",
                    "ORDER_DISCOUNT_ID" => (int)$arDiscounts[0]["ID"],
                    "ORDER_ID" => $orderId,
                    "ENTITY_TYPE" => 1,
                    "ENTITY_ID" => $bItems["ID"],
                    "ENTITY_VALUE" => "'".$bItems["ID"]."'",
                    "COUPON_ID" => 0,
                    "APPLY" => "'Y'"
                );

                $DB->PrepareFields("b_sale_order_rules");


                $ID = $DB->Insert("b_sale_order_rules", $arFields, $err_mess.__LINE__);

                PayqrLog::log($strError);

                PayqrLog::log($err_mess);

                $ID = intval($ID);

                if (strlen($strError)<=0)
                {
                    PayqrLog::log("DB->Commit");
                    $DB->Commit();
                }
                else
                {
                    PayqrLog::log("DB->Rollback");
                    PayqrLog::log($strError);
                    PayqrLog::log($err_mess);
                    $DB->Rollback();
                }
            }
        }

        PayqrLog::log("Succeess creating order & send response: " . $orderId);

        return $orderId;
    }

    /**
     * @return array|bool
     */
    private function getOrderbyInvoice()
    {
        global $DB;

        $request = array();

        PayqrLog::log("Проверка наличия записи о заказе по invoiceId: " . $this->invoice->getInvoiceId());

        if($res = $DB->Query("SELECT * FROM b_payqr_invoice WHERE invoice_id='".$this->invoice->getInvoiceId()."'", true))
        {
            $invoice = $res->GetNext();

            if(isset($invoice["order_id"], $invoice["amount"]) && !empty($invoice["order_id"]) && !empty($invoice["amount"]))
            {
                PayqrLog::log("Заказ уже сформирован");

                $request["order_id"] = $invoice["order_id"];

                $this->invoice->setOrderId($invoice["order_id"]);
            }
            if(isset($invoice["amount"]) && !empty($invoice["amount"]))
            {
                PayqrLog::log("Заказ уже сформирован. И нашли цену заказа");

                $request["amount"] = $invoice["amount"];

                $this->invoice->setAmount(round($invoice["amount"],2));
            }

            return $request;
        }

        PayqrLog::log("Заказ еще системой не сформирован");

        return false;
    }
    /**
     * заказ оплачен уже или нет
     */
    public function getOrderPaidStatus($invoice_id)
    {        
        $db = PayqrModuleDb::getInstance();
        $invoice = $db->select("select * from ".PayqrModuleDb::getInvoiceTable()." where invoice_id=?", array($invoice_id), array("s"));
        return $invoice;
    }
    
    /**
     * функция отмены заказа
     */
    public function cancelOrder()
    {
        PayqrLog::log("cancelOrder() Отменяем заказ");
    }
    
    /**
     * функция отмены заказа
     */
    public function syncOrder()
    {
        
    }

    /**
     * Преобразуем данные в соответствии с кодировкой сайта
     */
    private function convertDataFromPayQR2Bitrix($data, $toCharacterSet = "windows-1251")
    {
        if(defined('LANG_CHARSET'))
        {
            return mb_convert_encoding($data, LANG_CHARSET);
        }
    }
}
