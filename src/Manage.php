<?php

declare(strict_types=1);

namespace Dotclear\Plugin\construction;

use Dotclear\App;
use Dotclear\Core\Process;
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
class Manage extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // nullsafe PHP < 8.0
        if (!App::blog()->isDefined()) {
            return false;
        }

        $s = My::settings();

        if (!empty($_POST['saveconfig'])) {
            try {
                $allowed_ip = [];
                foreach (explode("\n", $_POST['construction_allowed_ip']) as $ip) {
                    $allowed_ip[] = trim($ip);
                }
                $extra_urls = [];
                foreach (explode(',', $_POST['construction_extra_urls']) as $url) {
                    $extra_urls[] = trim($url);
                }

                $s->put('flag', !empty($_POST['construction_flag']));
                $s->put('allowed_ip', json_encode($allowed_ip));
                $s->put('title', $_POST['construction_title']);
                $s->put('message', $_POST['construction_message']);
                $s->put('extra_urls', json_encode($extra_urls));

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

        // nullsafe PHP < 8.0
        if (!App::blog()->isDefined()) {
            return;
        }

        $s       = My::settings();
        $editor  = App::auth()->getOption('editor');
        $nb_rows = count(json_decode($s->get('allowed_ip'), true));
        if ($nb_rows < 2) {
            $nb_rows = 2;
        } elseif ($nb_rows > 10) {
            $nb_rows = 10;
        }

        Page::openModule(
            My::name(),
            Page::jsConfirmClose('opts-forms') .
            App::behavior()->callBehavior('adminPostEditor', $editor['xhtml'], 'construction', ['#construction_message'], 'xhtml') .
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
                        (new Checkbox('construction_flag', (bool) $s->get('flag')))->value(1),
                        (new Label(__('Plugin activation'), Label::OUTSIDE_LABEL_AFTER))->for('construction_flag')->class('classic'),
                    ]),
                    (new Para())->items([
                        (new Label(__('Allowed IP:'), Label::OUTSIDE_LABEL_BEFORE))->for('construction_allowed_ip'),
                        (new Textarea('construction_allowed_ip', Html::escapeHTML(implode("\n", json_decode($s->get('allowed_ip'), true)))))->cols(20)->rows($nb_rows),
                    ]),
                    (new Note())->class('form-note')->text(sprintf(__('Your IP is <strong>%s</strong> - the allowed IP can view the blog normally.'), (string) Http::realIP())),
                    (new Para())->class('area')->items([
                        (new Label(__('Extra allowed URL types:'), Label::OUTSIDE_LABEL_BEFORE))->for('construction_extra_urls'),
                        (new Input('construction_extra_urls'))->size(20)->maxlenght(255)->class('maximal')->value(Html::escapeHTML(implode(',', json_decode($s->get('extra_urls'), true)))),
                    ]),
                ]),
                (new Div())->class('fieldset')->items([
                    (new Text('h4', __('Presentation'))),
                    (new Para())->class('area')->items([
                        (new Label(__('Title:'), Label::OUTSIDE_LABEL_BEFORE))->for('construction_title'),
                        (new Input('construction_title'))->size(20)->maxlenght(255)->class('maximal')->value(Html::escapeHTML($s->get('title'))),
                    ]),
                    (new Para())->class('area')->items([
                        (new Label(__('Message:'), Label::OUTSIDE_LABEL_BEFORE))->for('construction_message'),
                        (new Textarea('construction_message', Html::escapeHTML($s->get('message'))))->cols(40)->rows(10),
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
