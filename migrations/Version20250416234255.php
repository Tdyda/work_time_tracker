<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416234255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE work_time_entry (id INT AUTO_INCREMENT NOT NULL, employee_id CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)', start_time DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', end_time DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', start_day DATE NOT NULL, INDEX IDX_7530D1DF8C03F15C (employee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE work_time_entry ADD CONSTRAINT FK_7530D1DF8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE work_time_entry DROP FOREIGN KEY FK_7530D1DF8C03F15C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE work_time_entry
        SQL);
    }
}
