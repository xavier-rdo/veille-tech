#Ubuntu/Debian

![Logo Ubuntu](img/ubuntu-logo.png)
![Logo Debian](img/debian-logo.png)

* [Gestion des paquets](#paquets)
* [Dossiers et fichiers](#files)
* [Mémo Vim](#vim)
* [Commandes utiles](#tips)

##<a name="paquets"></a>Gestion des paquets

Réf. : http://doc.ubuntu-fr.org/apt-get

```
-- Rechercher un paquet installé
$ dpkg -l | grep postgresql

-- Rechercher un paquet
$ sudo apt-cache search postgresql

-- Détails d'un paquet
$ sudo apt-cache show postgresql-server-9.4

-- Supprimer un paquet + ses fichiers de configuration
$ sudo apt-get purge postgresql-server-9.4

-- Suppression des paquets d'installation
$ sudo apt-get clean
```

##<a name="files"></a>Dossiers et fichiers

```
-- Rechercher un fichier :
$ sudo find /home/xavier -name mon_fichier -print
$ locate mon_fichier

-- Actualiser la base de données d'index des fichiers
$ sudo updatedb

-- Compresser (zipper) un répertoire/fichier avec tar ('tape archiver') :
$ tar -zcvf votre_archive.tar.gz votre_dossier/

-- Décompresser :
$ tar -zxvf votre_archive.tar.gz

-- x : extrait
-- c : crée l'archive
-- f : utilise le fichier passé en paramètre
-- v : mode verbeux
-- z : compression Gzip
```

##<a name="vim"></a>Mémo Vim

###Navigation

|Commande |Description         |
|---------|--------------------|
|`CTL`+`B`|Page up             |
|`CTL`+`F`|Page down           |
|nG       |Atteindre la ligne n|
|G        |Fin de fichier      |
|g        |Début de fichier    |
|^        |Début de ligne      |
|$        |Fin de ligne        |
|{        |Début de paragraphe |
|}        |Fin de paragraphe   |
|e ou E   |Fin du mot courant  |
|b ou B   |Mot précédent       |
|w ou W   |Mot suivant         |

###Actions

|Commande |Description                  |
|---------|-----------------------------|
|dw       |Supprimer un mot             |
|dd       |Supprimer une ligne          |
|.        |Répéter la dernière action   |
|5.       |Répéter 5x la dernière action|

##<a name="tips"></a>Commandes utiles

###Configuration matérielle et logicielle (`uname`)

```
-- Version du noyau Linux (ex: 3.13.0-71-generic)
$ uname -r
-- Type du processeur (ex: x86_64)
$ uname -p
-- Plate-forme matérielle (ex: x86_64)
$ uname -i
```

###Connaître la distribution (`lsb_release`)

```
-- LSB : Linux Standard Base / Informations sur la distribution Linux
$ lsb_release --all
-- Description:  Ubuntu 14.04.3 LTS
-- Release:    14.04
-- Codename:   trusty
```
