FROM node:lts-alpine

RUN mkdir -p /srv/app/graphql-react-example-client
WORKDIR /srv/app/graphql-react-example-client

COPY package.json /srv/app/graphql-react-example-client
COPY package-lock.json /srv/app/graphql-react-example-client

RUN npm install

COPY . /srv/app/graphql-react-example-client

CMD ["npm", "start"]