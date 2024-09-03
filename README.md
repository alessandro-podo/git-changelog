# GitChangelogGenerator

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
composer require alessandro_podo/git-changelog-generator
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    AlessandroPodo\GitChangelogGenerator\GitChangelogGenerator::class => ['all' => true],
];
```
### Step 3: Create Config

```yaml
# config/packages/git_changelog_generator.yaml

git_changelog_generator:
  validateMapping:
    ROLE_*':
      - <visibility footer>
  scopes:
    - ...
```

### Step 4: Create Controller

#### Controller
```php

use AlessandroPodo\GitChangelogGenerator\Service\Changelog\Changelog;

#[Route(path: '/changelog')]
public function change(
    Changelog $changelog,
): Response {
   return $this->render('changelog/index.html.twig', ['content'=> $changelog->render()]);
}
```

#### TwigTemplate
```twig
# extend base Template
{% block body %}
    {{ content|raw }}
{% endblock %}
```

### Einstellungen

```yaml
# Dateiname kann unten den Optionen angepasst werden. Default ist: plannedChangesFile.yml
composer:
    -
        title: Composer
        description: Composer
        ready: false
        type: refactor
```

Damit werden geplante Änderungen aufgeführt.
Ist das File leer, wird angezeigt, dass keine Änderungen geplant sind. 
Gibt es das File nicht, wird nichts angezeigt

### CommitMessage

* Wenn im Footer `title:` enthalten ist, dann wird dieser genutzt
* Wenn im Footer `description:` enthalten ist, dann wird dieser genutzt
* Wenn im Footer `visibility:`/`v:` enthalten ist, dann wird dieser genutzt, sonst wird der Default aus dem ConfigFile genutzt
