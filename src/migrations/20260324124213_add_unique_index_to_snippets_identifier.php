<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUniqueIndexToSnippetsIdentifier extends AbstractMigration
{
    public function up(): void
    {
        $q = <<<SQL
            SELECT `identifier`, GROUP_CONCAT(DISTINCT `id` ORDER BY `id` ASC) AS `ids`
            FROM `snippets`
            GROUP BY `identifier` HAVING COUNT(*) > 1;
        SQL;

        $duplicates = $this->fetchAll($q);
        if (count($duplicates) > 0) {
            $duplicatesForException = [];
            foreach ($duplicates as $duplicate) {
                $duplicatesForException[$duplicate['identifier']] = $duplicate['ids'];
            }
            throw new Exception(
                "Unable to add unique index to 'snippets.identifier' column because there are duplicate values."
                . PHP_EOL . "Fix snippets with same identifier (IDs included): " . PHP_EOL
                . print_r($duplicatesForException, true) . PHP_EOL
            );
        }

        $this->table('snippets')
            ->removeIndex('identifier')
            ->addIndex('identifier', ['unique' => true])
            ->update();
    }

    public function down(): void
    {
        $this->table('snippets')
            ->removeIndex('identifier')
            ->addIndex('identifier', ['unique' => false])
            ->update();
    }
}
