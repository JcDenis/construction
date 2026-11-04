<?php

declare(strict_types=1);

namespace Dotclear\Plugin\construction;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Core\Backend\{
    Notices,
    Page
};
use Dotclear\Helper\Html\Form\{
    Checkbox,
    Div,
    Form,
    Hidden,
    Input,
    Label,
    Note,
    Para,
    Submit,
    Text,
    Textarea
};
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;
use Exception;

/**
 * @brief       construction manage class.
 * @ingroup     construction
 *
 * @author      Osku (author)
 * @author      Jean-Christian Denis (latest)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Manage
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (!empty($_POST['saveconfig'])) {
            try {
                $allowed_ip = [];
                if (isset($_POST['construction_allowed_ip']) && is_string($_POST['construction_allowed_ip'])) {
                    foreach (explode("\n", $_POST['construction_allowed_ip']) as $ip) {
                        $allowed_ip[] = trim($ip);
                    }
                }
                $extra_urls = [];
                if (isset($_POST['construction_extra_urls']) && is_string($_POST['construction_extra_urls'])) {
                    foreach (explode(',', $_POST['construction_extra_urls']) as $url) {
                        $extra_urls[] = trim($url);
                    }
                }

                My::settings()->put('flag', !empty($_POST['construction_flag']));
                My::settings()->put('allowed_ip', json_encode($allowed_ip));
                My::settings()->put('title', isset($_POST['construction_title']) && is_string($_POST['construction_title']) ? $_POST['construction_title'] : '');
                My::settings()->put('message', isset($_POST['construction_message']) && is_string($_POST['construction_message']) ? $_POST['construction_message'] : '');
                My::settings()->put('extra_urls', json_encode($extra_urls));

                App::blog()->triggerBlog();

                Notices::addSuccessNotice(
                    __('Settings successfully updated.')
                );

                My::redirect();
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $s       = My::settings();
        $editor  = App::auth()->prefs()->get('interface')->get('editor');
        $editor  = is_array($editor) && isset($editor['xhtml']) && is_string($editor['xhtml']) ? $editor['xhtml'] : '';
        $ips     = json_decode($s->getStr('allowed_ip', false), true);
        $ips     = is_array($ips) ? array_filter($ips, is_string(...)) : [];
        $urls    = json_decode($s->getStr('extra_urls', false), true);
        $urls    = is_array($urls) ? array_filter($urls, is_string(...)) : [];
        $nb_rows = count($ips);
        if ($nb_rows < 2) {
            $nb_rows = 2;
        } elseif ($nb_rows > 10) {
            $nb_rows = 10;
        }

        Page::openModule(
            My::name(),
            Page::jsConfirmClose('opts-forms') .
            App::behavior()->callBehavior('adminPostEditor', $editor, 'construction', ['#construction_message'], 'xhtml') .
            Page::jsLoad('backend')
        );

        echo
        Page::breadcrumb([
            __('Plugins') => '',
            My::name()    => '',
        ]) .
        Notices::getNotices() .

        (new Div())->id('construction_options')->items([
            (new Form(My::id() . 'form'))->method('post')->action(My::manageUrl())->fields([
                (new Div())->class('fieldset')->items([
                    (new Text('h4', __('Configuration'))),
                    (new Para())->class('filed')->items([
                        (new Checkbox('construction_flag', $s->getBool('flag', false)))->value(1),
                        (new Label(__('Plugin activation'), Label::OUTSIDE_LABEL_AFTER))->for('construction_flag')->class('classic'),
                    ]),
                    (new Para())->items([
                        (new Label(__('Allowed IP:'), Label::OUTSIDE_LABEL_BEFORE))->for('construction_allowed_ip'),
                        (new Textarea('construction_allowed_ip', Html::escapeHTML(implode("\n", $ips))))->cols(20)->rows($nb_rows),
                    ]),
                    (new Note())->class('form-note')->text(sprintf(__('Your IP is <strong>%s</strong> - the allowed IP can view the blog normally.'), (string) Http::realIP())),
                    (new Para())->class('area')->items([
                        (new Label(__('Extra allowed URL types:'), Label::OUTSIDE_LABEL_BEFORE))->for('construction_extra_urls'),
                        (new Input('construction_extra_urls'))->size(20)->maxlength(255)->class('maximal')->value(Html::escapeHTML(implode(',', $urls))),
                    ]),
                ]),
                (new Div())->class('fieldset')->items([
                    (new Text('h4', __('Presentation'))),
                    (new Para())->class('area')->items([
                        (new Label(__('Title:'), Label::OUTSIDE_LABEL_BEFORE))->for('construction_title'),
                        (new Input('construction_title'))->size(20)->maxlength(255)->class('maximal')->value(Html::escapeHTML($s->getStr('title', false))),
                    ]),
                    (new Para())->class('area')->items([
                        (new Label(__('Message:'), Label::OUTSIDE_LABEL_BEFORE))->for('construction_message'),
                        (new Textarea('construction_message', Html::escapeHTML($s->getStr('message', false))))->cols(40)->rows(10),
                    ]),
                ]),
                (new Para())->items([
                    (new Submit(['saveconfig']))->value(__('Save')),
                    ... My::hiddenFields(),
                ]),
            ]),
        ])->render();

        Page::closeModule();
    }
}
