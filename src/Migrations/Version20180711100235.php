<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180711100235 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE channel_amazon_process (id SERIAL NOT NULL, channel_amazon_id INT DEFAULT NULL, type INT NOT NULL, parameters VARCHAR(255) DEFAULT NULL, status INT DEFAULT 0 NOT NULL, data VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C95B4E9797618A64 ON channel_amazon_process (channel_amazon_id)');
        $this->addSql('CREATE TABLE channel_amazon (id SERIAL NOT NULL, user_id INT NOT NULL, name VARCHAR(128) NOT NULL, merchant_id VARCHAR(128) NOT NULL, marketplace_id VARCHAR(128) NOT NULL, api_tokenCheckAmazonCredentialsService TEXT NOT NULL, authenticated BOOLEAN DEFAULT \'false\', status INT DEFAULT 0, service_url VARCHAR(128) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7F38B209A76ED395 ON channel_amazon (user_id)');
        $this->addSql('ALTER TABLE channel_amazon_process ADD CONSTRAINT FK_C95B4E9797618A64 FOREIGN KEY (channel_amazon_id) REFERENCES channel_amazon (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE channel_amazon ADD CONSTRAINT FK_7F38B209A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE channel_amazon_process DROP CONSTRAINT FK_C95B4E9797618A64');
        $this->addSql('DROP TABLE channel_amazon_process');
        $this->addSql('DROP TABLE channel_amazon');
    }
}
