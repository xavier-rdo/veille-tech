#Docker

* [Installation sur Ubuntu 14.04 (LTS)](#installation)
* [Les images Docker](#images)
* [Les containers](#containers)
* [Les volumes](#volumes)

##Références :

* Site officiel : [https://www.docker.com/](https://www.docker.com/)
* Documentation : [https://docs.docker.com/](https://docs.docker.com/)
* Dépôts (images) : [Docker Hub](https://hub.docker.com/)
* Images officielles : [https://github.com/docker-library/docs](https://github.com/docker-library/docs)

##<a name="installation"></a>Installation sur Ubuntu 14.04 (LTS)

Réf. : [https://docs.docker.com/engine/installation/ubuntulinux/](https://docs.docker.com/engine/installation/ubuntulinux/)

```
$ # Ajouter une nouvelle clé GPG :
$ $ sudo apt-key adv --keyserver hkp://p80.pool.sks-keyservers.net:80 --recv-keys 58118E89F3A912897C070ADBF76221572C52609D

$ sudo touch /etc/apt/sources.list.d/docker.list
$ sudo echo "deb https://apt.dockerproject.org/repo ubuntu-trusty main" >> /etc/apt/sources.list.d/docker.list

$ sudo apt-get update
$ # Purger l'ancien dépôt :
$ sudo apt-get purge lxc-docker
$ # Conseillé : ajouter linux-image-extra (pour pouvoir utiliser le syst. AUFS) :
$ sudo apt-get install linux-image-extra-$(uname -r)
$ sudo apt-get install docker-engine
```

##<a name="images">Les images Docker

```
$ # Rechercher une image (Réf. : https://hub.docker.com/) :
$ docker search mongodb

$ # Liste des images téléchargées :
$ docker images

$ # Télécharger une image :
$ docker pull tutum/mongodb
```

##<a name="containers">Les containers

###Liste des containers

```
$ # Liste des containers en cours d'exécution :
$ docker ps
$ # Liste des containers (y compris arrêtés) :
$ docker ps --all
```

###Démarrer un container

Réf: https://docs.docker.com/engine/reference/run/

```
$ # Démarrer un container en console interactive :
$ docker run -t -i ubuntu:latest

$ # Démarrer un container en mode détaché (démon) :
$ docker run -d --name redis redis:latest  --bind 127.0.0.1
$ # Démarrer une console CLI connectée à un serveur :
$ docker run --rm -it --net container:redis --name redis-cli redis-cli -h 127.0.0.1

$ # Arrêter un container (cf. docker ps pour obtenir l'identifiant du container) :
$ docker stop container_id
```

###Mapper des ports

##<a name="volumes">Les volumes

###Créer un container de volume

###Lier un container de volume à un container exécutable

###Lier un volume du host à un container
