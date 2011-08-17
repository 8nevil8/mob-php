<?php
/**
 * Description of MenuController
 *
 * @author nevil
 */
class menu_Controller extends Controller {
    private $selectedMenuItem = null;
    protected $menuItems = array();

    public function setSelectedMenuItem($selectedMenuItem) {
        $this->selectedMenuItem = $selectedMenuItem;
    }

    public function getSelectedMenuItem() {
        return $this->selectedMenuItem;
    }

    public function action_index() {
        $menuItems = '';
        foreach ($this->menuItems as $itemId => $targetUrl) {
            $view = new View('menu_item.tpl', $this->getDir());
            $view->set('label', $this->messages->msg('menu', $itemId));
            $view->set('itemId', $itemId);
            $view->set('targetUrl', $targetUrl);
            $menuItems.= $view->fetch();
        }
        $class = $this->messages->getLang();
        $view = new View('menu.tpl', $this->getDir());
        $view->set('class', $class);
        $view->set('menuItems', $menuItems);
        return $view->fetch();
    }

    protected function getDir() {
        return '/menu/';
    }

    public function __construct($selectedMenuItem = false, $name = false) {
        parent::__construct($name);
        $this->menuItems = Ini::parse($this->getAbsoluteDir() . Config::$CONFIG_DIR_PREFIX . 'menu.ini');
        if ($selectedMenuItem) {
            $this->setSelectedMenuItem($selectedMenuItem);
        }
    }
}
?>
