<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250614214702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE file (file_uuid UUID NOT NULL, user_id UUID NOT NULL, original_filename VARCHAR(255) NOT NULL, size BIGINT NOT NULL, mime_type VARCHAR(255) NOT NULL, storage_path VARCHAR(255) NOT NULL, uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(file_uuid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8C9F3610279401A ON file (storage_path)
        SQL);
        // >>> ADD THIS LINE: COMPOSITE UNIQUE INDEX FOR (original_filename, user_id) <<<
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_FILE_ORIGINAL_FILENAME_USER ON file (original_filename, user_id)
        SQL);
        // >>> END ADDED LINE <<<
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8C9F3610A76ED395 ON file (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN file.uploaded_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (uuid UUID NOT NULL, email VARCHAR(70) NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(uuid))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN "user".created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE file ADD CONSTRAINT FK_8C9F3610A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (uuid) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE file DROP CONSTRAINT FK_8C9F3610A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE file
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
    }
}
