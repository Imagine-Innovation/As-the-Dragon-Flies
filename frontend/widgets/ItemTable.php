<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

class ItemTable extends Widget {

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
            'property' => 'categories',
            'class' => '',
            'is-repeated' => true,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor', 'Weapon', 'Tool', 'Gear', 'Poison'],
        ],
        'Name' => [
            'column-header' => 'Name',
            'property' => 'name',
            'class' => '',
            'is-repeated' => false,
            'is-link' => true,
            'iconography' => null,
            'filter' => ['Armor', 'Weapon', 'Tool', 'Gear', 'Pack', 'Poison'],
        ],
        'Image' => [
            'column-header' => 'Image',
            'property' => 'fileName',
            'class' => '',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => 'image',
            'filter' => ['Armor', 'Weapon', 'Tool', 'Gear', 'Pack', 'Poison'],
        ],
        'Description' => [
            'column-header' => 'Description',
            'property' => 'description',
            'class' => '',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Pack'],
        ],
        'Cost' => [
            'column-header' => 'Cost',
            'property' => 'price',
            'class' => 'text-center',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor', 'Weapon', 'Tool', 'Gear', 'Pack', 'Poison'],
        ],
        'Quantity' => [
            'column-header' => 'Quantity',
            'property' => 'quantity',
            'class' => 'text-center',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Tool', 'Gear'],
        ],
        'Weight' => [
            'column-header' => 'Weight',
            'property' => 'weightString',
            'class' => 'text-center',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor', 'Weapon', 'Tool', 'Gear', 'Pack'],
        ],
        'Armor Class (AC)' => [
            'column-header' => 'Armor Class (AC)',
            'property' => 'armorClass',
            'class' => 'text-center',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor'],
        ],
        'Strength' => [
            'column-header' => 'Strength',
            'property' => 'armorStrength',
            'class' => 'text-center',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Armor'],
        ],
        'Disadvantage' => [
            'column-header' => 'Disadvantage',
            'property' => 'armorDisadvantage',
            'class' => 'text-center',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => 'icon',
            'filter' => ['Armor'],
        ],
        'Damage' => [
            'column-header' => 'Damage',
            'property' => 'weaponDamageDice',
            'class' => 'text-center',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Weapon'],
        ],
        'Properties' => [
            'column-header' => 'Properties',
            'property' => 'weaponProperties',
            'class' => '',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Weapon'],
        ],
        'Poison type' => [
            'column-header' => 'Poison type',
            'property' => 'poisonType',
            'class' => '',
            'is-repeated' => false,
            'is-link' => false,
            'iconography' => null,
            'filter' => ['Poison'],
        ],
    ];
    private const LUT_TYPE = ['Armor', 'Weapon', 'Tool', 'Gear', 'Pack', 'Poison'];

    public $items;
    public $itemType;
    private static $type;
    private static $models;
    private static $columns;
    private static $previousContent;

    public function init() {
        Yii::debug("*** Debug ***  ItemTable Widget - init()", __METHOD__);
        parent::init();

        self::$columns = self::COLS;
    }

    public function run() {
        Yii::debug("*** Debug ***  ItemTable Widget - run()", __METHOD__);
        Yii::debug("*** Debug ***  ItemTable Widget *** itemType=$this->itemType", __METHOD__);
        self::$previousContent = "";
        self::$type = self::LUT_TYPE[$this->itemType - 1];
        self::$models = $this->items;

        return $this->render('item-table');
    }

    /**
     * Renders the HTML content for a table cell based on the specified column
     * configuration, model, and column index.
     *
     * This function generates HTML content for a table cell, considering various
     * column configurations such as icons, scope (for the first column), unique
     * content, and links. It also maintains state to handle unique content display.
     *
     * @param array $col An associative array representing the configuration of the column.
     *                   It includes keys such as 'iconography' (string), 'class' (string),
     *                   'is-repeated' (boolean), 'is-link' (boolean).
     * @param common\models\Item $model The Item model containing information for the current row.
     * @param int $colIndex The index of the current column.
     *
     * @return string The HTML content for the table cell, including any necessary
     *                tags and formatting.
     */
    private static function renderTableCell($col, $model, $colIndex) {
        Yii::debug("*** Debug ***  ItemTable Widget - renderTableCell() - colIndex=$colIndex, col['property']=" . $col['property'], __METHOD__);
        // Retrieve the content for the table cell using the model's property.
        /*
          if ($col['property'] === "categories") {
          $categories = [];
          foreach ($model->categories as $category) {
          $categories[] = $category->name;
          }
          $cellContent = implode(", ", $categories);
          } else {
          $cellContent = $model[$col['property']];
          }
         *
         */
        $cellContent = $col['property'] === "categories" ? implode(", ", ArrayHelper::getColumn($model->categories, 'name')) : $model[$col['property']];

        // Format the content for display, considering whether it's an icon, an image
        // or needs HTML encoding.
        switch ($col['iconography']) {
            case 'icon':
                $display = '<i class="bi ' . $cellContent . '"></i>';
                break;
            case 'image':
                $display = '<img src="img/item/' . $cellContent . '" class="image-thumbnail">';
                break;
            default:
                $display = Html::encode($cellContent);
        }

        // Define the HTML tag properties, for the first column.
        $tag_name = ($colIndex === 0 ? 'th' : 'td');

        // Start building the HTML content for the table cell.
        $element = '<' . $tag_name . ' ' . ($colIndex === 0 ? 'scope="row"' : '') . ' class=" ' . $col['class'] . '">';

        // Handle unique content display based on the 'is-repeated' configuration.
        if ($col['is-repeated'] === false) {
            $display = $cellContent == self::$previousContent ? "&nbsp;" : $cellContent;
            self::$previousContent = $cellContent;
        }

        // Check if the column requires a link and construct the HTML accordingly.
        $innerHtml = $col['is-link'] ? '<a href="' . Url::toRoute(['item/view', 'id' => $model->id]) . '">' . $display . '</a>' : $display;

        // Return the final HTML content for the table cell.
        return $element . $innerHtml . "</$tag_name>";
    }

    public static function renderTableHeader() {
        Yii::debug("*** Debug ***  ItemTable Widget - renderTableHeader()", __METHOD__);
        $html = "";
        foreach (self::$columns as $col) {
            if (in_array(self::$type, $col['filter'])) {
                $html .= '<th class = "' . $col['class'] . '">' . $col['column-header'] . '</th>';
            }
        }
        return $html;
    }

    public static function renderTableBody() {
        Yii::debug("*** Debug ***  ItemTable Widget - renderTableBody()", __METHOD__);
        $html = "";
        foreach (self::$models as $item) {
            $html .= "<tr>";
            $i = 0;
            foreach (self::$columns as $col) {
                if (in_array(self::$type, $col['filter'])) {
                    $html .= self::renderTableCell($col, $item, $i++);
                } // endif
            } // endforeach column
            $html .= "</tr>";
        } // endforeach item i.e. line
        return $html;
    }
}
