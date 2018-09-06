<?php


use Phinx\Migration\AbstractMigration;

class AddUserIpField extends AbstractMigration
{
    public function change()
    {
        $this->table("messages")
            ->addColumn("user_ip", "string", [
               "null" => true
            ])
            ->addIndex("user_ip")
            ->update();
    }
}
