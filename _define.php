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
    '1.10',
    [
        'requires'    => [['core', '2.36']],
        'permissions' => 'My',
        'priority'    => 2000,
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-09-09T15:24:09+00:00',
    ]
);
