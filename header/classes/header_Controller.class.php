<?php
/**
 * Header controller class.
 *
 * @author nevil
 */
class header_Controller extends Controller {

    public function action_index() {
        $view = new View('header.tpl', $this->getDir());
        $view->set('img', $this->getDir() . Config::$MANAGED_RESOURCE_DIR_PREFIX . 'logo.gif');
        return $view->fetch();
    }

    protected function getDir() {
        return '/header/';
    }
}
?>
