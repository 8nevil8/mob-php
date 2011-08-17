<?
/**
 * Class for labels, messages, etc. Has methods for i18n support.
 *
 * @author nevil
 */
class Messages {
    public static $EnglishLang = '1';
    public static $RussianLang = '2';
    public static $Langs = array();
    public static $DEFAULT_LANG;
    protected $messages = array();
    private $currentLang;

    /**
     * Return instance of this class.
     * @param $context - directory with messages. null - application core dir;
     * @return Messages 
     */
    public static function getInstance($context=null) {
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

    private function __construct($context=null) {
        self::$DEFAULT_LANG = self::$EnglishLang;
        self::$Langs = array(
            self::$RussianLang => 'ru',
            self::$EnglishLang => 'en',
        );
        $this->currentLang = self::$Langs[self::$DEFAULT_LANG];
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
    public function lbl($key) {
        return $this->msg('label', $key);
    }

    /**
     * Fetches value by key and section
     * @param string $section
     * @param string $key
     * @return string
     */
    public function msg($section='message', $key=null) {
        $result = "";
        $lang = $this->currentLang;
        Debug::printMessage("Return message by key '$key' for language $lang from '$section'. Result message: $result;");
        if (null != $key) {
            $result = $this->messages[$section][$key][$lang];
        }
        return $result;
    }
}
?>
