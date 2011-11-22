<?
/**
 * Class for labels, messages, etc. Has methods for i18n support.
 *
 * @author nevil
 */
class Messages {
    public static $EnglishLang = 'en';
    public static $RussianLang = 'ru';
    public static $DEFAULT_LANG;
    protected $messages = array();
    private $currentLang;

    /**
     * Return instance of this class.
     * @param $context - directory with messages. null - application core dir;
     * @return Messages 
     */
    public static function getInstance($context = null) {
        return new Messages($context);
    }

    /**
     * Returns current language.
     * @return string 
     */
    public function getLang() {
        return $this->currentLang;
    }

    /**
     * Sets current language.
     * @param string $lang. 'ru', 'en' values.
     */
    public function setLang($lang) {
        if (null != $lang) {
            $this->currentLang = $lang;
        }
    }

    private function __construct($context = null) {
        $this->currentLang = isset($_GET['lang']) ? $_GET['lang'] : self::$RussianLang;
        $this->parseConfig($_SERVER['DOCUMENT_ROOT'] . Config::$APP_DIR);
        if (null != $context) {
            $this->parseConfig($context);
        }
    }

    /**
     * Parses messages.ini from core and $configDir
     * @param string $configDir absolute path to messages.ini
     */
    private function parseConfig($configDir) {
        $localConfig = $configDir . Config::$CONFIG_DIR_PREFIX . 'messages.ini';
        if (file_exists($localConfig)) {
            if (count($this->messages) > 0) {
                $this->messages = array_merge($this->messages, Ini::parse($localConfig));
            } else {
                $this->messages = Ini::parse($localConfig);
            }
        }
    }

    /**
     * String returns button label
     * @param string $key
     * @return label 
     */
    public function btn($key) {
        return $this->msg('button', $key);
    }

    /**
     * String returns label for fields, etc.
     * @param string $key
     * @return label 
     */
    public function lbl($key, $params = null) {
        return $this->msg('label', $key, $params);
    }

    /**
     * Fetches value by key and section
     * @param string $section
     * @param string $key
     * @return string
     */
    public function msg($section = 'message', $key = null, $params = null) {
        $result = "";
        $lang = $this->currentLang;
        if (null != $key) {
            $result = $this->messages[$section][$key][$lang];
        }
        Debug::printMessage("Return message by key '$key' for language '$lang' from '$section'. Result message: $result;");
        if (null != $params && is_array($params)) {
            foreach ($params as $key => $value) {
                $result = str_replace('::' . $key . '::', $value, $result);
            }
            Debug::printMessage("Filter message '$result' with '" . $params . "'");
        }
        return $result;
    }
}
?>
