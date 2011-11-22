<?
/**
 * Abstract implementation of Controller class.
 *
 * @author nevil
 */
abstract class Controller {
    /**
     * Array of child controllers.
     * @var Controller 
     */
    private $childConrollers = array();
    private $name;
    private $current_action;

    /**
     * Instance of Messages class.
     * @var Messages 
     */
    protected $messages;

    /**
     * Instance of Image Settings for Controller. Could be configured for each controller.
     * @var ImageSettings 
     */
    protected $imageSettings;
    protected $default_action = "index";
    protected $output;
    protected $errorStatus = array();

    public function __construct($name = false) {
        if (!$name) {
            $name = get_class($this);
        }
        $this->name = $name;
        $this->messages = Messages::getInstance($this->getAbsoluteDir());
        $this->imageSettings = ImageSettings::getInstance($this->getAbsoluteDir());
    }

    /**
     * Returns image settings instance
     * @return ImageSettings 
     */
    public function getImageSettings() {
        return $this->imageSettings;
    }

    /**
     * Returns instance of Messages class
     * @return Messages 
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * Adds child controller to associative array. If $key is not defined, $controller->getName() will be used as key.
     * @param Controller $controller
     * @param string $key 
     */
    public function addChildController($controller, $key = false) {
        if ($controller instanceof Controller) {
            $key = !$key ? $controller->getName() : $key;
            $this->childConrollers[$key] = $controller;
        }
    }

    /**
     * Sets active controller
     * @param Controller $controller 
     */
    public function setActiveController($controller) {
        $this->addChildController($controller, 'activeController');
    }

    /**
     * Returns current active controller. Basically used for main application controller.
     * @return Controller 
     */
    protected function getActiveController() {
        return $this->getChildController('activeController');
    }

    /**
     * Returns child controller
     * @param string $name
     * @return Controller 
     */
    public function getChildController($name) {
        return self::__get_from_array($this->childConrollers, $name);
    }

    public function getName() {
        return $this->name;
    }

    /**
     * Method for rendering content
     */
    function getContent() {
        return $this->output;
    }

    public static function _get($key_name, $default_value = false) {
        return self::__get_from_array($_GET, $key_name, $default_value);
    }

    public static function _post($key_name, $default_value = false) {
        return self::__get_from_array($_POST, $key_name, $default_value);
    }

    private static function __get_from_array($array, $key_name, $default_value = false) {
        if (!isset($array[$key_name]) || $array[$key_name] == "") return $default_value;
        return $array[$key_name];
    }

    public function _goto($url) {
        $is_ajax = $this->_get("ajax");
        if ($is_ajax) {
            $info = parse_url($url);
            $get = array();
            if (!empty($info['query'])) {
                parse_str($info['query'], $get);
            }
            $info['query'] = http_build_query(array_merge($get, array("ajax" => $is_ajax)));
            $url = (isset($info['scheme']) ? $info['scheme'] . "://" : "")
                . (isset($info['host']) ? $info['host'] : "")
                . (isset($info['path']) ? $info['path'] : "")
                . (isset($info['query']) ? "?" . $info['query'] : "");
        }
        header("Location: " . $url);
        exit;
    }

    /**
     * Executes run for child controller and performs specified action
     */
    public function run() {
        foreach ($this->childConrollers as $controller) {
            $controller->run();
        }
        $this->performAction();
        $is_ajax = Controller::_get("ajax", false);
        if ($is_ajax && $this->getActiveController()) {
            echo $this->getActiveController()->getContent();
            die();
        }
    }

    /**
     * Returns array of available methods from $this class.
     * @return array 
     */
    protected function getActions() {
        $methods = get_class_methods(get_class($this));
        $ret = array();
        foreach ($methods as $one) {
            if (strpos($one, "action_") === 0 && strpos($one, "action_submit") !== 0) {
                $ret[] = substr($one, strlen("action_"));
            }
        }
        return $ret;
    }

    /**
     * Checks whether action is allowed.
     * @param string $action
     * @return bool 
     */
    protected function actionAllowed($action) {
        return in_array($action, $this->getActions());
    }

    /**
     * Returns request params from _GET request.
     * @return array 
     */
    public static function getRequestParams() {
        return self::_get('param', array());
    }

    /**
     * Returns action params
     * @return array. 
     */
    public static function getActionParams() {
        $request_params = self::getRequestParams();
        if (!isset($request_params)) return array();
        if (!is_array($request_params)) return array();
        return $request_params;
    }

    /**
     * Performs specified action. Passes action params to specific method
     */
    public function performAction() {
        $action = $this->getAction();
        $method_name = "action_$action";
        if (method_exists($this, $method_name)) {
            $this->current_action = $action;
            $this->output = call_user_func_array(array($this, $method_name), self::getActionParams());
        } else {
            $this->current_action = $this->default_action;
            $method_name = "action_$this->default_action";
            $this->output = call_user_func_array(array($this, $method_name), array());
        }
    }

    public function getAction() {
        $action = $this->_get("action", $this->default_action);
        if (!is_array($action)) {
            return $action;
        }
        return $this->default_action;
    }

    public function actionUri($action) {
        $params = func_get_args();
        return call_user_func_array(
                array($this->uri(), "set"), $params
        );
    }

    public function uri() {
        return new UriConstructor();
    }

    public static function current() {
        return $this;
    }

    public static function error($output) {
        return "<span style='color:red'>$output</span>";
    }

    /**
     * Returns page title, which could be set by active controller.
     * @return string 
     */
    public function getPageTitle() {
        return '';
    }

    /**
     * Returns meta keys, which could be set by active controller.
     * @return string 
     */
    public function getMetaKeywords() {
        return '';
    }

    /**
     * Returns full path for getDir() method.
     * @return dir
     */
    protected final function getAbsoluteDir() {
        return $_SERVER['DOCUMENT_ROOT'] . $this->getDir();
    }

    /**
     * Returns relative based dir, where controller is located. Subclasses should override this method.
     */
    public abstract function getDir();

    /**
     * Loads css files from controller directory.
     * Returns associative array: key - dir, value - available css files
     * @return array 
     */
    protected function getCss() {
        $cssDir = $this->getAbsoluteDir() . Config::$CSS_DIR_PREFIX;
        return array($this->getDir() => $this->lookUpResources($cssDir));
    }

    /**
     * Loads js files from controller directory.
     * Returns associative array: key - dir, value - available js files
     * @return array 
     */
    protected function getJS() {
        $jsDir = $this->getAbsoluteDir() . Config::$JS_DIR_PREFIX;
        return array($this->getDir() => $this->lookUpResources($jsDir));
    }

    /**
     * Loads ALL css files from controller directory and merge with css from child controllers.
     * Returns associative array: key - dir, value - available css files
     * @return array 
     */
    protected function getAllCss() {
        $result = $this->getCss();
        foreach ($this->childConrollers as $controller) {
            $result = array_merge($result, $controller->getCss());
        }
        return $this->fetchCss($result);
    }

    /**
     * Loads ALL JS files from controller directory and merge with JS from child controllers.
     * Returns associative array: key - dir, value - available css files
     * @return array 
     */
    protected function getAllJS() {
        $result = $this->getJS();
        foreach ($this->childConrollers as $controller) {
            $result = array_merge($result, $controller->getJs());
        }
        return $this->fetchJs($result);
    }

    public function getFetchedJS() {
        return $this->fetchJs($this->getJS());
    }

    /**
     * Function returns fetched css from given directory.
     * @param file $cssDir
     * @return string 
     */
    public function getFetchedCss() {
        return $this->fetchCss($this->getCss());
    }

    /**
     * Function fetches css array to html (<link href=''...).
     * @param file $cssDir
     * @return string 
     */
    protected function fetchCss($cssArray, $cssWebDir = '') {
        $result = '';
        if (is_array($cssArray)) {
            foreach ($cssArray as $cssDir => $cssFiles) {
                foreach ($cssFiles as $css) {
                    $resultCss = $cssDir . Config::$CSS_DIR_PREFIX . $css;
                    $cssFileVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . $resultCss);
                    $result.= '<link href="' . $resultCss . '?v=' . $cssFileVersion . '" rel="stylesheet" type="text/css"/>';
                }
            }
        }
        return $result;
    }

    /**
     * Function fetches JS array to html (<script src=''...).
     * @param file $jsArray
     * @return string 
     */
    protected function fetchJS($jsArray, $webDir = '') {
        $result = '';
        if (is_array($jsArray)) {
            foreach ($jsArray as $jsDir => $jsFiles) {
                foreach ($jsFiles as $js) {
                    $resultJs = $jsDir . Config::$JS_DIR_PREFIX . $js;
                    $jsFileVersion = filemtime($_SERVER['DOCUMENT_ROOT'] . $resultJs);
                    $result.= '<script src="' . $resultJs . '?v=' . $jsFileVersion . '" type="text/javascript"></script>';
                }
            }
        }
        return $result;
    }

    protected function lookUpResources($sourceDir) {
        if (is_dir($sourceDir) && file_exists($sourceDir)) {
            $result = FileUtils::readDir($sourceDir, false);
            sort($result);
            return $result;
        } else {
            return array();
        }
    }
}
?>
