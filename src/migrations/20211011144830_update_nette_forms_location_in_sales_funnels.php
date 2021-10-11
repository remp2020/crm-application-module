<?php

use Phinx\Migration\AbstractMigration;

class UpdateNetteFormsLocationInSalesFunnels extends AbstractMigration
{
    public function up()
    {
        $this->query(<<<SQL
            UPDATE `sales_funnels`
            SET
                `body` = REPLACE(`body`, 'layouts/admin/js/netteForms.js', 'layouts/application/js/nette-forms/netteForms.js')
            WHERE
                `body` LIKE '%layouts/admin/js/netteForms.js%';

            UPDATE `sales_funnels`
            SET
                `body` = REPLACE(`body`, 'layouts/default/js/netteForms.js', 'layouts/application/js/nette-forms/netteForms.js')
            WHERE
                `body` LIKE '%layouts/default/js/netteForms.js%';
SQL
        );
    }

    public function down()
    {
        $this->query(<<<SQL
            UPDATE `sales_funnels`
            SET
                `body` = REPLACE(`body`, 'layouts/application/js/nette-forms/netteForms.js', 'layouts/default/js/netteForms.js')
            WHERE
                `body` LIKE '%layouts/application/js/nette-forms/netteForms.js%';
SQL
        );
    }
}
