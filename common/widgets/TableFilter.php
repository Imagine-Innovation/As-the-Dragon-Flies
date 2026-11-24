<?php

declare(strict_types=1);

namespace common\widgets;

use yii\base\Widget;

/**
 * TableFilter widget renders the filter controls used above an AJAX-loaded table:
 * a value filter (inline checkboxes/radios or a combo box when more than 3 values
 * are provided) and an optional free-text search form.
 *
 * Usage:
 * <?= \common\widgets\TableFilter::widget([
 *     'filterArray' => \common\components\AppStatus::getUserStatus(),
 *     'multipleValues' => true,
 *     'searchString' => true,
 * ]) ?>
 */
class TableFilter extends Widget
{

    /**
     * @var array<int|string, string>|null $filterArray Values to filter on, as
     * filter => label pairs where the key is the value applied as a filter and
     * the value is the label displayed in the UI. A simple list of values is
     * supported as a shortcut: consecutive integer keys starting at 0 are
     * structurally identical in PHP to a plain list, so such keys cannot be
     * distinguished and each value is used as both the filter and its label.
     * Callers must offset 0-indexed value => label maps (e.g. use string keys
     * or explicit label pairs). If omitted (or empty), no filter control is displayed.
     */
    public ?array $filterArray = [];

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
     * AJAX table loader (e.g. TableFilterManager collects the checked inputs and the
     * selected options named filterName).
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
     * @var string|null $onLoad JavaScript call executed when a filter value changes or the
     * search form is submitted. When omitted, the generic TableFilterManager loader
     * (backend/web/js/table-filter.js) is called with the configured filterName and searchId.
     */
    public ?string $onLoad = null;

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
                    'onLoad' => $this->onLoad ?? $this->getDefaultOnLoad(),
        ]);
    }

    /**
     * Builds the default JS loader call, honoring the configured control names.
     *
     * @return string
     */
    private function getDefaultOnLoad(): string
    {
        $filterName = str_replace(["\"", "'", " "], '', trim($this->filterName));
        $searchId = str_replace(["\"", "'", " "], '', trim($this->searchId));
        return "TableFilterManager.loadGenericAjaxTable(0, \"{$filterName}\", \"{$searchId}\");";
    }

    /**
     * Normalizes $filterArray into filter => label pairs. A simple list of values
     * yields value => value pairs; an associative filter => label array yields
     * filter => label pairs.
     *
     * @return array<string, string>
     */
    private function getItems(): array
    {
        $filterArray = is_array($this->filterArray) ? $this->filterArray : [];
        if (array_is_list($filterArray)) {
            $items = [];
            foreach ($filterArray as $value) {
                $items[(string) $value] = (string) $value;
            }
            return $items;
        }

        $items = [];
        foreach ($filterArray as $filter => $label) {
            $items[(string) $filter] = (string) $label;
        }
        return $items;
    }
}
