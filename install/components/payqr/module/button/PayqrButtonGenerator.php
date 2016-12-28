<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Класс используется для генерации кнопки в цмс
 *
 * @author 1
 */
class PayqrButtonGenerator 
{
    private $scenario = "buy";
    private $products;
    private $amount;
    private $user_data;

    private $type_cart = "cart";
    private $type_product = "product";
    private $type_category = "category";
    
    public function __construct($products=array(), $amount=0, $user_data=array())
    {
        $this->products = $products;
        $this->amount = $amount;
        $this->user_data = $user_data;
    }

    /**
    * Возвращает код скрипта PayQR для размещения в head интернет-сайта
    */
    public function getJs()
    {
      return '<script src="https://payqr.ru/popup.js?merchId=' . $this->getOption("merchantID") . '"></script>';
    }
    
    public function getCartButton()
    {
        if($this->getOption("cart_show_button") != "no")
        {
            $products = $this->products;
            return $this->get_button_html($this->scenario, $products, $this->type_cart);
        }
    }

    public function getProductButton()
    {
        if($this->getOption("product_show_button") != "no")
        {
            $products = $this->products;
            return $this->get_button_html($this->scenario, $products, $this->type_product);
        }
    }

    public function getCategoryButton()
    {
        if($this->getOption("category_show_button") != "no")
        {
            $products = $this->products;
            return $this->get_button_html($this->scenario, $products, $this->type_category);
        }
    }
    
    private function get_button_html($scenario, $products, $type)
    {
        $data = $this->get_data($scenario, $products, $type);
        $button_name = "";
        if($this->getOption("custom-button-name"))
        {
            $button_name = $this->getOption("custom-button-name");
        }
        $html = "<button";
        foreach($data as $attr=>$value)
        {
            if(is_array($value))
            {
                $value = implode(" ", $value);
            }
            if(!empty($value))
            {
                $html .= " $attr='$value'";
            }
        }
        $html .= ">$button_name</button>";
        return $html;
    }
  
  
    /**
     * @param $scenario
     * @param array $data
     * @return array|bool
     */
    private function get_data($scenario, $products, $type) 
    {
        $data = array();
        $data['data-scenario'] = $scenario;


        $cart_data = $products;
        $data_amount = 0;
        foreach ($cart_data as $item) {
            $data_amount += $item['amount'];
        }
        if($this->amount != 0)
        {
            $data_amount = $this->amount;
        }
        $data['data-amount'] = $data_amount;
        $data['data-cart'] = json_encode($cart_data);
        $data['data-firstname-required'] = $this->getOption('require_firstname');
        $data['data-lastname-required'] = $this->getOption('require_lastname');
        $data['data-middlename-required'] = $this->getOption('require_middlename');
        $data['data-phone-required'] = $this->getOption('require_phone');
        $data['data-email-required'] = $this->getOption('require_email');
        $data['data-delivery-required'] = $this->getOption('require_delivery');
        $data['data-deliverycases-required'] = $this->getOption('require_deliverycases');
        $data['data-pickpoints-required'] = $this->getOption('require_pickpoints');
        $data['data-promocode-required'] = $this->getOption('require_promocode');
        $data['data-promocard-required'] = $this->getOption('require_promocard');
        if(!empty($this->getOption('data-promocode-details-article')) || !empty($this->getOption('data-promocode-details-description')))
        {
            $data['data-promocode-details'] = json_encode(array($this->getOption('data-promocode-details-article'), $this->getOption('data-promocode-details-description')));
        }
        if(!empty($this->getOption('data-promocard-details-article') || !empty($this->getOption('data-promocard-details-description'))))
        {
            $data['data-promocard-details'] = json_encode(array($this->getOption('data-promocard-details-article'), $this->getOption('data-promocard-details-description')));
        }

        $userdata = array(
            "custom" => !empty($this->user_data)? $this->user_data : $this->getOption("data-userdata"),
        );
        $data['data-userdata'] = json_encode(array($userdata));
        $button_style = $this->get_button_style($type);
        $data['class'] = $button_style['class'];
        $data['style'] = $button_style['style'];

        return $data;
    }

    /**
     * Получить список стилей кнопки
     * 
     * @param string $type
     * @return array
     */
    private function get_button_style($type)
    {
        $style = array();
        $style['class'][] = 'payqr-button';
        $style['class'][] = $this->getOption($type . '_button_color');
        $style['class'][] = $this->getOption($type . '_button_form');
        $style['class'][] = $this->getOption($type . '_button_gradient');
        $style['class'][] = $this->getOption($type . '_button_text_case');
        $style['class'][] = $this->getOption($type . '_button_text_width');
        $style['class'][] = $this->getOption($type . '_button_text_size');
        $style['class'][] = $this->getOption($type . '_button_shadow');
        $style['style'][] = 'height:' . $this->getOption($type . '_button_height') . ';';
        $style['style'][] = 'width:' . $this->getOption($type . '_button_width') . ';';
                
        if($this->getOption("custom-button") == 1)
        {
            $style["class"][] = "payqr-button_idkfa";
            if($this->getOption("custom-button-classes"))
            {
                $style["class"][] = $this->getOption("custom-button-classes");
            }
            if($this->getOption("custom-button-styles"))
            {
                $style["style"][] = $this->getOption("custom-button-styles");
            }
        }

        return $style;
    }
    
    /**
     * Получить значение для настроек кнопки
     * 
     * @param string $key
     * @return string
     */
    private function getOption($key)
    {
        $module = new PayqrModule();
        $value = $module->getOption($key);
        return $value;
    }
}
