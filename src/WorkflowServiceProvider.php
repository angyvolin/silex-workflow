<?php

namespace Angyvolin\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Symfony\Bridge\Twig\Extension\WorkflowExtension;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\MultipleStateMarkingStore;
use Symfony\Component\Workflow\MarkingStore\SingleStateMarkingStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Validator\DefinitionValidatorInterface;
use Symfony\Component\Workflow\Validator\StateMachineValidator;
use Symfony\Component\Workflow\Validator\WorkflowValidator;
use Symfony\Component\Workflow\Workflow;

class WorkflowServiceProvider implements ServiceProviderInterface, BootableProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['workflow.factory'] = $app->protect(function ($definition, $markingStoreDefinition = null, $name) use ($app) {
            return new Workflow($definition, $markingStoreDefinition, $app['dispatcher'], $name);
        });

        $app['state_machine.factory'] = $app->protect(function ($definition, $markingStoreDefinition = null, $name) use ($app) {
            return new StateMachine($definition, $markingStoreDefinition, $app['dispatcher'], $name);
        });

        $app['workflow.marking_store.single_state'] = $app->protect(
            function ($property = 'marking', PropertyAccessorInterface $propertyAccessor = null) {
                return new SingleStateMarkingStore($property, $propertyAccessor);
            }
        );

        $app['workflow.marking_store.multiple_state'] = $app->protect(
            function ($property = 'marking', PropertyAccessorInterface $propertyAccessor = null) {
                return new MultipleStateMarkingStore($property, $propertyAccessor);
            }
        );

        $app['workflow.registry'] = function () {
            return new Registry();
        };

        $app['workflow.config'] = [];
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $this->registerWorkflowConfiguration($app);

        if (class_exists('Symfony\Bridge\Twig\Extension\WorkflowExtension')) {
            $app->extend('twig', function (\Twig_Environment $twig, Container $app) {
                $twig->addExtension(new WorkflowExtension($app['workflow.registry']));

                return $twig;
            });
        }
    }

    /**
     * @param Container $app
     */
    private function registerWorkflowConfiguration(Container $app)
    {
        if (!$workflows = $app['workflow.config']) {
            return;
        }

        foreach ($workflows as $name => $workflow) {
            $type = $workflow['type'];

            if (!isset($workflow['transitions'][0])) {
                foreach ((array) $workflow['transitions'] as $workflowName => $transition) {
                    if (array_key_exists('name', $transition)) {
                        continue;
                    }

                    $transition['name'] = $workflowName;
                    $workflow['transitions'][$workflowName] = $transition;
                }
            }

            $workflowId = $type.'.'.$name;
            $definitionId = $workflowId.'.definition';
            $markingStoreId = $workflowId.'.marking_store';

            $app[$definitionId] = $this->createDefinition($workflow, $type);

            if ($app['debug']) {
                $this
                    ->createValidator($type, isset($workflow['marking_store']['type']) ? $workflow['marking_store']['type'] : null)
                    ->validate($app[$definitionId], $name);
            }

            $app[$markingStoreId] = $this->createMarkingStore($workflow);

            $app[$workflowId] = function (Container $app) use ($name, $type, $definitionId, $markingStoreId) {
                return call_user_func($app[sprintf('%s.factory', $type)], $app[$definitionId], $app[$markingStoreId], $name);
            };

            $app->extend('workflow.registry', function (Registry $registry, Container $app) use ($workflow, $workflowId) {
                foreach ($workflow['supports'] as $supportedClass) {
                    $registry->add($app[$workflowId], $supportedClass);
                }

                return $registry;
            });
        }
    }

    /**
     * @param array $workflow
     * @param string $type
     *
     * @return \Closure
     */
    private function createDefinition(array $workflow, $type)
    {
        return function () use ($workflow, $type) {
            $transitions = [];

            foreach ((array) $workflow['transitions'] as $transition) {
                if ('workflow' === $type) {
                    $transitions[] = new Transition($transition['name'], $transition['from'], $transition['to']);
                } elseif ('state_machine' === $type) {
                    foreach ((array) $transition['from'] as $from) {
                        foreach ((array) $transition['to'] as $to) {
                            $transitions[] = new Transition($transition['name'], $from, $to);
                        }
                    }
                }
            }

            $initialPlace = isset($workflow['initial_place']) ? $workflow['initial_place'] : null;

            return new Definition($workflow['places'], $transitions, $initialPlace);
        };
    }

    /**
     * @param array $workflow
     *
     * @return \Closure
     */
    private function createMarkingStore(array $workflow)
    {
        return function (Container $app) use ($workflow) {
            if (isset($workflow['marking_store']['type'])) {
                return call_user_func_array(
                    $app['workflow.marking_store.'.$workflow['marking_store']['type']],
                    $workflow['marking_store']['arguments']
                );
            }

            if (isset($workflow['marking_store']['service'])) {
                return $app[$workflow['marking_store']['service']];
            }

            return null;
        };
    }

    /**
     * @param string $workflowType
     * @param string $markingStore
     *
     * @return DefinitionValidatorInterface
     */
    private function createValidator($workflowType, $markingStore)
    {
        if ('state_machine' === $workflowType) {
            return new StateMachineValidator();
        }

        if ('single_state' === $markingStore) {
            return new WorkflowValidator(true);
        }

        return new WorkflowValidator();
    }
}
