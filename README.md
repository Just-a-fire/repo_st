Для получения информации по репозиторию используется Github API.

Т.к. для сбора статистики нужно делать несколько запросов в Github API, то в случае объёмного репозитория лучше использовать
access_token для увеличения лимита
https://developer.github.com/v3/#rate-limiting

Если у Вас нет своего токена, его можно сгенерировать
https://help.github.com/articles/creating-an-access-token-for-command-line-use/
