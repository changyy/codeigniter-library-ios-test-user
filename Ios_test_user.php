<?php
// Usage:
//
// $obj = new IOS_Test_User( array( 'itc_admin_apple_id' => 'xxxx', 'itc_admin_password' => 'xxxx') );
// $this->load->library('ios_test_user', array( 'itc_admin_apple_id' => 'xxxx', 'itc_admin_password' => 'xxxx'));
//
// func 1: check test user count:
//   $obj->getTestUserNumber('AppAppleID');
//   $this->ios_test_user->getTestUserNumber('AppAppleID');
//
// func 2: add test user:
//   $obj->addTestUser('AppAppleID', 'TestUserAppleID');
//   $this->ios_test_user->addTestUser('AppAppleID', 'TestUserAppleID');
//
// func 3: remove test user
//   $obj->removeTestUser('AppAppleID', 'TestUserAppleID);
//   $this->ios_test_user->removeTestUser('AppAppleID', 'TestUserAppleID);
//
class IOS_Test_User {
	public function __construct($params) {
		$this->account = false;
		$this->password = false;
		if ( is_array($params) ) {
			if (isset($params['itc_admin_apple_id']))
				$this->account = $params['itc_admin_apple_id'];
			if (isset($params['itc_admin_password']))
				$this->password = $params['itc_admin_password'];
		}
		$this->resetCookie();
		$this->debug_enable = false;
	}

	public function __destruct() {
		if (file_exists($this->cookie_path))
			unlink($this->cookie_path);
	}

	public function getTestUserNumber($app_id) {
		if ($this->login()) {
			$ret = $this->request_handler(
				'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/ra/user/externalTesters/'.$app_id.'/',
				array(
				//	'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.86 Safari/537.36',
					'Content-Type: application/json',
					'Accept: application/json, text/javascript',
				), array(), array(), array(),
				$this->cookie_path
			);
			$json_obj = @json_decode($ret);
			if (isset($json_obj->statusCode) && !strcmp('SUCCESS', $json_obj->statusCode)) {
				if (isset($json_obj->data) && isset($json_obj->data->users) && is_array($json_obj->data->users))
					return count($json_obj->data->users);
			} else {
				if ($this->debug_enable)
					echo $ret;
			}
		}
		return false;
	}

	public function checkTestUserExists($app_id, $test_user_apple_id) {
		if ($this->login()) {
			$ret = $this->request_handler(
				'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/ra/user/externalTesters/'.$app_id.'/',
				array(
					'Content-Type: application/json',
				), array(), array(), array(),
				$this->cookie_path
			);
			$json_obj = @json_decode($ret);
			if (isset($json_obj->statusCode) && !strcmp('SUCCESS', $json_obj->statusCode)) {
				if (isset($json_obj->data) && isset($json_obj->data->users) && is_array($json_obj->data->users)) {
					foreach($json_obj->data->users as $user) {
						if (isset($user->emailAddress) && isset($user->emailAddress->value) && !strcasecmp($test_user_apple_id, $user->emailAddress->value))
							return true;
					}
				}
			} else {
				if ($this->debug_enable)
					echo $ret;
			}
		}
		return false;
	}

	public function addTestUser($app_id, $test_user_apple_id, $test_user_first_name = '', $test_user_last_name = '') {
		if ($this->login()) {
			// Step 1: Add User
			$ret = $this->request_handler(
				'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/ra/user/externalTesters/'.$app_id.'/',
				array(
					'Content-Type: application/json',
				), 
				array(
					'users' => array(
						array(
							'emailAddress' => array(
								'value' => $test_user_apple_id,
								'errorKeys' => array(),
							),
							'firstName' => array(
								'value' => $test_user_first_name,
							),
							'lastName' => array(
								'value' => $test_user_last_name,
							),
							'testing' => array(
								'value' => 'true',
							),
						),
					),
				), array(), array(),
				$this->cookie_path
			);
			$json_obj = @json_decode($ret);
			if (isset($json_obj->statusCode) && !strcmp('SUCCESS', $json_obj->statusCode)) {
				// Step 2: Check User	
				//return $this->checkTestUserExists($app_id, $test_user_apple_id);
				return true;
			} else {
				if ($this->debug_enable)
					echo $ret;
			}
		}
		return false;
	}

	public function removeTestUser($app_id, $test_user_apple_id) {
		if ($this->login()) {
			// Step 1: Remove User
			$ret = $this->request_handler(
				'https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/ra/user/externalTesters/'.$app_id.'/',
				array(
					'Content-Type: application/json',
				), 
				array(
					'users' => array(
						array(
							'emailAddress' => array(
								'value' => $test_user_apple_id,
							),
							'firstName' => array(
								'value' => '',
							),
							'lastName' => array(
								'value' => '',
							),
							'testing' => array(
								'value' => 'false',
							),
						),
					),
				), array(), array(),
				$this->cookie_path
			);
			$json_obj = @json_decode($ret);
			if (isset($json_obj->statusCode) && !strcmp('SUCCESS', $json_obj->statusCode)) {
				// Step 2: Check User	
				//return !$this->checkTestUserExists($app_id, $test_user_apple_id);
				return true;
			} else {
				if ($this->debug_enable)
					echo $ret;
			}
		}
		return false;
	}

	function resetCookie() {
		$this->cookie_path = tempnam('/tmp/', 'itc_');
	}

	function logout() {
		if (file_exists($this->cookie_path))
			unlink($this->cookie_path);
		resetCookie();
	}

	function login() {
		if (false == $this->account || false == $this->password)
			return false;

		$ret = $this->request_handler(
			'https://itunesconnect.apple.com/itc/static-resources/controllers/login_cntrl.js',
			array(
			//	'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.86 Safari/537.36',
			), array(), array(), array(),
			$this->cookie_path
		);

		$itcServiceKey = '';
		if (preg_match("/itcServiceKey = '(.*?)'/", $ret, $match))
			$itcServiceKey = $match[1];
		if (empty($itcServiceKey)) {
			if ($this->debug_enable)
				echo "[ERROR] empty itcServiceKey\n";
			return false;
		}
		if ($this->debug_enable)
			echo "[INFO] itcServiceKey:[$itcServiceKey]\n";

		$ret = $this->request_handler(
			'https://idmsa.apple.com/appleauth/auth/signin', 
			array(
				'Content-Type: application/json',
				'X-Requested-With: XMLHttpRequest',
				'Accept: application/json, text/javascript',
				'X-Apple-Widget-Key: ' . $itcServiceKey ,
			),
			array(
				'accountName' => $this->account,
				'password' => $this->password,
				'rememberMe' => false,
			),
			array(), array(), 
			$this->cookie_path
		);
		if ($this->debug_enable) {
			echo "--ret--begin--\n";
			echo $ret;
			echo "--ret--end--\n";
		}
		
		$ret = $this->request_handler(
			"https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa/wa/route?noext",
			array(), array(), array(), array(),
			$this->cookie_path
		);
		if ($this->debug_enable) {
			echo "--ret--begin--\n";
			echo $ret;
			echo "--ret--end--\n";
		}
		
		$ret = $this->request_handler(
			"https://itunesconnect.apple.com/WebObjects/iTunesConnect.woa",
			array(), array(), array(), array(),
			$this->cookie_path
		);

		if ($this->debug_enable) {
			echo "--ret--begin--\n";
			echo $ret;
			echo "--ret--end--\n";
		}

		return true;
	}

	function request_handler($url, $req_header = array(), $json_params = array(), $post_params = array() , $get_params = array(), $cookie_path = false) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url. (is_array($get_params) && count($get_params) > 0 ? '?'.http_build_query($get_params) : ''));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		if (is_array($json_params) && count($json_params) > 0) {
			//curl_setopt($ch, CURLOPT_POST, true);
			$payload = json_encode($json_params);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			//if (is_array($req_header)) {
			//	array_push($req_header, 'Content-Type: application/json');
			//	array_push($req_header, 'Content-Length: '.strlen($payload));
			//}
		}
		if (is_array($req_header) && count($req_header) > 0)
			curl_setopt($ch, CURLOPT_HTTPHEADER, $req_header);
		if (is_array($post_params) && count($post_params) > 0) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_params));
		}
		if ($cookie_path !== false) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_path); 
			curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_path);
		}
	
		$ret = curl_exec($ch);
	
		$info = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

		if ($this->debug_enable) {
			echo "CURLINFO_EFFECTIVE_URL\n";
			print_r($info);
			echo "\n";
		}
		
		curl_close($ch);
		return $ret;
	}
}
