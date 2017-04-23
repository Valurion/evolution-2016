<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tabs
 *
 * @author ben
 */
class Tabs {

    public function getTabs($controleur, $action, $donnees) {
        if (Configuration::get('tabsMode') == 'typepub') {
            $tabs = [''];
        }
    }

}
