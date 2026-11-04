# Sécurité

| Risque | Protection | Où |
| --- | --- | --- |
| Vol des mots de passe | Hachage avec l'algorithme recommandé par Symfony (`auto`), ré-encodé si l'algorithme évolue | `security.yaml`, `UtilisateurRepository::upgradePassword` |
| Force brute sur la connexion | 5 essais par quart d'heure, par adresse e-mail et par IP | `security.yaml` (`login_throttling`) |
| Requêtes forgées (CSRF) | Jeton sur chaque formulaire et chaque action (réserver, annuler, scanner…) | `csrf.yaml`, `#[IsCsrfTokenValid]` |
| Injection SQL | Requêtes Doctrine paramétrées, aucune concaténation de valeurs | `src/Repository`, `src/Service` |
| Injection HTML (XSS) | Échappement automatique de Twig | `templates/` |
| Accès aux pages d'un autre rôle | Rôles `ETUDIANT`, `PARTENAIRE`, `ADMIN` contrôlés sur chaque contrôleur | `#[IsGranted]`, `access_control` |
| Modifier la soirée d'un autre lieu | Voter qui vérifie le propriétaire | `Security/Voter/EvenementVoter` |
| Deviner le pass d'un autre | QR code aléatoire de 256 bits, unique en base | `Entity/Inscription` |
| Lien de réinitialisation volé | Seule l'empreinte SHA-256 est stockée, usage unique, une heure de validité | `Entity/DemandeReinitialisation` |
| Savoir qui a un compte | Même réponse que l'adresse existe ou non | `Service/ReinitialisationMotDePasse` |
| Spam de mails « mot de passe oublié » | 3 demandes par heure et par IP | `rate_limiter.yaml` |
| Vol de session par script | Cookie `HttpOnly` et `SameSite=Lax` | `framework.yaml` |
| Inscription d'un mineur | Âge calculé à partir de la date de naissance | `Form/Model/DemandeInscription` |
| Données personnelles (RGPD) | Suppression de compte par anonymisation, IP tronquée, journal effacé après un an | `ProfilController::supprimer`, `Service/Journal` |

Les tests fonctionnels vérifient les refus : étudiant sur l'espace partenaire, partenaire sur la soirée d'un autre lieu, réservation sans jeton CSRF, pass inconnu ou déjà scanné.
