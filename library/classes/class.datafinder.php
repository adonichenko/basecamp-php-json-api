<?php

/**
 * File: ./class.datafinder.php
 * 
 * @package library.1.0
 * @author Alexander Donichenko (adonichenko@gmail.com)
 */

/**
 * Universal datafinder
 * 
 * @package library.1.0
 * @author Alexander Donichenko (adonichenko@gmail.com)
 */
class Datafinder {

	/**
	 * 	 * @var array Curl-request results 
	 */
	protected $urlopen;

	/**
	 * 	 * @var array Server response code
	 */
	protected $getinfo;

	/**
	 * Connection to Db
	 * @var obj 
	 */
	protected $connection;

	/**
	 * Ini settings
	 * @var obj 
	 */
	protected $ini;

	/**
	 * 	 * @var string Access for properties 
	 */
	protected $_getts = 'urlopen/getinfo/ini/connection/';

	/**
	 * 	 * @var string Sets properties  
	 */
	protected $_setts = 'ini/';

	/**
	 * Construct method
	 * 
	 * @param string $classname 
	 * @return void
	 */
	public function __construct($classname = null) {
		$name = isset($classname) ? strtolower($classname) : strtolower(__CLASS__);
		$filename = LIB_INI . '/setting.' . $name . '.ini';

		if (is_file($filename) && is_readable($filename)) {
			$this->ini[$name] = parse_ini_file($filename, true);
		}
	}

	/**
	 * Get property
	 * @param string $property Name of the property
	 * @return void
	 * @throws Exception
	 */
	public function __get($property) {
		if (strpos($this->_getts, $property . '/') !== false) {
			return $this->$property;
		} elseif (method_exists($this, '_get_' . $property)) {
			return call_user_func(array($this, '_get_' . $property));
		} else {
			throw new Exception('Property "' . $property . '" is not accessible.');
		}
	}

	/**
	 * Set property
	 * @param string $property Name of the property
	 * @param void $value Value of the property
	 * @throws Exception
	 */
	public function __set($property, $value) {
		if (strpos($this->_setts, $property . '/') !== false) {
			$this->$property = $value;
		} elseif (method_exists($this, '_set_' . $property)) {
			call_user_func(array($this, '_set_' . $property), $value);
		} elseif (strpos($this->_getts, $property . '/') !== false || method_exists($this, '_get_' . $property)) {
			throw new Exception('Property "' . $property . '" is read-only.' . $value);
		} else {
			throw new Exception('Property "' . $property . '" is not accessible. ' . $value);
		}
	}

	/**
	 * Multi-cURLing
	 * 
	 * @param array $arurls urls-list
	 * @param array|null $arproxies ip:port for every url (ore one for all)
	 * @param array|null $aropts curl_setopt-parameters for every url (ore one for all)
	 * @return void
	 */
	public function urlOpen($arurls, $arproxies = null, $aropts = null) {
		$this->urlopen = null;
		$this->getinfo = null;

		$mh = curl_multi_init();
		// counters for $arproxies, $aropts
		$j = $k = 0;
		foreach ($arurls as $url) {
			$init[$url] = curl_init();
			$arref[strval($init[$url])] = $url;

			curl_setopt($init[$url], CURLOPT_URL, $url);
			curl_setopt($init[$url], CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($init[$url], CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($init[$url], CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($init[$url], CURLOPT_AUTOREFERER, 1);
			curl_setopt($init[$url], CURLOPT_SSL_VERIFYPEER, !preg_match("/^https/i", $url));
			curl_setopt($init[$url], CURLOPT_FOLLOWLOCATION, 1);

			if (isset($arproxies)) {
				if (empty($arproxies[$j])) {
					$j = 0;
				}
				$arproxy = explode(':', $arproxies[$j]);
				curl_setopt($init[$url], CURLOPT_PROXY, $arproxy[0]);
				curl_setopt($init[$url], CURLOPT_PROXYPORT, $arproxy[1]);
				curl_setopt($init[$url], CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			} else {
				$arproxy = array('iplocal');
			}

			if (isset($aropts)) {
				if (empty($aropts[$k])) {
					$k = 0;
				}
				$aropt = $aropts[$k];

				if (isset($aropt['httpheader'])) {
					curl_setopt($init[$url], CURLOPT_HTTPHEADER, $aropt['httpheader']);
				} else {
					curl_setopt($init[$url], CURLOPT_HTTPHEADER, array(
						 'User-Agent: Mozilla/4.0 (compatible MSIE 6.0 Windows NT 5.1 SV1)',
						 'Connection: Keep-Alive',
						 'Keep-Alive: 300',
						 'Accept: text/html',
						 'Accept-Language: ' . (empty($aropt['language']) ? 'ru-RU' : $aropt['language']),
						 'Accept-Charset: utf-8',
						 'Accept-Encode: gzip, deflate'
					));
					curl_setopt($init[$url], CURLOPT_ENCODING, 'gzip, deflate');
				}

				if (isset($aropt['header'])) {
					curl_setopt($init[$url], CURLOPT_HEADER, 1);
					curl_setopt($init[$url], CURLOPT_NOBODY, 1);
				} else {
					if (isset($aropt['followlocation'])) {
						curl_setopt($init[$url], CURLOPT_FOLLOWLOCATION, $aropt['followlocation']);
					}
				}

				if (isset($aropt['post'])) {
					curl_setopt($init[$url], CURLOPT_POST, 1);
					curl_setopt($init[$url], CURLOPT_POSTFIELDS, $aropt['post']);
				}

				if (isset($aropt['referer'])) {
					curl_setopt($init[$url], CURLOPT_REFERER, $aropt['referer']);
				}

				if (isset($aropt['cookie'])) {
					if (is_array($aropt['cookie'])) {
						$aropt['cookie'] = implode(";", $aropt['cookie']);
					}
					curl_setopt($init[$url], CURLOPT_COOKIE, $aropt['cookie']);
				}

				if (empty($aropt['nocookies']) && is_dir(LIB_CACHE . '/cookies/')) {
					curl_setopt($init[$url], CURLOPT_COOKIEJAR, LIB_CACHE . '/cookies/' . $arproxy[0] . '.txt');
					curl_setopt($init[$url], CURLOPT_COOKIEFILE, LIB_CACHE . '/cookies/' . $arproxy[0] . '.txt');
				}

				if (isset($aropt['timeout'])) {
					curl_setopt($init[$url], CURLOPT_TIMEOUT, $aropt['timeout']);
				} else {
					curl_setopt($init[$url], CURLOPT_TIMEOUT, 12);
				}
				if (isset($aropt['userpwd'])) {
					curl_setopt($init[$url], CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
					curl_setopt($init[$url], CURLOPT_USERPWD, $aropt['userpwd']);
				}
				if (isset($aropt['customrequest'])) {
					curl_setopt($init[$url], CURLOPT_CUSTOMREQUEST, $aropt['customrequest']);
				}
			}

			curl_multi_add_handle($mh, $init[$url]);

			$j++;
			$k++;
		}

		$running = null;

		$getinfo = array();

		do {
			curl_multi_exec($mh, $running);
			$info = curl_multi_info_read($mh);
			if ($info['msg'] == CURLMSG_DONE) {
				$ch = $info['handle'];
				$getinfo[$arref[strval($ch)]] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			}
			usleep(888);
		} while ($running > 0);

		$this->getinfo = $getinfo;

		foreach ($arurls as $url) {
			$this->urlopen[$url] = curl_multi_getcontent($init[$url]);
			if (!isset($this->getinfo[$url])) {
				$this->getinfo[$url] = 0;
			}
			curl_multi_remove_handle($mh, $init[$url]);
			curl_close($init[$url]);
		}

		curl_multi_close($mh);
	}

	/**
	 * Blocking other connections
	 */
	public function connection() {
		if ($this->connection == null) {
			$dbset = $this->ini['datafinder']['mysql'];
			try {
				$this->connection = new PDO($dbset['db_driver'] . ':host=' . $dbset['host'] . ';port=' . $dbset['port']
						  . ';dbname=' . $dbset['dbname'], $dbset['db_user'], $dbset['db_password']);
				$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				echo "\n" . $e->getMessage();
			}
		}
		return $this->connection;
	}

	/**
	 * execute SQL-query 
	 *
	 * Example:
	 * $data = array(':userid' => 1);
	 * $res = Datafinder::connection()->prepQuery('SELECT * FROM users WHERE userid = :userid', $data);
	 * 
	 * @param string $query SQL-query
	 * @param array $data Array with parameters
	 * @return array Result of the query
	 */
	public function prepQuery($query, $data) {
		try {
			$q = $this->connection()->prepare($query);
			if ($q->execute($data)) {
				$type_query = mb_strtoupper(mb_substr($query, 0, 6));
				if ($type_query == 'SELECT') {
					return $q->fetchAll(PDO::FETCH_ASSOC);					
				} else {
					return true;
				}
			}
			return false;
		} catch (PDOException $e) {
			echo "\n" . $e->getMessage();
		}
	}

	/**
	 * id last insert record
	 * 
	 * @return int last id 
	 */
	public function lastID() {
		try {
			return $this->connection()->lastInsertId();
		} catch (PDOException $e) {
			echo "\n" . $e->getMessage();
		}
	}
}
?>
