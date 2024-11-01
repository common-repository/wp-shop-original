<?php 
class Wpshop_Orders
{
  private static $instance = null;
	private $statuses = array();

	private function __construct()
	{
		$this->statuses[0] = __('New', 'wp-shop'); // Новый
		$this->statuses[1] = __('Paid', 'wp-shop'); // Оплачено
		$this->statuses[2] = __('Cancelled', 'wp-shop'); // Отменено
		$this->statuses[3] = __('In the process', 'wp-shop'); // В обработке
		$this->statuses[4] = __('Delivered', 'wp-shop'); // Доставлено
		$this->statuses[5] = __('Archive', 'wp-shop'); // Архив
		$this->statuses[6] = __('Untraceable', 'wp-shop'); // На проверке
 	}

	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new Wpshop_Orders();
		}
		return self::$instance;
	}

	public function getStatuses()
	{
		return $this->statuses;
	}

	public function getStatus($id)
	{
		if (isset($this->statuses[$id]))
		{
			return $this->statuses[$id];
		}
		throw new Exception("The status with id {$id} not found.");
	}

	public function save($id,$data)
	{
		global $wpdb;
		$wpdb->update($wpdb->prefix."wpshop_orders", $data, array('order_id' => $id),array("%s"),array("%s"));
	}
  
  static public function send_uds_return($order_id)
	{
    $order = new Wpshop_Order($order_id);    
    $uds_json = $order->getUdsInfo();
    $uds_arr = json_decode($uds_json,true);

    if($uds_arr['message_first']){
      $first_operation = json_decode($uds_arr['message_first'],true);
      
      $date = new DateTime();
      $url = 'https://udsgame.com/v1/partner/revert/'.$first_operation['operation']['id'];
      $uuid_v4 = get_option("wpshop.uds_user_id");//generate universally unique identifier version 4 (RFC 4122)
      $apiKey = get_option("wpshop.uds_api_key"); //set your api-key

      // Create a stream
      $opts = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Accept: application/json\r\n" .
                        "Accept-Charset: utf-8\r\n" .
                        "X-Api-Key: ".$apiKey."\r\n" .
                        "X-Origin-Request-Id: ".$uuid_v4."\r\n" .
                        "X-Timestamp: ".$date->format(DateTime::ATOM),
            'ignore_errors' => true
        )
      );

      $context = stream_context_create($opts);

      $result = file_get_contents($url, false, $context);
      //error_log('result1:'.$result,0);
    }
    
    if($uds_arr['message']){
      $operation = json_decode($uds_arr['message'],true);
      $date = new DateTime();
      $url = 'https://udsgame.com/v1/partner/revert/'.$operation['operation']['id'];
      $uuid_v4 = get_option("wpshop.uds_user_id");//generate universally unique identifier version 4 (RFC 4122)
      $apiKey = get_option("wpshop.uds_api_key"); //set your api-key

      // Create a stream
      $opts = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Accept: application/json\r\n" .
                        "Accept-Charset: utf-8\r\n" .
                        "X-Api-Key: ".$apiKey."\r\n" .
                        "X-Origin-Request-Id: ".$uuid_v4."\r\n" .
                        "X-Timestamp: ".$date->format(DateTime::ATOM),
            'ignore_errors' => true
        )
      );

      $context = stream_context_create($opts);

      $result = file_get_contents($url, false, $context);
      //error_log('result2:'.$result,0);
    }
  }
  
  static public function send_uds_purchase($order_id)
	{
    $order = new Wpshop_Order($order_id);
    
    $uds_json = $order->getUdsInfo();
    $uds_arr = json_decode($uds_json,true);

    $uds = new Wpshop_Uds();

    if($uds_arr['message_first']){
      $first_operation = json_decode($uds_arr['message_first'],true);
      $result = $uds->purchase($order_id,$uds_arr['key'], $uds_arr['part_id'], $uds_arr['total'], $uds_arr['scores'], $first_operation['purchase']['cash']);

      $uds_arr['message'] = $result;
      global $wpdb;   
      $param_arr = array(json_encode($uds_arr),$order_id);
      $wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}wpshop_orders SET order_uds='%s' WHERE order_id=%d",$param_arr));
    }

	}
  
	static public function setStatus($order_id,$status)
	{
		global $wpdb;
		$wpdb->update($wpdb->prefix."wpshop_orders", array('order_status'=>$status), array('order_id' => $order_id),array("%d"),array("%d"));
		$google = get_option("wpshop.google_analytic");
		if ($status == 1){
      $order = new Wpshop_Order($order_id);
			$full_price = $order->getTotalSum();
			$product = $order->getOrderItems($order_id);
			$delivery = $order->getDelivery();
      
      if($order->getUdsInfo()){
        $result = self::send_uds_purchase($order_id);
        //error_log($result,0);
      }
			
      $confirm = get_option('wpshop.payment_confirm');
      if ($confirm==1) {
        $email = get_option("wpshop.email");
        $user_name = get_option("wpshop.email_name");
        if($user_name) {
          $email_result=$user_name.' <'.$email.'>';
        }else {
          $email_result=$email;
        }
        $siteurl = get_bloginfo('wpurl');
        $message = __('Order #','wp-shop').$order_id.__(' has paid','wp-shop');
        wp_mail($email, __('Payment Confirmation','wp-shop')." #{$order_id} ".__('from site','wp-shop')." {$siteurl}",$message,
        array("Content-type: text/html; charset=UTF-8","Reply-To: {$email_result}","From:{$email_result}"));
      }
      
      $client_confirm = get_option('wpshop.client_payment_confirm');
      if ($client_confirm==1) {
        $email = get_option("wpshop.email");
        $client_email = $order->getOrderEmail();
        $user_name = get_option("wpshop.email_name");
        if($user_name) {
          $email_result=$user_name.' <'.$email.'>';
        }else {
          $email_result=$email;
        }
        $siteurl = get_bloginfo('wpurl');
        
        $view = new Wpshop_View();
        ob_start();
        $view->order_id = $order_id;
 
        if (!get_option("wpshop.mail_activate")){
          $view->render("mail/client_confirm.php");
        }else{
          $view->render("mail/client_confirm_custom.php");
        }

        wp_mail($client_email, __('Payment Confirmation','wp-shop')." #{$order_id} ".__('from site','wp-shop')." {$siteurl}", ob_get_clean(),
        array("Content-type: text/html; charset=UTF-8","Reply-To: {$email_result}","From:{$email_result}"));
      }
      
      if(!empty($google)){
				$data = array(
						  'info' => $product,
						  'price' => $full_price, // the price
						  't_num' => $order_id,
						  'shiping' => $delivery->cost
						);
				gaBuildHit( 'ecommerce', $data);
			}
		}elseif($status == 2){
      $order = new Wpshop_Order($order_id);
      if($order->getPayment()!='') {
        if($order->getPayment()=='tinkoff') {
          if($order->getCustom()!=''):
            include_once WPSHOP_DIR .'/classes/TinkoffMerchantAPI.php';
            $info = array(
              'PaymentId'  => (int)$order->getCustom()
            );
            
            $think_opt = get_option("wpshop.payments.tinkoff");
            $Tinkoff = new TinkoffMerchantAPI( $think_opt['terminal'], $think_opt['secret_key'], $think_opt['gateway'] );
            $Tinkoff->cancel($info);
          endif;
        }
      }
      if($order->getUdsInfo()){
        $result = self::send_uds_return($order_id);
        //error_log('return:'.$result,0);
      }
    }
	}

	static public function getCartOrders() {
		global $wpdb;
		$rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpshop_selected_items WHERE selected_items_session_id='".session_id()."'");
		return $rows;
  }
  
  static public function getCartPreOrders() {
		global $wpdb;
		$rows = $wpdb->get_results("SELECT selected_items_session_id AS session, selected_items_date AS date FROM {$wpdb->prefix}wpshop_selected_items WHERE 1=1 GROUP BY selected_items_session_id ORDER BY selected_items_date DESC",ARRAY_A);
		return $rows;
  }

  static public function getmetkaPreOrders($session) {
		global $wpdb;
		$rows = $wpdb->get_results("SELECT selected_items_id AS id FROM {$wpdb->prefix}wpshop_selected_items WHERE selected_items_session_id = '$session' AND metka = 'moderated' LIMIT 1",ARRAY_A);
		return $rows;
  }

  static public function setmetkaPreOrders($session) {
		global $wpdb;
    $wpdb->query("UPDATE {$wpdb->prefix}wpshop_selected_items SET metka=\"moderated\" WHERE selected_items_session_id = \"$session\"");
    
    return $wpdb->prefix;
  }
  
  static public function unsetmetkaPreOrders($session) {
		global $wpdb;
    $wpdb->query("UPDATE {$wpdb->prefix}wpshop_selected_items SET metka=\"\" WHERE selected_items_session_id = \"$session\"");
    
    return $wpdb->prefix;
	}
  
  static public function getCartUser($session) {
		global $wpdb;
		$rows = $wpdb->get_results("SELECT selected_user AS user FROM {$wpdb->prefix}wpshop_selected_items WHERE selected_items_session_id = '$session'",ARRAY_A);
		return $rows;
	}
	
	static public function getStatus_order($order_id) {
		global $wpdb;
		$status = $wpdb->get_results("SELECT order_status FROM {$wpdb->prefix}wpshop_orders WHERE order_id='{$order_id}'");
		return $status;
	}
}
