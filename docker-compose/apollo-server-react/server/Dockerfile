FROM node:lts-alpine

RUN mkdir -p /srv/app/graphql-react-example-server
WORKDIR /srv/app/graphql-react-example-server

COPY package.json /srv/app/graphql-react-example-server
COPY package-lock.json /srv/app/graphql-react-example-server
COPY store.sqlite /srv/app/graphql-react-example-server

RUN npm install

COPY . /srv/app/graphql-react-example-server

CMD ["npm", "start", "dev"]