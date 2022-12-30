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

dcCore::app()->blog->settings->addNamespace('construction');
$menu_class = '';

if (dcCore::app()->blog->settings->construction->construction_flag)
{
	dcCore::app()->addBehavior('adminPageHTMLHead','constructionadminPageHTMLHead');
	$menu_class = 'construction-blog';
}

$_menu['Blog']->addItem(__('Construction'),
	'plugin.php?p=construction','index.php?pf=construction/icon.png',
	preg_match('/plugin.php\?p=construction(&.*)?$/',$_SERVER['REQUEST_URI']),
	dcCore::app()->auth->check('admin',dcCore::app()->blog->id),
	$menu_class
);

function constructionadminPageHTMLHead()
{
	echo '<style type="text/css">'."\n".'@import "index.php?pf=construction/css/admin.css";'."\n".'</style>'."\n";
}

dcCore::app()->addBehavior('adminDashboardFavorites','constructionDashboardFavorites');

function constructionDashboardFavorites($core,$favs)
{
	$favs->register('construction', array(
		'title' => __('Construction'),
		'url' => 'plugin.php?p=construction',
		'small-icon' => 'index.php?pf=construction/icon.png',
		'large-icon' => 'index.php?pf=construction/icon-big.png',
		'permissions' => 'usage,contentadmin'
	));
}