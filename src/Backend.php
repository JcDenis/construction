<?php
/**
 * @brief construction, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Osku and contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('Construction'),
    dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
    urldecode(dcPage::getPF(basename(__DIR__) . '/icon.png')),
    preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__))) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
    dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]), dcCore::app()->blog->id),
    dcCore::app()->blog->settings->get(basename(__DIR__))->get('flag') ? 'construction-blog' : ''
);

dcCore::app()->addBehaviors([
    'adminPageHTMLHead'         => function () {
        if (dcCore::app()->blog->settings->get(basename(__DIR__))->get('flag')) {
            echo dcPage::cssModuleLoad(basename(__DIR__) . '/css/admin.css');
        }
    },
    'adminDashboardFavoritesV2' => function (dcFavorites $favs) {
        $favs->register(basename(__DIR__), [
            'title'       => __('Construction'),
            'url'         => dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)),
            'small-icon'  => urldecode(dcPage::getPF(basename(__DIR__) . '/icon.png')),
            'large-icon'  => urldecode(dcPage::getPF(basename(__DIR__) . '/icon-big.png')),
            'permissions' => dcCore::app()->auth->makePermissions([dcAuth::PERMISSION_ADMIN]),
        ]);
    },
]);
