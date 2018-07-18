<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180711134421 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE amazon_channel_process (id SERIAL NOT NULL, amazon_channel_id INT DEFAULT NULL, type INT NOT NULL, parameters VARCHAR(255) DEFAULT NULL, status INT DEFAULT 0 NOT NULL, data VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C95B4E9797618A64 ON amazon_channel_process (amazon_channel_id)');
        $this->addSql('CREATE TABLE amazon_channel (id SERIAL NOT NULL, user_id INT NOT NULL, name VARCHAR(128) NOT NULL, merchant_id VARCHAR(128) NOT NULL, marketplace_id VARCHAR(128) NOT NULL, api_token TEXT NOT NULL, authenticated BOOLEAN DEFAULT \'false\', status INT DEFAULT 0, service_url VARCHAR(128) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7F38B209A76ED395 ON amazon_channel (user_id)');
        $this->addSql('ALTER TABLE amazon_channel_process ADD CONSTRAINT FK_C95B4E9797618A64 FOREIGN KEY (amazon_channel_id) REFERENCES amazon_channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE amazon_channel ADD CONSTRAINT FK_7F38B209A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE amazon_channel_process DROP CONSTRAINT FK_C95B4E9797618A64');
        $this->addSql('DROP TABLE amazon_channel_process');
        $this->addSql('DROP TABLE amazon_channel');
    }
}
