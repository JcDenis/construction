<?php
/**
 * @file
 * @brief       The plugin construction definition
 * @ingroup     construction
 *
 * @defgroup    construction Plugin construction.
 *
 * Place your blog maintenance.
 *
 * @author      Osku (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'Construction',
    'Place your blog maintenance',
    'Osku and contributors',
    '1.9',
    [
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'priority'    => 2000,
        'type'        => 'plugin',
        'support'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/issues',
        'details'     => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/src/branch/master/README.md',
        'repository'  => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/raw/branch/master/dcstore.xml',
    ]
);
