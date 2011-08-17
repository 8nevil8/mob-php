<?
/**
 * Main application controller.
 *
 * @author nevil
 */
class core_Controller extends Controller {

    protected function getDir() {
        return Config::$APP_DIR;
    }

    function action_index() {
        $menuController = $this->getChildController('menu_Controller');
        $view = new View('app.tpl', Config::$APP_DIR);
        $view->set('appStyle', $this->getAllCss());
        $view->set('appJS', $this->getAllJS());
        $view->set('head', $this->getChildController('header_Controller')->getContent());
        $view->set('title', '');
        $view->set('metaKeywords', '');
        $view->set('menu', $menuController->getContent());
        $view->set('content', $this->getActiveController()->getContent());
        $view->set('selectedMenuItem', $menuController->getSelectedMenuItem());
        return $view->fetch();
    }
}
?>
