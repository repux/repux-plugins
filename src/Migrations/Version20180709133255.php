<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180709133255 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE shopify_store (id SERIAL NOT NULL, user_id INT NOT NULL, name VARCHAR(128) NOT NULL, nonce TEXT DEFAULT NULL, access_token TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4868B52BA76ED395 ON shopify_store (user_id)');
        $this->addSql('CREATE UNIQUE INDEX user_store_name_unique ON shopify_store (user_id, name)');
        $this->addSql('CREATE TABLE shopify_store_process (id SERIAL NOT NULL, shopify_store_id INT DEFAULT NULL, parameters VARCHAR(255) DEFAULT NULL, status INT DEFAULT 0 NOT NULL, data VARCHAR(255) DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A82079538882DFEE ON shopify_store_process (shopify_store_id)');
        $this->addSql('CREATE TABLE users (id SERIAL NOT NULL, eth_address VARCHAR(42) NOT NULL, auth_message VARCHAR(150) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E94E39DC5D ON users (eth_address)');
        $this->addSql('CREATE TABLE data_file (id SERIAL NOT NULL, user_id INT NOT NULL, file_size INT NOT NULL, file_id VARCHAR(255) DEFAULT NULL, file_mime_type VARCHAR(255) DEFAULT NULL, origin VARCHAR(50) NOT NULL, original_name VARCHAR(250) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by VARCHAR(255) DEFAULT NULL, updated_by VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_37D0FDF2A76ED395 ON data_file (user_id)');
        $this->addSql('CREATE TABLE user_auth_token (id SERIAL NOT NULL, user_id INT NOT NULL, hash VARCHAR(150) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_347236A2D1B862B8 ON user_auth_token (hash)');
        $this->addSql('CREATE INDEX IDX_347236A2A76ED395 ON user_auth_token (user_id)');
        $this->addSql('COMMENT ON COLUMN user_auth_token.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE shopify_store ADD CONSTRAINT FK_4868B52BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shopify_store_process ADD CONSTRAINT FK_A82079538882DFEE FOREIGN KEY (shopify_store_id) REFERENCES shopify_store (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE data_file ADD CONSTRAINT FK_37D0FDF2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_auth_token ADD CONSTRAINT FK_347236A2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE shopify_store_process DROP CONSTRAINT FK_A82079538882DFEE');
        $this->addSql('ALTER TABLE shopify_store DROP CONSTRAINT FK_4868B52BA76ED395');
        $this->addSql('ALTER TABLE data_file DROP CONSTRAINT FK_37D0FDF2A76ED395');
        $this->addSql('ALTER TABLE user_auth_token DROP CONSTRAINT FK_347236A2A76ED395');
        $this->addSql('DROP TABLE shopify_store');
        $this->addSql('DROP TABLE shopify_store_process');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE data_file');
        $this->addSql('DROP TABLE user_auth_token');
    }
}
