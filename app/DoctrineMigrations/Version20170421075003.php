<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170421075003 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_28F23E0C989D9B62 ON thesaurus');
        $this->addSql('ALTER TABLE fos_user DROP locked, DROP expired, DROP expires_at, DROP credentials_expired, DROP credentials_expire_at, CHANGE salt salt VARCHAR(255) DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user ADD locked TINYINT(1) NOT NULL, ADD expired TINYINT(1) NOT NULL, ADD expires_at DATETIME DEFAULT NULL, ADD credentials_expired TINYINT(1) NOT NULL, ADD credentials_expire_at DATETIME DEFAULT NULL, CHANGE salt salt VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE confirmation_token confirmation_token VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_28F23E0C989D9B62 ON thesaurus (slug)');
    }
}
