<?php

namespace Crm\ApplicationModule;

class Helpers
{
    /**
     * Makes SQL event wrapper that protects against multiple event executions
     * https://www.percona.com/blog/2015/02/25/using-mysql-event-scheduler-and-how-to-prevent-contention/
     * @param string $eventName
     * @param int    $minutesRepetition
     * @param string $internalSql
     *
     * @return string sql
     */
    public static function lockableScheduledEvent(string $eventName, int $minutesRepetition, string $internalSql): string
    {
        $lockName = $eventName . '_lock';

        $sql = <<<SQL
CREATE EVENT $eventName ON SCHEDULE EVERY $minutesRepetition MINUTE DO
BEGIN
  DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
  BEGIN
    DO RELEASE_LOCK('$lockName');
  END;        
  IF GET_LOCK('$lockName', 0) THEN
    $internalSql
  END IF;
  DO RELEASE_LOCK('$lockName');
END;
SQL;
        return $sql;
    }
}
