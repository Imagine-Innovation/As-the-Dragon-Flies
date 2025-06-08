<?php

namespace common\models;

use common\components\AppStatus;
use common\models\Image;
use common\models\PlayerCoin;
use Yii;

/**
 * This is the model class for table "player".
 *
 * @property int $id Primary key
 * @property int $level_id Foreign key to "level" table
 * @property int $user_id Foreign key to "user" table
 * @property int $race_id Foreign key to "race" table
 * @property int $class_id Foreign key to "character_class" table
 * @property int $background_id Foreign key to "background" table
 * @property int $history_id Foreign key to "history" table
 * @property int|null $alignment_id Foreign key to "alignment" table
 * @property int|null $image_id Foreign key to "image" table. May be empty if no avatar is chosen.
 * @property int|null $quest_id Optional foreign key to "quest" table
 * @property string|null $name Character name
 * @property int|null $status Status of the player (Deleted=0, Inactive=9, Active=10)
 * @property string|null $gender Gender: C=Child, F=Femelle, M=Male
 * @property int|null $age Age of the player. Validated regarding the actual race
 * @property int $experience_points Experience Points (XP)
 * @property int $hit_points Current Hit Points (HP)
 * @property int $max_hit_points Maximum Hit Points
 * @property int $armor_class Armor Class (AC)
 * @property int|null $created_at Creation timestamp
 * @property int|null $updated_at Last update timestamp
 *
 * @property Ability[] $abilities
 * @property Alignment $alignment
 * @property Background $background
 * @property CharacterClass $class
 * @property CreatureCondition[] $conditions
 * @property BackgroundHistory $history
 * @property Image $image
 * @property Item[] $cartItems
 * @property Item[] $items

 * @property Language[] $languages
 * @property Level $level
 * @property NotificationPlayer[] $notificationPlayers
 * @property Notification[] $triggeredNotifications
 * @property Notification[] $notifications
 * @property PlayerAbility[] $playerAbilities
 * @property PlayerCart[] $playerCarts
 * @property PlayerCoin[] $playerCoins
 * @property PlayerCondition[] $playerConditions
 * @property PlayerItem[] $playerItems
 * @property PlayerLanguage[] $playerLanguages
 * @property PlayerSkill[] $playerSkills
 * @property PlayerSpell[] $playerSpells
 * @property PlayerTrait[] $playerTraits
 * @property Quest $quest
 * @property QuestChat[] $questChats
 * @property QuestPlayer[] $questPlayers
 * @property QuestSession[] $questSessions
 * @property Quest[] $quests
 * @property Race $race
 * @property Skill[] $skills
 * @property Spell[] $spells
 * @property CharacterTrait[] $traits
 * @property User $user
 * @property UserLog[] $userLogs
 *
 * *********** Custom **********
 *
 * @property string $description
 * @property Notification[] $unreadNotifications
 *
 */
class Player extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['level_id', 'user_id', 'race_id', 'class_id', 'background_id', 'history_id', 'alignment_id', 'image_id', 'quest_id', 'status', 'age', 'experience_points', 'hit_points', 'max_hit_points', 'armor_class', 'created_at', 'updated_at'], 'integer'],
            [['user_id', 'race_id', 'class_id', 'background_id', 'history_id'], 'required'],
            [['gender'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['race_id'], 'exist', 'skipOnError' => true, 'targetClass' => Race::class, 'targetAttribute' => ['race_id' => 'id']],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
            [['alignment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Alignment::class, 'targetAttribute' => ['alignment_id' => 'id']],
            [['background_id'], 'exist', 'skipOnError' => true, 'targetClass' => Background::class, 'targetAttribute' => ['background_id' => 'id']],
            [['history_id'], 'exist', 'skipOnError' => true, 'targetClass' => BackgroundHistory::class, 'targetAttribute' => ['history_id' => 'id']],
            [['level_id'], 'exist', 'skipOnError' => true, 'targetClass' => Level::class, 'targetAttribute' => ['level_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
            ['status', 'default', 'value' => AppStatus::INACTIVE->value],
            ['status', 'in', 'range' => AppStatus::getValuesForPlayer()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'level_id' => 'Foreign key to \"level\" table',
            'user_id' => 'Foreign key to \"user\" table',
            'race_id' => 'Foreign key to \"race\" table',
            'class_id' => 'Foreign key to \"character_class\" table',
            'background_id' => 'Foreign key to \"background\" table',
            'history_id' => 'Foreign key to \"history\" table',
            'alignment_id' => 'Foreign key to \"alignment\" table',
            'image_id' => 'Foreign key to \"image\" table. May be empty if no avatar is chosen.',
            'quest_id' => 'Optional foreign key to \"quest\" table',
            'name' => 'Character name',
            'status' => 'Status of the player (Deleted=0, Inactive=9, Active=10)',
            'gender' => 'Gender: C=Child, F=Femelle, M=Male',
            'age' => 'Age of the player. Validated regarding the actual race',
            'experience_points' => 'Experience Points (XP)',
            'hit_points' => 'Current Hit Points (HP)',
            'max_hit_points' => 'Maximum Hit Points',
            'armor_class' => 'Armor Class (AC)',
            'created_at' => 'Creation timestamp',
            'updated_at' => 'Last update timestamp',
        ];
    }

    /**
     * Gets query for [[Abilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAbilities() {
        return $this->hasMany(Ability::class, ['id' => 'ability_id'])->via('playerAbilities');
    }

    /**
     * Gets query for [[Alignment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAlignment() {
        return $this->hasOne(Alignment::class, ['id' => 'alignment_id']);
    }

    /**
     * Gets query for [[Background]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBackground() {
        return $this->hasOne(Background::class, ['id' => 'background_id']);
    }

    /**
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClass() {
        return $this->hasOne(CharacterClass::class, ['id' => 'class_id']);
    }

    /**
     * Gets query for [[Conditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConditions() {
        return $this->hasMany(CreatureCondition::class, ['id' => 'condition_id'])->via('playerConditions');
    }

    /**
     * Gets query for [[History]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHistory() {
        return $this->hasOne(BackgroundHistory::class, ['id' => 'history_id']);
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getImage() {
        if ($this->image_id) {
            return $this->hasOne(Image::class, ['id' => 'image_id']);
        }
        return $this->hasOne(Image::class, ['id' => 1]);
    }

    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCartItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->via('playerCarts');
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->via('playerItems');
    }

    /**
     * Gets query for [[Languages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLanguages() {
        return $this->hasMany(Language::class, ['id' => 'language_id'])->via('playerLanguages');
    }

    /**
     * Gets query for [[Level]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLevel() {
        return $this->hasOne(Level::class, ['id' => 'level_id']);
    }

    /**
     * Gets query for [[NotificationPlayers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationPlayers() {
        return $this->hasMany(NotificationPlayer::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[TriggeredNotifications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTriggeredNotifications() {
        return $this->hasMany(Notification::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Notifications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotifications() {
        return $this->hasMany(Notification::class, ['id' => 'notification_id'])->via('notificationPlayers');
    }

    /**
     * Gets query for [[PlayerAbilities]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerAbilities() {
        return $this->hasMany(PlayerAbility::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerCarts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerCarts() {
        return $this->hasMany(PlayerCart::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerCoins]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerCoins() {
        return $this->hasMany(PlayerCoin::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerConditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerConditions() {
        return $this->hasMany(PlayerCondition::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerItems() {
        return $this->hasMany(PlayerItem::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerLanguages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerLanguages() {
        return $this->hasMany(PlayerLanguage::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerSkills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerSkills() {
        return $this->hasMany(PlayerSkill::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerSpells]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerSpells() {
        return $this->hasMany(PlayerSpell::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerTraits]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayerTraits() {
        return $this->hasMany(PlayerTrait::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuest() {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }

    /**
     * Gets query for [[QuestChats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestChats() {
        return $this->hasMany(QuestChat::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[QuestPlayers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestPlayers() {
        return $this->hasMany(QuestPlayer::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[QuestSessions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuestSessions() {
        return $this->hasMany(QuestSession::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Quests]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuests() {
        return $this->hasMany(Quest::class, ['id' => 'quest_id'])->via('questPlayers');
    }

    /**
     * Gets query for [[Race]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRace() {
        return $this->hasOne(Race::class, ['id' => 'race_id']);
    }

    /**
     * Gets query for [[Skills]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSkills() {
        return $this->hasMany(Skill::class, ['id' => 'skill_id'])->via('playerSkills');
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSpells() {
        return $this->hasMany(Spell::class, ['id' => 'spell_id'])->via('playerSpells');
    }

    /**
     * Gets query for [[Traits]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraits() {
        return $this->hasMany(CharacterTrait::class, ['id' => 'skill_id'])->via('playerTraits');
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[UserLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserLogs() {
        return $this->hasMany(UserLog::class, ['player_id' => 'id']);
    }

    /**
     * ************************
     *     Custom properties
     * ************************ */
    public function getAvatar() {
        if ($this->image) {
            return $this->image->file_name;
        }

        $query = Image::find()
                ->select(['image.file_name'])
                ->alias('image')
                ->innerJoin(['class_image' => 'class_image'], 'class_image.image_id = image.id')
                ->innerJoin(['race_group_image' => 'race_group_image'], 'image.id = race_group_image.image_id')
                ->innerJoin(['race' => 'race'], 'race_group_image.race_group_id = race.race_group_id');
        if ($this->race_id) {
            $query->andWhere(['race.id' => $this->race_id]);
        }
        if ($this->class_id) {
            $query->andWhere(['class_image.class_id' => $this->class_id]);
        }
        if ($this->gender) {
            $query->andWhere(['race_group_image.gender' => $this->gender]);
        }
        $images = $query->column();

        if (empty($images)) {
            return null;
        }
        return $images[array_rand($images)];
    }

    /**
     * Generates a description for the player character.
     *
     * @return string The description of the player character.
     */
    public function getDescription() {
        return 'A ' . $this->age . ' years old ' .
                ($this->gender == 'M' ? 'male' : 'femele') . ' ' .
                $this->race->name . ' ' .
                $this->level->name . ' ' .
                $this->class->name . ' ';
    }

    /**
     * Checks if a player is proficient with a specific item.
     *
     * @param int $item_id The ID of the item to check proficiency for.
     * @return bool|null Returns true if the player is proficient with the item, false if not,
     *                   or null if the player parameter is null.
     */
    public function isProficient($item_id) {
        $class = $this->class;
        $proficiency = ClassItem::findOne(['class_id' => $class->id, 'item_id' => $item_id]);

        return $proficiency ? true : false;
    }

    /**
     * Changes the status of the given model and updates its "updated_at" timestamp.
     *
     * @param string $status The new status to set.
     * @return bool Whether the status change was successful.
     */
    public function setStatus($status) {
        $this->status = $status;
        // Save the changes to the model and returns whether the save operation was successful
        return $this->save();
    }

    /**
     * Get unread notifications for this player
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUnreadNotifications() {
        return $this->hasMany(Notification::class, ['id' => 'notification_id'])
                        ->via('notificationPlayers', function ($query) {
                            $query->andWhere(['is_read' => 0]);
                        });
    }
}
