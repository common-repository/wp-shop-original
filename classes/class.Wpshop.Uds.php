<?php 

class Wpshop_Uds
{
	public $companyId;
	public $apikey;

	public function __construct(){
		$this->companyId = get_option("wpshop.uds_user_id");
		$this->apikey = get_option("wpshop.uds_api_key");
	}

	public function get_user($code = '', $phone = '') {
		$url = 'https://api.uds.app/partner/v2/customers/find?';
		if ($code != '' && $phone != '' ) {
			$url .= 'code=' . $code . '&phone=' . $phone;
		} elseif ($code != '') {
			$url .= 'code=' . $code;
		} elseif ($phone != '') {
			$url .= 'phone=' . $phone;
		}

		$string_auth = $this->companyId . ':' . $this->apikey;
		$auth_hash = base64_encode($this->companyId.':'.$this->apikey);
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Accept: application/json\r\n" .
        "Accept-Charset: utf-8\r\n" .
				"Authorization: Basic $auth_hash"
			)
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return $response;
	}	

	public function get_settings() {
		$url = 'https://api.uds.app/partner/v2/settings';
		
		$string_auth = $this->companyId . ':' . $this->apikey;
		$auth_hash = base64_encode($this->companyId.':'.$this->apikey);
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Accept: application/json\r\n" .
        "Accept-Charset: utf-8\r\n" .
				"Authorization: Basic $auth_hash"
			)
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return $response;
	}

	public function calc($code, $uid, $total, $points) {
		$url = 'https://api.uds.app/partner/v2/operations/calc';
		
		$string_auth = $this->companyId . ':' . $this->apikey;
		$auth_hash = base64_encode($this->companyId.':'.$this->apikey);
		$postData = json_encode(
			array(
				"code" => "$code",
				"participant" => array(
					"uid"=> "$uid"
				),
				"receipt" => array(
					"total" => (float)$total,
					"points" => (float)$points,
					"skipLoyaltyTotal" => (float)$points
				)
			)
		);
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $postData,
			CURLOPT_HTTPHEADER => array(
				"Authorization: Basic $auth_hash",
				"Content-Type: application/json"
			)
		));
	  $response = curl_exec($curl);

		curl_close($curl);
		return $response;
	}

	public function purchase($order_id,$code, $uid, $total, $points, $cash) {
		$url = 'https://api.uds.app/partner/v2/operations';
		$shop_id = $this->companyId;
		$string_auth = $this->companyId . ':' . $this->apikey;
		$auth_hash = base64_encode($this->companyId.':'.$this->apikey);
		$postData = json_encode(
			array(
				"code" => "$code",
				'cashier' => array(
					'externalId' => "$shop_id"
				),
				'receipt' => array(
					'total' => $total,
					'cash' => $cash,
					'points' => $points,
					'number' => "$order_id",
					'skipLoyaltyTotal' => 1
				)
			)
		);
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $postData,
			CURLOPT_HTTPHEADER => array(
				"Authorization: Basic $auth_hash",
				"Content-Type: application/json"
			)
		));
		error_log($postData,0);
		$response = curl_exec($curl);

		curl_close($curl);

		error_log(json_encode($response),0);
		return $response;
	}
}