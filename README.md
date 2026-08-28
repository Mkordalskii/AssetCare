# AssetCare

AssetCare is a smart maintenance manager for tracking assets, maintenance schedules, service history, documents and costs.

## Technology stack

- PHP
- Symfony
- PostgreSQL
- Docker
- Nginx

## Initial asset categories

Load the eight default categories without purging existing data:

```bash
docker compose exec php php bin/console doctrine:fixtures:load --group=asset-categories --append --no-interaction
```

The fixture skips existing names and does not reactivate inactive categories.
Categories are managed at `/categories`, with name search and Active/Inactive views.
Deactivation preserves existing asset relationships.
