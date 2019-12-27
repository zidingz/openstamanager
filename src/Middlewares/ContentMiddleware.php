<?php

namespace Middlewares;

use Modules\Module;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Update;

/**
 * Classe per l'impostazione automatica delle variabili rilevanti per il funzionamento del progetto.
 *
 * @since 2.5
 */
class ContentMiddleware extends Middleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->getRoute($request);
        if (empty($route) || !$this->database->isConnected() || Update::isUpdateAvailable()) {
            return $handler->handle($request);
        }

        $this->addVariable('user', auth()->getUser());

        $this->addVariable('order_manager_id', $this->database->isInstalled() ? module('Stato dei serivizi')['id'] : null);
        $this->addVariable('is_mobile', intval(isMobile()));

        // Versione
        $this->addVariable('version', \Update::getVersion());
        $this->addVariable('revision', \Update::getRevision());

        // Richiesta AJAX
        $this->addVariable('handle_ajax', $request->isXhr() && filter('ajax'));

        // Menu principale
        $this->addVariable('main_menu', $this->getMainMenu());

        return $handler->handle($request);
    }

    /**
     * Restituisce il menu principale del progetto.
     *
     * @param int $depth Profondità del menu
     *
     * @return string
     */
    public function getMainMenu($depth = 3)
    {
        $menus = Module::getHierarchy()->sortBy('order');

        $module = Module::getCurrent();
        $module_name = isset($module) ? $module->name : '';

        $result = '';
        foreach ($menus as $menu) {
            $result .= self::sidebarMenu($menu, $module_name, $depth)[0];
        }

        return $result;
    }

    /**
     * Restituisce l'insieme dei menu derivato da un'array strutturato ad albero.
     *
     * @param array $element
     * @param int   $actual
     * @param int   $max_depth
     * @param int   $actual_depth
     *
     * @return string
     */
    protected function sidebarMenu($element, $actual = null, $max_depth = 3, $actual_depth = 0)
    {
        if ($actual_depth >= $max_depth || $element['type'] != 'module') {
            return '';
        }

        $link = (!empty($element['option']) && $element['option'] != 'menu') ? $element->url('module') : 'javascript:;';

        $title = $element['title'];
        $target = '_self'; // $target = ($element['new'] == 1) ? '_blank' : '_self';
        $active = ($actual == $element['name']);
        $show = ($element->permission != '-' && !empty($element['enabled'])) ? true : false;

        $submenus = $element->getChildren();
        if (!empty($submenus)) {
            $temp = '';
            foreach ($submenus as $submenu) {
                $r = self::sidebarMenu($submenu, $actual, $actual_depth + 1);
                $active = $active || $r[1];
                if (!$show && $r[2]) {
                    $link = 'javascript:;';
                }
                $show = $show || $r[2];
                $temp .= $r[0];
            }
        }

        $result = '';
        if ($show) {
            $result .= '<li class="nav-item ';

            if (!empty($temp)) {
                $result .= ' has-treeview';
            }

            if ($active) {
                $result .= ' menu-open active actual';
            }

            $result .= '" id="'.$element['id'].'">
                <a href="'.$link.'" target="'.$target.'" class="nav-link">
                    <i class="'.$element['icon'].' nav-icon"></i>
                    <p>
                        '.$title;
            if (!empty($submenus) && !empty($temp)) {
                $result .= '
                        <i class="right fa fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
                    '.$temp.'
                </ul>';
            } else {
                $result .= '
                    </p>
                </a>';
            }
            $result .= '
            </li>';
        }

        return [$result, $active, $show];
    }
}
