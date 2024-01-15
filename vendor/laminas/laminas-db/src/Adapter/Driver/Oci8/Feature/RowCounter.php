<?php

namespace Laminas\Db\Adapter\Driver\Oci8\Feature;

use Laminas\Db\Adapter\Driver\Feature\AbstractFeature;
use Laminas\Db\Adapter\Driver\Oci8\Statement;

use function stripos;
use function strtolower;

/**
 * Class for count of results of a select
 */
class RowCounter extends AbstractFeature
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'RowCounter';
    }

    /**
     * @return null|int
     */
    public function getCountForStatement(Statement $statement)
    {
        $countStmt = clone $statement;
        $sql       = $statement->getSql();
        if ($sql === '' || stripos(strtolower($sql), 'select') === false) {
            return;
        }
        $countSql = 'SELECT COUNT(*) as "count" FROM (' . $sql . ')';
        $countStmt->prepare($countSql);
        $result   = $countStmt->execute();
        $countRow = $result->current();
        return $countRow['count'];
    }

    /**
     * @param string $sql
     * @return null|int
     */
    public function getCountForSql($sql)
    {
        if (stripos(strtolower($sql), 'select') === false) {
            return;
        }
        $countSql = 'SELECT COUNT(*) as "count" FROM (' . $sql . ')';
        $result   = $this->driver->getConnection()->execute($countSql);
        $countRow = $result->current();
        return $countRow['count'];
    }

    /**
     * @param Statement|string $context
     * @return callable
     */
    public function getRowCountClosure($context)
    {
        /** @var RowCounter $rowCounter */
        $rowCounter = $this;
        return function () use ($rowCounter, $context) {
            return $context instanceof Statement
                ? $rowCounter->getCountForStatement($context)
                : $rowCounter->getCountForSql($context);
        };
    }
}