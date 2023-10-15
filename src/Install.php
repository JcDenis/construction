<?php

declare(strict_types=1);

namespace Dotclear\Plugin\construction;

use Dotclear\App;
use Dotclear\Core\Process;
use Exception;

/**
 * @brief       construction install class.
 * @ingroup     construction
 *
 * @author      Osku (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            $s = My::settings();

            $s->put(
                'flag',
                false,
                'boolean',
                'Construction blog flag',
                false,
                true
            );

            $s->put(
                'allowed_ip',
                json_encode(['127.0.0.1']),
                'string',
                'Construction blog allowed ip',
                false,
                true
            );

            $s->put(
                'title',
                __('Work in progress'),
                'string',
                'Construction blog title',
                false,
                true
            );

            $s->put(
                'message',
                __('<p>The blog is currently under construction.</p>'),
                'string',
                'Construction blog message',
                false,
                true
            );

            $s->put(
                'extra_urls',
                json_encode([]),
                'string',
                'Construction blog message',
                false,
                true
            );

            return true;
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }
}
