<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of construction, a plugin for Dotclear 2.
# 
# Copyright (c) 2010 Osku and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$p_url = 'plugin.php?p=' . basename(dirname(__FILE__));

$page_title = __('Construction');

dcCore::app()->blog->settings->addNamespace('construction');
$s =& dcCore::app()->blog->settings->construction;

$flag = $s->construction_flag;
$allowed_ip = array();
$myip = http::realIP();
// editeur pour le message
$post_format = dcCore::app()->auth->getOption('post_format');
  $post_editor = dcCore::app()->auth->getOption('editor');
  $admin_post_behavior = '';
    if ($post_editor && !empty($post_editor[$post_format])) {
      $admin_post_behavior = dcCore::app()->callBehavior('adminPostEditor', $post_editor[$post_format],
                                                       'user_desc', array('#user_desc')
            );
        }
if (!empty($_POST['saveconfig']))
{
	try
	{
		$flag = (empty($_POST['construction_flag']))?false:true;

		$s->put('construction_flag',$flag,'boolean','Construction blog flag');
		$all_ip = explode("\n",$_POST['construction_allowed_ip']);
		foreach ($all_ip as $ip) {
			$allowed_ip[] = trim($ip);
		}
		$urls = explode(",",$_POST['construction_extra_urls']);
		foreach ($urls as $url) {
			$extra_urls[] = trim($url);
		}
		$s->put('construction_allowed_ip',serialize($allowed_ip),'string','Construction blog allowed ip');
		$s->put('construction_title',$_POST['construction_title'],'string','Construction blog title');		
		$s->put('construction_message',$_POST['construction_message'],'string','Construction blog message');
		$s->put('construction_extra_urls',serialize($extra_urls),'string','Construction extra allowed URLs');

		dcCore::app()->blog->triggerBlog();
		http::redirect($p_url.'&saved=1');
	}
	catch (Exception $e)
	{
		dcCore::app()->error->add($e->getMessage());
	}
}

$nb_rows = count(unserialize($s->construction_allowed_ip));
if ($nb_rows < 2) {
	$nb_rows = 2;
} elseif ($nb_rows > 10) {
	$nb_rows = 10;
}
?>
<html>
<head>
	<title><?php echo $page_title; ?></title>
	<?php echo  dcPage::jsToolBar().
	$admin_post_behavior.
	dcPage::jsConfirmClose('opts-forms').
	dcPage::jsLoad('index.php?pf=construction/js/config.js'); ?>
</head>
<body>
<?php
	echo dcPage::breadcrumb(
		array(
			html::escapeHTML(dcCore::app()->blog->name) => '',
			'<span class="page-title">'.$page_title.'</span>' => ''
		));

if (!empty($msg)) {
  dcPage::message($msg);}
  if (!empty($_GET['saved'])) {
  dcPage::success(__('Configuration successfully updated.'));
}
	
echo '<div id="construction_options">
<form method="post" action="'.$p_url.'">
<div class="fieldset">
<h4>'.__('Configuration').'</h4>
<p class="field">'.
form::checkbox('construction_flag', 1, $s->construction_flag).
'<label class="classic" for="construction_flag">'.__('Plugin activation').'</label>
</p>
<p><label for="construction_allowed_ip">'.__('Allowed IP:').'&nbsp;</label>'.
form::textarea('construction_allowed_ip',20,$nb_rows,html::escapeHTML(implode("\n",unserialize($s->construction_allowed_ip)))).
'</p>
<p class="info">'.sprintf(__('Your IP is <strong>%s</strong> - the allowed IP can view the blog normally.'),$myip).'</p>
<p class="area"><label for="construction_extra_urls">'.__('Extra allowed URL types:').'</label>'.
form::field('construction_extra_urls',20,255,html::escapeHTML(implode(',',unserialize($s->construction_extra_urls))),'maximal').
'</p>
</div>
<div class="fieldset">
<h4>'.__('Presentation').'</h4>
<p class="area"><label for="construction_title">'.__('Title:').'</label>'.
form::field('construction_title',20,255,html::escapeHTML($s->construction_title),'maximal').
'</p>
<p class="area"><label for="construction_message">'.__('Message:').'</label>'.
form::textarea('construction_message',40,10,html::escapeHTML($s->construction_message)).
'</p>
</div>
<p>'.form::hidden(array('p'),'construction').
dcCore::app()->formNonce().
'<input type="submit" name="saveconfig" value="'.__('Save').'" />
</p>
</form>
</div>';
?>
</body>
</html>