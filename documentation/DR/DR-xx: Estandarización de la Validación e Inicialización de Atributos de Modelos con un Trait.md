### ⚠️ EJEMPLO DE ADR ⚠️

#### DR-01: Estandarización de la Validación e Inicialización de Atributos de Modelos con un Trait

-   **Status:** Accepted
-   **Date:** 2025-01-15
-   **Authors:** Equipo de Desarrollo (ejemplo)

---

## Context and Problem Statement (Contexto y Declaración del Problema)

Dentro del desarrollo de la **Aplicación de Ejemplo**, se ha identificado una necesidad recurrente de validar e inicializar atributos para varios **modelos Eloquent** (ej. `Producto`, `Usuario`) antes de su persistencia o manipulación. La lógica para estas operaciones a menudo se duplica en diferentes métodos de controladores, servicios o incluso en los propios modelos, llevando a código redundante, inconsistente y difícil de mantener.

La dependencia exclusiva de `Form Request Validation` para la validación presenta limitaciones cuando se busca reutilizar la lógica de validación en contextos **no HTTP**, como operaciones en la consola, tareas en cola (que no pasan por una `Request`), o en la construcción de interfaces reactivas, donde la validación puede activarse en tiempo real sin una petición HTTP completa. Además, la inicialización de atributos a menudo implica transformaciones o lógicas específicas que también se repiten.

El objetivo es estandarizar y centralizar esta lógica para mejorar la mantenibilidad, la consistencia y la reutilización del código en el manejo de atributos de modelos.

---

## Considered Options (Opciones Consideradas)

Se consideraron las siguientes opciones para abordar el problema:

-   **Opción 1: Duplicación de Lógica en Controladores/Servicios (Ejemplo):** Mantener la lógica de validación e inicialización dispersa en cada controlador o servicio donde se necesite.
    * **Breve descripción:** Cada punto de la aplicación que interactúe con los atributos de un modelo implementaría su propia validación e inicialización.
-   **Opción 2: Implementación de Lógica en los Propios Modelos sin Trait (Ejemplo):** Mover la validación e inicialización directamente a métodos estáticos o de instancia en cada modelo Eloquent.
    * **Breve descripción:** Los modelos tendrían métodos como `validate()` o `initialize()` directamente definidos en ellos.
-   **Opción 3: Uso de `ModelAttributesTrait` (Opción Elegida - Ejemplo):** Definir un trait que encapsule la interfaz y la lógica común para la validación e inicialización de atributos.
    * **Breve descripción:** Creación de un `ModelAttributesTrait` (ej. ubicado en `App\Traits\ModelAttributesTrait.php`) que proporciona métodos abstractos `defineValidationRules` y `setDefaultAttributes` para ser implementados por los modelos que lo usen.

---

## Decision Outcome (Resultado de la Decisión)

**Chosen Option:** Opción 3: Uso de `ModelAttributesTrait`

### Rationale (Justificación)

Esta opción fue seleccionada por las siguientes razones fundamentales:

-   **Estandarización y Consistencia:** El trait impone una interfaz común (`defineValidationRules` y `setDefaultAttributes`) que cada modelo que lo utiliza debe implementar. Esto garantiza que la lógica de validación e inicialización se aborde de manera uniforme en toda la aplicación.
-   **Eliminación de Duplicidad:** Al centralizar la definición de la interfaz en un trait, se evita la repetición de la lógica de validación e inicialización a lo largo de la base de código.
-   **Desacoplamiento de `Form Request Validation`:** El método permite que la validación se realice de forma independiente de una `Form Request`. Esto es ventajoso para contextos asíncronos o de consola.
-   **Encapsulación de Lógica:** El método `setDefaultAttributes` encapsula la lógica de inicialización y transformación de atributos para un modelo, manteniendo el código limpio y organizado.
-   **Reusabilidad a través de Modelos:** El trait es aplicable a cualquier modelo Eloquent que necesite una validación o inicialización de atributos personalizada.

---

## Consequences (Consecuencias)

### Positive Consequences (Consecuencias Positivas)

-   **Mayor Cohesión:** La lógica está estrechamente ligada a los modelos a los que afecta.
-   **Menor Acoplamiento:** La lógica de validación ya no depende exclusivamente del ciclo de vida de una solicitud HTTP.
-   **Código Más Limpio:** Los controladores y servicios se vuelven más concisos.
-   **Mejora de la Experiencia del Desarrollador (DX):** La estandarización reduce la curva de aprendizaje.
-   **Facilita el Testing:** La lógica puede ser probada unitariamente de forma más aislada.
-   **Flexibilidad de Frontend:** Soporte mejorado para *frameworks* de UI reactivos (ej. Livewire, Filament).

### Negative Consequences (Consecuencias Negativas)

-   **Complejidad Inicial:** Añade un nivel de abstracción (el trait) que requiere comprensión por parte de los desarrolladores.
-   **Requerimiento de Implementación:** Cada modelo que use el trait debe implementar los métodos abstractos.
-   **Dependencia Nueva:** Introduce una dependencia de un paquete (ej. `acme/model-traits`) aunque sea interno.

### Neutral Consequences (Consecuencias Neutrales)

-   **Cambia el Enfoque de Validación:** Mueve parte de la responsabilidad de la validación de `Form Requests` o servicios a los propios modelos.
-   **Requiere que los Desarrolladores se Familiaricen:** Los desarrolladores deben entender el patrón de uso del trait al trabajar con los modelos afectados.