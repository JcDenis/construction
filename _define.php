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

if (!isset($this) || !is_object($this) || !method_exists($this, 'registerModule') || !isset($this->id) || !is_string($this->id)) {
    return;
}

$this->registerModule(
    'Construction',
    'Place your blog maintenance',
    'Osku and contributors',
    '2.13',
    [
        'requires'    => [['core', '2.39']],
        'permissions' => 'My',
        'priority'    => 2000,
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2026-11-04T23:44:28+00:00',
    ]
);
