#Ubuntu/Debian

* [Gestion des paquets](#paquets)
* [Dossiers et fichiers](#files)
* [Configuration matérielle et logicielle](#hardware)

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
##<a name="hardware"></a>Configuration matérielle et logicielle

```
-- Version du noyau Linux (ex: 3.13.0-71-generic)
$ uname -r
-- Type du processeur (ex: x86_64)
$ uname -p
-- Plate-forme matérielle (ex: x86_64)
$ uname -i

-- LSB : Linux Standard Base / Informations sur la distribution Linux
$ lsb_release --all
-- Description:  Ubuntu 14.04.3 LTS
-- Release:    14.04
-- Codename:   trusty

```
