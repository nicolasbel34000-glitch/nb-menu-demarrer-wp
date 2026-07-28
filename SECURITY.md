# Security policy

## Modèle de sécurité

NB Menu démarrer WP est une interface locale à l’administration WordPress :

- il ne crée aucun compte et n’accorde aucune capacité ;
- il revérifie les capacités WordPress avant d’exposer un menu ou un sous-menu ;
- il ne s’affiche que dans l’administration pour un utilisateur connecté ;
- il protège la sauvegarde par nonce et l’associe exclusivement à l’identifiant du compte courant ;
- il limite la taille et le nombre des favoris, dossiers et raccourcis ;
- il assainit tous les titres, identifiants et URL avant stockage ;
- il refuse les protocoles d’URL autres que HTTP, HTTPS, `mailto` et `tel` ;
- il injecte les données dans la page sous forme de JSON échappé et utilise `textContent` côté navigateur ;
- il n’exécute aucun code fourni par l’utilisateur ;
- il ne contacte aucun service externe et ne collecte aucune donnée.

Le plugin ne remplace pas le contrôle d’accès WordPress. Une page reste protégée par les droits de WordPress et de l’extension qui l’a créée, même lorsqu’un raccourci vers cette page existe.

## Reporting a vulnerability

Do not publish credentials, private keys, tokens, nonces, personal data or exploit details in a public issue.

Please use the repository’s **Private vulnerability reporting** feature under its Security tab. Include the affected version, reproduction steps and impact without including secrets from a production site.

## Repository hygiene

The repository must not contain production configuration, database exports, logs, backup archives or cryptographic private keys. The included `.gitignore` blocks common sensitive file formats, but every release archive must also pass a secret scan before publication.

La publication est bloquée automatiquement si elle détecte un secret probable, une primitive d’exécution PHP dangereuse, une archive suivie par Git, un fichier anormalement lourd ou la disparition d’un contrôle de sécurité essentiel.

## Supported versions

Security fixes are applied to the latest published release of NB Menu démarrer WP.
