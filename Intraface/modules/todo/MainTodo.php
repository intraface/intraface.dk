<?php
/**
 * @package Intraface_Todo
 */

class MainTodo extends Main {

    function __construct()
    {
        $this->module_name = 'todo';
        $this->menu_label = 'todo'; // Navnet der vil stå i menuen
        $this->show_menu = 1; // Skal modulet vises i menuen.
        $this->active = 1; // Er modulet aktivt.
        $this->menu_index = 240;
        $this->frontpage_index = 110;

        $this->addPreloadFile('TodoList.php');
        $this->addPreloadFile('TodoItem.php');

        //$this->addSubMenuItem('Indstillinger', 'setting.php');


        $this->includeSettingFile("settings.php");

        $this->addControlpanelFile('todo', '/modules/todo/setting.php');

        $this->addFrontpageFile('include_frontpage.php');
    }
}