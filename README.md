# fablab Setup Information

## Configuration
- Project name: fablab
- Database name: fablab
- Database user: ronaldo
- Database password: ronaldo
- CSS Framework: Bootstrap

## Starting the application
```bash
cd .
symfony server:start
```

## CSS Development
To watch for CSS changes and recompile automatically:
```bash
./watch-assets.sh
```

## Common Symfony commands
- Create a controller: `php bin/console make:controller`
- Create an entity: `php bin/console make:entity`
- Create a migration: `php bin/console make:migration`
- Run migrations: `php bin/console doctrine:migrations:migrate`
- Create a form: `php bin/console make:form`
- Clear cache: `php bin/console cache:clear`

## Access your application
http://localhost:8000
