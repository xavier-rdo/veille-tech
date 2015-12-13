#Composite

<blockquote>
    <strong>The composite pattern</strong> allows you to compose objects into tree structures to represent part-whole hierarchies.
    Composite lets clients treat individual obects and composition of objects uniformly.
</blockquote>

Modéliser une structure arborescente (hiérarchique) dans laquelle chaque composant (Component), implémente un (ou plusieurs) comportement(s) commun(s). En d'autres termes, l'ensemble peut être manipulé par le client de la même manière qu'une sous-partie ou chacun de ses objets individuellement.

On distingue les *branches* (ou *objets composites*) et les *feuilles*. Chaque noeud, qu'il soit branche ou feuille se nomme un composant (*component*).

Exemple : un système de fichiers :

* les répertoires représentent des branches (ou "composites")
* les fichiers représentent des feuilles
* Quel que soit le composant (répertoire ou fichier), il est possible de le renommer, le supprimer, le déplacer, etc.

Autres exemples : les bibliothèques de composants graphiques Java (Swing) qui définissent des conteneurs pouvant accueillir d'autres conteneurs (Frames, Panels, etc.), les formulaires et sous-formulaires de Symfony, la réprésentation d'un organigramme d'entreprise, les traitements de texte (sections, paragraphes, textes, etc.), des gestionnaire de tâches (et sous-tâches), etc.

<img src="images/Composite-design-pattern-1.png">

<img src="images/Composite-design-pattern-2.png">

