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

if (dcCore::app()->blog->settings->construction->construction_flag)
{
	dcCore::app()->addBehavior('publicBeforeDocument',array('publicBehaviorsConstruction','checkVisitor'));
}

dcCore::app()->tpl->addValue('ConstructionMessage',array('tplConstruction','ConstructionMessage'));
dcCore::app()->tpl->addValue('ConstructionTitle',array('tplConstruction','ConstructionTitle'));

class publicBehaviorsConstruction 
{
	public static function checkVisitor($core)
	{
		$tplset = dcCore::app()->themes->moduleInfo(dcCore::app()->blog->settings->system->theme,'tplset');
        if (!empty($tplset) && is_dir(dirname(__FILE__).'/default-templates/'.$tplset)) {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), dirname(__FILE__).'/default-templates/'.$tplset);
        } else {
            dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), dirname(__FILE__).'/default-templates/'.DC_DEFAULT_TPLSET);
        }
		$all_allowed_ip = unserialize(dcCore::app()->blog->settings->construction->construction_allowed_ip);
		$extra_urls = unserialize(dcCore::app()->blog->settings->construction->construction_extra_urls);
		if (!in_array(http::realIP(),$all_allowed_ip))
		{
			dcCore::app()->url->registerDefault(array('urlConstruction','constructionHandler'));
			dcCore::app()->url->registerError(array('urlConstruction','default503'));
			
			foreach (dcCore::app()->url->getTypes() as $k=>$v)
			{
				if (($k != 'contactme') && !in_array($k,$extra_urls))
				{
					dcCore::app()->url->register($k,$v['url'],$v['representation'],array('urlConstruction','p503'));
				}
			}

		}
	}
}

class urlConstruction extends dcUrlHandlers
{
	public static function p503()
	{
		throw new Exception ("Blog under construction",503);
	}
	
	public static function default503($args,$type,$e)
	{
		//if ($e->getCode() == 503) {
		$_ctx =& $GLOBALS['_ctx'];
		$core =& $GLOBALS['core'];
	
		header('Content-Type: text/html; charset=UTF-8');
		http::head(503,'Service Unavailable');
		dcCore::app()->url->type = '503';
		$_ctx->current_tpl = '503.html';
		$_ctx->content_type = 'text/html';
	
		echo dcCore::app()->tpl->getData($_ctx->current_tpl);
	
		# --BEHAVIOR-- publicAfterDocument
		dcCore::app()->callBehavior('publicAfterDocument',$core);
		exit;
		//}
	}
	
	public static function constructionHandler($args)
	{
		$core =& $GLOBALS['core'];
		dcCore::app()->url->type = 'default';
		self::p503();
		return;
	}
}

class tplConstruction
{
	public static function ConstructionMessage($attr)
	{
	$core =& $GLOBALS['core'];
		$f = dcCore::app()->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, 'dcCore::app()->blog->settings->construction->construction_message').'; ?>';
	}
	
	public static function ConstructionTitle($attr)
	{
		$core =& $GLOBALS['core'];
		$f = dcCore::app()->tpl->getFilters($attr);
		return '<?php echo '.sprintf($f, 'dcCore::app()->blog->settings->construction->construction_title').'; ?>';
	}	
}