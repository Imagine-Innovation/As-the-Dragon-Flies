<?php

namespace frontend\components;

use Yii;
use yii\web\Response;

class AjaxRequest {

    public CONST NOTAJAX = 0;
    public CONST FAILED = 1;
    public CONST NOTHING = 2;
    public CONST NOTFOUND = 3;
    public CONST NOTSAVED = 4;
    public CONST SAVED = 5;
    private CONST RESPONSE = [
        self::NOTAJAX => [
            'error' => true,
            'msg' => 'Not an Ajax POST request'
        ],
        self::FAILED => [
            'error' => true,
            'msg' => '%s failed'
        ],
        self::NOTHING => [
            'error' => true,
            'msg' => 'Nothing to delete'
        ],
        self::NOTFOUND => [
            'error' => true,
            'msg' => '%s not found'
        ],
        self::NOTSAVED => [
            'error' => false,
            'msg' => 'Could not save %s'
        ],
        self::SAVED => [
            'error' => false,
            'msg' => '%s saved'
        ],
    ];

    public $response;
    public $request;
    public $modelName;
    public $render;
    public $param = [];
    public $with = [];
    public $filter = [];
    public $select = [];
    public $innerJoin = [];
    public $sortOrder = [];
    public $defaultResponse;

    public function __construct($param) {
        $this->defaultResponse = self::RESPONSE;
        Yii::debug("*** Debug ***  __construct this->render=" . ($this->render ?? "null"), __METHOD__);
        //$this->render = $this->render ?? "ajax"; //default value
        foreach ($param as $key => $value) {
            if (is_array($value)) {
                Yii::debug($value, 'arrays', 'your-context-here');
            } else {
                Yii::debug("*** Debug ***  __construct param['$key']=" . ($value ?? 'null'), __METHOD__);
            }
            if (property_exists($this, $key)) {
                $this->$key = $value;
            } else {
                Yii::debug("*** Debug ***  __construct property '$key' does not exist", __METHOD__);
            }
        }
    }

    private function loadModels($query, $limit, $page) {
        $offset = $limit * $page;
        $models = $query->offset($offset)->limit($limit);

        if ($this->sortOrder) {
            $models->orderBy($this->sortOrder);
        }

        return $models->all();
    }

    private function buildQuery() {
        $modelName = 'common\\models\\' . $this->modelName;

        if ($this->innerJoin) {
            Yii::debug("*** Debug ***  buildQuery innerJoin " . count($this->innerJoin), __METHOD__);
            //$query = $model->find()->select($this->select ?? '*');
            $query = $modelName::find()->select($this->select ?? '*');

            foreach ($this->innerJoin as $join) {
                $query->innerJoin($join['table'], $join['clause']);
            }

            if ($this->filter) {
                $query->andWhere($this->filter);
            }
            return $query;
        }

        return $this->filter ? $modelName::find()->where($this->filter) : $modelName::find();
    }

    public function makeResponse($request) {
        $limit = $request->post('limit', 100);
        $pageNo = $request->post('page', 0);
        $query = $this->buildQuery();
        $count = $query->count();
        $pageCount = $count == 0 ? 1 : ceil($count / $limit);
        $page = max(0, min($pageNo, $pageCount));
        $render = $this->render ?? 'ajax';
        $this->response = [
            'error' => false, 'msg' => '',
            'content' => Yii::$app->controller->renderPartial($render, array_merge(
                            [
                                'models' => $this->loadModels($query, $limit, $page),
                                'count' => $count,
                                'page' => $page,
                                'pageCount' => $pageCount,
                                'limit' => $limit,
                            ], $this->param)
            )
        ];
        return true;
    }
}
