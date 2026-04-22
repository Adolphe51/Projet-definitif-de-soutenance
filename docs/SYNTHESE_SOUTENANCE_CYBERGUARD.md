# Synthese De Soutenance - CyberGuard

## 1. Presentation Du Projet

CyberGuard est une application web developpee avec Laravel 12, concue comme une plateforme de demonstration, de supervision et de defense en cybersécurité.

L'idee principale du projet est de reunir dans une meme application:

- un systeme d'authentification renforce
- un tableau de bord de supervision securite
- un moteur de simulation et de detection d'attaques
- un honeypot pour pieger et analyser des comportements suspects
- un intranet academique servant de terrain fonctionnel de test

Le projet a donc une double finalite:

- pedagogique, pour montrer des mecanismes de securite applicative
- technique, pour centraliser la detection, la surveillance et la reaction

## 2. Objectif General

L'objectif de CyberGuard est de proposer une plateforme capable de:

- detecter ou simuler des attaques informatiques
- surveiller l'activite suspecte en temps reel
- enregistrer les traces utiles a l'analyse
- renforcer la securite d'acces a l'application
- illustrer des mecanismes de defense dans un contexte concret

## 3. Fonctionnalites Principales

### Authentification securisee

Le systeme d'authentification repose sur:

- email et mot de passe
- etat temporaire `pending_auth`
- verification OTP par email
- creation d'une session securisee
- limitation des tentatives
- journalisation des acces et des echecs
- cookie d'acces securise pour proteger les routes sensibles

Cette partie renforce l'acces a la plateforme et limite les risques de compromission.

### Tableau de bord securite

Le dashboard permet d'afficher:

- le nombre total d'attaques
- les attaques critiques
- les IP bloquees
- les alertes non lues
- les activites recentes du honeypot
- les tendances generales du systeme

Il sert d'interface centrale de supervision.

### Detection et simulation d'attaques

Le projet integre un moteur capable de generer plusieurs types d'attaques:

- DDoS
- SQL Injection
- XSS
- Brute Force
- Port Scan
- Ransomware
- Phishing

Chaque attaque contient des informations comme:

- l'adresse IP source
- la cible
- le niveau de severite
- la localisation geographique
- les metadonnees reseau

### Honeypot

Le honeypot est une composante importante du projet. Il permet de deployer des pieges comme:

- un faux espace admin
- un faux phpMyAdmin
- un faux WordPress
- une fausse API
- un document canary

Le but est de capturer:

- les tentatives d'acces
- les identifiants saisis
- les payloads suspects
- les informations de contexte comme l'IP et le navigateur

### Intranet academique

Le projet contient aussi un intranet academique avec:

- gestion des etudiants
- gestion des cours
- inscriptions
- presences
- messagerie
- ressources pedagogiques

Cet intranet donne un cadre metier realiste au projet et sert de support aux scenarios de test.

## 4. Choix Techniques

### Technologies utilisees

- Laravel 12
- PHP 8.2+
- Blade
- JavaScript
- Vite
- Tailwind CSS
- SQLite en developpement
- MySQL possible en production

### Organisation du projet

L'application suit une architecture Laravel classique et proprement separee:

- `Controllers` pour la logique de navigation
- `Services` pour la logique metier
- `Models` pour la gestion des donnees
- `Middleware` pour les controles de securite
- `Views` pour l'interface utilisateur

Cette separation rend le projet plus maintenable et evolutif.

## 5. Aspects Securite

CyberGuard met l'accent sur plusieurs mesures de securite:

- authentification par OTP
- verification prealable du mot de passe avant emission du code
- limitation du nombre de tentatives
- verification de session
- controle CSRF renforce
- cookie `httpOnly` avec `sameSite=strict`
- blocage d'IP suspectes
- journal d'audit
- honeypot de collecte
- alertes en cas d'activite critique

Ces mecanismes montrent une approche defensive multicouche.

## 6. Base De Donnees Et Jeux De Donnees

La base de donnees contient notamment:

- utilisateurs
- sessions de securite
- permissions et roles
- attaques
- alertes
- simulations
- IP bloquees
- interactions honeypot
- donnees de l'intranet

Le projet inclut aussi des seeders permettant de generer:

- des comptes de demonstration
- des attaques de test
- des interactions honeypot
- des donnees intranet realistes

Cela facilite les demonstrations et les tests.

## 7. Resultat De L'Analyse Actuelle

L'analyse rapide du depot montre que:

- le coeur du projet est bien structure
- l'authentification OTP, le dashboard et l'intranet sont clairement en place
- la couche d'authentification a ete renforcee et mieux testee
- les modules attaques, alertes, geolocalisation, simulation et honeypot sont largement developpes
- certaines routes annoncees dans la documentation ne sont pas encore toutes branchees dans le routage actif

Autrement dit, le projet est solide dans sa conception, avec une base technique deja riche. La partie authentification est aujourd'hui plus mature, tandis que certaines briques cybersécurité visibles dans la documentation semblent encore en phase d'integration finale.

## 8. Forces Du Projet

- theme moderne et pertinent
- bonne coherence entre securite, supervision et demonstration
- architecture modulaire
- integration de plusieurs mecanismes de defense
- authentification OTP desormais plus robuste et defendable a l'oral
- presence d'une base de tests Laravel de nouveau operationnelle
- presence d'un intranet realiste pour contextualiser les tests
- documentation UML deja disponible

## 9. Limites Ou Points D'amelioration

- aligner completement les routes actives avec les fonctionnalites decrites dans la documentation
- corriger le dernier test en erreur pour obtenir une suite totalement verte
- consolider certaines integrations pour la partie cybersécurité visible dans l'interface
- renforcer encore la preparation a une mise en production reelle

## 10. Conclusion

CyberGuard est un projet pertinent de soutenance, car il ne se limite pas a une simple application CRUD. Il propose une vision plus large de la securite applicative en combinant:

- prevention
- detection
- surveillance
- reaction
- traçabilite

Le projet montre a la fois des competences en developpement web, en architecture logicielle et en securite informatique.

## 11. Phrase Courte Pour Presentation Orale

CyberGuard est une plateforme Laravel de supervision et de defense en cybersécurité qui combine authentification renforcee, detection d'attaques, honeypot et intranet de demonstration dans une architecture modulaire et pedagogique.
