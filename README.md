# WP Migrations

Um pacote para migrar postagens do WordPress para Laravel.

## Instalação

```bash
composer require combizera/wordpress-to-laravel-migrator
```

### Informações Importantes

- O pacote não faz a migração de imagens, apenas postagens (por enquanto).
- O pacote não faz a migração de páginas, apenas postagens (por enquanto).

### Itens Necesários

O seu projeto em Laravel deve ter uma `Model` para as Postagens, deve ter o nome de `Post`. Além de que nessa Model obrigatoriamente deve ter o campo `title`, `slug` e `content`.
