.PHONY: help

APP_SERVER_PORT := $(or ${X},${X},4000)
APP_CLIENT_PORT := $(or ${X},${X},3000)

# Inspired by https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
help:
	@grep -E '^[@a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

services-start: ## Start React client & GraphQL server services (containers)
	docker-compose up

services-sh@server: ## Open shell in server container
	docker-compose exec server /bin/sh

services-sh@client: ## Open shell in client container
	docker-compose exec client /bin/sh

services-list:  ## List running services (containers)
	docker-compose ps

browse@server: ## Open GraphQL Playground in browser - Default port 4000 (Usage for another port: make browse@server APP_SERVER_PORT=4001)
	open http://localhost:${APP_SERVER_PORT}

browse@client: ## Open React client app in browser - Default port 3000 (Usage for another port: make browse@client APP_CLIENT_PORT=3001)
	open http://localhost:${APP_CLIENT_PORT}
