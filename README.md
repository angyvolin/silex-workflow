# WorkflowServiceProvider [![License](https://poser.pugx.org/angyvolin/silex-workflow/license)](https://packagist.org/packages/angyvolin/silex-workflow) [![Latest Stable Version](https://poser.pugx.org/angyvolin/silex-workflow/v/stable)](https://packagist.org/packages/angyvolin/silex-workflow) [![Total Downloads](https://poser.pugx.org/angyvolin/silex-workflow/downloads)](https://packagist.org/packages/angyvolin/silex-workflow)

Silex 2.* service provider for Symfony Workflow component.

![workflow](https://github.com/angyvolin/silex-workflow-demo/blob/master/web/img/workflow.png)

## About
The Workflow component provides tools for managing a workflow or finite state machine. [symfony/workflow](https://github.com/symfony/workflow) was introduced in Symfony 3.2.

## Installation
`composer require angyvolin/silex-worflow`

> To be able to use twig helpers you also require a [symfony/twig-bridge](https://github.com/symfony/twig-bridge) package:
> 
> `composer require symfony/twig-bridge`

## Configuration
Register the service and pass workflow configuration:

```php
<?php

use Angyvolin\Provider\WorkflowServiceProvider;

$app->register(new WorkflowServiceProvider(), array(
    'workflow.config' => $workflowConfig,
));

```

## Usage
See [silex-workflow-demo](https://github.com/angyvolin/silex-workflow-demo)

## License
[MIT License](LICENSE.md)
