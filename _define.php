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
if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */		"Construction",
	/* Description*/	"Place your blog maintenance",
	/* Author */		"Osku and contributors",
	/* Version */		'1.4',
	/* Properties */
	array(
		'permissions' => 'admin',
		'priority' =>		2000,
		'type' => 'plugin',
		'dc_min' => '2.24',
		'support' => 'http://forum.dotclear.org/viewtopic.php?id=42875',
		'details' => 'http://plugins.dotaddict.org/dc2/details/construction'
		)
);