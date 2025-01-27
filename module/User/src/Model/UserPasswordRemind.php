<?php

namespace Autowp\User\Model;

use Zend\Db\Sql;
use Zend\Db\TableGateway\TableGateway;

class UserPasswordRemind
{
    /**
     * @var TableGateway
     */
    private $table;

    /**
     * @var string
     */
    private $salt;

    public function __construct(TableGateway $table, string $salt)
    {
        $this->table = $table;
        $this->salt = $salt;
    }

    public function garbageCollect(): int
    {
        return (int)$this->table->delete([
            'created < DATE_SUB(NOW(), INTERVAL 10 DAY)'
        ]);
    }

    public function deleteToken(string $token)
    {
        $this->table->delete([
            'hash = ?' => $token
        ]);
    }

    /**
     * @suppress PhanUndeclaredMethod, PhanPluginMixedKeyNoKey
     * @param string $token
     * @return int
     */
    public function getUserId(string $token): int
    {
        $uprRow = $this->table->select([
            'hash = ?' => $token,
            'created > DATE_SUB(NOW(), INTERVAL 10 DAY)'
        ])->current();

        return $uprRow ? (int)$uprRow['user_id'] : 0;
    }

    /**
     * @suppress PhanUndeclaredMethod, PhanDeprecatedFunction
     * @param int $userId
     * @return string
     */
    public function createToken(int $userId): string
    {
        do {
            $token = md5($this->salt . uniqid());
            $exists = (bool)$this->table->select([
                'hash' => $token
            ])->current();
        } while ($exists);

        $this->table->insert([
            'user_id' => $userId,
            'hash'    => $token,
            'created' => new Sql\Expression('NOW()')
        ]);

        return $token;
    }
}
