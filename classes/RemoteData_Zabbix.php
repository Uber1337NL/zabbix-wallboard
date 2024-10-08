<?php

/**
 * Summary of RemoteData_Zabbix
 */
class RemoteData_Zabbix {
	/**
	 * Summary of URL
	 * @var 
	 */
	protected $URL;
	/**
	 * Summary of USERNAME
	 * @var 
	 */
	protected $USERNAME;
	/**
	 * Summary of PASSWORD
	 * @var 
	 */
	protected $PASSWORD;
	/**
	 * Summary of BASIC_AUTH
	 * @var 
	 */
	protected $BASIC_AUTH;
	/**
	 * Summary of AUTH_HASH
	 * @var 
	 */
	protected $AUTH_HASH;
	/**
	 * Summary of ZBX_VERSION
	 * @var 
	 */
	protected $ZBX_VERSION;
	
	/**
	 * Summary of __construct
	 * @param mixed $URL
	 * @param mixed $USERNAME
	 * @param mixed $PASSWORD
	 * @param mixed $BASIC_AUTH
	 */
	public function __construct($URL,$USERNAME,$PASSWORD,$BASIC_AUTH) {
		$this->URL = $URL;
		$this->USERNAME = $USERNAME;
		$this->PASSWORD = $PASSWORD;
		$this->BASIC_AUTH = $BASIC_AUTH;
		
		if (isset($_SESSION['AUTH_HASH'])) {
			$this->AUTH_HASH = $_SESSION['AUTH_HASH'];
		}
		else {
			$this->AUTH_HASH = $this->api_query('user.login', ['password' => $this->PASSWORD, 'username' => $this->USERNAME]);
			$this->ZBX_VERSION = $this->get_zbx_version();
		}
	}
	
	/**
	 * Summary of __destruct
	 */
	public function __destruct() {
		if (isset($_SESSION['AUTH_HASH'])) {
			unset($_SESSION['AUTH_HASH']);
		}
		$this->AUTH_HASH = null;
	}
	
	/**
	 * Summary of get_hostgroups
	 * @param mixed $PARAMS
	 * @return array
	 */
	public function get_hostgroups($PARAMS) {
		return $this->api_fetch_array('hostgroup.get',$PARAMS);
	}
	
	/**
	 * Summary of get_triggers
	 * @param mixed $PARAMS
	 * @return array
	 */
	public function get_triggers($PARAMS) {
		return $this->api_fetch_array('trigger.get',$PARAMS);
	}
	
	/**
	 * Summary of get_eventdetails
	 * @param mixed $PARAMS
	 * @return array
	 */
	public function get_eventdetails($PARAMS) {
		$EVENTDETAILS = $this->api_fetch_array('event.get',$PARAMS);
		foreach ($EVENTDETAILS[0]['acknowledges'] as $ACKED_KEY => $ACKED_FIELD) {
			if (!isset($EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['alias'])) {
				$EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['name'] = "Inaccessible UserID";
				$EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['surname'] = $EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['userid'];
			}
			else {
				if (!isset($EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['name'])) {
					$EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['name'] = '';
				}
				if (!isset($EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['surname'])) {
					$EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['surname'] = '';
				}
				if ($EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['name'] === '' AND $EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['surname'] === '') {
					$EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['name'] = $EVENTDETAILS[0]['acknowledges'][$ACKED_KEY]['alias'];
				}
			}
		}
		return $EVENTDETAILS;
	}
	
	/**
	 * Summary of add_acknowledge
	 * @param mixed $EVENTID
	 * @param mixed $MESSAGE
	 * @return void
	 */
	public function add_acknowledge($EVENTID,$MESSAGE) {
		if ($this->ZBX_VERSION[0] >= 4) {
			$this->api_query('event.acknowledge', ['eventids' => $EVENTID, 'message' => $MESSAGE, 'action' => 6]);
		}
		else {
			$this->api_query('event.acknowledge', ['eventids' => $EVENTID, 'message' => $MESSAGE]);
		}
	}
	
	/**
	 * Summary of get_zbx_version
	 * @return bool|string[]
	 */
	public function get_zbx_version() {
		return explode(".",$this->api_query('apiinfo.version', []));
	}
	
	/**
	 * Summary of api_fetch_array
	 * @param mixed $METHOD
	 * @param mixed $PARAMS
	 * @return array
	 */
	private function api_fetch_array($METHOD, $PARAMS) {
		$RESULT = $this->api_query($METHOD,$PARAMS);
		if (is_array($RESULT))
			return $RESULT;
		else
			return [$RESULT];
	}
	
	/**
	 * Summary of api_query
	 * @param mixed $METHOD
	 * @param mixed $PARAMS
	 * @throws \Exception
	 * @return mixed
	 */
	private function api_query($METHOD, $PARAMS = []) {
		if ($this->AUTH_HASH == NULL && $METHOD != 'user.login')
			throw new Exception('No active API login',11);

		$DATA_JSON = ($METHOD === 'apiinfo.version') ? $this->api_curl(
			$this->URL,
			json_encode(
				[
					'method' => $METHOD,
					'id' => 1,
					'params' => $PARAMS,
					'jsonrpc' => "2.0"
				]
			)
		) : $this->api_curl(
					$this->URL,
					json_encode(
						[
							'auth' => $this->AUTH_HASH,
							'method' => $METHOD,
							'id' => 1,
							'params' => $PARAMS,
							'jsonrpc' => "2.0"
						]
					)
				);
		
		$DATA = json_decode($DATA_JSON, true);
		if (!empty($DATA['result'])) {
			return $DATA['result'];
		} 
		elseif (!empty($DATA['error'])) {
				throw new Exception('API Error: [' . $DATA['error']['code'] . '] ' . $DATA['error']['message'] . ' - ' . $DATA['error']['data'],12);
		}
		else {
			return false;
		}
	}

	/**
	 * Summary of api_curl
	 * @param mixed $URL
	 * @param mixed $DATA
	 * @return bool|string
	 */
	private function api_curl($URL, $DATA) {
		$CURL = curl_init($URL);

		$HEADERS = [];
		$HEADERS[]  = 'Content-Type: application/json-rpc';
		$HEADERS[]  = 'User-Agent: ZbxWallboard';

		$CURL_OPTS = [
			CURLOPT_RETURNTRANSFER => true,     // Allows for the return of a curl handle
			CURLOPT_TIMEOUT => 30,              // Maximum number of seconds to allow curl to process the entire request
			CURLOPT_CONNECTTIMEOUT => 5,        // Maximm number of seconds to establish a connection, shouldn't take 5 seconds
			CURLOPT_SSL_VERIFYHOST => false,    // Incase we have a fake SSL Cert...
			CURLOPT_SSL_VERIFYPEER => false,    // Ditto
			CURLOPT_FOLLOWLOCATION => false,    // Incase there's a redirect in place (moved zabbix url), follow it automatically
			CURLOPT_FRESH_CONNECT => true,      // Ensures we don't use a cached connection or response
			CURLOPT_ENCODING => 'gzip',
			CURLOPT_HTTPHEADER => $HEADERS,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => (is_array($DATA) ? http_build_query($DATA) : $DATA)
		];

		if ($this->BASIC_AUTH === 1) {
			$CURL_OPTS[CURLOPT_HTTPAUTH] = CURLOPT_HTTPAUTH;
			$CURL_OPTS[CURLOPT_USERPWD] = "{$this->USERNAME}:{$this->PASSWORD}";
		}
		
		curl_setopt_array($CURL, $CURL_OPTS);
		$RESULT = @curl_exec($CURL);
		curl_close($CURL);
		return $RESULT;
	}
}

