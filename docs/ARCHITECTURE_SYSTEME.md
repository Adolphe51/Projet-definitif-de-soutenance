# Architecture Systeme CyberGuard

## Objectif

Ce document fige la lecture correcte du systeme pour eviter les incoherences entre code, UML et documentation.

## 1. Perimetre reel du projet

CyberGuard est un monolithe Laravel qui combine :

- une authentification forte par OTP
- un mini-site metier protege
- un noyau SOC de supervision et de traitement d'incidents
- un honeypot secondaire
- des commandes Artisan de simulation et de maintenance

## 2. Domaine fonctionnel du mini-site

Le mini-site metier manipule uniquement les objets utiles au parcours principal :

- `Usager`
- `Service metier`
- `Rattachement d'acces a un service`
- `Message interne`

Les anciens objets secondaires qui n'alimentaient plus ni l'interface, ni l'audit, ni la detection ont ete retires du perimetre utile.

## 3. Controle d'acces

Routes admin :

- prefixes `/dashboard`, `/alerts`, `/attacks`, `/geo`, `/simulations`, `/honeypot`
- middleware `secure`
- middleware `role:admin`

Routes mini-site :

- prefixe `/intranet`
- middlewares `csrf`, `blocked.ip`, `session.security`, `ip.authorized`, `audit`

Routes pieges publiques :

- `/admin`
- `/phpmyadmin`

Ces deux routes sont publiques par design parce qu'elles servent de honeypot.

## 4. Acteurs

- `Administrateur` : consulte et traite les incidents SOC
- `Utilisateur metier` : travaille sur le mini-site apres authentification
- `Attaquant` : interagit avec les routes pieges ou provoque un evenement detectable

## 5. Flux principal

1. L'utilisateur soumet email et mot de passe.
2. Le systeme envoie un OTP.
3. L'utilisateur valide l'OTP.
4. CyberGuard cree une session securisee et pose le cookie `access_token`.
5. L'utilisateur accede au mini-site ou, s'il est `admin`, au dashboard.
6. Une action metier peut etre auditee.
7. Si le contenu ou le comportement est suspect, une attaque et une alerte peuvent etre creees.
8. L'administrateur qualifie ensuite l'incident dans l'interface SOC.

## 6. Scenarios de detection assumes

Les scenarios privilegies par le systeme et la documentation sont :

- `Brute Force`
- `SQL Injection`
- `XSS`

## 7. UML de reference

Les sources a considerer comme reference sont :

- `public/UML/diagram-class.puml`
- `public/UML/diagram-deployment.puml`
- `public/UML/diagram-cas-utilisation/diagram-admin-systeme.puml`
- `public/UML/diagram-cas-utilisation/diagram-user-intranet.puml`

## 8. Ce qu'il ne faut plus presenter

Pour rester coherent, il vaut mieux ne plus presenter le projet comme :

- un intranet academique complet
- un systeme ou le mini-site est public
- une plateforme qui genere automatiquement des attaques pour animer l'interface
