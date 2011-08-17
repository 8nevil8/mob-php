<?
/**
 * Provides common methods for retrieving image properties.
 *
 * @author nevil
 */
class ImageSettings {
    private $images;

    private function __construct($localConfig = null) {
        $this->parseConfig($_SERVER['DOCUMENT_ROOT'] . Config::$APP_DIR);
        if (null != $localConfig) {
            $this->parseConfig($localConfig);
        }
    }

    /**
     * Parses img.ini from core and $configDir
     * @param string $configDir absolute path to img.ini
     */
    protected function parseConfig($configDir) {
        $localConfig = $configDir . Config::$CONFIG_DIR_PREFIX . 'img.ini';
        if (file_exists($localConfig)) {
            if (count($this->images) > 0) {
                $this->images = array_merge($this->images, Ini::parse($localConfig, true));
            } else {
                $this->images = Ini::parse($localConfig, true);
            }
        }
    }

    /**
     * Return instance of this class.
     * @param $configDir - directory with messages. null - default value;
     * @return ImageSettings 
     */
    public static function getInstance($configDir=null) {
        return new ImageSettings($configDir);
    }

    public function getWidth($prefix, $key='img') {
        return $this->getImagePropertyValue($prefix, $key, 'width');
    }

    public function getHeight($prefix, $key='img') {
        return $this->getImagePropertyValue($prefix, $key, 'height');
    }

    private function getImagePropertyValue($prefix, $key, $property) {
        return $this->images[$prefix][$key][$property];
    }
}
?>
