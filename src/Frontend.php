<?php

declare(strict_types=1);

namespace Dotclear\Plugin\construction;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Helper\Network\Http;
use Exception;

/**
 * @brief       construction frontend class.
 * @ingroup     construction
 *
 * @author      Osku (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehavior('publicBeforeDocumentV2', function (): void {
            if (!My::settings()->get('flag')) {
                return;
            }

            $tplset = App::themes()->getDefine(App::blog()->settings()->get('system')->get('theme'))->get('tplset');
            if (empty($tplset) || !is_dir(implode(DIRECTORY_SEPARATOR, [My::path(), 'default-templates', $tplset]))) {
                $tplset = App::config()->defaultTplset();
            }
            App::frontend()->template()->appendPath(implode(DIRECTORY_SEPARATOR, [My::path(), 'default-templates', $tplset]));

            $all_allowed_ip = json_decode((string) My::settings()->get('allowed_ip'), true);
            if (!is_array($all_allowed_ip)) {
                $all_allowed_ip = [];
            }
            $extra_urls = json_decode((string) My::settings()->get('extra_urls'), true);
            if (!is_array($extra_urls)) {
                $extra_urls = [];
            }
            if (!in_array(Http::realIP(), $all_allowed_ip)) {
                App::url()->registerDefault(function (?string $args): void {
                    App::url()->type = 'default';

                    throw new Exception('Blog under construction', 503);
                });
                App::url()->registerError(function (?string $args, ?string $type, Exception $e): void {
                    header('Content-Type: text/html; charset=UTF-8');
                    Http::head(503, 'Service Unavailable');
                    App::url()->type = '503';
                    App::frontend()->context()->__set('current_tpl', '503.html');
                    App::frontend()->context()->__set('content_type', 'text/html');

                    echo App::frontend()->template()->getData(App::frontend()->context()->__get('current_tpl'));

                    # --BEHAVIOR-- publicAfterDocumentV2
                    App::behavior()->callBehavior('publicAfterDocumentV2');
                    exit;
                });

                foreach (App::url()->getTypes() as $k => $v) {
                    if (($k != 'contactme') && !in_array($k, $extra_urls)) {
                        App::url()->register($k, $v['url'], $v['representation'], function () {
                            throw new Exception('Blog under construction', 503);
                        });
                    }
                }
            }
        });

        App::frontend()->template()->addValue('ConstructionMessage', function (ArrayObject $attr): string {
            return '<?php echo ' . sprintf(
                App::frontend()->template()->getFilters($attr),
                'App::blog()->settings()->get("' . My::id() . '")->get("message")'
            ) . '; ?>';
        });
        App::frontend()->template()->addValue('ConstructionTitle', function (ArrayObject $attr): string {
            return '<?php echo ' . sprintf(
                App::frontend()->template()->getFilters($attr),
                'App::blog()->settings()->get("' . My::id() . '")->get("title")'
            ) . '; ?>';
        });

        return true;
    }
}
