<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m220112_072538_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),

            'code' => $this->string(64)->notNull()->unique(),
            'nick_name' => $this->string(64),

            'password_hash' => $this->string(64)->notNull(),
            'password_salt' => $this->string(64)->notNull(),
            'password_expired' => $this->boolean()->notNull()->defaultValue(1),

            'disabled' => $this->boolean()->notNull()->defaultValue(0),
            'expire_at' => $this->timestamp()->null(),

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
        $this->dropTable('{{%user}}');
    }
}
