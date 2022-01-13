<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_profile}}`.
 */
class m220112_073555_create_user_profile_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_profile}}', [
            'id' => $this->primaryKey(),
            'uid' => $this->integer()->notNull()->unique(),

            'name' => $this->string(64),
            'gender' => $this->boolean(),
            'birthday' => $this->datetime(),
            'avatar_url' => $this->text(),

            'email' => $this->string(64),
            'email_verified' => $this->boolean()->notNull()->defaultValue(0),

            'cellphone' => $this->string(64),
            'cellphone_verified' => $this->boolean()->notNull()->defaultValue(0),

            'signature' => $this->text(),

            'remarks' => $this->text(),
            'create_by' => $this->integer(),
            'create_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'update_by' => $this->integer(),
            'update_at' => $this->timestamp()->null()->append('on update CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_profile}}');
    }
}
