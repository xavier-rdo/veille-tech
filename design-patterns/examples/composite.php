<?php

/**
 * Exemple d'implémentation du design pattern Composite en PHP
 * (structure arborescente constituée de composants, branches ou feuilles)
 *
 * Elle modélise l'organigramme d'une entreprise (services et employés).
 * Les services incluent d'autres services et/ou des employés.
 * Les employés possèdent des compétences (skills). Les compétences d'un
 * service sont constituées de la somme des compétences des sous-services
 * qui le composent et/ou de ses employés.
 */

/**
 * Interface implémentée par tous les composants de la structure arborescente,
 * que ce soit une branche ou une feuille.
 * Correspond à "Component" dans le diagramme des participants du pattern Composite.
 * Il s'agit donc du comportement exposé par tous les éléments de l'arborescence, quel
 * que soit leur situation dans la hiérarchie ou leur nature (feuille, branche) : chaque
 * élement possède un ensemble de compétences.
 */
interface Skillable
{
    public function getSkills();
}

/**
 * Interface implémentée par tous les composites de la structure arborescente.
 * Elle correspond à "Composite" dans le diagramme des participants du pattern Composite.
 * OUtre le fait qu'elle étend Skillable, elle contient toutes les
 * méthodes de gestion de ses noeuds/enfants (add, remove, etc.).
 */
interface SkillableComposite extends Skillable
{
    public function add(Skillable $skillable);

    public function remove(Skillable $killable);
}

/**
 * Classe représentant la plus petite unité de notre arborescence (les feuilles).
 * Elle implémente donc uniquement l'interface Skillable (et non pas CompositeSkillable).
 */
class Employee implements Skillable
{
    private $name;
    private $skills = [];

    public function getSkills()
    {
        return $this->skills;
    }

    // Autres méthodes nécessaires ou utiles sans lien direct avec le pattern Composite :
    public function __construct($name) { $this->name = $name; }
    public function __toString() { return $this->name; }
    public function setSkills(array $skills = []) { $this->skills = $skills; }
    public function getName() { return $this->name; }
    public function addSkills(array $skills = []) { }
    public function removeSkill($skill) { }
}

/**
 * Classe modélisant un composite (elle implémente donc SkillableComposite)
 */
class Department implements SkillableComposite
{
    private $name;
    private $children = [];

    public function add(Skillable $skillable)
    {
        $this->children[] = $skillable;
    }

    public function remove(Skillable $skillable) {
        // To be implemented
    }

    /**
     * La méthode principale à implémenter dans le cadre du pattern Composite :
     *
     * @return array
     */
    public function getSkills()
    {
        $skills = [];
        foreach ($this->children as $skillable) {
            $skills = array_unique(
                array_merge(
                    $skills,
                    $skillable->getSkills()
                )
            );
        }

        return $skills;
    }

    // Autres méthodes nécessaires et/ou utiles sans lien direct avec le pattern Composite :
    public function __construct($name) { $this->name = $name; }
    public function __toString() { return $this->name; }
    public function getName() { return $this->name; }
}

// Instantiation de trois employés possédant des compétences :
$benjamin = new Employee('Benjamin');
$benjamin->setSkills(['PHP', 'Java']);
echo "Compétences de $benjamin :\n";
print_r($benjamin->getSkills());

$camille = new Employee('Camille');
$camille->setSkills(['CSS', 'Illustrator','Javascript']);
echo "Compétences de $camille :\n";
print_r($camille->getSkills());

$thomas = new Employee('Thomas');
$thomas->setSkills(['Javascript', 'PHP', 'Node.js']);
echo "Compétences de $thomas :\n";
print_r($thomas->getSkills());

// Instatiation de trois services :
$rd    = new Department("R&d");
$front = new Department("R&d / Frontend");
$back  = new Department("R&d / Backend");

// Distribution des compétences :
$front->add($camille);
$front->add($thomas);
$back->add($benjamin);
$rd->add($front);
$rd->add($back);

echo "Compétences du service '$front' :\n";
print_r($front->getSkills());
echo "Compétences du service '$back' :\n";
print_r($back->getSkills());
echo "Compétences du service '$rd' : \n";
print_r($rd->getSkills());
