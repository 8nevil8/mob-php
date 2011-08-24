<?
/**
 * Utility for retrieving resources from configured dirs
 *
 * @author nevil
 */
class ResourceUtils {
    private static $instance;

    private function __construct() {
        
    }

    /**
     * Return singleton
     * @return ResourceUtils 
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new ResourceUtils();
        }
        return self::$instance;
    }

    /**
     * Returns resizable image.
     * @param string $imgSrc
     * @param int $width
     * @param int $height
     * @param string $title
     * @return img
     */
    public function getResizableImg($imgSrc, $width=0, $height=0, $title='') {
        return '<img src="/resize_image.php?src=' . $imgSrc .
            ($width > 0 ? '&w=' . $width : '') .
            ($height > 0 ? '&h=' . $height : '') . '" title="' . $title . '"/>';
    }

    /**
     * Returns no_img.png 
     * @param int $width
     * @param int $height
     * @param string $title
     * @return img
     */
    public function getNoImg($width=0, $height=0, $title='') {
        return $this->getResizableImg(Config::$APP_RESOURCE_DIR . 'img/no_img.png', $width, $height, $title);
    }

    /**
     * Returns spacer.gif
     * @param int $width
     * @param int $height
     * @return img
     */
    public function getSpacerImg($width=0, $height=0) {
        return $this->getResizableImg(Config::$APP_RESOURCE_DIR . 'img/spacer.gif', $width, $height);
    }
}
?>
