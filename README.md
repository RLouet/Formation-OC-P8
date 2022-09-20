Phan : ```php vendor/bin/phan --allow-polyfill-parser```

Php CS Fixer : ```composer phpcsfixer```  

#PhpUnit
## unitary
tests : ```php bin/phpunit```

coverage html : ```php bin/phpunit --coverage-html tests/code-coverage```

coverage text : ```php bin/phpunit --coverage-text```

## functional
save response html : ```php bin/phpunit > public/test.html ```

Run tests :
```
symfony console doctrine:database:drop --force --env=test
symfony console doctrine:database:create --env=test
symfony console doctrine:migrations:migrate -n --env=test
symfony console doctrine:fixtures:load -n --env=test
php bin/phpunit
```