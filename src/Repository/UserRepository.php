<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function upgradePassword(
        PasswordAuthenticatedUserInterface $user,
        string $newHashedPassword,
    ): void {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     activeMediasCount: int
     * }>
     */
    public function findActiveGuestsPaginated(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT u.id, u.name, COUNT(m.id) AS "activeMediasCount"
        FROM "user" u
        LEFT JOIN media m ON m.user_id = u.id
        WHERE u.is_active = true AND u.is_admin = false
        GROUP BY u.id, u.name
        ORDER BY u.name ASC
        LIMIT :limit OFFSET :offset
    ';

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);

        $rows = $stmt->executeQuery()->fetchAllAssociative();

        return array_map(
            static fn (array $row): array => [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'activeMediasCount' => (int) $row['activeMediasCount'],
            ],
            $rows
        );
    }
}
