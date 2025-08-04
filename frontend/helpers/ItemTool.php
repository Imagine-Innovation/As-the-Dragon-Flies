<?php

namespace frontend\helpers;

use common\models\PlayerItem;
use common\models\Weapon;

class ItemTool {

    // Define the available weapon properties as an associative array.
    // /!\ Don't forget to update this array when the data model changes
    Const FULL_WEAPON_PROPERTIES = [
        'need_ammunition' => 'Need ammunition',
        'is_two_handed' => 'Two Handed',
        'is_finesse' => 'Finesse',
        'is_heavy' => 'Heavy',
        'is_light' => 'Light',
        'is_loading' => 'Loading',
        'is_range' => 'Range (%s)',
        'is_reach' => 'Reach',
        'is_special' => 'Special',
        'is_thrown' => 'Thrown (%s)',
        'is_versatile' => 'Versatile (%s)',
    ];
    Const LITE_WEAPON_PROPERTIES = [
        'is_two_handed' => 'Two Handed',
        'is_finesse' => 'Finesse',
        'is_range' => 'Range (%s)',
        'is_reach' => 'Reach',
        'is_special' => 'Special',
        'is_thrown' => 'Thrown (%s)',
        'is_versatile' => 'Versatile (%s)',
    ];

    private static function getWeaponProperties(Weapon &$weapon, array $propertiesConst): string {

        $properties = [];

        foreach ($propertiesConst as $property => $displayName) {
            if ($weapon->$property) {
                if (str_contains($displayName, '%s')) {
                    $value = ($property == 'is_versatile') ?
                            $weapon->versatile_dice :
                            $weapon->range_min . '-' . $weapon->range_max;
                    $properties[] = sprintf($displayName, $value);
                } else {
                    $properties[] = $displayName;
                }
            }
        }
        return implode(", ", $properties);
    }

    public static function getFullWeaponProperties(Weapon &$weapon): string {
        return self::getWeaponProperties($weapon, self::FULL_WEAPON_PROPERTIES);
    }

    public static function getLiteWeaponProperties(Weapon &$weapon): string {
        return self::getWeaponProperties($weapon, self::LITE_WEAPON_PROPERTIES);
    }

    public static function getRemainingAmunitions(PlayerItem &$playerItem): string|null {
        $weapon = $playerItem->weapon;

        if (!$weapon->need_ammunition || !$weapon->amunition_id) {
            return null;
        }

        $amunition = $weapon->amunition->name;

        $weaponAmunition = PlayerItem::findOne(['player_id' => $playerItem->player_id, 'item_id' => $weapon->amunition_id]);
        if (!$weaponAmunition) {
            return "You need {$amunition} to use it";
        }

        switch ($weaponAmunition->quantity) {
            case 0;
                return "No more {$amunition}";
            case 1;
                return "Only one {$amunition} left";
            default:
                return "{$weaponAmunition->quantity} {$amunition}s left";
        }
    }
}
