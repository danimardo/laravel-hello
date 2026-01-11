# laravel-hello Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-01-11

## Active Technologies

- PHP 8.4+ (requerido por Symfony dependencies de Laravel 12) + Laravel 12.x, Livewire 3.x, Tailwind CSS 3.4.x, daisyUI 5.x, MariaDB 10.11+ (001-laravel-counter-auth)

## Project Structure

```text
src/
tests/
```

## Commands

# Add commands for PHP 8.4+ (requerido por Laravel 12)

## Code Style

PHP 8.4+ (requerido por Laravel 12): Follow standard conventions

## Recent Changes

- 001-laravel-counter-auth: Added PHP 8.4+ (requerido por Laravel 12) + Laravel 12.x, Livewire 3.x, Tailwind CSS 3.4.x, daisyUI 5.x, MariaDB 10.11+

<!-- MANUAL ADDITIONS START -->
[PRIORIDAD ALTA]

Los mensajes de salida deben estar en español.

- Ubicación de documentos normativos:
  - specs/001-laravel-counter-auth/spec.md(especificación funcional)
  - .specify\memory\constitution.md (reglas/limitaciones de diseño)
  -  Historias.md (Historias de usuario)
- Instrucciones para todos los agentes y colaboradores:
  - Sigue estrictamente las definiciones del plan de implementación en /historias.md, .specify\memory\constitution.md y specs/001-laravel-counter-auth/spec.md
  - No modifiques el comportamiento fuera de los criterios descritos en esos ficheros.
  - Si detectas discrepancias entre el código y esos documentos, solicita autorización antes de aplicar cualquier cambio que altere el comportamiento.
  - Cuando una tarea no esté cubierta por esos documentos, propone cambios como diffs o notas de diseño, pero no los apliques sin validación explícita.
- Confirmación obligatoria:
  - Antes de realizar refactors o “arreglos” no especificados, pregunta y espera confirmación.
  - Evita optimizaciones que cambien efectos observables si no están contempladas en historias.md, constitution.md o spec.md.
- Cumplimiento y trazabilidad:
  - Referencia siempre el/los criterio(s) de aceptación correspondiente(s) al realizar cambios.
  - Si un criterio es ambiguo, pide aclaración en lugar de asumir comportamiento.
  
Si arreglando un fallo o aclarando una duda o añadiendo una pequeña caracteristica si se dan una o varias de estas situaciones:
- La funcionalidad sigue siendo parte del mismo feature
- Los cambios son clarificaciones/refinamientos, no nuevas funcionalidades
- El alcance general no ha cambiado significativamente
hemos de actualizar las especificaciones para reflejar la implementación real. De esta forma podremos mantener una única fuente de verdad.

Para implementar el código, **utiliza Context7 para consultar la documentación más reciente y asegúrate de que la implementación cumpla con el Spec y use la sintaxis más moderna
<!-- MANUAL ADDITIONS END -->