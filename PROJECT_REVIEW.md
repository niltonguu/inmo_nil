# Revisión técnica del proyecto

## Resumen ejecutivo
Este documento resume observaciones de arquitectura, seguridad y mantenibilidad encontradas al revisar el código actual. Se priorizan los puntos que pueden generar riesgos inmediatos o deuda técnica relevante.

## Hallazgos principales
- **Configuración sensible en código**: Las credenciales de base de datos están embebidas en `app/Config/Database.php`, junto con un manejo de errores que usa `die()` mostrando el mensaje completo de la excepción. Esto expone secretos y detalles internos si ocurre un fallo en producción.【F:app/Config/Database.php†L4-L24】
- **Ruteo sin validación ni restricciones de método**: El router carga cualquier archivo controlador y ejecuta cualquier método indicado por parámetros `?c=` y `?a=` sin validaciones adicionales (por ejemplo, restringir a métodos públicos o a verbos HTTP específicos). Esto facilita la invocación de acciones críticas vía GET y abre superficie para abusos o descubrimiento de endpoints internos.【F:router.php†L5-L33】
- **Endpoints públicos que exponen datos**: Varias acciones del `ApiController` consultan tablas (`tipo_persona`, `tipo_documentos_identidad`, `ubigeos`, `users`) sin requerir autenticación, devolviendo potencialmente catálogos completos o datos de usuarios a cualquier visitante.【F:app/Controllers/ApiController.php†L48-L122】
- **Acoplamiento y responsabilidad múltiple en Documentos**: `DocumentosController` mezcla control de sesión/roles, lógica de negocio, armado de payloads, render de HTML y generación de archivos en disco en el mismo archivo extenso. Esta falta de separación complica pruebas y mantenimiento, y aumenta el riesgo de cambios colaterales en flujos críticos de documentos.【F:app/Controllers/DocumentosController.php†L4-L195】
- **Sesiones iniciadas desde varios puntos**: Controladores como `AuthController` suponen una sesión activa pero también llaman `session_start()` en métodos como `logout`, lo que puede producir warnings o comportamientos inconsistentes según el punto de entrada. Centralizar el inicio de sesión evitaría duplicación y efectos secundarios.【F:app/Controllers/AuthController.php†L6-L66】

## Recomendaciones clave
1. Extraer la configuración sensible a variables de entorno (por ejemplo, usando `.env`) y reemplazar `die()` por un logger más mensajes genéricos al usuario final.
2. Endurecer el router: validar lista blanca de controladores/acciones, restringir métodos HTTP para acciones de escritura y considerar un middleware de CSRF para peticiones mutantes.
3. Proteger endpoints que devuelven catálogos o datos de usuarios con autenticación y, si aplica, con control de permisos por rol.
4. Refactorizar `DocumentosController` en servicios o clases separadas (autorización, orquestación de plantillas, persistencia/generación de archivos) y cubrir los flujos con pruebas automatizadas.
5. Inicializar la sesión en un único bootstrap (por ejemplo, en `index.php`) y asumir que los controladores reciben una sesión activa para evitar llamadas repetidas.

## Estado tras la limpieza de redundancias
- Se eliminó el respaldo obsoleto `DocumentosController-Juan.php` y el árbol duplicado `app/TemplatesXX`, dejando solo las plantillas vigentes. No se encontraron referencias activas a estos archivos en el ruteo actual, por lo que no se requiere acción adicional.
- Verificación reciente: ambos elementos permanecen ausentes del árbol (`app/Controllers/DocumentosController-Juan.php`, `app/TemplatesXX/`) y no aparecen entradas residuales en el repositorio.

## Validaciones más recientes
- **Linting PHP**: Se ejecutó `php -l` sobre todos los archivos en `app/` y no se encontraron errores de sintaxis.
- **Composer**: `composer validate --no-check-publish` pasa con la advertencia de que falta declarar una licencia; el esquema general es válido y los paquetes instalados coinciden con el lock actual.
