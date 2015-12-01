#Docker

##Références :

* Site officiel : [https://www.docker.com/](https://www.docker.com/)
* Documentation : [https://docs.docker.com/](https://docs.docker.com/)
* Dépôts (images) : [Docker Hub](https://hub.docker.com/)
* Images officielles : [https://github.com/docker-library/docs](https://github.com/docker-library/docs)

#Installation sur Ubuntu 14.04 (LTS)

Réf. : [https://docs.docker.com/engine/installation/ubuntulinux/](https://docs.docker.com/engine/installation/ubuntulinux/)

```
$ # Ajouter une nouvelle clé GPG :
$ $ sudo apt-key adv --keyserver hkp://p80.pool.sks-keyservers.net:80 --recv-keys 58118E89F3A912897C070ADBF76221572C52609D

$ sudo touch /etc/apt/sources.list.d/docker.list
$ sudo echo "deb https://apt.dockerproject.org/repo ubuntu-trusty main" >> /etc/apt/sources.list.d/docker.list

$ sudo apt-get update
$ #Purger l'ancien dépôt :
$ sudo apt-get purge lxc-docker
$ # Conseillé : ajouter linux-image-extra (pour pouvoir utiliser le syst. AUFS) :
$ sudo apt-get install linux-image-extra-$(uname -r)
$ sudo apt-get install docker-engine
```
