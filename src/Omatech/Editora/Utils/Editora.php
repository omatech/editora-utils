<?php

namespace Omatech\Editora\Utils;

class Editora {

		private static $conn;

		public function __construct($conn) {
				if (is_array($conn)) {
						$config = new \Doctrine\DBAL\Configuration();
						//..
						$connectionParams = array(
							'dbname' => $conn['dbname'],
							'user' => $conn['dbuser'],
							'password' => $conn['dbpass'],
							'host' => $conn['dbhost'],
							'driver' => 'pdo_mysql',
							'charset' => 'utf8'
						);
						$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
				}
				self::set_connection($conn);
		}

		static function set_connection($conn) {
				//var_dump($conn);
				self::$conn = $conn;
		}

		static function get_url_data($language, $nice_url) {
				if (!isset($language)) {
						return ['type' => 'Home', 'class_tag' => 'Home'];
				} else {// tenim idioma
						if (!isset($nice_url)) {
								return ['type' => 'ChangeLanguage'
									, 'language' => $language];
						} else {// check valid urlnice
								$nice_url = self::$conn->quote($nice_url);
								$language = self::$conn->quote($language);
								$sql = "select n.inst_id, n.niceurl, i.class_id, c.tag, i.key_fields nom_intern
								from omp_niceurl n
								, omp_instances i
								, omp_classes c
								where n.language = $language
								and n.niceurl = $nice_url
								and i.id=n.inst_id
								and i.class_id=c.id
								";

								$row = self::$conn->fetchAssoc($sql);
								if ($row) {
										return ['type' => 'Instance'
											, 'id' => $row['inst_id']
											, 'class_tag' => ucfirst($row['tag'])
											, 'class_id' => $row['class_id']
											, 'nom_intern' => $row['nom_intern']
											, 'language' => $language
										];
								} else {
										return ['type' => 'Error', 'language' => $language];
								}
						}
				}

				//echo $sql;
				$row = self::$conn->fetchAssoc($sql);
		}

		static function control_objecte($obj, $lg) {
				$inst_id_from_url = self::get_inst_id_from_url($obj, $lg);

				if ($inst_id_from_url > 0)
						return true;
				return false;
		}

		static function default_object_accio($obj, $lg) {
				//echo 'HOLA default_object_accio';
				//global $dbh;
				$id = self::get_inst_id_from_url($obj, $lg);
				if (!isset($id))
						return 'error';

				$sql = "select c.tag
				from omp_classes c
				, omp_instances i
				where i.id = $id
				and i.class_id = c.id";

				//echo $sql;
				$row = self::$conn->fetchAssoc($sql);
				if ($row) {
						return $row['tag'];
				}
				return 'error';
		}

		static function control_classe($class) {
				if (file_exists(DIR_ACCIONS . '/' . $class . '.php'))
						return TRUE;
				return FALSE;
		}

		static function control_sortida($out) {
				return 'html';
		}

		static function get_nice_from_id($id = null, $lg = null) {
				$sql = "select niceurl as id from omp_niceurl n, omp_instances i where i.id=inst_id and inst_id=" . $id . " and language='" . $lg . "'";
				$row = self::$conn->fetchAssoc($sql);

				if (isset($row['id']))
						return $row['id'];
				else
						return '';
		}

		protected function get_attr_info($key)
		{
				
				if (is_numeric($key))
				{
						$key=self::$conn->quote($key);
						$sql = "SELECT * FROM omp_attributes where id=$key";
				}
				else
				{
						$key=self::$conn->quote($key);
						$sql = "SELECT * FROM omp_attributes where name=$key";
				}
				return self::$conn->fetchAssoc($sql);
		}

		protected function get_attr_id($key) 
		{// get attribute id from key or empty string		
				if (is_numeric($key))
						return $key;

				$key=self::$conn->quote($key);
				$sql = "select id from omp_attributes where name=$key";
				$row = self::$conn->fetchAssoc($sql);

				if (isset($row['id']))
						return $row['id'];
				else
						return '';
		}

		public function get_inst_id_from_value($class_tag, $attr_key, $value) 
	  {// retorna -1 si no existeix la instancia d'aquesta class o el id si existeix
				$class_tag = self::$conn->quote($class_tag);
				$value = self::$conn->quote($value);

				$attr_info = self::get_attri_info($attr_key);

				$sql = "SELECT i.id
		FROM omp_instances i
		, omp_classes c
		, omp_values v
		WHERE 
		 i.class_id = c.id
		AND c.tag=$class_tag
		AND v.inst_id = i.id
		AND v.atri_id = ".$attr_info['id']."
		AND v.text_val = $value";

				$row = self::$conn->fetchAssoc($sql);

				if ($row) {
						return $row['id'];
				}
				return -1;
		}

		static function get_inst_id_from_url($url, $lg) {
				if ($url == 'home')
						$_REQUEST['inst_id_from_url'] = HOMEID;
				//echo('get_inst_id_from_url: '.$url);
				// optimitzacio excel.lent, si per aquest request ja tenim el inst_id_from_url settejat, el retornem i punto
				if (isset($_REQUEST['inst_id_from_url']) && $_REQUEST['inst_id_from_url'] > 0)
						return $_REQUEST['inst_id_from_url'];

				//if (!$dbh) return -1;

				$url = str_replace("/", "", $url);
				// echo $url;
				if (is_numeric($url)) {//Comprovem que no tinguem URL maca per aquest id
						$sql = "select inst_id as id, class_id, niceurl from omp_niceurl n, omp_instances i where language='" . $lg . "' and n.inst_id='" . $url . "' and inst_id=i.id";
						if ($_REQUEST['req_info'] == 0)
								$sql.=" and i.status = 'O'";
						//$result = mysql_query($sql,$dbh);
						//if (!$result) return -2;
						//$row = mysql_fetch_array($result, MYSQL_ASSOC);
						$row = self::$conn->fetchAssoc($sql);

						if ($row) {
								// Permanent redirection
								header("HTTP/1.1 301 Moved Permanently");
								header("Location: " . URL_APLI . '/' . $lg . '/' . $row['niceurl']);
								die();
						}

						//Si no tenim URL maca, l'obrim per identificador.
						$sql = "select distinct i.id as id from omp_instances i,omp_class_attributes ca, omp_attributes a where i.id='$url' and i.class_id=ca.class_id and atri_id=a.id and a.type='Z'";
						if ($_REQUEST['req_info'] == 0)
								$sql.=" and status = 'O'";

						/* 					$result = mysql_query($sql,$dbh);
						  if (!$result) return -2;

						  if (mysql_num_rows($result) == 1) {
						  $row = mysql_fetch_array($result, MYSQL_ASSOC);
						  $_REQUEST['inst_id_from_url']=$row['id'];
						  return $row['id'];
						  }
						 */

						$row = self::$conn->fetchAssoc($sql);
						if ($row) {
								$_REQUEST['inst_id_from_url'] = $row['id'];
								return $row['id'];
						}
				}


				$sql = "select distinct inst_id as id from omp_niceurl n, omp_instances i where n.niceurl='" . $url . "' and inst_id=i.id";
				if (isset($_REQUEST['req_info']) && $_REQUEST['req_info'] == 0)
						$sql.=" and i.status = 'O'";
				//echo $sql;
//				$result = mysql_query($sql,$dbh);
//				if (!$result) return -2;
//
//				if (mysql_num_rows($result) == 1) {
//					$row = mysql_fetch_array($result, MYSQL_ASSOC);
//					$_REQUEST['inst_id_from_url']=$row['id'];
//					return $row['id'];
//				}

				$row = self::$conn->fetchAssoc($sql);
				if ($row) {
						$_REQUEST['inst_id_from_url'] = $row['id'];
						return $row['id'];
				}
		}

		/* 		
		  static function get_parent_inst_id_from_url ($url, $lg)
		  {
		  global $dbh;
		  if (!$dbh)
		  return -1;

		  $url=str_replace("/","",$url);

		  $sql="select id from omp_instances where id='$url'";
		  if ($_REQUEST['req_info']==0) {
		  $sql.=" and status = 'O'";
		  }

		  $result = mysql_query($sql,$dbh);
		  if (!$result)
		  return -2;


		  if (mysql_num_rows($result) == 1) {
		  $row = mysql_fetch_array($result, MYSQL_ASSOC);
		  return $row['id'];
		  }
		  else {
		  $sql="select ri.parent_inst_id as id from omp_instances i, omp_relation_instances ri, omp_niceurl n
		  where i.id=ri.child_inst_id and i.id=n.inst_id and n.language='".$lg."' and niceurl='".$url."'";
		  if ($_REQUEST['req_info']==0) {
		  $sql.=" and i.status = 'O'";
		  }
		  $result = mysql_query($sql,$dbh);

		  if (!$result) return -2;

		  if (mysql_num_rows($result) == 1) {
		  $row = mysql_fetch_array($result, MYSQL_ASSOC);
		  return $row['id'];
		  }
		  }
		  }
		 */

		static function comproba_idioma($lg) {
				global $array_langs;
				if (in_array($lg, $array_langs, true)) {
						return true;
				}
				return false;
		}

}
