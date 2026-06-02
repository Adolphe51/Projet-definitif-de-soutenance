## Version Affinee A Partir Du Memoire

Cette section reprend la logique exacte du memoire :

- contexte
- problematique
- objectifs
- architecture modulaire
- application metier
- authentification OTP
- journalisation
- detection
- alertes
- incidents
- tests
- limites
- perspectives

Elle est prevue pour une soutenance avec support PowerPoint et demonstration.

## Texte Slide Par Slide

### Slide 1 - Titre

#### Titre a mettre

`Conception d une plateforme pedagogique de cybersécurité pour la simulation et la detection des cyberattaques dans une application web`

#### Sous-titre

`CyberGuard`

#### Contenu

- Realise par : `FATOMBI Olivier` et `GUERE Adolphe`
- Filiere : `Informatique`
- Option : `Securite des Systemes et Reseau Informatique`
- Supervision : `Dr Issifou Bata IMOROU`
- Stage : `COSIT Benin`

### Slide 2 - Contexte

#### Titre

`Contexte de l etude`

#### Texte conseille

- Les applications web occupent une place centrale dans les organisations.
- Elles manipulent des donnees sensibles et soutiennent des traitements critiques.
- Cette evolution augmente la surface d exposition aux cybermenaces.
- Dans un cadre academique, les notions de securite restent souvent trop theoriques.

### Slide 3 - Problematique

#### Titre

`Problematique`

#### Texte conseille

`Comment concevoir et implementer une plateforme pedagogique de cybersécurité capable de simuler, detecter et visualiser des cyberattaques dans une application web, sans trahir les contraintes de simplicite, de lisibilite et d usage academique ?`

### Slide 4 - Objectifs

#### Titre

`Objectifs du projet`

#### Texte conseille

`Objectif general`

Concevoir et developper une plateforme pedagogique de cybersécurité permettant de simuler, detecter et superviser certaines cyberattaques dans une application web.

`Objectifs specifiques`

- mettre en place une authentification renforcee
- enregistrer et exploiter les actions sensibles
- detecter plusieurs scenarios d attaque web cibles
- centraliser les alertes et incidents
- offrir un support de demonstration via une application metier

### Slide 5 - Justification

#### Titre

`Pourquoi CyberGuard ?`

#### Texte conseille

- besoin d un environnement de demonstration controle
- besoin d une solution plus lisible qu un SIEM industriel
- besoin de relier action utilisateur, detection et supervision
- besoin d un outil pedagogique simple, stable et reproductible

### Slide 6 - Cibles Et Besoins

#### Titre

`Cibles et besoins identifies`

#### Texte conseille

`Public cible`

- enseignants
- etudiants
- demonstrateurs
- structures souhaitant un prototype de sensibilisation

`Besoins essentiels`

- surveillance continue
- alertes simples et exploitables
- tracabilite des actions
- simulation maitrisee
- simplicite de deploiement et cout reduit

### Slide 7 - Etude De L Existant

#### Titre

`Etude de l existant`

#### Texte conseille

Solutions observees :

- `IBM QRadar`
- `ELK Stack`
- `Wazuh`

Constat :

- solutions puissantes
- bonne centralisation des logs
- bonnes capacites de detection et visualisation
- mais trop complexes pour une demonstration academique simple

### Slide 8 - Solution Proposee

#### Titre

`Solution proposee : CyberGuard`

#### Texte conseille

CyberGuard est une plateforme pedagogique de cybersécurité qui permet de :

- securiser l acces
- journaliser les actions
- detecter des comportements suspects
- generer des alertes
- assister l analyse d incidents
- simuler des attaques dans un environnement controle

### Slide 9 - Architecture Generale

#### Titre

`Architecture globale du systeme`

#### Texte conseille

CyberGuard repose sur une organisation modulaire :

- interface web
- authentification renforcee
- application metier
- journalisation
- moteur de detection
- supervision
- honeypot secondaire

`Flux general`

Action -> Controle d acces -> Journalisation -> Detection -> Alerte -> Incident -> Supervision

### Slide 10 - Innovations Apportees

#### Titre

`Innovations apportees par CyberGuard`

#### Texte conseille

- detection contextualisee
- supervision pedagogique
- visualisation claire des incidents
- automatisation partielle des reactions
- articulation entre application metier et securite

### Slide 11 - Fonctionnalites Principales

#### Titre

`Fonctionnalites principales`

#### Texte conseille

- authentification securisee avec OTP
- gestion d une application metier de demonstration
- journalisation des actions utilisateurs
- detection de `Brute Force`, `SQL Injection` et `XSS`
- tableau de bord de supervision
- centre d alertes
- gestion des incidents
- simulation d attaques

### Slide 12 - Environnement Technique

#### Titre

`Environnement technique`

#### Texte conseille

- systeme de developpement : `Linux / Ubuntu`
- `PHP 8.2`
- `Laravel 12`
- `Composer`
- `Node.js`
- `Vite`
- `SQLite` en developpement
- `MySQL` possible pour un contexte plus proche de la production

#### Message a faire passer

Le projet reste volontairement simple, leger et compatible avec un environnement local de demonstration.

### Slide 13 - Interface Utilisateur

#### Titre

`Interfaces principales`

#### Texte conseille

- page de connexion email + mot de passe
- page OTP
- tableau de bord de supervision
- centre d alertes
- gestion des incidents
- application metier : dashboard, usagers, services, messages

Tu peux ici utiliser les figures du memoire.

### Slide 14 - Scenario De Demonstration

#### Titre

`Scenario de demonstration`

#### Texte conseille

1. connexion administrateur
2. verification OTP
3. action sur l application metier
4. generation d un evenement
5. journalisation
6. detection d un comportement suspect
7. creation d une alerte
8. consultation de l incident
9. analyse ou blocage

### Slide 15 - Tests Et Validation

#### Titre

`Tests et validation`

#### Texte conseille

La validation repose sur deux niveaux :

- verifications fonctionnelles
- tests automatises Laravel / PHPUnit

Scenarios verifies :

- authentification avec OTP
- limitation de debit
- protection des routes
- controle d acces par role
- securite des sessions
- auto-blocage d IP
- detection et generation d alertes
- fonctionnement de l application metier

### Slide 16 - Resultats Obtenus

#### Titre

`Resultats obtenus`

#### Texte conseille

- authentification renforcee fonctionnelle
- journalisation coherente des actions
- detection efficace dans le perimetre choisi
- alertes lisibles et exploitables
- incidents consultables dans une chaine de supervision unifiee
- plateforme pedagogique stable et demonstrative

### Slide 17 - Limites

#### Titre

`Limites du projet`

#### Texte conseille

- perimetre limite a `Brute Force`, `SQL Injection` et `XSS`
- environnement local et demonstratif
- choix faits en faveur de la lisibilite pedagogique
- pas une plateforme SOC de production a grande echelle

### Slide 18 - Perspectives

#### Titre

`Perspectives`

#### Texte conseille

- integration de nouveaux scenarios d attaque
- extension du moteur de regles
- meilleure correlation des evenements
- detection comportementale plus avancee
- usage possible de l intelligence artificielle
- deploiement sur une architecture plus distribuee

### Slide 19 - Conclusion

#### Titre

`Conclusion`

#### Texte conseille

CyberGuard montre qu il est possible de concevoir une plateforme pedagogique coherente qui relie :

- controle d acces
- audit
- detection
- alertes
- incidents
- supervision

Le projet constitue a la fois un support de demonstration, un outil d apprentissage et une base de reflexion pour de futurs travaux en cybersécurité applicative.

### Slide 20 - Questions

#### Texte conseille

`Merci pour votre attention`

`Questions / Observations`

---

## Script Oral De Soutenance

Le script ci-dessous est prevu pour une soutenance d environ `8 a 12 minutes`.
Tu peux l adapter selon le temps disponible.

### Slide 1 - Titre

Bonjour Mesdames et Messieurs les membres du jury.
Nous allons vous presenter notre travail intitule : conception d une plateforme pedagogique de cybersécurité pour la simulation et la detection des cyberattaques dans une application web.
La solution que nous avons realisee s appelle CyberGuard.

### Slide 2 - Contexte

Le contexte de notre travail est celui de la multiplication des applications web dans les organisations.
Ces applications manipulent des donnees sensibles, assurent des traitements critiques et deviennent donc des cibles naturelles pour plusieurs types d attaques.
Dans le meme temps, l apprentissage de la cybersécurité reste souvent trop theorique.
Il existe donc un besoin d outil pedagogique capable de montrer concretement comment une application peut etre surveillee, comment des comportements suspects peuvent etre detectes et comment les incidents peuvent etre visualises.

### Slide 3 - Problematique

Notre problematique a ete la suivante :
comment concevoir et implementer une plateforme pedagogique de cybersécurité capable de simuler, detecter et visualiser des cyberattaques dans une application web, tout en respectant des contraintes de simplicite, de lisibilite et d usage academique.

### Slide 4 - Objectifs

L objectif general du projet a ete de concevoir et developper une plateforme pedagogique permettant de simuler, detecter et superviser certaines cyberattaques dans une application web.
De facon specifique, nous voulions securiser l acces au systeme, journaliser les actions sensibles, detecter plusieurs scenarios d attaque, centraliser les alertes et fournir une application metier servant de support de demonstration.

### Slide 5 - Justification

La justification du projet vient du fait que les solutions industrielles existantes sont souvent puissantes, mais parfois trop lourdes ou trop complexes pour une demonstration academique.
Nous avions donc besoin d une solution intermediaire, suffisamment realiste pour produire des evenements exploitables, mais suffisamment simple pour etre comprise par des etudiants et des encadreurs.

### Slide 6 - Cibles Et Besoins

CyberGuard s adresse prioritairement aux enseignants, aux etudiants et aux demonstrateurs.
Les besoins identifies etaient la surveillance continue, des alertes simples et exploitables, la tracabilite, la simulation controlee, et un deploiement local a cout reduit.

### Slide 7 - Etude De L Existant

Dans l etude de l existant, nous avons observe des solutions comme IBM QRadar, ELK Stack et Wazuh.
Ces outils offrent une bonne centralisation des logs, des capacites de detection et de visualisation.
Mais dans notre cadre, ils restent souvent trop complexes pour une demonstration academique simple.
Cette analyse a renforce le choix de concevoir une plateforme pedagogique adaptee a notre besoin.

### Slide 8 - Solution Proposee

La solution proposee est CyberGuard.
Il s agit d une plateforme web pedagogique qui combine securisation de l acces, application metier, journalisation, detection, alertes, incidents et supervision.
L idee centrale est de relier une action ou un evenement a une chaine complete de traitement visible par l administrateur.

### Slide 9 - Architecture Generale

Sur le plan architectural, CyberGuard repose sur plusieurs blocs modulaires :
une interface web, une authentification renforcee, une application metier, un systeme de journalisation, un moteur de detection, un module de supervision et un honeypot secondaire.
Le flux commence par une action utilisateur ou un acteur simule.
Cette action passe par les controles d acces, elle est journalisee, analysee, puis peut produire une alerte et un incident visible dans l interface d administration.

### Slide 10 - Innovations Apportees

L innovation principale de CyberGuard est qu il ne cherche pas seulement a proteger une application, mais aussi a rendre les mecanismes de securite visibles et compréhensibles.
Nous avons donc privilegie une detection contextualisee, une supervision pedagogique et une automatisation partielle, afin de garder un bon equilibre entre demonstration et coherence technique.

### Slide 11 - Fonctionnalites Principales

Les fonctionnalites principales du systeme sont :
l authentification OTP, l application metier, la journalisation, la detection de Brute Force, SQL Injection et XSS, le tableau de bord de supervision, le centre d alertes, la gestion des incidents et la simulation d attaques.

### Slide 12 - Environnement Technique

Pour l implementation, nous avons utilise un environnement local de type Linux ou Ubuntu, avec PHP 8.2, Laravel 12, Composer, Node.js, Vite et une base SQLite en developpement, avec possibilite d utiliser MySQL dans un cadre plus proche de la production.
Ce choix reste coherent avec les objectifs pedagogiques du projet : simplicite, cout reduit et rapidite de mise en oeuvre.

### Slide 13 - Interface Utilisateur

Au niveau des interfaces, nous avons mis en place plusieurs vues importantes :
la connexion par identifiants, la verification OTP, le tableau de bord, le centre d alertes, la gestion des incidents et l application metier.
L application metier n est pas decorative : elle produit de vraies actions exploitables par les mecanismes d audit et de detection.

### Slide 14 - Scenario De Demonstration

Le scenario de demonstration que nous avons retenu suit une logique simple.
L administrateur se connecte, valide son OTP, puis une action est realisee dans l application metier ou une simulation est lancee.
Cette action produit un evenement, cet evenement est journalise, le moteur de detection l analyse, puis une alerte est creee et un incident devient consultable dans la supervision.
Cette chaine permet de voir concretement le fonctionnement global de CyberGuard.

### Slide 15 - Tests Et Validation

La validation du projet repose sur deux niveaux complementaires :
des tests fonctionnels, observables pendant la demonstration, et une suite de tests automatises avec Laravel et PHPUnit.
Les tests ont notamment porte sur l authentification OTP, le controle d acces, la limitation de debit, la securite des sessions, le blocage automatique d IP, la detection des attaques et le bon fonctionnement de l application metier.

### Slide 16 - Resultats Obtenus

Les resultats montrent que CyberGuard remplit bien son role.
L authentification renforcee fonctionne, les actions sont journalisees, le moteur de detection est pertinent dans le perimetre choisi, les alertes sont lisibles, et les incidents s intègrent dans une chaine de supervision unifiee.
Autrement dit, la plateforme permet de relier authentification, activite metier, detection et supervision dans un cadre pedagogique maitrise.

### Slide 17 - Limites

Malgre ces resultats, certaines limites doivent etre reconnues.
Le projet reste volontairement centre sur trois types d attaques : Brute Force, SQL Injection et XSS.
Il est deploye dans un environnement local et demonstratif.
Enfin, plusieurs choix ont ete faits pour favoriser la lisibilite pedagogique plutot qu une industrialisation complete.

### Slide 18 - Perspectives

Comme perspectives, nous envisageons l integration de nouveaux scenarios d attaque, l extension du moteur de regles, une meilleure correlation des evenements, l ajout de techniques de detection plus avancees, y compris basees sur l analyse comportementale ou l intelligence artificielle, ainsi qu un deploiement sur une architecture plus distribuee.

### Slide 19 - Conclusion

Pour conclure, CyberGuard constitue une plateforme pedagogique coherente qui articule authentification, audit, detection, alertes, incidents et supervision.
Le projet apporte une contribution utile dans un cadre academique, a la fois comme support de demonstration, outil d apprentissage et base de reflexion pour des travaux futurs en cybersécurité applicative.

### Slide 20 - Questions

Je vous remercie pour votre attention.
Nous sommes maintenant disponibles pour vos questions et observations.

---

## Conseils Pour La Soutenance

### Si tu presentes seul

Tu peux parler en `8 a 10 minutes` en accelerant legerement sur :

- l etude de l existant
- la liste detaillee des technologies

et en mettant plus l accent sur :

- le probleme
- l architecture
- la demonstration
- les resultats

### Si vous presentez a deux

Repartition possible :

- `Personne 1`
  slides 1 a 9
- `Personne 2`
  slides 10 a 20

Ou bien :

- `Personne 1`
  contexte, problematique, objectifs, etude de l existant
- `Personne 2`
  solution, architecture, demonstration, tests, conclusion

### Questions probables du jury

Prevois des reponses sur :

- pourquoi avoir choisi seulement `Brute Force`, `SQL Injection` et `XSS`
- en quoi CyberGuard se distingue d un SIEM classique
- pourquoi utiliser une application metier integree
- quelles sont les limites d un environnement local
- comment evoluer vers une solution plus proche de la production

### Reponse courte type

`Nous avons volontairement limite le perimetre pour garder une plateforme pedagogique lisible, stable et demonstrative. L objectif etait moins de couvrir toutes les menaces que de montrer clairement la chaine complete : action, journalisation, detection, alerte, incident et supervision.`
