<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PushNotifications extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        $this->table('push_notifications')
            ->addColumn('title', 'string', ['limit' => 255])
            ->addColumn('message', 'text')
            ->addColumn('country_id', 'integer')
            ->addColumn('status', 'enum', ['values' => ['in_queue', 'success', 'partial', 'failed', 'in_progress']])
            ->addColumn('sent', 'integer', ['default' => 0])
            ->addColumn('in_progress', 'integer', ['default' => 0])
            ->addColumn('failed', 'integer', ['default' => 0])
            ->addColumn('in_queue', 'integer', ['default' => 0])
            ->addIndex(['country_id'])
            ->addForeignKey(
                'country_id',
                'countries',
                'id',
                [
                    'delete' => 'NO_ACTION',
                    'update' => 'NO_ACTION',
                    'constraint' => 'push_notifications_country_id',
                ]
            )
            ->create();
    }

    public function down(): void
    {
        $this->table('push_notifications')
            ->drop();
    }
}
