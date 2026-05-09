# Guide De Presentation UML - Soutenance CyberGuard

Date : 2026-05-04

## Objectif

Ce guide sert a presenter les UML en restant strictement alignes avec le code actuel :

- authentification `email + mot de passe + OTP`
- mini-site metier protege par session securisee
- audit des actions
- detection `Brute Force`, `SQL Injection`, `XSS`
- supervision SOC
- honeypot secondaire

## Deux messages a garder constants

1. Le mini-site n'est pas public : toutes les actions `/intranet/*` exigent une session authentifiee valide.
2. Le mini-site sert de surface metier protegee pour produire des actions auditees, des detections ciblees et des alertes explicables.

## Ordre recommande

1. Cas d'utilisation admin / systeme
2. Cas d'utilisation mini-site
3. Diagramme de composants
4. Diagramme de classes
5. Diagramme de deploiement
6. Sequence d'authentification
7. Sequence de controle d'acces
8. Sequence generale
9. Diagramme d'etat de l'attaque
10. Diagramme d'activite incident SOC
11. Sequence honeypot

## Cas d'utilisation admin / systeme

Fichier :

- `public/UML/diagram-cas-utilisation/diagram-admin-systeme.puml`

A dire :

- l'administrateur passe par l'authentification OTP
- le dashboard, les alertes, les incidents et les simulations sont reserves au role `admin`
- le mini-site metier produit les donnees qui alimentent ensuite l'analyse SOC

## Cas d'utilisation mini-site

Fichier :

- `public/UML/diagram-cas-utilisation/diagram-user-intranet.puml`

A dire :

- l'utilisateur doit d'abord s'authentifier
- toute consultation ou modification du mini-site inclut cette session securisee
- les actions peuvent etre auditees puis detectees si le contenu est suspect

## Diagramme de classes

Fichier :

- `public/UML/diagram-class.puml`

A dire :

- `User`, `SecuritySession`, `AuthCode`, `UserRole`, `Permission` couvrent l'acces
- `Usager`, `ServiceMetier`, `RattachementService`, `MessageInterne` couvrent le mini-site
- `Attack`, `Alert`, `BlockedIp`, `AttackComment`, `Simulation`, `DetectionRule` couvrent le SOC
- le diagramme ne conserve plus d'objets morts ou secondaires qui n'apportent rien au parcours de demonstration

## Diagramme de deploiement

Fichier :

- `public/UML/diagram-deployment.puml`

A dire :

- la cible propre est un VPS `Nginx + PHP-FPM + Laravel`
- HTTPS, cron scheduler, base MySQL et mail OTP font partie du dispositif
- le honeypot reste visible mais se limite a quelques routes pieges publiques

## Selection minimale si le temps est court

1. `diagram-cas-utilisation/diagram-admin-systeme.puml`
2. `diagram-cas-utilisation/diagram-user-intranet.puml`
3. `diagram-class.puml`
4. `diagram-deployment.puml`
5. `sequence-authentification.puml`
6. `sequence-generale.puml`

## Message cle

CyberGuard doit etre presente comme un systeme coherent :

- entree securisee
- surface metier protegee
- audit
- detection
- supervision
- analyse et reponse
