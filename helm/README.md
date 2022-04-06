# Деплой приложений

## Деплой site/ dev-окружение

``` shell
helm upgrade --install  -n p5s-dev atom site/ -f site/values.dev.yaml
```

## Деплой песочниц

``` shell
helm upgrade --install stripmag site/ -f site/values.dev.yaml -n $BRANCH_NS --set "ingress.hosts[0].host=$BRANCH.dev.stripmag.ru" --set 'ingress.hosts[0].paths[0].path=\'
```
