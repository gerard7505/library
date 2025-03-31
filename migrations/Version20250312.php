<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea las tablas book e image';
    }

    public function up(Schema $schema): void
    {
        // Crea la tabla 'book'
        $this->addSql('
            CREATE TABLE book (
                id INT AUTO_INCREMENT NOT NULL, 
                isbn VARCHAR(30) NOT NULL, 
                author VARCHAR(30) DEFAULT NULL, 
                published VARCHAR(50) DEFAULT NULL, 
                publisher VARCHAR(30) DEFAULT NULL, 
                title VARCHAR(30) DEFAULT NULL, 
                subtitle VARCHAR(50) DEFAULT NULL, 
                description TEXT DEFAULT NULL, 
                website VARCHAR(100) DEFAULT NULL, 
                pages INT DEFAULT NULL, 
                category VARCHAR(20) DEFAULT NULL, 
                PRIMARY KEY(id)
            )
        ');

        // Crea la tabla 'image'
        $this->addSql('
            CREATE TABLE image (
                id INT AUTO_INCREMENT NOT NULL, 
                url VARCHAR(255) NOT NULL, 
                size VARCHAR(50) DEFAULT NULL, 
                book_id INT NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_IMAGE_BOOK FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE
            )
        ');
    }

    public function down(Schema $schema): void
    {
        // Eliminar las tablas en caso de rollback
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE book');
    }
}
