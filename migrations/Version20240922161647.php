<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240922161647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, trick_id INT NOT NULL, user_id INT NOT NULL, text VARCHAR(5000) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9474526CB281BE2E (trick_id), INDEX IDX_9474526CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CB281BE2E FOREIGN KEY (trick_id) REFERENCES trick (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment_main DROP FOREIGN KEY FK_991E6F88A76ED395');
        $this->addSql('ALTER TABLE comment_main DROP FOREIGN KEY FK_991E6F88B281BE2E');
        $this->addSql('ALTER TABLE comment_response DROP FOREIGN KEY FK_233F68FDA76ED395');
        $this->addSql('ALTER TABLE comment_response DROP FOREIGN KEY FK_233F68FD910DC59A');
        $this->addSql('DROP TABLE comment_main');
        $this->addSql('DROP TABLE comment_response');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment_main (id INT AUTO_INCREMENT NOT NULL, trick_id INT NOT NULL, user_id INT NOT NULL, text VARCHAR(5000) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_991E6F88B281BE2E (trick_id), INDEX IDX_991E6F88A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE comment_response (id INT AUTO_INCREMENT NOT NULL, comment_main_id INT NOT NULL, user_id INT NOT NULL, text VARCHAR(5000) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_233F68FD910DC59A (comment_main_id), INDEX IDX_233F68FDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE comment_main ADD CONSTRAINT FK_991E6F88A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment_main ADD CONSTRAINT FK_991E6F88B281BE2E FOREIGN KEY (trick_id) REFERENCES trick (id)');
        $this->addSql('ALTER TABLE comment_response ADD CONSTRAINT FK_233F68FDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment_response ADD CONSTRAINT FK_233F68FD910DC59A FOREIGN KEY (comment_main_id) REFERENCES comment_main (id)');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CB281BE2E');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('DROP TABLE comment');
    }
}
