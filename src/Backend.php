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

use dcCore;
use Dotclear\Core\Backend\{
    Favorites,
    Menus
};
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        My::addBackendMenuItem(Menus::MENU_PLUGINS, [], '(&.*)?$', My::settings()->get('flag') ? 'construction-blog' : '');

        dcCore::app()->addBehaviors([
            'adminPageHTMLHead' => function (): void {
                if (My::settings()->get('flag')) {
                    echo My::cssLoad('backend');
                }
            },
            'adminDashboardFavoritesV2' => function (Favorites $favs): void {
                $favs->register(My::id(), [
                    'title'       => My::name(),
                    'url'         => My::manageUrl(),
                    'small-icon'  => My::icons(),
                    'large-icon'  => My::icons(),
                    'permissions' => dcCore::app()->auth->makePermissions([dcCore::app()->auth::PERMISSION_ADMIN]),
                ]);
            },
        ]);

        return true;
    }
}
