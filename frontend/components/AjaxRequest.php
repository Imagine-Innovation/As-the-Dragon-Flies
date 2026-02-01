<?php

namespace frontend\components;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\Request;

class AjaxRequest
{

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

    /** @var array{error: bool, msg: string, content?: string} $response */
    public array $response;
    public Request $request;
    public string $modelName;
    public ?string $render = null;

    /** @var array<string, mixed> $param */
    public array $param = [];

    /** @var array<string, mixed>|null $with */
    public ?array $with = null;

    /** @var array<string, mixed>|null $filter */
    public ?array $filter = null;

    /** @var array<string, mixed>|null $select */
    public ?array $select = null;

    /** @var array<array{table: string, clause: string}>|null $innerJoin */
    public ?array $innerJoin = null;

    /** @var array<string, mixed>|null $sortOrder */
    public ?array $sortOrder = null;

    /** @var array<int, array<string, bool|string>> $defaultResponse */
    public array $defaultResponse = [];

    /**
     *
     * @param array<string, mixed> $param
     */
    public function __construct(array $param)
    {
        $this->defaultResponse = self::RESPONSE;
        foreach ($param as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     *
     * @template T of \yii\db\ActiveRecord
     * @param ActiveQuery<T> $query
     * @param int $limit
     * @param int $page
     * @return T[]  // Array of ActiveRecord models of type T
     */
    private function loadModels(ActiveQuery $query, int $limit, int $page): array
    {
        $offset = $limit * $page;
        $models = $query->offset($offset)->limit($limit);

        if ($this->sortOrder) {
            $models->orderBy($this->sortOrder);
        }

        return $models->all();
    }

    /**
     *
     * @template T of \yii\db\ActiveRecord
     * @return \yii\db\ActiveQuery<T>
     * @phpstan-ignore-next-line
     */
    private function buildQuery(): ActiveQuery
    {
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

    /**
     *
     * @param Request $request
     * @return bool
     */
    public function makeResponse(Request $request): bool
    {
        $postLimit = $request->post('limit');
        $postPageNo = $request->post('page');

        $limit = is_numeric($postLimit) ? (int) $postLimit : 100;
        $pageNo = is_numeric($postPageNo) ? (int) $postPageNo : 0;

        $query = $this->buildQuery();
        $count = (int) $query->count();
        $pageCount = ($count === 0) ? 1 : ceil($count / $limit);
        $page = (int) max(0, min($pageNo, $pageCount));
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
