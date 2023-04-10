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

dcPage::check(dcCore::app()->auth->makePermissions([
    dcAuth::PERMISSION_ADMIN,
]));

$s = dcCore::app()->blog->settings->get(basename(__DIR__));

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

        $s->put('flag', empty($_POST['construction_flag']) ? false : true);
        $s->put('allowed_ip', json_encode($allowed_ip));
        $s->put('title', $_POST['construction_title']);
        $s->put('message', $_POST['construction_message']);
        $s->put('extra_urls', json_encode($extra_urls));

        dcCore::app()->blog->triggerBlog();

        dcAdminNotices::addSuccessNotice(
            __('Settings successfully updated.')
        );

        dcCore::app()->adminurl->redirect(
            'admin.plugin.' . basename(__DIR__)
        );
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

$nb_rows = count(json_decode($s->get('allowed_ip'), true));
if ($nb_rows < 2) {
    $nb_rows = 2;
} elseif ($nb_rows > 10) {
    $nb_rows = 10;
}

$editor = dcCore::app()->auth->getOption('editor');

echo '
<html><head><title>' . __('Construction') . '</title>' .
dcPage::jsConfirmClose('opts-forms') .
dcCore::app()->callBehavior('adminPostEditor', $editor['xhtml'], 'construction', ['#construction_message'], 'xhtml') .
dcPage::jsModuleLoad(basename(__DIR__) . '/js/index.js') . '
</head><body>' .

dcPage::breadcrumb([
    __('Plugins')      => '',
    __('Construction') => '',
]) .
dcPage::notices() . '

<div id="construction_options">
<form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.' . basename(__DIR__)) . '">
<div class="fieldset">
<h4>' . __('Configuration') . '</h4>

<p class="field">' .
form::checkbox('construction_flag', 1, $s->get('flag')) . '
<label class="classic" for="construction_flag">' . __('Plugin activation') . '</label>
</p>

<p><label for="construction_allowed_ip">' . __('Allowed IP:') . '</label> ' .
form::textarea('construction_allowed_ip', 20, $nb_rows, html::escapeHTML(implode("\n", json_decode($s->get('allowed_ip'), true)))) .
'</p>
<p class="form-note">' . sprintf(__('Your IP is <strong>%s</strong> - the allowed IP can view the blog normally.'), (string) http::realIP()) . '</p>

<p class="area"><label for="construction_extra_urls">' . __('Extra allowed URL types:') . '</label>' .
form::field('construction_extra_urls', 20, 255, html::escapeHTML(implode(',', json_decode($s->get('extra_urls'), true))), 'maximal') .
'</p>

</div>
<div class="fieldset">
<h4>' . __('Presentation') . '</h4>
<p class="area"><label for="construction_title">' . __('Title:') . '</label>' .
form::field('construction_title', 20, 255, html::escapeHTML($s->get('title')), 'maximal') .
'</p>

<p class="area"><label for="construction_message">' . __('Message:') . '</label>' .
form::textarea('construction_message', 40, 10, html::escapeHTML($s->get('message'))) .
'</p>
</div>
<p>' .
form::hidden(['p'], 'construction') .
dcCore::app()->formNonce() . '
<input type="submit" name="saveconfig" value="' . __('Save') . '" />
</p>
</form>
</div>

</body>
</html>';
