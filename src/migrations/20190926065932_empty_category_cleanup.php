<?php

use Phinx\Migration\AbstractMigration;

class EmptyCategoryCleanup extends AbstractMigration
{
    public function up()
    {
        $sql = <<<SQL
delete from config_categories
where id in (

  select * from (
    select config_categories.id from config_categories
    left join configs on config_category_id = config_categories.id
    where configs.id is null
  ) t2

);
SQL;
        $this->execute($sql);

    }
}
