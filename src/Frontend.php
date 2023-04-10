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
if (!defined('DC_RC_PATH')) {
    return null;
}

dcCore::app()->addBehavior('publicBeforeDocument', function () {
    if (!dcCore::app()->blog->settings->get(basename(__DIR__))->get('flag')) {
        return;
    }

    $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->get('system')->get('theme'), 'tplset');
    if (!empty($tplset) && is_dir(implode(DIRECTORY_SEPARATOR, [__DIR__, 'default-templates', $tplset]))) {
        dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), implode(DIRECTORY_SEPARATOR, [__DIR__, 'default-templates', $tplset]));
    } else {
        dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), implode(DIRECTORY_SEPARATOR, [__DIR__, 'default-templates', DC_DEFAULT_TPLSET]));
    }

    $all_allowed_ip = json_decode(dcCore::app()->blog->settings->get(basename(__DIR__))->get('allowed_ip'), true);
    if (!is_array($all_allowed_ip)) {
        $all_allowed_ip = [];
    }
    $extra_urls = json_decode(dcCore::app()->blog->settings->get(basename(__DIR__))->get('extra_urls'), true);
    if (!in_array(http::realIP(), $all_allowed_ip)) {
        dcCore::app()->url->registerDefault(function ($args) {
            dcCore::app()->url->type = 'default';

            throw new Exception('Blog under construction', 503);
        });
        dcCore::app()->url->registerError(function ($args, $type, $e) {
            header('Content-Type: text/html; charset=UTF-8');
            http::head(503, 'Service Unavailable');
            dcCore::app()->url->type = '503';
            dcCore::app()->ctx->__set('current_tpl', '503.html');
            dcCore::app()->ctx->__set('content_type', 'text/html');

            echo dcCore::app()->tpl->getData(dcCore::app()->ctx->__get('current_tpl'));

            # --BEHAVIOR-- publicAfterDocument
            dcCore::app()->callBehavior('publicAfterDocumentV2');
            exit;
        });

        foreach (dcCore::app()->url->getTypes() as $k => $v) {
            if (($k != 'contactme') && !in_array($k, $extra_urls)) {
                dcCore::app()->url->register($k, $v['url'], $v['representation'], function () {
                    throw new Exception('Blog under construction', 503);
                });
            }
        }
    }
});

dcCore::app()->tpl->addValue('ConstructionMessage', function ($attr) {
    return '<?php echo ' . sprintf(
        dcCore::app()->tpl->getFilters($attr),
        'dcCore::app()->blog->settings->get("' . basename(__DIR__) . '")->get("message")'
    ) . '; ?>';
});
dcCore::app()->tpl->addValue('ConstructionTitle', function ($attr) {
    return '<?php echo ' . sprintf(
        dcCore::app()->tpl->getFilters($attr),
        'dcCore::app()->blog->settings->get("' . basename(__DIR__) . '")->get("title")'
    ) . '; ?>';
});
