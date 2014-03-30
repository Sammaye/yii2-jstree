<?php

namespace sammaye\jstree;

use Yii;
use yii\helpers\Json;
use yii\helpers\Html;
use yii\widgets\InputWidget;
use sammaye\jstree\JsTreeAsset;

class JsTree extends InputWidget
{
	/**
	 * Plugins to load for the tree
	 * @var array
	 */
	public $plugins = ["themes", "html_data", "sort", "ui"];

	public $bind = [];

	/**
	 * Form the array that will be fed directly into the JQuery plugin
	 *
	 * @return The array that contains the configuration of the widget
	 */
	public function makeOptions()
	{
		$plugins_array = []; // We need to split out the listed plugins from their config
		$config_array = [];

		foreach($this->plugins as $plugin => $config){ // Scroll through the array given to us by the user

			$plugins_array[] = is_numeric($plugin) ? $config : $plugin; // If the array key is numeric then the user has put no config to the plugin

			if(!is_numeric($plugin)){ // Then add this plugin to the config list
				$config_array[$plugin] = $config;
			}
		}

		return array_merge(
			$config_array, ["plugins" => $plugins_array] // Mege the two so we have loaded plugins with their config
		);
	}

	/**
	 * @see framework/CWidget::run()
	 * @return $html The HTML of the tree object
	 */
	public function run()
	{
		JsTreeAsset::register($this->getView());
		
		$js_binds = '';
		foreach($this->bind as $event => $function){
			$js_binds .= "$('.js_tree_".$this->attribute." div').bind('".$event."', $function);";
		}

		$this->getView()->registerJs('
			$(function(){
				$(".js_tree_'.$this->attribute.' div").bind("loaded.jstree", function (event, data) {
					$c_selected = [];
					data.inst.get_checked().each(function(i, node){
						//console.log("node", node);
						$c_selected[$c_selected.length] = $(node).find(":checkbox").val();
					});
					//$(this).parent().children("input").val(JSON.stringify($c_selected));
				}).jstree(
					'.Json::encode($this->makeOptions()).'
				);

				//Array Remove - By John Resig (MIT Licensed)
				Array.prototype.remove = function(from, to) {
				  var rest = this.slice((to || from) + 1 || this.length);
				  this.length = from < 0 ? this.length + from : from;
				  return this.push.apply(this, rest);
				};

				$(".js_tree_'.$this->attribute.' div").bind("check_node.jstree", function(e, data){
					var obj = data.rslt.obj.children(":checkbox");

					if($(this).parent().children("input").val() == "" || $(this).parent().children("input").val() == null){
						$c_selected = []; // if empty make new object
					}else{
						$c_selected = JSON.parse($(this).parent().children("input").val()); // Get all currently selected in this list
					}

					// Search array for the value if it does not exist add it
					var found = false;
					for(var i=0; i<$c_selected.length; i++){
						if($c_selected[i] == obj.val())
							found = true;
					}

					if(!found){
						$c_selected[$c_selected.length] = obj.val();
					}
					$(this).parent().children("input").val(JSON.stringify($c_selected));
				});

				$(".js_tree_'.$this->attribute.' div").bind("uncheck_node.jstree", function(e, data){
					var obj = data.rslt.obj.children(":checkbox");

					if($(this).parent().children("input").val() == "" || $(this).parent().children("input").val() == null){
						$c_selected = []; // if empty make new object
					}else{
						$c_selected = JSON.parse($(this).parent().children("input").val()); // Get all currently selected in this list
					}

					// Search array for the value if it does not exist add it
					for(var i=0; i<$c_selected.length; i++){
						if($c_selected[i] == obj.val())
							$c_selected.remove(i);
					}
					$(this).parent().children("input").val(JSON.stringify($c_selected));
				});

				'.$js_binds.'
			});
		', yii\web\View::POS_READY); // Add the initial load of the JS widget to the page

		//$cs->registerScript('Yii.'.get_class($this).'#'.$id.'.binds', $js_binds);

		$html = Html::beginTag("div", ["class" => "js_tree_" . $this->attribute]); // Start building the html
			$html .= Html::beginTag("div");
			$html .= Html::endTag("div");
			$html .= Html::activeTextInput($this->model, $this->attribute, ["style" => "display:none;", 'value' => $this->value]);
		$html .= Html::endTag("div");

		echo $html; // Return the full tree and all its components
	}
}