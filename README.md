# Altech Omega Backend Test

## Running

### Initialization
- Change database url in `.env`

```
composer install

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Testing
```
# On first terminal
symfony server:start

# On second terminal
php bin/phpunit
```

----------

### Optimalization

#### Database
On Database side, at minimum we add Indexing into frequent used Table as example
we index Book table by id, title, and author_id
Author table by id, name

next step we will do logical partition, 
Book table can be partitioned by either author_id, publish_date or both
Author table can be partitioned by either id, birth_date or both

if frequent search needed, adding special "search engine" like elasticsearch will do.

#### Cache
On cache side, minimal http cache are handled by [symfony](https://symfony.com/doc/current/http_cache.html)
enable PHP OPCache also helping store precompiled script bytecode in shared memory