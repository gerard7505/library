<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314073939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the users table';
    }

    public function up(Schema $schema): void
    {
        // Creamos la tabla users
        $this->addSql('
            CREATE TABLE users (
                id INT AUTO_INCREMENT NOT NULL, 
                email VARCHAR(180) NOT NULL, 
                password VARCHAR(255) NOT NULL, 
                roles JSON NOT NULL, 
                PRIMARY KEY(id), 
                UNIQUE(email)
            )');
    }

    public function down(Schema $schema): void
    {
        // Eliminamos la tabla users si revertimos la migración
        $this->addSql('DROP TABLE users');
    }
}
