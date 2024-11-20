<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveObsoleteCalendarColumns extends AbstractMigration
{
    public function up(): void
    {
        $this->table('calendar')
            ->removeColumn('day_name')
            ->removeColumn('month_name')
            ->removeColumn('holiday_flag')
            ->removeColumn('weekend_flag')
            ->update();
    }

    public function down(): void
    {
        $this->output->writeln('Down migration is not available, up migration was destructive.');
    }
}
