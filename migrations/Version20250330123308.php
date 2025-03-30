<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250330123308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_profile ADD candidate_accepted TINYINT(1) DEFAULT 0 NOT NULL, CHANGE date_of_birth date_of_birth DATETIME DEFAULT NULL, CHANGE nationality nationality VARCHAR(100) DEFAULT NULL, CHANGE school school VARCHAR(100) DEFAULT NULL, CHANGE course_name course_name VARCHAR(100) DEFAULT NULL, CHANGE specialization specialization VARCHAR(255) DEFAULT NULL, CHANGE course_start_date course_start_date DATETIME DEFAULT NULL, CHANGE student_card student_card VARCHAR(255) DEFAULT NULL, CHANGE research_subject research_subject VARCHAR(255) DEFAULT NULL, CHANGE cv cv VARCHAR(255) DEFAULT NULL, CHANGE video_link video_link VARCHAR(255) DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(30) DEFAULT NULL, CHANGE status status VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE candidate_workflow CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE step2_submitted_at step2_submitted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE step2_reviewed_at step2_reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE selected_at selected_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE rejected_at rejected_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE jury_profile CHANGE proffession proffession VARCHAR(100) DEFAULT NULL, CHANGE mini_cv mini_cv VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE registration_workflow CHANGE data data JSON DEFAULT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE role CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE validation_token validation_token VARCHAR(100) DEFAULT NULL, CHANGE validation_token_expires_at validation_token_expires_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_workflow CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\', CHANGE step2_submitted_at step2_submitted_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\', CHANGE step2_reviewed_at step2_reviewed_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\', CHANGE selected_at selected_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\', CHANGE rejected_at rejected_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE role CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`, CHANGE validation_token validation_token VARCHAR(100) DEFAULT \'NULL\', CHANGE validation_token_expires_at validation_token_expires_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE jury_profile CHANGE proffession proffession VARCHAR(100) DEFAULT \'NULL\', CHANGE mini_cv mini_cv VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE registration_workflow CHANGE data data LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE candidate_profile DROP candidate_accepted, CHANGE date_of_birth date_of_birth DATETIME DEFAULT \'NULL\', CHANGE nationality nationality VARCHAR(100) DEFAULT \'NULL\', CHANGE school school VARCHAR(100) DEFAULT \'NULL\', CHANGE course_name course_name VARCHAR(100) DEFAULT \'NULL\', CHANGE specialization specialization VARCHAR(255) DEFAULT \'NULL\', CHANGE course_start_date course_start_date DATETIME DEFAULT \'NULL\', CHANGE student_card student_card VARCHAR(255) DEFAULT \'NULL\', CHANGE research_subject research_subject VARCHAR(255) DEFAULT \'NULL\', CHANGE cv cv VARCHAR(255) DEFAULT \'NULL\', CHANGE video_link video_link VARCHAR(255) DEFAULT \'NULL\', CHANGE address address VARCHAR(255) DEFAULT \'NULL\', CHANGE phone phone VARCHAR(30) DEFAULT \'NULL\', CHANGE status status VARCHAR(50) DEFAULT \'NULL\'');
    }
}
