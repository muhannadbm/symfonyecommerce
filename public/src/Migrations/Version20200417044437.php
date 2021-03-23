<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200417044437 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category CHANGE title title VARCHAR(50) DEFAULT NULL, CHANGE parentid parentid VARCHAR(40) DEFAULT NULL');
   
        $this->addSql('ALTER TABLE product ADD seller_id INT NOT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE sellerid sellerid INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD8DE820D9 FOREIGN KEY (seller_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD8DE820D9 ON product (seller_id)');
        $this->addSql('ALTER TABLE user CHANGE phone phone VARCHAR(50) DEFAULT NULL, CHANGE address address VARCHAR(180) DEFAULT NULL, CHANGE city city VARCHAR(50) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649D4E6F81 ON user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category CHANGE title title VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE parentid parentid VARCHAR(40) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD8DE820D9');
        $this->addSql('DROP INDEX IDX_D34A04AD8DE820D9 ON product');
        $this->addSql('ALTER TABLE product DROP seller_id, CHANGE sellerid sellerid VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE INDEX IDX_D34A04AD8DE820D9 ON product (sellerid)');
        $this->addSql('DROP INDEX UNIQ_8D93D649D4E6F81 ON user');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE updated_at updated_at DATETIME DEFAULT NULL, CHANGE address address VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE phone phone VARCHAR(40) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE city city VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
