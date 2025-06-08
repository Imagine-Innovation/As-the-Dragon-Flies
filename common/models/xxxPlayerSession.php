<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "player_session".
 *
 * @property string $session_id Primary key
 * @property int $player_id Foreign key to 'player' table
 * @property string|null $client_id Connection ID (socket)
 *
 * @property Player $player
 */
class PlayerSession extends \yii\db\ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'player_session';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['session_id', 'player_id'], 'required'],
            [['player_id'], 'integer'],
            [['session_id', 'client_id'], 'string', 'max' => 64],
            [['client_id'], 'unique'],
            [['session_id'], 'unique'],
            [['player_id'], 'exist', 'skipOnError' => true, 'targetClass' => Player::class, 'targetAttribute' => ['player_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'session_id' => 'Primary key',
            'player_id' => 'Foreign key to \'player\' table',
            'client_id' => 'Connection ID (socket)',
        ];
    }

    /**
     * Gets query for [[Player]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlayer() {
        return $this->hasOne(Player::class, ['id' => 'player_id']);
    }

    /**
     * Scope method to find active sessions
     * @return \yii\db\ActiveQuery
     */
    public static function find() {
        return parent::find();
    }

    /**
     * Find the players' sessions
     *
     * @param int $playerId
     * @return \yii\db\ActiveQuery
     */
    public static function findPlayerSessions(int $playerId) {
        return static::find()
                        ->where(['player_id' => $playerId])
                        ->andWhere(['is not', 'player_session.client_id', null])
                        ->all();
    }

    /**
     * Find the players' sessions involved in a quest
     *
     * @param int $questId
     * @return \yii\db\ActiveQuery
     */
    public static function findQuestSessions(int $questId) {
        return static::find()
                        ->select('player_session.*')
                        ->innerJoinWith('player')
                        ->where(['player.quest_id' => $questId])
                        ->andWhere(['is not', 'player_session.client_id', null])
                        ->all();
    }
}
