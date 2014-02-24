<?php
namespace sammaye\jstree;

use Yii;
use yii\web\AssetBundle;

class JsTreeAsset extends AssetBundle
{
	public $js = ['jquery.jstree.js'];
	
	public function init()
	{
		$this->sourcePath = __DIR__ . '/assets';
		parent::init();
	}
}