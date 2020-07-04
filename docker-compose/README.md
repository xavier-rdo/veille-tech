# Docker compose

Référence : https://docs.docker.com/compose/

## Exemple avec un serveur GraphQL Apollo et un client React/Apollo

Cet exemple applique `docker-compose` à l'app fullstack qui sert d'exemple dans le tutoriel officiel d'Apollo :

* Tutoriel en ligne : https://www.apollographql.com/docs/tutorial/introduction/
* Code source du tutoriel : https://github.com/apollographql/fullstack-tutorial

Cette application full stack inclut (notamment) :

* `apollo-server` côté serveur (répertoire `server`)
* `apollo-client` + `react` côté client (répertoire `client`)

⚠️ Le code source de l'application Apollo n'est pas embarqué ici. Seuls les fichiers liés à la Dockerisation sont présents.

Concernant l'intégration de `docker-compose`, cet exemple s'inspire de l'article paru sur medium.com intitulé ["Develop in Docker: a Node backend and a React front-end talking to each other"](https://medium.com/@xiaolishen/develop-in-docker-a-node-backend-and-a-react-front-end-talking-to-each-other-5c522156f634).

Rappels :

* Apollo server tourne sur le port 4000 (`GraphQL Playground`)
* Le client (`create-react-app`) tourne sur le port 3000

### Dockerisation de l'application

L'application intègre deux containers Docker :

- un container pour le serveur, cf. son [Dockerfile](apollo-server-react/server/Dockerfile)
- un container pour le serveur de dév. côté client, cf. son [Dockerfile](apollo-server-react/client/Dockerfile)

Noter que l'installation des dépendances JS se fait lorsque les containers sont démarrés (cf. fichiers `Dockerfile` côté `server` et côté `client`) : `RUN npm install`.

L'orchestration de ces deux containers se fait à l'aide du fichier [`docker-compose.yml`](apollo-server-react/docker-compose.yml). Les variables d'environnement référencées dans le fichier `docker-compose.yml` sont définies dans le fichier [.env](apollo-server-react/.env).

Enfin, un fichier [Makefile](apollo-server-react/Makefile) expose les commandes les plus utiles lors du développement (démarrage des containers, liste des containers, ouvrir un shell dans le container `client`, ouvrir un shell dans le container `server`, etc.). Exemple: démarrer les containers ==> `make services-start`.

💡 Obtenir la liste des commandes Make disponibles : `make help`

### Timestamps

- Date de mise à jour: juillet 2020
- Version de Docker compose: 1.25.5
- Version de React: 6.9.0-alpha.0
- Version d'Apollo Server: 2.6.1
