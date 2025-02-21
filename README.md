# 📝 WP Migrations

![Packagist Version](https://img.shields.io/packagist/v/combizera/wordpress-to-laravel-migrator)
![Downloads](https://img.shields.io/packagist/dt/combizera/wordpress-to-laravel-migrator)
![License](https://img.shields.io/github/license/combizera/wordpress-to-laravel-migrator)
![PHP Version](https://img.shields.io/packagist/php-v/combizera/wordpress-to-laravel-migrator)

Um pacote para migrar postagens do **WordPress** para **Laravel** de maneira simples e eficiente.

## Instalação

```bash
composer require combizera/wordpress-to-laravel-migrator
```

### Informações Importantes

- O pacote não faz a migração de imagens, apenas postagens (por enquanto).
- O pacote não faz a migração de páginas, apenas postagens (por enquanto).

### Itens Necesários

Para que o pacote funcione corretamente, alguns itens são necessários:

- [ ] Ter uma `Model` para as Postagens, que necessariamente deve ter o nome de `Post`;
- [ ] A `Model` deve ter os campos `title`, `slug` e `content`;
- [ ] Ter um arquivo `.xml` com as postagens do WordPress.

## Utilização

Com todos os itens necessários, basta rodar o comando abaixo:

```php
php artisan wp:migrate storage/migration.xml
```
