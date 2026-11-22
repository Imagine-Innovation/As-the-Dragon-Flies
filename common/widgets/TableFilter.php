<?php

namespace common\widgets;

use yii\base\Widget;

/**
 * TableFilter widget renders the filter controls used above an AJAX-loaded table:
 * a value filter (inline checkboxes/radios or a combo box when more than 3 values
 * are provided) and an optional free-text search form.
 *
 * Usage:
 * <?= \common\widgets\TableFilter::widget([
 *     'filterArray' => \common\components\AppStatus::getValuesForUser(),
 *     'multipleValues' => true,
 *     'searchString' => true,
 * ]) ?>
 */
class TableFilter extends Widget
{

    /**
     * @var array<int|string, int|string> $filterArray List of possible values to filter on.
     * A plain list of values (the label is the value itself) or value => label pairs.
     * If omitted (or empty), no filter control is displayed.
     */
    public array $filterArray = [];

    /**
     * @var bool $multipleValues Whether several values can be filtered at the same time.
     * Has no action when $filterArray is omitted.
     */
    public bool $multipleValues = true;

    /**
     * @var bool $searchString Whether the free-text search form is added.
     */
    public bool $searchString = false;

    /**
     * @var string $filterName Name of the filter inputs, as selected by the generic
     * AJAX table loader (e.g. AccessRightManager collects input[type=checkbox][name=application]).
     */
    public string $filterName = 'application';

    /**
     * @var string $searchId Id and name of the search input.
     */
    public string $searchId = 'routeSearch';

    /**
     * @var string $searchPlaceholder Placeholder displayed in the search input.
     */
    public string $searchPlaceholder = 'Search route';

    /**
     * @var string $onLoad JavaScript call executed when a filter value changes or the
     * search form is submitted.
     */
    public string $onLoad = 'AccessRightManager.loadGenericAjaxTable(0);';

    /**
     *
     * @return string
     */
    public function run(): string
    {
        $items = $this->getItems();
        if ($items === [] && !$this->searchString) {
            return '';
        }
        return $this->render('table-filter', [
                    'items' => $items,
                    'multipleValues' => $this->multipleValues,
                    'searchString' => $this->searchString,
                    'filterName' => $this->filterName,
                    'searchId' => $this->searchId,
                    'searchPlaceholder' => $this->searchPlaceholder,
                    'onLoad' => $this->onLoad,
        ]);
    }

    /**
     * Normalizes $filterArray into value => label pairs.
     * A simple list of values yields value => value pairs; an associative
     * filter => label array yields filter => label pairs.
     *
     * @return array<string, string>
     */
    private function getItems(): array
    {
        if (array_is_list($this->filterArray)) {
            $items = [];
            foreach ($this->filterArray as $value) {
                $items[(string) $value] = (string) $value;
            }
            return $items;
        }

        $items = [];
        foreach ($this->filterArray as $filter => $label) {
            $items[(string) $filter] = (string) $label;
        }
        return $items;
    }
}
