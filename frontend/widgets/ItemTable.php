<?php

namespace frontend\widgets;

use common\models\Item;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

class ItemTable extends Widget
{

    /**
     *  Define constant for column configurations.
     *      column-header   Column header caption
     *      property        Item model property to display
     *      class           CSS class to apply
     *      is-repeated     True/False indicator to eliminate duplicate values.
     *                      Relie on the $previousValue property to define if the
     *                      current value is the same as the previous one
     *      is-link         True/False indicator that defines if the column
     *                      should contain a link to the Item View page
     *      iconography     Define what king of iconography to display: icon or image
     *      filter          Array of the the Item Types for which this column
     *                      should be displayed
     */
    private const COLS = [
        'Category' => [
            'column-header' => 'Category',
            'property' => 'category',
            'class' => '',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor', 'Weapon', 'Tool', 'Gear', 'Poison'],
        ],
        'Name' => [
            'column-header' => 'Name',
            'property' => 'name',
            'class' => '',
            'is-repeated' => true,
            'is-link' => true,
            'iconography' => null,
            'filter' => ['Armor', 'Shield', 'Weapon', 'Tool', 'Gear', 'Pack', 'Poison'],
        ],
        'Image' => [
            'column-header' => 'Image',
            'property' => 'image',
            'class' => '',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => 'image',
            'filter' => ['Armor', 'Shield', 'Weapon', 'Tool', 'Gear', 'Pack', 'Poison'],
        ],
        'Description' => [
            'column-header' => 'Description',
            'property' => 'description',
            'class' => 'w-50',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Pack'],
        ],
        'Cost' => [
            'column-header' => 'Cost',
            'property' => 'price',
            'class' => 'text-center',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor', 'Shield', 'Weapon', 'Tool', 'Gear', 'Pack', 'Poison'],
        ],
        'Quantity' => [
            'column-header' => 'Quantity',
            'property' => 'quantity',
            'class' => 'text-center',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Tool', 'Gear'],
        ],
        'Weight' => [
            'column-header' => 'Weight',
            'property' => 'pounds',
            'class' => 'text-center',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor', 'Shield', 'Weapon', 'Tool', 'Gear', 'Pack'],
        ],
        'Armor Class (AC)' => [
            'column-header' => 'Armor Class (AC)',
            'property' => 'armorClass',
            'class' => 'text-center',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor', 'Shield'],
        ],
        'Strength' => [
            'column-header' => 'Strength',
            'property' => 'armorStrength',
            'class' => 'text-center',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor'],
        ],
        'Disadvantage' => [
            'column-header' => 'Disadvantage',
            'property' => 'armorDisadvantage',
            'class' => 'text-center',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => 'icon',
            'filter' => ['Armor'],
        ],
        'Damage' => [
            'column-header' => 'Damage',
            'property' => 'damageDice',
            'class' => 'text-center',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Weapon'],
        ],
        'Properties' => [
            'column-header' => 'Properties',
            'property' => 'weaponProperties',
            'class' => '',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Weapon'],
        ],
        'Poison type' => [
            'column-header' => 'Poison type',
            'property' => 'poisonType',
            'class' => '',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Poison'],
        ],
    ];
    private const LUT_TYPE = ['Armor', 'Weapon', 'Tool', 'Gear', 'Pack', 'Poison', 'Shield', 'Scroll'];

    /** @var Item[] $items */
    public array $items = [];
    public int $itemTypeId;
    private string $type;
    private string $previousContent;

    /** @var array<string, array<string, mixed>> $columns */
    private array $columns;

    /**
     * {@inheritdoc}
     */
    public function init(): void {
        Yii::debug("*** Debug ***  ItemTable Widget - init()", __METHOD__);
        parent::init();

        $this->columns = self::COLS;
    }

    /**
     *
     * @return string
     */
    public function run(): string {
        Yii::debug("*** Debug ***  ItemTable Widget - run(), itemTypeId={$this->itemTypeId}");
        $this->previousContent = "";
        $this->type = self::LUT_TYPE[$this->itemTypeId - 1];

        return $this->render('item-table', [
                    'tableHeader' => $this->renderTableHeader(),
                    'tableBody' => $this->renderTableBody(),
        ]);
    }

    /**
     * Renders the HTML content for a table cell based on the specified column
     * configuration, model, and column index.
     *
     * This function generates HTML content for a table cell, considering various
     * column configurations such as icons, scope (for the first column), unique
     * content, and links. It also maintains state to handle unique content display.
     *
     * @param array<string, mixed> $col An associative array representing the configuration of the column.
     *                   It includes keys such as 'iconography' (string), 'class' (string),
     *                   'is-repeated' (boolean), 'is-link' (boolean).
     * @param Item $model The Item model containing information for the current row.
     * @param int $colIndex The index of the current column.
     *
     * @return string The HTML content for the table cell, including any necessary
     *                tags and formatting.
     */
    private function renderTableCell(array $col, Item $model, int $colIndex): string {
        Yii::debug("*** Debug ***  ItemTable Widget - renderTableCell() - colIndex={$colIndex}, col['property']={$col['property']}");
        // Retrieve the content for the table cell using the model's property.
        $cellContent = ($col['property'] === 'categories') ?
                implode(", ", ArrayHelper::getColumn($model->categories, 'name')) :
                $model[$col['property']];

        Yii::debug("*** Debug ***  ItemTable Widget - renderTableCell() - cellContent={$cellContent}");

        // Format the content for display, considering whether it's an icon, an image
        // or needs HTML encoding.
        $display = match ($col['iconography']) {
            'icon' => "<i class=\"bi {$cellContent}\"></i>",
            'image' => "<img src=\"img/item/{$cellContent}\" class=\"image-thumbnail\">",
            default => Html::encode($cellContent)
        };

        Yii::debug("*** Debug ***  ItemTable Widget - renderTableCell() - display={$display}");

        // Define the HTML tag properties, for the first column.
        $tagName = ($colIndex === 0) ? 'th' : 'td';

        // Start building the HTML content for the table cell.
        $scope = ($colIndex === 0) ? ' scope="row"' : '';
        $element = "<{$tagName}{$scope} class=\"{$col['class']}\">";

        // Handle unique content display based on the 'is-repeated' configuration.
        if ($col['is-repeated'] === false) {
            $display = ($cellContent === $this->previousContent) ? '&nbsp;' : $cellContent;
            $this->previousContent = $cellContent;
        }

        // Check if the column requires a link and construct the HTML accordingly.
        $innerHtml = $col['is-link'] ?
                '<a href="' . Url::toRoute(['item/view', 'id' => $model->id]) . '">' . $display . '</a>' :
                $display;

        // Return the final HTML content for the table cell.
        return $element . $innerHtml . "</{$tagName}>";
    }

    /**
     *
     * @return string
     */
    public function renderTableHeader(): string {
        $html = "";
        foreach ($this->columns as $col) {
            if (in_array($this->type, $col['filter'])) {
                $html .= '<th class = "' . $col['class'] . '">' . $col['column-header'] . '</th>';
            }
        }
        return $html;
    }

    /**
     *
     * @return string
     */
    public function renderTableBody(): string {
        $html = '';
        foreach ($this->items as $item) {
            $html .= '<tr>';
            $i = 0;
            foreach ($this->columns as $col) {
                if (in_array($this->type, $col['filter'])) {
                    $html .= $this->renderTableCell($col, $item, $i++);
                }
            }
            $html .= "</tr>";
        }
        return $html;
    }
}
