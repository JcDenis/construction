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

try {
    // Version
    if (!dcCore::app()->newVersion(
        basename(__DIR__),
        dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version')
    )) {
        return null;
    }

    $s = dcCore::app()->blog->settings->get(basename(__DIR__));

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
    dcCore::app()->error->add($e->getMessage());
}

return false;
