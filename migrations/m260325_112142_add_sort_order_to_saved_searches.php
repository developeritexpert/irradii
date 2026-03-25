<?php

use yii\db\Migration;

class m260325_112142_add_sort_order_to_saved_searches extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%saved_searches}}', 'sort_order', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%saved_searches}}', 'sort_order');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260325_112142_add_sort_order_to_saved_searches cannot be reverted.\n";

        return false;
    }
    */
}
