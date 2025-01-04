## Установка
```
sh install.sh # запросит sudo
```

## Использование
http://localhost

### Подключение xdebug в PHPStorm
1.
* `Menu | Settings | PHP`
* `Cli Interpreter | ...`
* `+` | `From Docker, Vagrant, VM, WSL, Remote`
* `Docker Compose`
    * Server: `New... | OK`
    * Configuration files: `./docker-compose.yml`
    * Service: `phpdoc`
    * `OK`
        * `Connect to existing container ('docker-compose exec')`
        * `OK`

2.
* `PHP | Servers`
* `+`
    * Name: `xdebug-server`
    * Host: `localhost`
    * Port: `9001`
    * Debugger: `Xdebug`

    * `Use path mappings (select if the server is remote or symlinks are used)`
        * File/Directory: Project files -> первый (он там единственный в Project files) -> www/ -> public/
        * Absolute path on the server: `/var/www/bitrix/public`

3.
* `PHP | Debug`
* `Xdebug` | `Debug port`: (добавить через запятую `9001`)

4.
* `Run | Edit Configurations...`
* `+` | `PHP Remote Debug`
    * Name: `xdebug-server`
    * Configuration:
        * `Filter debug connection by IDE key`
        * Server: `xdebug-server`
        * IDE key(session id): `PHPSTORM`

5. Зеленый жучок сверху справа
