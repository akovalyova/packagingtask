### init
- `printf "UID=$(id -u)\nGID=$(id -g)" > .env`
- `docker-compose up -d`
- `docker-compose run shipmonk-packing-app bash`
- `composer install && vendor/bin/doctrine orm:schema-tool:create && vendor/bin/doctrine dbal:run-sql "$(cat data/packaging-data.sql)"`

### run
- `php run.php "$(cat sample.json)"`

### adminer
- Open `http://localhost:8080/?server=mysql&username=root&db=packing`
- Password: secret

### TO DO
- Add logging for api errrors
- Fix api json schema , recheck validation
- Review and fix phpstan warnings (array types, etc.)
- Save extracted response in cache (now apiresponse is saved only and then needed to be extracted again)
