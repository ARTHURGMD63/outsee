<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DemandeReinitialisation;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Mot de passe oublié : un lien à usage unique, valable une heure, envoyé
 * par e-mail. Le jeton n'existe en clair que dans le mail.
 */
final readonly class ReinitialisationMotDePasse
{
    public function __construct(
        private UtilisateurRepository $utilisateurs,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urls,
        private UserPasswordHasherInterface $hacheur,
        #[Autowire('%env(MAILER_FROM)%')]
        private string $expediteur,
    ) {
    }

    /**
     * Ne dit jamais si l'adresse existe : la réponse affichée est la même
     * dans les deux cas, pour ne pas révéler qui a un compte.
     */
    public function demander(string $email): void
    {
        $utilisateur = $this->utilisateurs->findOneByEmail($email);
        if (null === $utilisateur || [] === $utilisateur->getRoles()) {
            return;
        }

        $jeton = bin2hex(random_bytes(32));
        $this->entityManager->persist(new DemandeReinitialisation($utilisateur->getEmail(), $jeton));
        $this->entityManager->flush();

        $this->mailer->send((new TemplatedEmail())
            ->from($this->expediteur)
            ->to($utilisateur->getEmail())
            ->subject('Réinitialise ton mot de passe Outsee')
            ->htmlTemplate('emails/reinitialisation.html.twig')
            ->context([
                'prenom' => $utilisateur->getPrenom(),
                'lien' => $this->urls->generate('mot_de_passe_nouveau', ['jeton' => $jeton], UrlGeneratorInterface::ABSOLUTE_URL),
            ]));
    }

    public function demandeValide(string $jeton): ?DemandeReinitialisation
    {
        $demande = $this->entityManager->getRepository(DemandeReinitialisation::class)
            ->findOneBy(['tokenHash' => DemandeReinitialisation::empreinte($jeton)]);

        return $demande?->estValide() ? $demande : null;
    }

    public function changer(DemandeReinitialisation $demande, string $nouveauMotDePasse): ?Utilisateur
    {
        $utilisateur = $this->utilisateurs->findOneByEmail($demande->getEmail());
        if (null === $utilisateur) {
            return null;
        }

        $utilisateur->setPassword($this->hacheur->hashPassword($utilisateur, $nouveauMotDePasse));
        $demande->consommer();
        $this->entityManager->flush();

        return $utilisateur;
    }
}
