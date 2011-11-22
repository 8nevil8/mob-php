<?
/**
 * View - default view class.
 *
 * @author nevil
 */
class View {
    private $fileLocation;
    protected $file = '';
    protected $params = array();

    /**
     * Constructor.
     * @param string $file
     * @param array $params
     * @return View 
     */
    function __construct($file='', $dir='', $params='') {
        if ($file) {
            $this->file = $file;
        }
        if (is_array($params)) {
            $this->params = $params;
        };
        if ('' != $dir) {
            $this->setFileLocation($dir);
        }
        return $this;
    }

    function set($key, $var) {
        $this->params[$key] = $var;
        return $this;
    }

    /**
     * Sets template dir before resource/tpl. Should start and end with '/'. $_SERVER['DOCUMENT_ROOT'] will be used as root
     * @param Directory $dir 
     */
    function setFileLocation($dir) {
        $this->fileLocation = !strpos($dir, $_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] . $dir : $dir;
    }

    function getFileLocation() {
        if (!isset($this->fileLocation)) {
            $this->fileLocation = dirname($_SERVER['SCRIPT_FILENAME']) . '/';
        }
        return $this->fileLocation . Config::$APP_TEMPLATE_DIR;
    }

    function fetch($params='') {
        global $TT, $LANG;
        if (is_array($params)) {
            $this->params = array_merge($this->params, $params);
        }
        extract($this->params);
        ob_start();
        require($this->getFileLocation() . $this->file);
        return ob_get_clean();
    }
}
?>
