# WorkflowServiceProvider

Silex service provider for [symfony/workflow](https://github.com/symfony/workflow) component.

This version is intended for use with Silex 2.*.

## Installation

`composer require angyvolin/silex-worflow`

> To be able to use twig helpers you also require a [symfony/twig-bridge](https://github.com/symfony/twig-bridge) package:
>`composer require symfony/twig-bridge`

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

See [silex-workflow-demo
![workflow](https://github.com/angyvolin/silex-workflow-demo/blob/master/web/img/workflow.png)
](https://github.com/angyvolin/silex-workflow-demo)

## Author

* Andrii Volin angy.volin@gmail.com
