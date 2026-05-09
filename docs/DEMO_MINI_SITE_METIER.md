# Guide de Demonstration du Mini-Site Metier

## Objectif

Le mini-site metier sert a montrer une chaine simple et credible :

1. connexion securisee par mot de passe + OTP
2. action metier dans `/intranet`
3. journalisation et analyse de la charge utile
4. lecture des traces, alertes et incidents dans CyberGuard

Il ne faut pas le presenter comme un produit autonome complet. C'est une surface de demonstration reliee au SOC.

## Ce que le mini-site demontre bien

- consultation et modification de donnees metier credibles
- separation entre profil `metier` et profil `admin`
- audit des actions applicatives
- detection de contenus suspects de type `SQL Injection` et `XSS`
- correlation entre un geste metier et une alerte visible dans l'interface SOC

## Ce qu'il vaut mieux ne pas promettre

- une simulation reseau complete depuis le mini-site
- une demonstration native du `Brute Force` via les ecrans CRUD
- un intranet academique ou RH exhaustif

Pour `Brute Force`, le meilleur parcours de demonstration reste :

- soit l'authentification et les echecs OTP / login
- soit le laboratoire `/simulations`
- soit une commande Artisan dediee a la demonstration SOC

## Preparation avant soutenance

### 1. Initialiser l'application

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

### 2. Lancer la queue

Le listener `ProcessIntranetDataChange` est queue. Si la queue ne tourne pas, les CRUD du mini-site fonctionneront, mais les remontees audit / detection ne seront pas visibles tout de suite.

```bash
php artisan queue:work
```

Si tu veux une demonstration encore plus directe en local, tu peux aussi basculer temporairement `QUEUE_CONNECTION=sync`.

### 3. Comptes utiles

- `admin@gmail.com` / `Admin@123`
- `metier@gmail.com` / `Metier@123`

Conseil pratique :

- ouvre une session `metier` dans un navigateur
- ouvre une session `admin` dans un autre navigateur ou en navigation privee

Tu pourras ainsi montrer le mini-site et le dashboard en parallele sans te deconnecter.

### 4. Recuperer l'OTP en local

En environnement local :

- le code OTP est expose en session de debug
- le mailer par defaut de `.env.example` est `MAIL_MAILER=log`

En pratique, pour une soutenance locale, il faut verifier :

- le toast ou l'affichage de debug sur l'etape OTP
- ou les logs si tu choisis de presenter l'envoi par mail journalise

## Parcours de demonstration recommande

## Scenario 1. Flux metier normal

But :
montrer qu'une action legitime traverse bien la chaine de securite sans bruit excessif.

Etapes :

1. se connecter avec `metier@gmail.com`
2. ouvrir `/intranet`
3. aller sur `Usagers` ou `Services`
4. creer ou modifier une fiche avec un contenu normal
5. expliquer que l'action est auditee meme si elle ne produit pas d'attaque
6. basculer sur la session `admin` et ouvrir `Alerts` ou `Attacks`

Message oral utile :

- "Le mini-site sert d'entree metier. Tout ce qui est fait ici passe par la session securisee, l'audit et les controles de plateforme."

## Scenario 2. Detection SQL Injection

But :
montrer qu'un contenu applicatif suspect devient un incident exploitable.

Parcours conseille :

1. te connecter en `metier`
2. ouvrir `Messages` ou `Services`
3. creer un contenu avec une charge utile SQL dans `body`, `title` ou `description`

Exemples efficaces :

```text
SELECT * FROM users WHERE id = 1 UNION SELECT password FROM admin
```

```text
' OR '1'='1
```

4. enregistrer le formulaire
5. laisser la queue traiter l'evenement
6. ouvrir la session `admin`
7. aller sur `Alerts`, puis sur `Attacks`
8. montrer l'attaque creee, son type, sa severite et sa description

Ce qui se passe reellement :

- le mini-site emet `IntranetDataChanged`
- le listener analyse les champs texte
- si un motif SQL est trouve, CyberGuard cree une attaque `SQL Injection`
- une alerte associee apparait cote SOC

## Scenario 3. Detection XSS

But :
montrer la detection d'un contenu HTML / JavaScript malicieux saisi dans une interface metier.

Parcours conseille :

1. te connecter en `metier`
2. ouvrir `Services` ou `Messages`
3. creer ou modifier un enregistrement avec un contenu du type :

```html
<script>alert("XSS")</script>
```

ou :

```html
<img src=x onerror=alert("XSS")>
```

4. enregistrer
5. basculer sur la session `admin`
6. montrer la remontee dans `Alerts` ou `Attacks`

Message oral utile :

- "Ici, le mini-site ne lance pas l'attaque dans le navigateur de la soutenance. Il sert a montrer qu'une charge utile suspecte saisie par un utilisateur est detectee, tracee et remontee au SOC."

## Scenario 4. Commandes de demonstration pretes a l'emploi

Si tu veux preparer des donnees avant la soutenance ou rejouer un cas sans retaper les payloads a la main :

```bash
php artisan intranet:vulnerabilities sql
php artisan intranet:vulnerabilities xss
php artisan intranet:vulnerabilities inject
php artisan intranet:vulnerabilities clean
```

Usage recommande :

- `sql` pour preparer un cas d'injection SQL
- `xss` pour preparer un cas XSS
- `inject` pour charger plusieurs supports de demonstration d'un coup
- `clean` pour repartir d'un etat plus propre

Important :
la commande `bruteforce` prepare un contexte de demonstration, mais ce n'est pas le meilleur levier pour montrer la detection temps reel depuis le mini-site.

## Enchainement de soutenance en 3 minutes

1. connexion `metier` avec OTP
2. ouverture du mini-site
3. saisie d'un message ou service avec un contenu suspect
4. validation du formulaire
5. bascule vers la session `admin`
6. consultation de `Alerts`
7. ouverture de l'incident dans `Attacks`
8. explication de la correlation entre action metier, audit et detection

## Points de vigilance

- verifier que `php artisan queue:work` tourne avant la demo
- garder deux sessions separees : `metier` et `admin`
- preparer 1 cas SQL et 1 cas XSS a l'avance
- nettoyer les donnees avec `php artisan intranet:vulnerabilities clean` entre deux repetitions si besoin
- ne pas presenter le mini-site comme une vraie plateforme de production complete

## Liens utiles

- [README.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/README.md)
- [INTRANET_README.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/INTRANET_README.md)
- [docs/ARCHITECTURE_SYSTEME.md](/home/olivierfatombi/Desktop/prog/academie/stage3/Projet-definitif-de-soutenance/docs/ARCHITECTURE_SYSTEME.md)
