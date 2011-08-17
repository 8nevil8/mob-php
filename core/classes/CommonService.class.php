<?
/**
 * Description of CommonService
 *
 * @author nevil
 */
class CommonService {
    protected $resourceDir;
    protected $lang;
    /**
     * @var DB
     */
    protected $dbUtils;
    /**
     * @var FileUtils
     */
    protected $fileUtils;

    public function __construct() {
        $this->dbUtils = new DB();
        $this->fileUtils = FileUtils::getInstance();
        $this->lang = Messages::$DEFAULT_LANG;
    }

    /**
     * Sets resource location for units.
     *
     * @param file $resourceDir
     */
    public function setResourceDir($resourceDir) {
        $this->resourceDir = $resourceDir;
    }

    /**
     * Sets current system language.
     *
     * @param string $resourceDir
     */
    public function setLang($lang) {
        $this->lang = $lang;
    }
}
?>
