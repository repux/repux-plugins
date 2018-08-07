<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180801085213 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE amazon_channel_process ADD data_file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE amazon_channel_process DROP data');
        $this->addSql('ALTER TABLE amazon_channel_process ALTER status SET DEFAULT 10');
        $this->addSql('ALTER TABLE amazon_channel_process ADD CONSTRAINT FK_FADBBBC9F7C02F9D FOREIGN KEY (data_file_id) REFERENCES data_file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FADBBBC9F7C02F9D ON amazon_channel_process (data_file_id)');
        $this->addSql('ALTER INDEX idx_c95b4e9797618a64 RENAME TO IDX_FADBBBC95CE305B8');
        $this->addSql('ALTER TABLE shopify_store_process ADD data_file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shopify_store_process DROP data');
        $this->addSql('ALTER TABLE shopify_store_process ALTER status SET DEFAULT 10');
        $this->addSql('ALTER TABLE shopify_store_process ADD CONSTRAINT FK_A8207953F7C02F9D FOREIGN KEY (data_file_id) REFERENCES data_file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A8207953F7C02F9D ON shopify_store_process (data_file_id)');
        $this->addSql('ALTER TABLE amazon_channel ALTER status SET DEFAULT 10');
        $this->addSql('ALTER INDEX idx_7f38b209a76ed395 RENAME TO IDX_21123230A76ED395');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE shopify_store_process DROP CONSTRAINT FK_A8207953F7C02F9D');
        $this->addSql('DROP INDEX UNIQ_A8207953F7C02F9D');
        $this->addSql('ALTER TABLE shopify_store_process ADD data VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE shopify_store_process DROP data_file_id');
        $this->addSql('ALTER TABLE shopify_store_process ALTER status SET DEFAULT 0');
        $this->addSql('ALTER TABLE amazon_channel_process DROP CONSTRAINT FK_FADBBBC9F7C02F9D');
        $this->addSql('DROP INDEX UNIQ_FADBBBC9F7C02F9D');
        $this->addSql('ALTER TABLE amazon_channel_process ADD data VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE amazon_channel_process DROP data_file_id');
        $this->addSql('ALTER TABLE amazon_channel_process ALTER status SET DEFAULT 0');
        $this->addSql('ALTER INDEX idx_fadbbbc95ce305b8 RENAME TO idx_c95b4e9797618a64');
        $this->addSql('ALTER TABLE amazon_channel ALTER status SET DEFAULT 0');
        $this->addSql('ALTER INDEX idx_21123230a76ed395 RENAME TO idx_7f38b209a76ed395');
    }
}
