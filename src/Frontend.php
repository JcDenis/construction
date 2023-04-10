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

use ArrayObject;
use dcCore;
use dcNsProcess;
use Dotclear\Helper\Network\Http;
use Exception;

class Frontend extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = My::phpCompliant();

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->addBehavior('publicBeforeDocument', function (): void {
            if (!dcCore::app()->blog->settings->get(My::id())->get('flag')) {
                return;
            }

            $tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->get('system')->get('theme'), 'tplset');
            if (!empty($tplset) && is_dir(implode(DIRECTORY_SEPARATOR, [My::root(), 'default-templates', $tplset]))) {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), implode(DIRECTORY_SEPARATOR, [My::root(), 'default-templates', $tplset]));
            } else {
                dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), implode(DIRECTORY_SEPARATOR, [My::root(), 'default-templates', DC_DEFAULT_TPLSET]));
            }

            $all_allowed_ip = json_decode(dcCore::app()->blog->settings->get(My::id())->get('allowed_ip'), true);
            if (!is_array($all_allowed_ip)) {
                $all_allowed_ip = [];
            }
            $extra_urls = json_decode(dcCore::app()->blog->settings->get(My::id())->get('extra_urls'), true);
            if (!in_array(Http::realIP(), $all_allowed_ip)) {
                dcCore::app()->url->registerDefault(function ($args) {
                    dcCore::app()->url->type = 'default';

                    throw new Exception('Blog under construction', 503);
                });
                dcCore::app()->url->registerError(function ($args, $type, $e) {
                    header('Content-Type: text/html; charset=UTF-8');
                    Http::head(503, 'Service Unavailable');
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

        dcCore::app()->tpl->addValue('ConstructionMessage', function (ArrayObject $attr): string {
            return '<?php echo ' . sprintf(
                dcCore::app()->tpl->getFilters($attr),
                'dcCore::app()->blog->settings->get("' . My::id() . '")->get("message")'
            ) . '; ?>';
        });
        dcCore::app()->tpl->addValue('ConstructionTitle', function (ArrayObject $attr): string {
            return '<?php echo ' . sprintf(
                dcCore::app()->tpl->getFilters($attr),
                'dcCore::app()->blog->settings->get("' . My::id() . '")->get("title")'
            ) . '; ?>';
        });

        return true;
    }
}
