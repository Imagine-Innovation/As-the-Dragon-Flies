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

    public function __construct(array $param) {
        $this->defaultResponse = self::RESPONSE;
        foreach ($param as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    private function loadModels(yii\db\ActiveQuery $query, int $limit, int $page): array {
        $offset = $limit * $page;
        $models = $query->offset($offset)->limit($limit);

        if ($this->sortOrder) {
            $models->orderBy($this->sortOrder);
        }

        return $models->all();
    }

    private function buildQuery(): yii\db\ActiveQuery {
        $modelName = 'common\\models\\' . $this->modelName;

        if ($this->innerJoin) {
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

    public function makeResponse(yii\web\Request $request): bool {
        $limit = $request->post('limit', 100);
        $pageNo = $request->post('page', 0);
        $query = $this->buildQuery();
        $count = $query->count();
        $pageCount = $count == 0 ? 1 : ceil($count / $limit);
        $page = max(0, min($pageNo, $pageCount));
        $render = $this->render ?? 'index';
        $this->response = [
            'error' => false, 'msg' => '',
            'content' => Yii::$app->controller->renderPartial("ajax/{$render}", array_merge(
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
