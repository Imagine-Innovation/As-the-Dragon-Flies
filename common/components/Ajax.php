<?php

namespace common\components;

use Yii;
use yii\base\Component;

class Ajax extends Component {

    /**
     * Convert an array into a string. The output format is 
     * e(0,0), e(0,1), ...e(0,n); e(1,0), ...e(1,n);...; e(m,0), ...e(m,n);
     * The output string will be processed by Client-Side JS to rebuild the initial array
     * 
     * @param Array $rows Table of the different ages of the player
     * 
     * @return string
     * */
    public static function encodeJSTable($rows) {
        $table = [];
        foreach ($rows as $row) {
            $table[] = implode(", ", $row);
        }
        $jsTable = implode("; ", $table);

        return $jsTable;
    }

    /**
     * Decodes abilities from a serialized string into an associative array.
     *
     * The serialized string contains tuples separated by "|", where each tuple consists
     * of an ability ID and its corresponding value separated by ",".
     *
     * @param string $table The serialized string containing abilities.
     *
     * @return array An associative array where keys are ability IDs
     *               and values are their corresponding values.
     */
    public static function decodeJSTable($table) {
        $array = [];
        $rows = explode("|", $table);
        foreach ($rows as $row) {
            if (str_contains($row, ',')) {
                $element = explode(",", $row);
                $array[$element[0]] = $element[1];
            } else {
                $array[] = $row;
            }
        }
        return $array;
    }
}
