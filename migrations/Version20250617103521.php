<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250617103521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD first_name VARCHAR(100) NOT NULL, ADD last_name VARCHAR(100) NOT NULL, ADD address VARCHAR(255) NOT NULL, ADD postal_code VARCHAR(20) NOT NULL, ADD city VARCHAR(100) NOT NULL, ADD phone VARCHAR(20) NOT NULL, ADD notes LONGTEXT DEFAULT NULL, DROP shipping_address, DROP billing_address, CHANGE created_at created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD billing_address VARCHAR(255) NOT NULL, DROP first_name, DROP last_name, DROP postal_code, DROP city, DROP phone, DROP notes, CHANGE created_at created_at DATETIME NOT NULL, CHANGE address shipping_address VARCHAR(255) NOT NULL
        SQL);
    }
}
