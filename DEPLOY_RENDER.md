# Despliegue en Render

Guía mínima para desplegar Qualiwuarma en Render usando el `Dockerfile` y
`render.yaml` de este repo. El contenedor incluye PHP 8.2 y un entorno virtual
de Python 3 (para el subproceso de `PrediccionIAService`), y la base de datos
es PostgreSQL gestionado por Render (no SQLite).

## Pasos

1. Sube este branch a GitHub (`git push`), si aún no lo hiciste.
2. En el dashboard de Render: **New +** → **Blueprint** → selecciona el repo.
   Render detecta `render.yaml` automáticamente y crea:
   - Un servicio web Docker (`qualiwuarma`).
   - Una base de datos Postgres (`qualiwuarma-db`), vinculada automáticamente
     vía las variables `DB_*`.
3. Define manualmente el único secreto que `render.yaml` deja sin valor:
   - `GROQ_API_KEY` (dashboard → servicio → Environment). Opcional: si lo
     dejas vacío, la sugerencia de recetas con IA queda deshabilitada, el
     resto del sistema funciona igual.
4. Deploy. El `entrypoint.sh` corre `migrate --force` automáticamente en cada
   arranque, así que las migraciones nuevas se aplican solas.

## Notas importantes

- **La base de datos ya no es SQLite.** Los datos que tengas localmente
  (`database/database.sqlite`) no se migran solos: si quieres llevar el
  histórico real de la institución a producción, hay que exportarlo e
  importarlo a Postgres aparte (pídemelo cuando llegue el momento).
- **Modelos IA entrenados**: `storage/app/ia_modelos/*.joblib` vive dentro
  del contenedor y **se pierde en cada deploy** (el filesystem de Render no es
  persistente por defecto en el plan free). Hay que volver a ejecutar
  `php artisan ia:entrenar` después de cada deploy, o contratar un disco
  persistente de Render y montarlo en esa ruta.
- El plan `free` de Render duerme el servicio tras inactividad; el primer
  request tras dormir tarda más (cold start).
