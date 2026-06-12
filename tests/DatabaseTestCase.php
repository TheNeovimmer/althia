<?php
namespace Tests;

abstract class DatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->rollback();
        parent::tearDown();
    }

    private function beginTransaction(): void
    {
        $this->getDb()->beginTransaction();
    }

    private function rollback(): void
    {
        if ($this->getDb()->inTransaction()) {
            $this->getDb()->rollBack();
        }
    }
}
