CREATE TABLE b_payqr_items (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(100) NOT NULL DEFAULT '',
  htmltype varchar(100) NOT NULL DEFAULT '',
  htmlvalue text NOT NULL,
  htmlpossiblevalues text NOT NULL,
  description varchar(100) NOT NULL DEFAULT '',
  active tinyint(1) DEFAULT '1',
  PRIMARY KEY (id),
  KEY name (name),
  KEY active (active)
);

INSERT INTO b_payqr_items VALUES (1,'merchant_id','text','','','����� �������� (MerchantID)',1),
(2,'secret_key_in','text','','','�������� ��������� ���� (SecretKeyIn)',1),
(3,'secret_key_out','text','','','��������� ��������� ���� (SecretKeyOut)',1),
(4,'hook_handler_url','text','http://<URL ������ �����>/rest/index.php?_rest=receiver','','URL ��� �����������',1),
(5,'log_url','text','http://<URL ������ �����>/rest/payqr.log','','URL PayQR �����',1),
(6,'cart_id','text','','','ID ������� \'�������\'',1),
(7,'cart_show_button','select','no','{yes:\'��\',no:\'���\'}','���������� ������ PayQR �� �������� ������� �������',1),
(8,'cart_button_color','select','default','{default:\'�� ���������\', green:\'�������\', red:\'�������\', \'blue\':\'�����\', orange: \'���������\'}','���� ������ �� �������� ������� �������',1),
(9,'cart_button_form','select','default','{default:\'�� ���������\', sharp:\'��� ����������\', rude:\'����������� ����������\', soft:\'������ ����������\', sleek:\'������������ ����������\', oval:\'������������ ����������\'}','����� ������ �� �������� ������� �������',1),
(10,'cart_button_shadow','select','default','{default:\'�� ���������\', shadow:\'��������\', noshadow:\'���������\'}','���� ������ �� �������� ������� �������',1),
(11,'cart_button_gradient','select','default','{default:\'�� ���������\', flat:\'��������\', gradient:\'�������\'}','�������� ������ �� �������� ������� �������',1),
(12,'cart_button_text_trans','select','default','{default:\'�� ���������\', \'small\':\'�����\', \'medium\':\'������\', \'large\':\'������\'}','������ ������ ������ �� �������� ������� �������',1),
(13,'cart_button_text_width','select','default','{default:\'�� ���������\', \'normal\':\'��������\', \'bold\':\'�������\'}','������ ����� ������ ������ �� �������� ������� �������',1),
(14,'cart_button_text_case','select','default','{default:\'�� ���������\', \'lowercase\':\'������\', \'standartcase\':\'�����������\', \'upper\':\'�������\'}','������� ������ ������ �� �������� ������� ������',1),
(15,'cart_button_height','text','auto','','������ ������ �� �������� ������� �������',1),(16,'cart_button_width','text','auto','','������ ������ �� �������� ������� �������',1),
(17,'product_show_button','select','no','{yes:\'��\',no:\'���\'}','���������� ������ PayQR �� �������� �������� ������',1),
(18,'product_button_color','select','default','{default:\'�� ���������\', green:\'�������\', red:\'�������\', \'blue\':\'�����\', orange: \'���������\'}','���� ������ �� �������� �������� ������',1),
(19,'product_button_form','select','default','{default:\'�� ���������\', sharp:\'��� ����������\', rude:\'����������� ����������\', soft:\'������ ����������\', sleek:\'������������ ����������\', oval:\'������������ ����������\'}','����� ������ �� �������� �������� ������',1),
(20,'product_button_shadow','select','default','{default:\'�� ���������\', shadow:\'��������\', noshadow:\'���������\'}','���� ������ �� �������� �������� ������',1),(21,'product_button_gradient','select','default','{default:\'�� ���������\', flat:\'��������\', gradient:\'�������\'}','�������� ������ �� �������� �������� ������',1),
(22,'product_button_text_trans','select','default','{default:\'�� ���������\', \'small\':\'�����\', \'medium\':\'������\', \'large\':\'������\'}','������ ������ ������ �� �������� �������� ������',1),
(23,'product_button_text_width','select','default','{default:\'�� ���������\', \'normal\':\'��������\', \'bold\':\'�������\'}','������ ����� ������ ������ �� �������� �������� ������',1),
(24,'product_button_text_case','select','default','{default:\'�� ���������\', \'lowercase\':\'������\', \'standartcase\':\'�����������\', \'upper\':\'�������\'}','������� ������ ������ �� �������� �������� ������',1),
(25,'product_button_height','text','auto','','������ ������ �� �������� �������� ������',1),
(26,'product_button_width','text','auto','','������ ������ �� �������� �������� ������',1),
(27,'category_show_button','select','no','{yes:\'��\',no:\'���\'}','���������� ������ PayQR �� �������� ��������� �������',1),
(28,'category_button_color','select','default','{default:\'�� ���������\', green:\'�������\', red:\'�������\', \'blue\':\'�����\', orange: \'���������\'}','���� ������ �� �������� ��������� �������',1),
(29,'category_button_form','select','default','{default:\'�� ���������\', sharp:\'��� ����������\', rude:\'����������� ����������\', soft:\'������ ����������\', sleek:\'������������ ����������\', oval:\'������������ ����������\'}','����� ������ �� �������� ��������� �������',1),
(30,'category_button_shadow','select','default','{default:\'�� ���������\', shadow:\'��������\', noshadow:\'���������\'}','���� ������ �� �������� ��������� �������',1),
(31,'category_button_gradient','select','default','{default:\'�� ���������\', flat:\'��������\', gradient:\'�������\'}','�������� ������ �� �������� ��������� �������',1),
(32,'category_button_text_trans','select','default','{default:\'�� ���������\', \'small\':\'�����\', \'medium\':\'������\', \'large\':\'������\'}','������ ������ ������ �� �������� ��������� �������',1),
(33,'category_button_text_width','select','default','{default:\'�� ���������\', \'normal\':\'��������\', \'bold\':\'�������\'}','������ ����� ������ ������ �� �������� ��������� �������',1),
(34,'category_button_text_case','select','default','{default:\'�� ���������\', \'lowercase\':\'������\', \'standartcase\':\'�����������\', \'upper\':\'�������\'}','������� ������ ������ �� �������� ��������� ������',1),
(35,'category_button_height','text','auto','','������ ������ �� �������� ��������� �������',1),(36,'category_button_width','text','auto','','������ ������ �� �������� ��������� �������',1),(37,'status_creatted','text','','','������ PayQR ������ \'������\'',1),
(38,'status_paid','text','','','������ PayQR ������ \'�������\'',1),(39,'status_cancelled','text','','','������ PayQR ������ \'�������\'',1),
(40,'status_completed','text','','','������ PayQR ������ \'��������\'',1),(41,'require_firstname','select','default','{default:\'�� ���������\', deny:\'�� �����������\', required:\'�����������\'}','����������� ��� ����������',1),
(42,'require_lastname','select','default','{default:\'�� ���������\', deny:\'�� �����������\', required:\'�����������\'}','����������� ������� ����������',1),
(43,'require_middlename','select','default','{default:\'�� ���������\', deny:\'�� �����������\', required:\'�����������\'}','����������� �������� ����������',1),
(44,'require_phone','select','default','{default:\'�� ���������\', deny:\'�� �����������\', required:\'�����������\'}','����������� ����� �������� ����������',1),
(45,'require_email','select','required','{default:\'�� ���������\', deny:\'�� �����������\', required:\'�����������\'}','����������� ���������� e-mail �����������',1),
(46,'require_delivery','select','default','{default:\'�� ���������\', deny:\'�� �����������\', required:\'�����������\', nonrequired:\'��������� ����� ������\'}','����������� ����� ��������',1),
(47,'require_deliverycases','select','default','{default:\'�� ���������\', deny:\'�� �����������\', required:\'�����������\'}','����� �� ���� � �������� ������� ��������',1),
(48,'require_pickpoints','select','default','{default:\'�� ���������\', deny:\'�� �����������\', required:\'�����������\'}','����� �� ���� � �������� ����� ����������',1),
(49,'require_promo_code','select','default','{default:\'�� ���������\', deny:\'�� �����������\', nonrequired:\'��������� ����� ������\'}','���������� ������ �����-���',1),
(50,'require_promo_card','select','default','{default:\'�� ���������\', deny:\'�� �����������\', nonrequired:\'��������� ����� ������\'}','���������� ������ ����� ����������',1),
(51,'promo_code','text','','','��������� �������� �����-��������������',1),
(52,'user_message_order_creating_text','text','','','��������� ��� �������� ������',1),
(53,'user_message_order_creating_url','text','','','������ ��� �������� ������',1),
(54,'user_message_order_creating_imageurl','text','','','����������� ��� �������� ������',1),
(55,'user_message_order_paid_text','text','','','��������� ��� ������ ������',1),
(56,'user_message_order_paid_url','text','','','������ ��� ������ ������',1),
(57,'user_message_order_paid_imageurl','text','','','����������� ��� ������ ������',1),
(58,'user_message_order_revert_text','text','','','��������� ��� �������� �������� �������',1),
(59,'user_message_order_revert_url','text','','','������ ��� �������� �������� �������',1),
(60,'user_message_order_revert_imageurl','text','','','����������� ��� �������� �������� �������',1),
(61,'bitrix_payqr_paysystem_id','text','','','������������� ��������� ������� PayQR � ������� �������',1);

CREATE TABLE IF NOT EXISTS b_payqr_invoice (
    id int(11) NOT NULL AUTO_INCREMENT,
    invoice_id varchar(100) NOT NULL,
    invoice_type varchar(100) NOT NULL,
    order_id varchar(100) DEFAULT NULL,
    amount decimal(10,2) DEFAULT NULL,
    INDEX order_id_index (order_id),
    INDEX invoice_index (invoice_id, invoice_type),
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS b_payqr_log (
  log_id int(11) NOT NULL AUTO_INCREMENT,
  data text NOT NULL,
  event_id varchar(100) NOT NULL,
  event_type varchar(100) NOT NULL,
  payqr_number varchar(100) NOT NULL,
  datetime datetime NOT NULL,
  order_id int(11) DEFAULT NULL,
   INDEX event_id_index (event_id),
   PRIMARY KEY (log_id)
);

CREATE TABLE IF NOT EXISTS b_payqr_user (
    user_id int(11) NOT NULL AUTO_INCREMENT,
    username varchar(100) NOT NULL,
    password varchar(100) NOT NULL,
    merch_id varchar(100) DEFAULT NULL,
    settings text DEFAULT NULL,
    PRIMARY KEY (user_id)
);

INSERT INTO b_payqr_user VALUES (1,'payqr', md5('payqr'), '000000-00000', '');