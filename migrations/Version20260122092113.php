<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260122092113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du champ is_active avec valeur par défaut TRUE pour les utilisateurs existants';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD is_active BOOLEAN DEFAULT TRUE');

        $this->addSql('UPDATE "user" SET is_active = TRUE WHERE is_active IS NULL');

        $this->addSql('ALTER TABLE "user" ALTER COLUMN is_active SET NOT NULL');

        $this->addSql('ALTER TABLE "user" ALTER COLUMN is_active DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP COLUMN is_active');
    }
}
