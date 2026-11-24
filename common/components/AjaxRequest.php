<?php

namespace common\components;

use Yii;
use yii\db\ActiveQuery;
use yii\web\Request;

class AjaxRequest
{

    public const NOTAJAX = 0;
    public const FAILED = 1;
    public const NOTHING = 2;
    public const NOTFOUND = 3;
    public const NOTSAVED = 4;
    public const SAVED = 5;
    private const RESPONSE = [
        self::NOTAJAX => [
            'error' => true,
            'msg' => 'Not an Ajax POST request',
        ],
        self::FAILED => [
            'error' => true,
            'msg' => '%s failed',
        ],
        self::NOTHING => [
            'error' => true,
            'msg' => 'Nothing to delete',
        ],
        self::NOTFOUND => [
            'error' => true,
            'msg' => '%s not found',
        ],
        self::NOTSAVED => [
            'error' => false,
            'msg' => 'Could not save %s',
        ],
        self::SAVED => [
            'error' => false,
            'msg' => '%s saved',
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
                $query->innerJoin($join);
            }

            if ($this->filter) {
                $query->andWhere($this->filter);
            }

            if ($this->with) {
                $query->with($this->with);
            }
            return $query;
        }

        $query = $this->filter ? $modelName::find()->where($this->filter) : $modelName::find();
        if ($this->with) {
            $query->with($this->with);
        }
        return $query;
    }

    /**
     * Validates the raw values posted by the TableFilter widget. Only scalar
     * values that are valid integer representations and whitelisted are returned.
     *
     * @param mixed $posted the raw posted filter values
     * @param array<int> $allowedStatuses whitelist of acceptable values
     * @return array<int>|null the validated values, null when nothing usable was posted
     */
    public static function postedStatuses(mixed $posted, array $allowedStatuses): ?array
    {
        if (!is_array($posted)) {
            return null;
        }
        $statuses = self::validateStatuses($posted, $allowedStatuses);
        return $statuses === [] ? null : $statuses;
    }

    /**
     * @param array<mixed> $posted
     * @param array<int> $allowedStatuses
     * @return array<int>
     */
    private static function validateStatuses(array $posted, array $allowedStatuses): array
    {
        $statuses = [];
        foreach ($posted as $value) {
            $status = self::validatedStatus($value, $allowedStatuses);
            if ($status !== null) {
                $statuses[] = $status;
            }
        }
        return array_values(array_unique($statuses));
    }

    /**
     * @param array<int> $allowedStatuses
     */
    private static function validatedStatus(mixed $value, array $allowedStatuses): ?int
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }
        $status = filter_var($value, FILTER_VALIDATE_INT);
        if ($status === false || !in_array($status, $allowedStatuses, true)) {
            return null;
        }
        return $status;
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
        $pageCount = $count === 0 ? 1 : ceil($count / $limit);
        $page = (int) max(0, min($pageNo, $pageCount));
        $render = $this->render ?? 'index';
        $this->response = [
            'error' => false,
            'msg' => '',
            'content' => Yii::$app->controller->renderPartial("ajax/{$render}", array_merge([
                'models' => $this->loadModels($query, $limit, $page),
                'count' => $count,
                'page' => $page,
                'pageCount' => $pageCount,
                'limit' => $limit,
                            ], $this->param)),
        ];
        return true;
    }
}
