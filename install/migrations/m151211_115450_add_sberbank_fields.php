<?php

class m151211_115450_add_sberbank_fields extends yupe\components\DbMigration
{
	public function safeUp()
	{
		// id заказа в системе сбербанка
		$this->addColumn('{{store_order}}', 'orderId', 'VARCHAR(150)');
	}
}