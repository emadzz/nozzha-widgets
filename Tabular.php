<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace nozzha\widgets;

use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\View;

class Tabular extends \yii\base\Widget {

    /**
     *
     * @var \yii\widgets\ActiveForm
     */
    public $form;

    /**
     *
     * @var \yii\base\Model[]
     */
    public $models;

    /**
     *
     * @var array
     */
    public $delimiters = [
        'index' => '__index__',
        'rowNumber' => '{__no__}'
    ];

    /**
     *
     * @var \Closure
     */
    public $itemRenderer;

    /**
     *
     * @var string
     */
    public $template;

    /**
     *
     * @var \yii\base\Model
     */
    public $skeletonModel;

    /**
     *
     * @var string
     */
    public $jsFunction;

    /**
     * 
     * @var integer
     */
    public $jsFunctionPos = View::POS_HEAD;

    public function init() {
        parent::init();

        if (!$this->jsFunction) {
            $this->jsFunction = 'getSkeletonItem' . $this->form->id;
        }

        ob_start();
    }

    public function run() {
        $template = ob_get_clean();

        if (!$this->template) {
            $this->template = $template;
        }

        $this->registerSkeleton();

        return $this->renderItems();
    }

    protected function renderItems() {
        $items = '';

        $index = 0;
        foreach ($this->models As $model) {
            $items .= $this->renderItem($model, $index++);
        }

        return $items;
    }

    protected function renderItem($model, $index) {
        if ($this->itemRenderer instanceof \Closure) {
            $fields = call_user_func($this->itemRenderer, $this->form, $model,
                    $index);
        }

        $preParams = [
            $this->delimiters['index'] => $index,
            $this->delimiters['rowNumber'] => $index + 1
        ];

        $params = ArrayHelper::merge($fields, $preParams);

        return strtr($this->template, $params);
    }

    protected function registerSkeleton() {
        $params = call_user_func($this->itemRenderer, $this->form,
                $this->skeletonModel, '__index__');

        $attributes = [];

        foreach ($params As $param) {
            if ($param instanceof ActiveField &&
                    !empty($clientOptions = $param->getPublicClientOptions())) {
                $attributes[] = $clientOptions;
            }
        }

        $html = strtr($this->template, $params);

        $this->getView()->registerJs("
            function {$this->jsFunction}(index) {
                var iRegex = /" . preg_quote($this->delimiters['index']) . "/g;
                var nRegex = /" . preg_quote($this->delimiters['rowNumber']) . "/g;
                    
                var data = " . Json::encode(['attributes' => $attributes, 'html' => $html]) . ";
                
                for(var i in data.attributes) {
                    var attr = data.attributes[i];
                    
                    attr.id = attr.id.replace(iRegex, index);
                    attr.name = attr.name.replace(iRegex, index);
                    attr.container = attr.container.replace(iRegex, index);
                    attr.input = attr.input.replace(iRegex, index);
                }
                
                data.html = data.html.replace(iRegex, index).replace(nRegex, index+1);

                return data;
            }
        ", $this->jsFunctionPos);
    }

}
