<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240823062617 extends AbstractMigration
{
  public function getDescription(): string
  {
    return '';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE author (
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
          name CLOB NOT NULL, bio CLOB NOT NULL,
          birth_date DATE NOT NULL
        )');

    for ($i = 1; $i <= 3; $i++) {
      $this->addSql("INSERT INTO author (name, bio, birth_date) VALUES ('AUTHOR {$i}', 'lorem impsum dolor', '2024-10-10')");
    }

    $this->addSql('CREATE TABLE book (
          id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
          title VARCHAR(255) NOT NULL,
          description CLOB DEFAULT NULL,
          publish_date DATE NOT NULL,
          author_id INTEGER NOT NULL,
          FOREIGN KEY (author_id) REFERENCES authors(id)
        )');

    for ($i = 1; $i <= 3; $i++) {
      for ($x = 1; $x <= 5; $x++) {
        $this->addSql("INSERT INTO book (title, description, publish_date, author_id) VALUES ('BOOK {$x}-{$i}', 'lorem impsum dolor', '2024-10-10', {$i})");
      }
    }
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE author');
    $this->addSql('DROP TABLE book');
  }
}
