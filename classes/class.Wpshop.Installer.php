<?php 

class Wpshop_Installer
{
	private $wpdb;
	private $tables = array('wpshop_orders'=>array('columns'=> array (
												array('Field'=>'order_id'),
												array('Field'=>'order_date'),
												array('Field'=>'order_discount'),
												array('Field'=>'order_payment'),
												array('Field'=>'client_name'),
												array('Field'=>'client_email'),
												array('Field'=>'client_ip'),
												array('Field'=>'client_id'),
												array('Field'=>'order_status'),
												array('Field'=>'order_delivery'),
												array('Field'=>'order_comment'),
												array('Field'=>'order_promo'),
												array('Field'=>'order_delivery_cost'),
												array('Field'=>'order_delivery_adress'),
                        array('Field'=>'order_uds'),
                        array('Field'=>'custom_field')
												)
											),
							'wpshop_ordered' => array('columns' => array(
												array('Field'=>'ordered_id'),
												array('Field'=>'ordered_pid'),
												array('Field'=>'ordered_page_id'),
												array('Field'=>'ordered_name'),
												array('Field'=>'ordered_cost'),
												array('Field'=>'ordered_count'),
												array('Field'=>'ordered_key'),
												array('Field'=>'ordered_digit_count'),
												array('Field'=>'ordered_digit_live'),
											)
										),

							'wpshop_selected_items' => array('columns' => array(
												array('Field'=>'selected_items_id'),
												array('Field'=>'selected_items_session_id'),
												array('Field'=>'selected_items_item_id'),
												array('Field'=>'selected_items_key'),
												array('Field'=>'selected_items_name'),
												array('Field'=>'selected_items_href'),
												array('Field'=>'selected_items_cost'),
												array('Field'=>'selected_items_num'),
												array('Field'=>'selected_items_sklad'),
												array('Field'=>'selected_items_promo'),
                        array('Field'=>'selected_items_uds'),
                        array('Field'=>'selected_items_uds_scores'),
												array('Field'=>'selected_items_cost_start'),
												array('Field'=>'selected_items_date'),
												array('Field'=>'selected_user'),
												array('Field'=>'metka')
                      )
                    )
              );
	public function __construct()
	{
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->createOrderTable();
		$this->createOptions();
		Wpshop_Forms::getInstance()->checkcforms(Wpshop_Payment::getSingleton()->getPayments());
	}

	private function checkTable($tableName)
	{
		$actualColumns = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}`;");
		if($actualColumns){
		  foreach($this->tables[$tableName]['columns'] as $neededColumn)
		  {
			$find = false;
			foreach($actualColumns as $column)
			{
			  if ($neededColumn['Field'] == $column->Field)
			  {
				$find = true;
				break;
			  }
			}
			if (!$find)
			{
			  return false;
			}
		  }
		  return true;
		}else{
		  return 'no_table';
		}
	} 
	
	private function alterNewCols($tableName)
	{
		if($tableName == 'wpshop_orders') {
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'order_promo';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_orders` ADD `order_promo` VARCHAR( 50 ) AFTER  `order_comment` ;");
			}
		}
    
    if($tableName == 'wpshop_orders') {
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'order_delivery_cost';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_orders` ADD `order_delivery_cost` FLOAT AFTER  `order_promo` ;");
			}
		}
	
	  if($tableName == 'wpshop_orders') {
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'order_delivery_adress';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_orders` ADD `order_delivery_adress` TEXT AFTER `order_delivery_cost` ;");
			}
		}
    
    if($tableName == 'wpshop_orders') {
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'order_uds';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_orders` ADD `order_uds` TEXT NOT NULL AFTER `order_delivery_adress` ;");
			}
		}
    
    if($tableName == 'wpshop_orders') {
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'custom_field';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_orders` ADD `custom_field` VARCHAR( 255 ) NOT NULL AFTER `order_uds` ;");
			}
		}
    
    
		
		if($tableName == 'wpshop_selected_items') { 
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'selected_items_promo';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_selected_items` ADD `selected_items_promo` BIGINT NOT NULL DEFAULT '0' AFTER  `selected_items_sklad`;");
			}
		}
    
    if($tableName == 'wpshop_selected_items') { 
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'selected_items_uds';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_selected_items` ADD `selected_items_uds` VARCHAR( 6 ) NOT NULL AFTER  `selected_items_promo`;");
			}
		}
    
    if($tableName == 'wpshop_selected_items') { 
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'selected_items_uds_scores';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_selected_items` ADD `selected_items_uds_scores` VARCHAR( 255 ) NOT NULL AFTER  `selected_items_uds`;");
			}
		}
    
    if($tableName == 'wpshop_selected_items') { 
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'selected_items_cost_start';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_selected_items` ADD `selected_items_cost_start` FLOAT NOT NULL AFTER `selected_items_uds`;");
			}
		}

		if($tableName == 'wpshop_selected_items') { 
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'selected_items_date';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_selected_items` ADD `selected_items_date` VARCHAR( 255 ) NOT NULL AFTER `selected_items_cost_start`;");
			}
		}

		if($tableName == 'wpshop_selected_items') { 
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'selected_user';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_selected_items` ADD `selected_user` INT NOT NULL AFTER `selected_items_date`;");
			}
		}
		
		if($tableName == 'wpshop_selected_items') { 
			$checkCol = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}` LIKE 'metka';");
			if(!$checkCol) {
				$this->wpdb->query("ALTER TABLE `{$this->wpdb->prefix}wpshop_selected_items` ADD `metka` VARCHAR( 255 ) NOT NULL AFTER `selected_user`;");
			}
		}
	}


	private function dropTable($tableName)
	{
		$this->wpdb->query("DROP TABLE `{$this->wpdb->prefix}{$tableName}`;");
		//echo mysql_error();
	}

	/**
	 * Создает таблицы для сохранения заказов
	 */
	private function createOrderTable()
	{
		if ($this->checkTable('wpshop_orders')=='no_table'){		
			$sql = "CREATE TABLE `{$this->wpdb->prefix}wpshop_orders`
					(
						`order_id` INT NOT NULL AUTO_INCREMENT ,
						`order_date` INT NOT NULL,
						`order_discount` FLOAT,
						`order_payment` VARCHAR(20),
						`client_name` VARCHAR( 100 ),
						`client_email` VARCHAR( 50 ),
						`client_ip`  VARCHAR( 50 ),
						`client_id` INT NOT NULL DEFAULT '0',
						`order_status` INT NULL,
						`order_delivery` VARCHAR( 50 ),
						`order_comment` TEXT,
						`order_promo` VARCHAR( 50 ),
						`order_delivery_cost` FLOAT,
						`order_delivery_adress` TEXT,
            `order_uds` TEXT NOT NULL,
            `custom_field` VARCHAR(255) NOT NULL,
						PRIMARY KEY ( `order_id` )
					) ENGINE = INNODB DEFAULT CHARSET=utf8;";
			$this->wpdb->query($sql);
		}elseif(!$this->checkTable('wpshop_orders')){
			$this->alterNewCols('wpshop_orders');
		}
		
		if ($this->checkTable('wpshop_ordered')=='no_table') {
			$sql = "CREATE TABLE `{$this->wpdb->prefix}wpshop_ordered` (
					`ordered_id` INT NOT NULL AUTO_INCREMENT ,
					`ordered_pid` INT NOT NULL,
					`ordered_name` VARCHAR( 256) NOT NULL,
					`ordered_cost` FLOAT,
					`ordered_count` INT,
					`ordered_page_id` INT,
					`ordered_key` VARCHAR(255),
					`ordered_digit_count` INT NOT NULL DEFAULT '0',
					`ordered_digit_live` INT NOT NULL DEFAULT '0',
					 PRIMARY KEY ( `ordered_id` ),
					 FOREIGN KEY (`ordered_pid`) REFERENCES {$this->wpdb->prefix}wpshop_orders(`order_id`) ON DELETE CASCADE
				) ENGINE = INNODB DEFAULT CHARSET=utf8;";
			$this->wpdb->query($sql);
		}elseif(!$this->checkTable('wpshop_ordered')){
			$this->dropTable('wpshop_orders');
			$sql = "CREATE TABLE `{$this->wpdb->prefix}wpshop_ordered` (
					`ordered_id` INT NOT NULL AUTO_INCREMENT ,
					`ordered_pid` INT NOT NULL,
					`ordered_name` VARCHAR( 256) NOT NULL,
					`ordered_cost` FLOAT,
					`ordered_count` INT,
					`ordered_page_id` INT,
					`ordered_key` VARCHAR(255),
					`ordered_digit_count` INT NOT NULL DEFAULT '0',
					`ordered_digit_live` INT NOT NULL DEFAULT '0',
					 PRIMARY KEY ( `ordered_id` ),
					 FOREIGN KEY (`ordered_pid`) REFERENCES {$this->wpdb->prefix}wpshop_orders(`order_id`) ON DELETE CASCADE
				) ENGINE = INNODB DEFAULT CHARSET=utf8;";
			$this->wpdb->query($sql);
		}
		

		if ($this->checkTable('wpshop_selected_items')=='no_table'){
			$sql = "CREATE TABLE `{$this->wpdb->prefix}wpshop_selected_items`
				(
					`selected_items_id` INT NOT NULL AUTO_INCREMENT,
					`selected_items_session_id` VARCHAR( 40 ) NOT NULL,
					`selected_items_item_id` INT NOT NULL,
					`selected_items_key` VARCHAR(255),
					`selected_items_name` VARCHAR(256) NOT NULL,
					`selected_items_href` VARCHAR(255),
					`selected_items_cost` FLOAT,
					`selected_items_num` INT,
					`selected_items_sklad` INT,
					`selected_items_promo` BIGINT NOT NULL DEFAULT '0',
          `selected_items_uds` VARCHAR( 6 ) NOT NULL,
          `selected_items_uds_scores` VARCHAR( 255 ) NOT NULL,
          `selected_items_cost_start` FLOAT NOT NULL,
					`selected_items_date` VARCHAR( 255 ) NOT NULL,
					`selected_user` INT NOT NULL,
					`metka` VARCHAR( 255 ) NOT NULL,
					PRIMARY KEY ( `selected_items_id` )
				) ENGINE = INNODB DEFAULT CHARSET=utf8;";
			$this->wpdb->query($sql);
		}elseif(!$this->checkTable('wpshop_selected_items')){
			$this->alterNewCols('wpshop_selected_items');
		}

	}
  
  private function wpshop_install_generate_password($number)
	{
		$arr = array('a','b','c','d','e','f',
					'g','h','i','j','k','l',
					'm','n','o','p','r','s',
					't','u','v','x','y','z',
					'A','B','C','D','E','F',
					'G','H','I','J','K','L',
					'M','N','O','P','R','S',
					'T','U','V','X','Y','Z',
					'1','2','3','4','5','6',
					'7','8','9','0');
		// Генерируем пароль
		$pass = "";
		for($i = 0; $i < $number; $i++)
		{
			// Вычисляем случайный индекс массива
			$index = rand(0, count($arr) - 1);
			$pass .= $arr[$index];
		}
		return $pass;
  }
  
 	private function createOptions()
	{
    $passfrase = $this->wpshop_install_generate_password(6);
		add_option("wp-shop_cssfile","default.css");
		add_option("wp-shop_cform",Wpshop_Forms::getInstance()->getDefaultForm());
		add_option("wp-shop_position","top");
		add_option("wp-shop_show-cost",1);
    add_option("wp-shop_relink",dechex(rand(0x1000,0xFFFFFF)));
		add_option("wp-shop-link_ie6","");
		add_option("wpshop.partner_param","");
		add_option("wpshop.email",get_bloginfo('admin_email'));
    add_option("wpshop_yandex_delivery",array('base_lenght' =>'1','base_width'=>'1','base_height'=>'1','base_weight'=>'1'));
		add_option("wpshop.currency", __('$', 'wp-shop')); // руб.
		add_option("wpshop.payments.activate","0");
		add_option("wpshop.mail_activate","0");
		add_option("wpshop.show_panel",1);
		add_option("wpshop.price_trim",1);
    add_option("wpshop.payment_confirm",0);
		add_option("wpshop.sort_price",0);
		add_option("wpshop_merchant","");
		add_option("wpshop.hide_auth","none");
		add_option("wpshop_merchant_system","");
		add_option("wpshop.payments.wm",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=wm_success','failedUrl'=>get_bloginfo("url").'/?wpshopcarts=wm_failed'));
		add_option("wpshop.payments.tinkoff", array('delivery' => array(0 => 'postByCountry', 1 => 'postByWorld', 2 => 'vizit', 3 => 'courier'), 'successUrl' => get_bloginfo("url") . '/?wpshopcarts=tinkoff_success', 'failedUrl' => get_bloginfo("url") . '/?wpshopcarts=tinkoff_failed'));
		add_option("wpshop.payments.cripto", array('delivery' => array(0 => 'postByCountry', 1 => 'postByWorld', 2 => 'vizit', 3 => 'courier'), 'successUrl' => get_bloginfo("url") . '/?wpshopcarts=cripto_success', 'failedUrl' => get_bloginfo("url") . '/?wpshopcarts=cripto_failed'));		
		add_option("wpshop.payments.ym",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=ym_success'));
		add_option("wpshop.payments.yandex_kassa",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'tax'=>'1','successUrl'=>get_bloginfo("url").'/?wpshopcarts=yandex_kassa_success','failedUrl'=>get_bloginfo("url").'/?wpshopcarts=yandex_kassa_failed','resultUrl'=>get_bloginfo("url").'/wpshop/yandex_'.$passfrase,'passfrase'=>$passfrase));
		add_option("wpshop.payments.cash",array('delivery'=>array( 0 => 'courier')));
		add_option("wpshop.payments.ek",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=ek_success','failedUrl'=>get_bloginfo("url").'/?wpshopcarts=ek_failed','resultUrl'=>get_bloginfo("url").'/wpshop/wallet_'.$passfrase,'passfrase'=>$passfrase,'tax'=>'tax_ru_1'));
		add_option("wpshop.payments.bank",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier')));
		add_option("wpshop.payments.robokassa",array('login'=>'demo','pass1'=>'Morbid11','pass2'=>'Visions22','delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier')));
    add_option("wpshop.payments.primearea",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=primearea_success','failedUrl'=>get_bloginfo("url").'/?wpshopcarts=primearea_failed'));
		add_option("wpshop.payments.paypal",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'success'=>get_bloginfo("url").'/?wpshopcarts=paypal_success'));
		
		add_option("wpshop.payments.sber",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=sber_success','failedUrl'=>get_bloginfo("url").'/?wpshopcarts=sber_failed','stage'=>'one','currency_sber'=>'643','test'=>true,'measure'=>'шт.','tax'=>'0','ffd'=>'1'));
	 add_option("wpshop.payments.icredit",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=icredit_success','currency'=>'1','test'=>true));	
	 add_option("wpshop.payments.interkassa",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=interkassa_success','failedUrl'=>get_bloginfo("url").'/?wpshopcarts=interkassa_failed','currency'=>'UAH'));	
    add_option("wpshop.payments.sofort",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=sofort_success','failedUrl'=>get_bloginfo("url").'/?wpshopcarts=sofort_failed','resultUrl'=>get_bloginfo("url").'/wpshop/sofort_'.$passfrase,'passfrase'=>$passfrase));
    add_option("wpshop.payments.simplepay",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'success'=>get_bloginfo("url").'/?wpshopcarts=simplepay_success','failed'=>get_bloginfo("url").'/?wpshopcarts=simplepay_failed'));
    add_option("wpshop.payments.chronopay",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'success'=>get_bloginfo("url").'/?wpshopcarts=chronopay_success','failed'=>get_bloginfo("url").'/?wpshopcarts=chronopay_failed'));
		add_option("wpshop.payments.post",array('delivery' => array(0=>'postByCountry',1=>'postByWorld')));
		add_option("wpshop.payments.vizit",array('activate'=>1,'delivery'=>array( 0 => 'vizit')));

		add_option("wpshop.delivery",array('vizit'=>array('cost'=>0)));
		add_option("wpshop.cart.discount","0");
		add_option("wpshop.cart.minzakaz","0.1");
		add_option("wpshop.cart.minzakaz_info",'<br/><br/><h2>'.__('Amount of your order is below of the minimum. Please order something else!', 'wp-shop').'</h2>'); // Сумма Вашего заказа ниже минимальной. Пожалуйста закажите еще что-нибудь!

		add_option("wpshop.email",get_bloginfo('admin_email'));

		add_option("wpshop.loginza.widget_id", '');
		add_option("wpshop.loginza.secret_key", '');
    
    //uds
    add_option("wpshop.uds_user_id",'');
    add_option("wpshop.uds_api_key",'');
    add_option("wpshop.uds_external",'');
    add_option("wpshop.udsactive",0);
    add_option("wpshop.uds_text",'');
    add_option("wpshop.uds_link",'');
    add_option("wpshop.uds_percents",100);
    //uds

    $checkek = get_option("wpshop.payments.ek");
    $checkya = get_option("wpshop.payments.yandex_kassa");
    if($checkek['passfrase']=='') {
      update_option("wpshop.payments.ek",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=ek_success','failedUrl'=>get_bloginfo("url").'/?wpshopcarts=ek_failed','resultUrl'=>get_bloginfo("url").'/wpshop/wallet_'.$passfrase,'passfrase'=>$passfrase));
    }
    if($checkya['passfrase']=='') {
      update_option("wpshop.payments.yandex_kassa",array('delivery' => array(0=>'postByCountry',1=>'postByWorld',2=>'vizit',3=>'courier'),'successUrl'=>get_bloginfo("url").'/?wpshopcarts=yandex_kassa_success','failedUrl'=>get_bloginfo("url").'/?wpshopcarts=yandex_kassa_failed','resultUrl'=>get_bloginfo("url").'/wpshop/yandex_'.$passfrase,'passfrase'=>$passfrase));
    }
		// Удаляет опцию, так как потеряла свою актуальность
		delete_option("wp-shop_show-variety");
		delete_option("wpshop-linkfor","");
		delete_option("wp-shop-window");
	}
}
