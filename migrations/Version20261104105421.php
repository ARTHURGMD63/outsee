<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Schéma initial d'Outsee : comptes, établissements, événements, squads,
 * vie sociale, back-office commercial, et le catalogue des badges.
 */
final class Version20261104105421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schéma initial et catalogue des badges';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, note SMALLINT NOT NULL, commentaire LONGTEXT DEFAULT NULL, user_id INT NOT NULL, evenement_id INT NOT NULL, INDEX idx_avis_evenement (evenement_id, note), UNIQUE INDEX unique_avis (user_id, evenement_id), INDEX IDX_8F91ABF0A76ED395 (user_id), INDEX IDX_8F91ABF0FD02F13 (evenement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE badges (code VARCHAR(50) NOT NULL, nom VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, icon VARCHAR(32) DEFAULT NULL, couleur VARCHAR(40) DEFAULT NULL, PRIMARY KEY (code)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crm_clients (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(191) NOT NULL, categorie VARCHAR(20) DEFAULT \'bar\' NOT NULL, ville VARCHAR(100) DEFAULT \'Clermont-Ferrand\' NOT NULL, adresse VARCHAR(255) DEFAULT NULL, contact_nom VARCHAR(191) DEFAULT NULL, contact_role VARCHAR(100) DEFAULT NULL, contact_email VARCHAR(191) DEFAULT NULL, contact_tel VARCHAR(40) DEFAULT NULL, statut VARCHAR(20) DEFAULT \'prospect\' NOT NULL, offre VARCHAR(20) DEFAULT \'aucune\' NOT NULL, mrr NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL, essai_jusqu_au DATE DEFAULT NULL, signe_le DATE DEFAULT NULL, perdu_le DATE DEFAULT NULL, motif_perte VARCHAR(255) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, etablissement_id INT DEFAULT NULL, responsable_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_EC8362E3FF631228 (etablissement_id), INDEX k_statut (statut), INDEX IDX_EC8362E353C59D72 (responsable_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE crm_interactions (id INT AUTO_INCREMENT NOT NULL, prochaine_action VARCHAR(255) DEFAULT NULL, prochaine_action_le DATE DEFAULT NULL, fait TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, type VARCHAR(20) DEFAULT \'note\' NOT NULL, contenu LONGTEXT NOT NULL, client_id INT NOT NULL, auteur_id INT DEFAULT NULL, INDEX k_client (client_id, created_at), INDEX k_relance (prochaine_action_le, fait), INDEX IDX_6F6BB6F919EB6921 (client_id), INDEX IDX_6F6BB6F960BB6FE6 (auteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE economies (id INT AUTO_INCREMENT NOT NULL, montant NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL, date_economie DATE NOT NULL, user_id INT NOT NULL, evenement_id INT NOT NULL, INDEX idx_eco_user_date (user_id, date_economie), INDEX IDX_A537A5B2A76ED395 (user_id), INDEX IDX_A537A5B2FD02F13 (evenement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etablissement_photos (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, fichier VARCHAR(255) NOT NULL, legende VARCHAR(160) DEFAULT NULL, position INT DEFAULT 0 NOT NULL, etablissement_id INT NOT NULL, INDEX idx_etab_position (etablissement_id, position), INDEX IDX_C24F365FFF631228 (etablissement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etablissements (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(200) NOT NULL, type VARCHAR(20) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, ville VARCHAR(100) DEFAULT \'Clermont-Ferrand\', latitude NUMERIC(10, 7) DEFAULT NULL, longitude NUMERIC(10, 7) DEFAULT NULL, user_id INT NOT NULL, INDEX idx_etab_ville (ville), INDEX IDX_29F65EB1A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement_photo_signalements (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, motif VARCHAR(30) NOT NULL, details VARCHAR(500) DEFAULT NULL, photo_id INT NOT NULL, reporter_id INT NOT NULL, UNIQUE INDEX unique_signalement (photo_id, reporter_id), INDEX IDX_8A6405757E9E4C8C (photo_id), INDEX IDX_8A640575E1CFE6F5 (reporter_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement_photos (id INT AUTO_INCREMENT NOT NULL, masquee TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, fichier VARCHAR(255) NOT NULL, legende VARCHAR(255) DEFAULT NULL, evenement_id INT NOT NULL, user_id INT NOT NULL, INDEX idx_evphoto_event (evenement_id, created_at), UNIQUE INDEX unique_photo_par_personne (evenement_id, user_id), INDEX IDX_BB83305BFD02F13 (evenement_id), INDEX IDX_BB83305BA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenements (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(20) NOT NULL, style_musique VARCHAR(20) DEFAULT NULL, date_heure DATETIME NOT NULL, date_fin DATETIME DEFAULT NULL, quota INT DEFAULT 100 NOT NULL, reduction INT DEFAULT 0 NOT NULL, prix_normal NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL, is_flash TINYINT DEFAULT 0 NOT NULL, flash_expiry DATETIME DEFAULT NULL, is_gratuit TINYINT DEFAULT 0 NOT NULL, is_sponsorise TINYINT DEFAULT 0 NOT NULL, sponsor_formule VARCHAR(20) DEFAULT NULL, sponsor_tarif NUMERIC(8, 2) DEFAULT \'0.00\' NOT NULL, sponsor_jusqu_au DATETIME DEFAULT NULL, lieu VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, etablissement_id INT NOT NULL, INDEX idx_ev_date (date_heure), INDEX idx_ev_etab_date (etablissement_id, date_heure), INDEX idx_ev_type_date (type, date_heure), INDEX idx_ev_etab_creation (etablissement_id, created_at), INDEX idx_sponsor (is_sponsorise, sponsor_jusqu_au), INDEX idx_style_musique (style_musique, date_heure), INDEX IDX_E10AD400FF631228 (etablissement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE finance_mouvements (id INT AUTO_INCREMENT NOT NULL, sens VARCHAR(10) NOT NULL, categorie VARCHAR(20) NOT NULL, libelle VARCHAR(191) NOT NULL, montant_ht NUMERIC(10, 2) NOT NULL, tva_taux NUMERIC(5, 2) DEFAULT \'20.00\' NOT NULL, date_mouvement DATE NOT NULL, statut VARCHAR(10) DEFAULT \'regle\' NOT NULL, moyen VARCHAR(40) DEFAULT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, client_id INT DEFAULT NULL, cree_par INT DEFAULT NULL, INDEX k_periode (date_mouvement, sens), INDEX IDX_E88F388519EB6921 (client_id), INDEX IDX_E88F38854C28E2B5 (cree_par), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE follows_etablissements (created_at DATETIME NOT NULL, user_id INT NOT NULL, etablissement_id INT NOT NULL, INDEX IDX_412223FCA76ED395 (user_id), INDEX IDX_412223FCFF631228 (etablissement_id), PRIMARY KEY (user_id, etablissement_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE follows_users (statut VARCHAR(10) DEFAULT \'pending\' NOT NULL, created_at DATETIME NOT NULL, responded_at DATETIME DEFAULT NULL, follower_id INT NOT NULL, followed_id INT NOT NULL, INDEX idx_follows_demandes (followed_id, statut), INDEX idx_follows_suivis (follower_id, statut, followed_id), INDEX IDX_BD07A4E5AC24F853 (follower_id), INDEX IDX_BD07A4E5D956F010 (followed_id), PRIMARY KEY (follower_id, followed_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscriptions (id INT AUTO_INCREMENT NOT NULL, qr_code VARCHAR(64) NOT NULL, statut VARCHAR(10) DEFAULT \'inscrit\' NOT NULL, checkin_le DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, evenement_id INT NOT NULL, INDEX idx_insc_ev_statut (evenement_id, statut), INDEX idx_insc_user_statut_date (user_id, statut, created_at), UNIQUE INDEX unique_inscription (user_id, evenement_id), UNIQUE INDEX idx_insc_qr (qr_code), INDEX IDX_74E0281CA76ED395 (user_id), INDEX IDX_74E0281CFD02F13 (evenement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitations (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(10) DEFAULT \'pending\' NOT NULL, created_at DATETIME NOT NULL, type VARCHAR(10) NOT NULL, target_id INT NOT NULL, from_user_id INT NOT NULL, to_user_id INT NOT NULL, INDEX k_to (to_user_id, statut), UNIQUE INDEX uniq_invitation (from_user_id, to_user_id, type, target_id), INDEX IDX_232710AE2130303A (from_user_id), INDEX IDX_232710AE29F6EE60 (to_user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE liste_attente (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(190) NOT NULL, cree_le DATETIME NOT NULL, parrain_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_A54CC198E7927C74 (email), INDEX idx_parrain (parrain_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE password_resets (id INT AUTO_INCREMENT NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, used TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_9EDAFEA1B3BC57DA (token_hash), INDEX idx_email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rappels_envoyes (envoye_le DATETIME NOT NULL, type VARCHAR(20) NOT NULL, inscription_id INT NOT NULL, INDEX IDX_2D1428E25DAC5993 (inscription_id), PRIMARY KEY (inscription_id, type)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE squad_membres (id INT AUTO_INCREMENT NOT NULL, joined_at DATETIME NOT NULL, squad_id INT NOT NULL, user_id INT NOT NULL, INDEX idx_sm_user_date (user_id, joined_at), UNIQUE INDEX unique_membre (squad_id, user_id), INDEX IDX_4F5A078CDF1B2C7C (squad_id), INDEX IDX_4F5A078CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE squads (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, cree_par_association TINYINT DEFAULT 0 NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, niveau VARCHAR(10) DEFAULT \'tous\' NOT NULL, date_heure DATETIME NOT NULL, lieu VARCHAR(255) DEFAULT NULL, quota INT DEFAULT 10 NOT NULL, created_at DATETIME NOT NULL, createur_id INT NOT NULL, INDEX idx_sq_date (date_heure), INDEX IDX_25AF682F73A201E5 (createur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_badges (unlocked_at DATETIME NOT NULL, user_id INT NOT NULL, badge_code VARCHAR(50) NOT NULL, INDEX IDX_1DA448A7A76ED395 (user_id), INDEX IDX_1DA448A7D31BDC81 (badge_code), PRIMARY KEY (user_id, badge_code)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_blocks (created_at DATETIME NOT NULL, blocker_id INT NOT NULL, blocked_id INT NOT NULL, INDEX k_blocked (blocked_id), INDEX IDX_ABBF8E45548D5975 (blocker_id), PRIMARY KEY (blocker_id, blocked_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_interets (interet VARCHAR(40) NOT NULL, user_id INT NOT NULL, INDEX k_interet (interet, user_id), INDEX IDX_5D96FD03A76ED395 (user_id), PRIMARY KEY (user_id, interet)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_reports (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(10) DEFAULT \'nouveau\' NOT NULL, traite_le DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, motif VARCHAR(30) NOT NULL, details VARCHAR(500) DEFAULT NULL, traite_par INT DEFAULT NULL, reporter_id INT NOT NULL, reported_id INT NOT NULL, INDEX k_reported (reported_id, statut), INDEX IDX_A912B94F401856C (traite_par), INDEX IDX_A912B94FE1CFE6F5 (reporter_id), INDEX IDX_A912B94F94BDEEB6 (reported_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(191) NOT NULL, password VARCHAR(255) NOT NULL, ecole VARCHAR(100) DEFAULT NULL, promo VARCHAR(10) DEFAULT NULL, ville VARCHAR(100) DEFAULT NULL, photo VARCHAR(255) DEFAULT NULL, date_naissance DATE DEFAULT NULL, cgu_acceptees_le DATETIME DEFAULT NULL, type VARCHAR(20) DEFAULT \'etudiant\' NOT NULL, compte_prive TINYINT DEFAULT 1 NOT NULL, theme VARCHAR(10) DEFAULT \'light\' NOT NULL, notifications_lues_le DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX idx_users_type_ecole (type, ecole), INDEX idx_users_type_date (type, created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crm_clients ADD CONSTRAINT FK_EC8362E3FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissements (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE crm_clients ADD CONSTRAINT FK_EC8362E353C59D72 FOREIGN KEY (responsable_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE crm_interactions ADD CONSTRAINT FK_6F6BB6F919EB6921 FOREIGN KEY (client_id) REFERENCES crm_clients (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE crm_interactions ADD CONSTRAINT FK_6F6BB6F960BB6FE6 FOREIGN KEY (auteur_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE economies ADD CONSTRAINT FK_A537A5B2A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE economies ADD CONSTRAINT FK_A537A5B2FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etablissement_photos ADD CONSTRAINT FK_C24F365FFF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE etablissements ADD CONSTRAINT FK_29F65EB1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_photo_signalements ADD CONSTRAINT FK_8A6405757E9E4C8C FOREIGN KEY (photo_id) REFERENCES evenement_photos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_photo_signalements ADD CONSTRAINT FK_8A640575E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_photos ADD CONSTRAINT FK_BB83305BFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_photos ADD CONSTRAINT FK_BB83305BA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenements ADD CONSTRAINT FK_E10AD400FF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE finance_mouvements ADD CONSTRAINT FK_E88F388519EB6921 FOREIGN KEY (client_id) REFERENCES crm_clients (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE finance_mouvements ADD CONSTRAINT FK_E88F38854C28E2B5 FOREIGN KEY (cree_par) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE follows_etablissements ADD CONSTRAINT FK_412223FCA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE follows_etablissements ADD CONSTRAINT FK_412223FCFF631228 FOREIGN KEY (etablissement_id) REFERENCES etablissements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE follows_users ADD CONSTRAINT FK_BD07A4E5AC24F853 FOREIGN KEY (follower_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE follows_users ADD CONSTRAINT FK_BD07A4E5D956F010 FOREIGN KEY (followed_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscriptions ADD CONSTRAINT FK_74E0281CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscriptions ADD CONSTRAINT FK_74E0281CFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenements (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AE2130303A FOREIGN KEY (from_user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitations ADD CONSTRAINT FK_232710AE29F6EE60 FOREIGN KEY (to_user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rappels_envoyes ADD CONSTRAINT FK_2D1428E25DAC5993 FOREIGN KEY (inscription_id) REFERENCES inscriptions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE squad_membres ADD CONSTRAINT FK_4F5A078CDF1B2C7C FOREIGN KEY (squad_id) REFERENCES squads (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE squad_membres ADD CONSTRAINT FK_4F5A078CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE squads ADD CONSTRAINT FK_25AF682F73A201E5 FOREIGN KEY (createur_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_badges ADD CONSTRAINT FK_1DA448A7A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_badges ADD CONSTRAINT FK_1DA448A7D31BDC81 FOREIGN KEY (badge_code) REFERENCES badges (code) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_blocks ADD CONSTRAINT FK_ABBF8E45548D5975 FOREIGN KEY (blocker_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_blocks ADD CONSTRAINT FK_ABBF8E4521FF5136 FOREIGN KEY (blocked_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_interets ADD CONSTRAINT FK_5D96FD03A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reports ADD CONSTRAINT FK_A912B94F401856C FOREIGN KEY (traite_par) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_reports ADD CONSTRAINT FK_A912B94FE1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_reports ADD CONSTRAINT FK_A912B94F94BDEEB6 FOREIGN KEY (reported_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql(<<<'SQL'
            INSERT INTO badges (code, nom, description, icon, couleur) VALUES
            ('first_event',  'Premier pas',      'Ton tout premier event',     'trophee',   'var(--rouge)'),
            ('five_events',  'Régulier',         '5 events à ton actif',       'flamme',    'var(--orange)'),
            ('ten_events',   'Noctambule',       '10 events validés',          'lune',      'var(--bleu)'),
            ('first_squad',  'Team player',      'Rejoint ta première squad',  'drapeau',   'var(--lime)'),
            ('five_squads',  'Social butterfly', '5 squads rejointes',         'papillon',  'var(--rouge)'),
            ('first_follow', 'Connecté',         'Suivi ta première personne', 'personnes', 'var(--bleu)'),
            ('reviewer',     'Critique',         'Laissé ton premier avis',    'oiseau',    'var(--orange)'),
            ('early_bird',   'Early bird',       'Inscrit 7j avant un event',  'etoile',    'var(--lime)'),
            ('saver_50',     'Économe',          '50€ économisés au total',    'piece',     'var(--rouge)')
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0A76ED395');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0FD02F13');
        $this->addSql('ALTER TABLE crm_clients DROP FOREIGN KEY FK_EC8362E3FF631228');
        $this->addSql('ALTER TABLE crm_clients DROP FOREIGN KEY FK_EC8362E353C59D72');
        $this->addSql('ALTER TABLE crm_interactions DROP FOREIGN KEY FK_6F6BB6F919EB6921');
        $this->addSql('ALTER TABLE crm_interactions DROP FOREIGN KEY FK_6F6BB6F960BB6FE6');
        $this->addSql('ALTER TABLE economies DROP FOREIGN KEY FK_A537A5B2A76ED395');
        $this->addSql('ALTER TABLE economies DROP FOREIGN KEY FK_A537A5B2FD02F13');
        $this->addSql('ALTER TABLE etablissement_photos DROP FOREIGN KEY FK_C24F365FFF631228');
        $this->addSql('ALTER TABLE etablissements DROP FOREIGN KEY FK_29F65EB1A76ED395');
        $this->addSql('ALTER TABLE evenement_photo_signalements DROP FOREIGN KEY FK_8A6405757E9E4C8C');
        $this->addSql('ALTER TABLE evenement_photo_signalements DROP FOREIGN KEY FK_8A640575E1CFE6F5');
        $this->addSql('ALTER TABLE evenement_photos DROP FOREIGN KEY FK_BB83305BFD02F13');
        $this->addSql('ALTER TABLE evenement_photos DROP FOREIGN KEY FK_BB83305BA76ED395');
        $this->addSql('ALTER TABLE evenements DROP FOREIGN KEY FK_E10AD400FF631228');
        $this->addSql('ALTER TABLE finance_mouvements DROP FOREIGN KEY FK_E88F388519EB6921');
        $this->addSql('ALTER TABLE finance_mouvements DROP FOREIGN KEY FK_E88F38854C28E2B5');
        $this->addSql('ALTER TABLE follows_etablissements DROP FOREIGN KEY FK_412223FCA76ED395');
        $this->addSql('ALTER TABLE follows_etablissements DROP FOREIGN KEY FK_412223FCFF631228');
        $this->addSql('ALTER TABLE follows_users DROP FOREIGN KEY FK_BD07A4E5AC24F853');
        $this->addSql('ALTER TABLE follows_users DROP FOREIGN KEY FK_BD07A4E5D956F010');
        $this->addSql('ALTER TABLE inscriptions DROP FOREIGN KEY FK_74E0281CA76ED395');
        $this->addSql('ALTER TABLE inscriptions DROP FOREIGN KEY FK_74E0281CFD02F13');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AE2130303A');
        $this->addSql('ALTER TABLE invitations DROP FOREIGN KEY FK_232710AE29F6EE60');
        $this->addSql('ALTER TABLE rappels_envoyes DROP FOREIGN KEY FK_2D1428E25DAC5993');
        $this->addSql('ALTER TABLE squad_membres DROP FOREIGN KEY FK_4F5A078CDF1B2C7C');
        $this->addSql('ALTER TABLE squad_membres DROP FOREIGN KEY FK_4F5A078CA76ED395');
        $this->addSql('ALTER TABLE squads DROP FOREIGN KEY FK_25AF682F73A201E5');
        $this->addSql('ALTER TABLE user_badges DROP FOREIGN KEY FK_1DA448A7A76ED395');
        $this->addSql('ALTER TABLE user_badges DROP FOREIGN KEY FK_1DA448A7D31BDC81');
        $this->addSql('ALTER TABLE user_blocks DROP FOREIGN KEY FK_ABBF8E45548D5975');
        $this->addSql('ALTER TABLE user_blocks DROP FOREIGN KEY FK_ABBF8E4521FF5136');
        $this->addSql('ALTER TABLE user_interets DROP FOREIGN KEY FK_5D96FD03A76ED395');
        $this->addSql('ALTER TABLE user_reports DROP FOREIGN KEY FK_A912B94F401856C');
        $this->addSql('ALTER TABLE user_reports DROP FOREIGN KEY FK_A912B94FE1CFE6F5');
        $this->addSql('ALTER TABLE user_reports DROP FOREIGN KEY FK_A912B94F94BDEEB6');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE badges');
        $this->addSql('DROP TABLE crm_clients');
        $this->addSql('DROP TABLE crm_interactions');
        $this->addSql('DROP TABLE economies');
        $this->addSql('DROP TABLE etablissement_photos');
        $this->addSql('DROP TABLE etablissements');
        $this->addSql('DROP TABLE evenement_photo_signalements');
        $this->addSql('DROP TABLE evenement_photos');
        $this->addSql('DROP TABLE evenements');
        $this->addSql('DROP TABLE finance_mouvements');
        $this->addSql('DROP TABLE follows_etablissements');
        $this->addSql('DROP TABLE follows_users');
        $this->addSql('DROP TABLE inscriptions');
        $this->addSql('DROP TABLE invitations');
        $this->addSql('DROP TABLE liste_attente');
        $this->addSql('DROP TABLE password_resets');
        $this->addSql('DROP TABLE rappels_envoyes');
        $this->addSql('DROP TABLE squad_membres');
        $this->addSql('DROP TABLE squads');
        $this->addSql('DROP TABLE user_badges');
        $this->addSql('DROP TABLE user_blocks');
        $this->addSql('DROP TABLE user_interets');
        $this->addSql('DROP TABLE user_reports');
        $this->addSql('DROP TABLE users');
    }
}
