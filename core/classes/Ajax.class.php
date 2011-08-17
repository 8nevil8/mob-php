<?
class Ajax{
	static private $key = 'ajax';
	public static function key(){ return self::$key; }
	
	public static function isAjaxRequest(){ 
		return (bool)self::getTarget();
	}
	
	public static function getTarget(){ 
		if(!empty($_POST[self::$key])) return $_POST[self::$key];
		if(!empty($_GET[self::$key])) return $_GET[self::$key];
		return false;
	}

	public static function pureGet(){
		$get = $_GET;
		unset($get[self::$key]);
		return $get;
	}
	
}
?>