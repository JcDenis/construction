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

use dcAuth;
use dcCore;
use dcNsProcess;
use dcPage;
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

class Manage extends dcNsProcess
{
    public static function init(): bool
    {
        static::$init = defined('DC_CONTEXT_ADMIN')
            && !is_null(dcCore::app()->auth) && !is_null(dcCore::app()->blog) // nullsafe PHP < 8.0
            && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                dcAuth::PERMISSION_CONTENT_ADMIN,
            ]), dcCore::app()->blog->id);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return false;
        }

        $s = dcCore::app()->blog->settings->get(My::id());

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

                dcCore::app()->blog->triggerBlog();

                dcPage::addSuccessNotice(
                    __('Settings successfully updated.')
                );

                dcCore::app()->adminurl->redirect(
                    'admin.plugin.' . My::id()
                );
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        }

        return true;
    }

    public static function render(): void
    {
        if (!static::$init) {
            return;
        }

        // nullsafe PHP < 8.0
        if (is_null(dcCore::app()->auth) || is_null(dcCore::app()->blog) || is_null(dcCore::app()->adminurl)) {
            return;
        }

        $s       = dcCore::app()->blog->settings->get(My::id());
        $editor  = dcCore::app()->auth->getOption('editor');
        $nb_rows = count(json_decode($s->get('allowed_ip'), true));
        if ($nb_rows < 2) {
            $nb_rows = 2;
        } elseif ($nb_rows > 10) {
            $nb_rows = 10;
        }

        dcPage::openModule(
            My::name(),
            dcPage::jsConfirmClose('opts-forms') .
            dcCore::app()->callBehavior('adminPostEditor', $editor['xhtml'], 'construction', ['#construction_message'], 'xhtml') .
            dcPage::jsModuleLoad(My::id() . '/js/backend.js')
        );

        echo
        dcPage::breadcrumb([
            __('Plugins') => '',
            My::name()    => '',
        ]) .
        dcPage::notices() .

        (new Div())->id('construction_options')->items([
            (new Form(My::id() . 'form'))->method('post')->action(dcCore::app()->adminurl->get('admin.plugin.' . My::id()))->fields([
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
                    dcCore::app()->formNonce(false),
                    (new Hidden(['p'], 'construction')),
                    (new Submit(['saveconfig']))->value(__('Save')),
                ]),
            ]),
        ])->render();

        dcPage::closeModule();
    }
}
