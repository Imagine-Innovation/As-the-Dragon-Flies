<?php

namespace common\models;

use common\components\AppStatus;
use frontend\components\PlayerComponent;
use common\models\Image;
use common\models\PlayerCoin;
use common\models\Weapon;
use Yii;

/**
 * This is the model class for table "player".
 *
 * @property int $id Primary key
 * @property int $class_id Foreign key to “character_class” table
 * @property int $level_id Foreign key to “level” table
 * @property int $user_id Foreign key to “user” table
 * @property int $race_id Foreign key to “race” table
 * @property int $background_id Foreign key to “background” table
 * @property int|null $alignment_id Foreign key to “alignment” table
 * @property int|null $image_id Foreign key to “image” table. May be empty if no avatar is chosen.
 * @property int|null $quest_id Optional foreign key to “quest” table
 * @property string|null $name Character name
 * @property string|null $description Player description
 * @property int|null $status Status of the player (Deleted=0, Inactive=9, Active=10)
 * @property string|null $gender Gender: C=Child, F=Femelle, M=Male
 * @property int|null $age Age of the player. Validated regarding the actual race
 * @property int $experience_points Experience Points (XP)
 * @property int $hit_points Current Hit Points (HP)
 * @property int $max_hit_points Maximum Hit Points
 * @property int $armor_class Armor Class (AC)
 * @property int|null $speed Actual speed
 * @property int|null $created_at Creation timestamp
 * @property int|null $updated_at Last update timestamp
 *
 * @property Ability[] $abilities
 * @property Alignment|null $alignment
 * @property Background $background
 * @property CharacterClass $class
 * @property Image|null $image
 * @property Item[] $cartItems
 * @property Item[] $items
 * @property Language[] $languages
 * @property Level $level
 * @property NotificationPlayer[] $notificationPlayers
 * @property Notification[] $triggeredNotifications
 * @property Notification[] $notifications
 * @property PlayerAbility[] $playerAbilities
 * @property PlayerBody $playerBody
 * @property PlayerCart[] $playerCarts
 * @property PlayerCoin[] $playerCoins
 * @property PlayerItem[] $playerItems
 * @property PlayerLanguage[] $playerLanguages
 * @property PlayerSkill[] $playerSkills
 * @property PlayerSpell[] $playerSpells
 * @property PlayerTrait[] $playerTraits
 * @property Quest $quest
 * @property QuestPlayer[] $questPlayers
 * @property QuestSession[] $questSessions
 * @property QuestTurn[] $questTurns
 * @property Quest[] $initiatedQuests
 * @property Quest $questToPlay
 * @property Quest[] $quests
 * @property Race $race
 * @property Skill[] $skills
 * @property Spell[] $spells
 * @property CharacterTrait[] $traits
 * @property User $user
 * @property UserLog[] $userLogs
 *
 * *********** Custom Properties **********
 *
 * @property Item[] $weapons
 * @property string|null $avatar
 * @property string $description
 * @property Notification[] $unreadNotifications
 *
 * *********** Custom Methods **********
 *
 * bool|null isProficient(int $itemId)
 * bool setStatus(int $status)
 * bool|null addCoins(?int $quantity, string $coin = 'gp')
 * bool|null addItems(?int $itemId, int $quantity = 1
 *
 */
class Player extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const GENDER_C = 'C';
    const GENDER_F = 'F';
    const GENDER_M = 'M';
    // Game master Id
    const GAME_MASTER_ID = 1;

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
            [['alignment_id', 'image_id', 'quest_id', 'name', 'description', 'gender', 'age', 'speed', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['level_id'], 'default', 'value' => 1],
            [['status'], 'default', 'value' => AppStatus::INACTIVE->value],
            [['status'], 'in', 'range' => AppStatus::getValuesForPlayer()],
            [['experience_points'], 'default', 'value' => 0],
            [['armor_class'], 'default', 'value' => 10],
            [['class_id', 'user_id', 'race_id', 'background_id'], 'required'],
            [['class_id', 'level_id', 'user_id', 'race_id', 'background_id', 'alignment_id', 'image_id', 'quest_id', 'status', 'age', 'experience_points', 'hit_points', 'max_hit_points', 'armor_class', 'speed', 'created_at', 'updated_at'], 'integer'],
            [['description', 'gender'], 'string'],
            [['name'], 'string', 'max' => 64],
            ['name', 'match', 'pattern' => '/^[a-zA-Z0-9\s]+$/', 'message' => 'Name can only contain alphanumeric characters and spaces.'],
            ['gender', 'in', 'range' => array_keys(self::optsGender())],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['race_id'], 'exist', 'skipOnError' => true, 'targetClass' => Race::class, 'targetAttribute' => ['race_id' => 'id']],
            [['class_id'], 'exist', 'skipOnError' => true, 'targetClass' => CharacterClass::class, 'targetAttribute' => ['class_id' => 'id']],
            [['alignment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Alignment::class, 'targetAttribute' => ['alignment_id' => 'id']],
            [['background_id'], 'exist', 'skipOnError' => true, 'targetClass' => Background::class, 'targetAttribute' => ['background_id' => 'id']],
            [['level_id'], 'exist', 'skipOnError' => true, 'targetClass' => Level::class, 'targetAttribute' => ['level_id' => 'id']],
            [['image_id'], 'exist', 'skipOnError' => true, 'targetClass' => Image::class, 'targetAttribute' => ['image_id' => 'id']],
            [['quest_id'], 'exist', 'skipOnError' => true, 'targetClass' => Quest::class, 'targetAttribute' => ['quest_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'Primary key',
            'class_id' => 'Foreign key to “character_class” table',
            'level_id' => 'Foreign key to “level” table',
            'user_id' => 'Foreign key to “user” table',
            'race_id' => 'Foreign key to “race” table',
            'background_id' => 'Foreign key to “background” table',
            'alignment_id' => 'Foreign key to “alignment” table',
            'image_id' => 'Foreign key to “image” table. May be empty if no avatar is chosen.',
            'quest_id' => 'Optional foreign key to “quest” table',
            'name' => 'Character name',
            'description' => 'Player description',
            'status' => 'Status of the player (Deleted=0, Inactive=9, Active=10)',
            'gender' => 'Gender: C=Child, F=Femelle, M=Male',
            'age' => 'Age of the player. Validated regarding the actual race',
            'experience_points' => 'Experience Points (XP)',
            'hit_points' => 'Current Hit Points (HP)',
            'max_hit_points' => 'Maximum Hit Points',
            'armor_class' => 'Armor Class (AC)',
            'speed' => 'Actual speed',
            'created_at' => 'Creation timestamp',
            'updated_at' => 'Last update timestamp',
        ];
    }

    /**
     * Gets query for [[Abilities]].
     *
     * @return \yii\db\ActiveQuery<Ability>
     */
    public function getAbilities() {
        return $this->hasMany(Ability::class, ['id' => 'ability_id'])->viaTable('player_ability', ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Alignment]].
     *
     * @return \yii\db\ActiveQuery<Alignment>
     */
    public function getAlignment() {
        return $this->hasOne(Alignment::class, ['id' => 'alignment_id']);
    }

    /**
     * Gets query for [[Background]].
     *
     * @return \yii\db\ActiveQuery<Background>
     */
    public function getBackground() {
        return $this->hasOne(Background::class, ['id' => 'background_id']);
    }

    /**
     * Gets query for [[Class]].
     *
     * @return \yii\db\ActiveQuery<CharacterClass>
     */
    public function getClass() {
        return $this->hasOne(CharacterClass::class, ['id' => 'class_id']);
    }

    /**
     * Gets query for [[Image]].
     *
     * @return \yii\db\ActiveQuery<Image>
     */
    public function getImage() {
        return $this->hasOne(Image::class, ['id' => 'image_id']);
    }

    /**
     * Gets query for [[CartItems]].
     *
     * @return \yii\db\ActiveQuery<Item>
     */
    public function getCartItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->viaTable('player_cart', ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Items]].
     *
     * @return \yii\db\ActiveQuery<Item>
     */
    public function getItems() {
        return $this->hasMany(Item::class, ['id' => 'item_id'])->viaTable('player_item', ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Languages]].
     *
     * @return \yii\db\ActiveQuery<Language>
     */
    public function getLanguages() {
        return $this->hasMany(Language::class, ['id' => 'language_id'])->viaTable('player_language', ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Level]].
     *
     * @return \yii\db\ActiveQuery<Level>
     */
    public function getLevel() {
        return $this->hasOne(Level::class, ['id' => 'level_id']);
    }

    /**
     * Gets query for [[NotificationPlayers]].
     *
     * @return \yii\db\ActiveQuery<NotificationPlayer>
     */
    public function getNotificationPlayers() {
        return $this->hasMany(NotificationPlayer::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[TriggeredNotifications]].
     *
     * @return \yii\db\ActiveQuery<Notification>
     */
    public function getTriggeredNotifications() {
        return $this->hasMany(Notification::class, ['initiator_id' => 'id']);
    }

    /**
     * Gets query for [[Notifications]].
     *
     * @return \yii\db\ActiveQuery<Notification>
     */
    public function getNotifications() {
        return $this->hasMany(Notification::class, ['id' => 'notification_id'])->viaTable('notification_player', ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerAbilities]].
     *
     * @return \yii\db\ActiveQuery<PlayerAbility>
     */
    public function getPlayerAbilities() {
        return $this->hasMany(PlayerAbility::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerBody]].
     *
     * @return \yii\db\ActiveQuery<PlayerBody>
     */
    public function getPlayerBody() {
        return $this->hasOne(PlayerBody::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerCarts]].
     *
     * @return \yii\db\ActiveQuery<PlayerCart>
     */
    public function getPlayerCarts() {
        return $this->hasMany(PlayerCart::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerCoins]].
     *
     * @return \yii\db\ActiveQuery<PlayerCoin>
     */
    public function getPlayerCoins() {
        return $this->hasMany(PlayerCoin::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerItems]].
     *
     * @return \yii\db\ActiveQuery<PlayerItem>
     */
    public function getPlayerItems() {
        return $this->hasMany(PlayerItem::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerLanguages]].
     *
     * @return \yii\db\ActiveQuery<PlayerLanguage>
     */
    public function getPlayerLanguages() {
        return $this->hasMany(PlayerLanguage::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerSkills]].
     *
     * @return \yii\db\ActiveQuery<PlayerSkill>
     */
    public function getPlayerSkills() {
        return $this->hasMany(PlayerSkill::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerSpells]].
     *
     * @return \yii\db\ActiveQuery<PlayerSpell>
     */
    public function getPlayerSpells() {
        return $this->hasMany(PlayerSpell::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[PlayerTraits]].
     *
     * @return \yii\db\ActiveQuery<PlayerTrait>
     */
    public function getPlayerTraits() {
        return $this->hasMany(PlayerTrait::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Quest]].
     *
     * @return \yii\db\ActiveQuery<Quest>
     */
    public function getQuest() {
        return $this->hasOne(Quest::class, ['id' => 'quest_id']);
    }

    /**
     * Gets query for [[QuestPlayers]].
     *
     * @return \yii\db\ActiveQuery<QuestPlayer>
     */
    public function getQuestPlayers() {
        return $this->hasMany(QuestPlayer::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[QuestSessions]].
     *
     * @return \yii\db\ActiveQuery<QuestSession>
     */
    public function getQuestSessions() {
        return $this->hasMany(QuestSession::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[QuestTurns]].
     *
     * @return \yii\db\ActiveQuery<QuestTurn>
     */
    public function getQuestTurns() {
        return $this->hasMany(QuestTurn::class, ['player_id' => 'id']);
    }

    /**
     * Gets query for [[InitiatedQuests]].
     *
     * @return \yii\db\ActiveQuery<Quest>
     */
    public function getInitiatedQuests() {
        return $this->hasMany(Quest::class, ['initiator_id' => 'id']);
    }

    /**
     * Gets query for [[QuestToPlay]].
     *
     * @return \yii\db\ActiveQuery<Quest>
     */
    public function getQuestToPlay() {
        return $this->hasOne(Quest::class, ['current_player_id' => 'id']);
    }

    /**
     * Gets query for [[Quests]].
     *
     * @return \yii\db\ActiveQuery<Quest>
     */
    public function getQuests() {
        return $this->hasMany(Quest::class, ['id' => 'quest_id'])->viaTable('quest_player', ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Race]].
     *
     * @return \yii\db\ActiveQuery<Race>
     */
    public function getRace() {
        return $this->hasOne(Race::class, ['id' => 'race_id']);
    }

    /**
     * Gets query for [[Skills]].
     *
     * @return \yii\db\ActiveQuery<Skill>
     */
    public function getSkills() {
        return $this->hasMany(Skill::class, ['id' => 'skill_id'])->viaTable('player_skill', ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Spells]].
     *
     * @return \yii\db\ActiveQuery<Spell>
     */
    public function getSpells() {
        return $this->hasMany(Spell::class, ['id' => 'spell_id'])->viaTable('player_spell', ['player_id' => 'id']);
    }

    /**
     * Gets query for [[Traits]].
     *
     * @return \yii\db\ActiveQuery<CharacterTrait>
     */
    public function getTraits() {
        return $this->hasMany(CharacterTrait::class, ['id' => 'trait_id'])->viaTable('player_trait', ['player_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery<User>
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[UserLogs]].
     *
     * @return \yii\db\ActiveQuery<UserLog>
     */
    public function getUserLogs() {
        return $this->hasMany(UserLog::class, ['player_id' => 'id']);
    }

    /**
     * column gender ENUM value labels
     * @return string[]
     */
    public static function optsGender(): array {
        return [
            self::GENDER_C => 'C',
            self::GENDER_F => 'F',
            self::GENDER_M => 'M',
        ];
    }

    /**
     * @return string
     */
    public function displayGender(): string {
        return self::optsGender()[$this->gender];
    }

    /**
     * @return bool
     */
    public function isGenderC(): bool {
        return $this->gender === self::GENDER_C;
    }

    /**
     *
     * @return void
     */
    public function setGenderToC(): void {
        $this->gender = self::GENDER_C;
    }

    /**
     * @return bool
     */
    public function isGenderF(): bool {
        return $this->gender === self::GENDER_F;
    }

    /**
     *
     * @return void
     */
    public function setGenderToF(): void {
        $this->gender = self::GENDER_F;
    }

    /**
     * @return bool
     */
    public function isGenderM(): bool {
        return $this->gender === self::GENDER_M;
    }

    /**
     *
     * @return void
     */
    public function setGenderToM(): void {
        $this->gender = self::GENDER_M;
    }

    /**
     * ************************
     *     Custom properties
     * ************************ */

    /**
     * Gets query for [[Weapons]].
     *
     * @return \yii\db\ActiveQuery<Weapon>
     */
    public function getWeapons() {
        return $this->hasMany(Weapon::class, ['id' => 'item_id'])->viaTable('player_item', ['player_id' => 'id']);
    }

    /**
     *
     * @return string|null
     */
    public function getAvatar(): ?string {
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

        return empty($images) ? null : $images[array_rand($images)];
    }

    /**
     * Generates a description for the player character.
     *
     * @return string The description of the player character.
     */
    public function getDescription(): string {
        $gender = match ($this->gender) {
            'M' => 'male',
            'F' => 'female',
            default => '',
        };
        $age = $this->age ? "{$this->age}-years-old" : '';
        $alignment = $this->alignment ? $this->alignment->name : '';
        $description = "{$age} {$gender} {$this->race->name}, {$this->level->name} {$alignment} {$this->class->name}";
        return strtolower($description);
    }

    /**
     * Get unread notifications for this player
     *
     * @return \yii\db\ActiveQuery<Notification>
     */
    public function getUnreadNotifications() {
        return $this->hasMany(Notification::class, ['id' => 'notification_id'])
                        ->via('notificationPlayers', function ($query) {
                            $query->andWhere(['is_read' => 0]);
                        });
    }

    /**
     * ************************
     *     Custom methods
     * ************************ */

    /**
     * Checks if a player is proficient with a specific item.
     *
     * @param int $item_id The ID of the item to check proficiency for.
     * @return bool|null Returns true if the player is proficient with the item, false if not,
     *                   or null if the player parameter is null.
     */
    public function isProficient(int $item_id): ?bool {
        $class = $this->class;

        return PlayerComponent::isProficient($class->id, $item_id);
    }

    /**
     * Changes the status of the given model and updates its "updated_at" timestamp.
     *
     * @param int $status The new status to set.
     * @return bool Whether the status change was successful.
     */
    public function setStatus(int $status): bool {
        $this->status = $status;
        // Save the changes to the model and returns whether the save operation was successful
        return $this->save();
    }

    /**
     *
     * @param int|null $quantity
     * @param string $coin
     * @return bool|null
     */
    public function addCoins(?int $quantity, string $coin = 'gp'): ?bool {
        Yii::debug("*** debug *** - Player - addCoins(quantity=" . ($quantity ?? 'null') . ", coin={$coin})");
        if ($quantity === null || $quantity === 0) {
            return null;
        }

        $updatedRows = PlayerCoin::updateAll(
                ['quantity' => new \yii\db\Expression("quantity+{$quantity}")],
                ['player_id' => $this->id, 'coin' => $coin]
        );

        if ($updatedRows === 0) {
            $playerCoinGp = new PlayerCoin([
                'player_id' => $this->id,
                'coin' => $coin,
                'quantity' => $quantity
            ]);
            return $playerCoinGp->save();
        }
        return true;
    }

    /**
     *
     * @param int $itemId
     * @param int $quantity
     * @return bool|null
     */
    public function addItems(int $itemId, int $quantity = 1): ?bool {
        Yii::debug("*** debug *** - Player - addItems(itemId={$itemId}, quantity={$quantity})");
        if ($quantity === 0) {
            return null;
        }

        $updatedRows = PlayerItem::updateAll(
                ['quantity' => new \yii\db\Expression("quantity+{$quantity}")],
                ['player_id' => $this->id, 'item_id' => $itemId]
        );

        if ($updatedRows === 0) {
            $item = Item::findOne($itemId);
            if ($item === null) {
                return false;
            }
            $playerItem = new PlayerItem([
                'player_id' => $this->id,
                'item_id' => $itemId,
                'quantity' => $quantity,
                'item_type' => $item->itemType->name,
                'item_name' => $item->name,
                'image' => $item->image,
                'is_carrying' => 1,
            ]);
            return $playerItem->save();
        }
        return true;
    }
}
