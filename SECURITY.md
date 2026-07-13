# Security policy

## Reporting a vulnerability

Do not publish credentials, private keys, tokens, nonces, personal data or exploit details in a public issue.

Please use the repository’s **Private vulnerability reporting** feature under its Security tab. Include the affected version, reproduction steps and impact without including secrets from a production site.

## Repository hygiene

The repository must not contain production configuration, database exports, logs, backup archives or cryptographic private keys. The included `.gitignore` blocks common sensitive file formats, but every release archive must also pass a secret scan before publication.

## Supported versions

Security fixes are applied to the latest published release of WP Start Menu.
