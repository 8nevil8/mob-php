<?
/**
 * Description of core_admin_Controller
 *
 * @author nevil
 */
class core_admin_Controller extends core_Controller {

    protected function getDir() {
        return '/core/admin/';
    }

    protected function getJS() {
        $jsDir = $this->getAbsoluteDir() . Config::$JS_DIR_PREFIX;
        $result = array($this->getDir() => $this->lookUpResources($jsDir));
        $jsDir = $_SERVER['DOCUMENT_ROOT'].parent::getDir() . Config::$JS_DIR_PREFIX;
        $result = array_merge($result, array(parent::getDir() => $this->lookUpResources($jsDir)));
        return $result;
    }
}
?>
