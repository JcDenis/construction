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
declare(strict_types=1);

namespace Dotclear\Plugin\construction;

use dcAdmin;
use dcCore;
use dcPage;
use dcFavorites;
use dcNsProcess;

class Backend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && My::phpCompliant()
            && !is_null(dcCore::app()->auth) && !is_null(dcCore::app()->blog) // nullsafe PHP < 8.0
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
            ]), dcCore::app()->blog->id);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return false;
        }

        dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
            My::name(),
            dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
            dcPage::getPF(My::id() . '/icon.png'),
            preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.' . My::id())) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
            dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_ADMIN]), dcCore::app()->blog->id),
            dcCore::app()->blog->settings->get(My::id())->get('flag') ? 'construction-blog' : ''
        );

        dcCore::app()->addBehaviors([
            'adminPageHTMLHead' => function (): void {
                // nullsafe PHP < 8.0
                if (is_null(dcCore::app()->blog)) {
                    return;
                }
                if (dcCore::app()->blog->settings->get(My::id())->get('flag')) {
                    echo dcPage::cssModuleLoad(My::id() . '/css/backend.css');
                }
            },
            'adminDashboardFavoritesV2' => function (dcFavorites $favs): void {
                // nullsafe PHP < 8.0
                if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->adminurl)) {
                    return;
                }
                $favs->register(My::id(), [
                    'title'       => My::name(),
                    'url'         => dcCore::app()->adminurl->get('admin.plugin.' . My::id()),
                    'small-icon'  => dcPage::getPF(My::id() . '/icon.png'),
                    'large-icon'  => dcPage::getPF(My::id() . '/icon-big.png'),
                    'permissions' => dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_ADMIN]),
                ]);
            },
        ]);

        return true;
    }
}
