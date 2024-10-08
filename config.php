<?php

$CONFIG = [
	//===================================================================== Zabbix API credentials
	'ZABBIX' => [
		/* Full URL to Zabbix api. Includes api_jsonrpc.php! */
		'URL' => 'https://zabbix.domain.tld/api_jsonrpc.php',

		/* User which should be used for api connection */
		'USERNAME' => 'zbxwallboard',

		/* 
			Password for api user.
			Values:
				'string' - means used password
				null - no password used, only needed for unauth guest user
			*/
		'PASSWORD' => 'SUPERSECRETPASSWORD',
		/* 
			Basic Auth is optional. Depends on your Zabbix installation.
			If activated we will do a double authentication:
				1) HTTP basic auth against webserver
				2) regular api auth against JSON RPC
			Same credentials for both.
			
			Values:
				0 - means disabled
				1 - means activated
			*/
		'BASIC_AUTH' => 0,

		'ENABLED' => true
	],

	//===================================================================== Reverse Proxy
	/*
		Special setting for usage behind reverse proxys and different url-paths
		on reverse proxy and backend server.
		
		For example:
			Reverse Proxy URL (used in browser):
				https://www.domain.tld/tools/zbxwallboard/
			Backend URL (used from proxy to backend webserver)
				https://hidden.webserver.domain.tld/zbxwallboard/
				
			So REVERSE_PROXY_PATH should be '/tools'.
	   */
	'REVERSE_PROXY_PATH' => '',

	//===================================================================== Display options
	'DISPLAY' => [
		/* Defines the application name ;) */
		'TITLE' => 'Zabbix Wallboard',

		/* Defines how much problems should be displayed - Value of 0 means unlimited*/
		'PROBLEM_COUNT_SHOW' => 0,

		/* 
			Simple blinking lunch reminder if there are no problems to show.
			Very important and critical feature for each day in the office. :D
			
			Just activate it with true/false and set the start/end time.
			Times are defined in 24h-format without delimiters.
			Examples:
				8:15 am ->  815
				12:00 pm -> 1200
				4:45 pm -> 1645
			*/
		'LUNCH_REMINDER' => true,
		'LUNCH_REMINDER_START' => 1200,
		'LUNCH_REMINDER_END' => 1230
	],

	//===================================================================== API search parameters
	/* 
		Defines the search parameters for Zabbix API.
		Normally no change needed.
	   */
	'TRIGGER_SEARCH_PARAMS' => [
		'output' => 'extend',
		'selectHosts' => 'extend',
		'selectLastEvent' => 'extend',
		'expandData' => 'true',
		'expandDescription' => 'true',
		'min_severity' => 0,
		'groupids' => null,
		'withLastEventUnacknowledged' => null,
		'maintenance' => null,
		'monitored' => 'true',
		'only_true' => 'true',
		'skipDependent' => 'true',
		'sortfield' => 'lastchange',
		'sortorder' => 'DESC'
	],

	'HOSTGROUP_SEARCH_PARAMS' => [
		'output' => 'extend',
		'sortfield' => 'name',
		'sortorder' => 'ASC'
	],

	'EVENT_SEARCH_PARAMS' => [
		'eventids' => null,
		'output' => 'extend',
		'select_acknowledges' => 'extend'
	],

	//===================================================================== Severities
	/* 
		Defines the Names of severities.
		Normally no change needed.
	   */
	'SEVERITIES' => [
		0 => 'Not classified',
		1 => 'Information',
		2 => 'Warning',
		3 => 'Average',
		4 => 'High',
		5 => 'Disaster'
	],

];
