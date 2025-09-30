<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241126193743 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Book (title VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, imageURL VARCHAR(500) NOT NULL, description VARCHAR(1000) NOT NULL, genre VARCHAR(255) NOT NULL, INDEX IDX_6BD70C0F835033F8 (genre), PRIMARY KEY(title)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE Genre (name VARCHAR(255) NOT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE Review (iD INT AUTO_INCREMENT NOT NULL, content VARCHAR(10000) NOT NULL, rating INT NOT NULL, user VARCHAR(36) NOT NULL, book VARCHAR(255) NOT NULL, INDEX IDX_7EEF84F08D93D649 (user), INDEX IDX_7EEF84F0CBE5A331 (book), PRIMARY KEY(iD)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE User (username VARCHAR(36) NOT NULL, password VARCHAR(255) NOT NULL, profilePictureURL VARCHAR(500) NOT NULL, userType INT NOT NULL, INDEX IDX_2DA179778A918066 (userType), PRIMARY KEY(username)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE UserType (iD INT NOT NULL, name VARCHAR(100) NOT NULL, level INT NOT NULL, PRIMARY KEY(iD)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE credentials (i_d INT AUTO_INCREMENT NOT NULL, username VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(i_d)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE register_credentials (i_d INT AUTO_INCREMENT NOT NULL, username VARCHAR(20) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(i_d)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE Book ADD CONSTRAINT FK_6BD70C0F835033F8 FOREIGN KEY (genre) REFERENCES Genre (name)');
        $this->addSql('ALTER TABLE Review ADD CONSTRAINT FK_7EEF84F08D93D649 FOREIGN KEY (user) REFERENCES User (username)');
        $this->addSql('ALTER TABLE Review ADD CONSTRAINT FK_7EEF84F0CBE5A331 FOREIGN KEY (book) REFERENCES Book (title)');
        $this->addSql('ALTER TABLE User ADD CONSTRAINT FK_2DA179778A918066 FOREIGN KEY (userType) REFERENCES UserType (iD)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Book DROP FOREIGN KEY FK_6BD70C0F835033F8');
        $this->addSql('ALTER TABLE Review DROP FOREIGN KEY FK_7EEF84F08D93D649');
        $this->addSql('ALTER TABLE Review DROP FOREIGN KEY FK_7EEF84F0CBE5A331');
        $this->addSql('ALTER TABLE User DROP FOREIGN KEY FK_2DA179778A918066');
        $this->addSql('DROP TABLE Book');
        $this->addSql('DROP TABLE Genre');
        $this->addSql('DROP TABLE Review');
        $this->addSql('DROP TABLE User');
        $this->addSql('DROP TABLE UserType');
        $this->addSql('DROP TABLE credentials');
        $this->addSql('DROP TABLE register_credentials');
    }
}
