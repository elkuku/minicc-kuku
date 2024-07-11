### Docker
#### Backup
    docker exec CONTAINER /usr/bin/mysqldump -u main -pmain main > backup.sql

#### Restore
    cat backup.sql | docker exec -i CONTAINER /usr/bin/mysql -u main -pmain main
