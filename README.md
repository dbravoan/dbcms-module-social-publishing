# dbravoan/dbcms-module-social-publishing 1.0.0

## Qué hace

`dbravoan/dbcms-module-social-publishing` aporta conexiones de cuentas, publicaciones
revisables, envío y reconciliación. Requiere Marketing. Instala/activa módulos,
configura permisos de app/cuenta por red y sigue las guías del proveedor en el panel.

Autoriza cuentas mediante OAuth, revisa destino/contenido y aprueba/programa.
Credenciales y recibos son privados. Reautorizar o cambiar credenciales no acredita
publicación: comprueba respuesta y resultado remoto final.

El scheduler ejecuta `social-publishing:dispatch` cada minuto y la variante
`--metrics` cada hora. Configura workers Marketing de envío/reconciliación,
timeouts y almacén de mutex compartido. Evita scheduler duplicado.

Un envío interrumpido puede haber terminado remotamente: conserva intención/recibo
y reconcilia estados inciertos o pendientes de confirmación antes de reintentar.
Revocar cuentas, desactivar propietarios o cambiar permisos detiene fases futuras.
Cancelar no deshace publicaciones remotas.

## Requisitos y dependencias

- `dbravoan/dbcms-core` `^1.0`
- `dbravoan/dbcms-module-marketing` `^1.0`
- Identificador CMS: `social-publishing`

## Instalación y activación

Instala el paquete con Composer e instálalo/actívalo en el CMS (`social-publishing`).

**Ajustes con módulo inactivo (Fix B):** si el módulo está instalado pero inactivo, puedes abrir y guardar su configuración en el panel; el runtime en el sitio requiere activarlo.

## Configuración

Ajustes del módulo (claves por nombre):

- `facebook_client_id`
- `facebook_client_secret`
- `instagram_client_id`
- `instagram_client_secret`
- `linkedin_client_id`
- `linkedin_client_secret`
- `youtube_client_id`
- `youtube_client_secret`
- `timezone`

## Rutas

Rutas admin OAuth/publicaciones del módulo.

## Comandos Artisan

- `social-publishing:privacy` — firma: `social-publishing:privacy {action : export, erase-local, prune or cleanup-artifacts} {--id= : Explicit account or removed-publication UUID} {--verified : Request scope and retention restrictions were checked}`
- `social-publishing:dispatch` — firma: `social-publishing:dispatch {--metrics}`

## Permisos

- `read:social-publishing`
- `manage:social-publishing`
- `approve:social-publishing`
- `manage:social-accounts`

## Privacidad y datos

Conexiones nuevas, entradas editoriales y procesamiento remoto requieren
`legal.processing` revisado para `social-publishing`, con datos, destinatarios,
transferencias y retención reales. El trabajo remoto vuelve a comprobar política;
revocación local y comandos de derechos siguen disponibles inactivo.

Ejecuta comandos con `./vendor/bin/sail artisan`. `social-publishing:privacy export
--id=<account-uuid> --verified` crea un export privado sin tokens ni recibos privados
del proveedor. Elimínalo tras atender la solicitud. `erase-local` con las mismas
opciones requiere cuenta revocada/manual y rechaza entregas pendientes, inciertas
o arrendadas. Solo borra datos locales; el borrado remoto es una acción separada.

`social-publishing:privacy prune` vence estados OAuth e historiales terminados con
`retention_days` positivo. Criterios manuales requieren revisión; entregas activas/
inciertas e historiales de borrador se conservan. Los archivos privados se limpian
después del commit. Si falla, reintenta `cleanup-artifacts
--id=<removed-publication-uuid> --verified`; se rechazan publicaciones existentes
y enlaces de almacenamiento inseguros. Reasigna/revoca referencias de cuenta antes
de eliminar al usuario que la conectó.

## Enlaces

- [Extensiones](../../../docs/EXTENSIONS.md)
- [Núcleo y extensiones](../../../specs/CORE_AND_EXTENSIONS.md)
- [README del núcleo](../../../packages/core/README.md)
- [README del monorepo](../../../README.md)
