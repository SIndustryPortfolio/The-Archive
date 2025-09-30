<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250413190059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE Book (iD INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, imageURL VARCHAR(500) NOT NULL, description VARCHAR(1000) NOT NULL, genre INT NOT NULL, INDEX IDX_6BD70C0F835033F8 (genre), PRIMARY KEY(iD)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE Genre (iD INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(iD)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE Review (iD INT AUTO_INCREMENT NOT NULL, content VARCHAR(10000) NOT NULL, rating INT NOT NULL, user INT NOT NULL, book INT NOT NULL, INDEX IDX_7EEF84F08D93D649 (user), INDEX IDX_7EEF84F0CBE5A331 (book), PRIMARY KEY(iD)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE User (iD INT AUTO_INCREMENT NOT NULL, username VARCHAR(36) NOT NULL, password VARCHAR(255) NOT NULL, profilePictureURL VARCHAR(500) NOT NULL, apiToken VARCHAR(255) DEFAULT NULL, userType INT NOT NULL, INDEX IDX_2DA179778A918066 (userType), PRIMARY KEY(iD)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE UserType (iD INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, level INT NOT NULL, PRIMARY KEY(iD)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE credentials (iD INT AUTO_INCREMENT NOT NULL, username VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(iD)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE register_credentials (iD INT AUTO_INCREMENT NOT NULL, username VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(iD)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Book ADD CONSTRAINT FK_6BD70C0F835033F8 FOREIGN KEY (genre) REFERENCES Genre (iD)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Review ADD CONSTRAINT FK_7EEF84F08D93D649 FOREIGN KEY (user) REFERENCES User (iD)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Review ADD CONSTRAINT FK_7EEF84F0CBE5A331 FOREIGN KEY (book) REFERENCES Book (iD)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE User ADD CONSTRAINT FK_2DA179778A918066 FOREIGN KEY (userType) REFERENCES UserType (iD)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE Book DROP FOREIGN KEY FK_6BD70C0F835033F8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Review DROP FOREIGN KEY FK_7EEF84F08D93D649
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE Review DROP FOREIGN KEY FK_7EEF84F0CBE5A331
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE User DROP FOREIGN KEY FK_2DA179778A918066
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Book
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Genre
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE Review
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE User
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE UserType
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE credentials
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE register_credentials
        SQL);
    }
}
