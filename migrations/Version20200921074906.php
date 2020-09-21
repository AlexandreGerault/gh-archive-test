<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200921074906 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actor (id BIGINT NOT NULL, event_id BIGINT DEFAULT NULL, login VARCHAR(255) NOT NULL, gravatar_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, avatar_url VARCHAR(255) NOT NULL, INDEX IDX_447556F971F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id BIGINT NOT NULL, type VARCHAR(255) NOT NULL, public TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, payload LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organization (id BIGINT NOT NULL, event_id BIGINT DEFAULT NULL, login VARCHAR(255) NOT NULL, gravatar_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, avatar_url VARCHAR(255) NOT NULL, INDEX IDX_C1EE637C71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE repo (id BIGINT NOT NULL, event_id BIGINT DEFAULT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_5C5CBBFF71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE actor ADD CONSTRAINT FK_447556F971F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE organization ADD CONSTRAINT FK_C1EE637C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE repo ADD CONSTRAINT FK_5C5CBBFF71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actor DROP FOREIGN KEY FK_447556F971F7E88B');
        $this->addSql('ALTER TABLE organization DROP FOREIGN KEY FK_C1EE637C71F7E88B');
        $this->addSql('ALTER TABLE repo DROP FOREIGN KEY FK_5C5CBBFF71F7E88B');
        $this->addSql('DROP TABLE actor');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE repo');
    }
}
