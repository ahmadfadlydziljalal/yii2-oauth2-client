<?php

use yii\db\Migration;

/**
 * Class m221216_005857_AlterOauthAuthorizationCodesTable
 */
class m221216_005857_AlterOauthAuthorizationCodesTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m221216_005857_AlterOauthAuthorizationCodesTable cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m221216_005857_AlterOauthAuthorizationCodesTable cannot be reverted.\n";

        return false;
    }
    */
}
