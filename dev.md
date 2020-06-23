### Heroku
#### wkhtmltopdf
    heroku buildpacks:add https://github.com/dscout/wkhtmltopdf-buildpack.git
    heroku config:set WKHTMLTOPDF_VERSION="0.12.4"

### Docker
#### Backup
    docker exec CONTAINER /usr/bin/mysqldump -u main --password=main main > backup.sql

#### Restore
    cat backup.sql | docker exec -i CONTAINER /usr/bin/mysql -u main --password=main main
