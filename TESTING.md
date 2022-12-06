# Testing

The following script described the procedure to run tests inside docker container with the provided `docker-compose.yml` file
```shell
# start
docker-compose up zookeeper redis neo4j mysql solr rda-registry -d
docker-compose exec solr wait-for-solr.sh && docker-compose exec solr /bin/create-collections.sh && docker-compose exec rda-registry /bin/fix-permissions.sh
docker-compose up mycelium -d

# wait-for-mycelium.sh

# build
docker-compose exec -w /opt/apps/registry/current rda-registry composer install -o

# test
docker-compose exec -w /opt/apps/registry/current rda-registry vendor/bin/phpunit --configuration phpunit.xml --testsuite unit --coverage-clover 'test-reports/clover.xml'

# shut services down
docker-compose down

docker volume prune -f
```