<?php

namespace Angyvolin\Provider\Tests;

use Angyvolin\Provider\WorkflowServiceProvider;
use PHPUnit\Framework\TestCase;
use Silex\Application;
use Symfony\Bridge\Twig\Extension\WorkflowExtension;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\MultipleStateMarkingStore;
use Symfony\Component\Workflow\MarkingStore\SingleStateMarkingStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Workflow;

class WorkflowServiceProviderTest extends TestCase
{
    public function testRegisterWithoutConfiguration()
    {
        $app = $this->createApplication();
        $app->register(new WorkflowServiceProvider());
        $app->boot();

        $this->assertInstanceOf(Registry::class, $app['workflow.registry']);
        $this->assertInternalType('array', $app['workflow.config']);
        $this->assertEmpty($app['workflow.config']);

        $this->assertInstanceOf(SingleStateMarkingStore::class, $app['workflow.marking_store.single_state']());
        $this->assertInstanceOf(MultipleStateMarkingStore::class, $app['workflow.marking_store.multiple_state']());

        $definition = new Definition([], []);
        $this->assertInstanceOf(Workflow::class, $app['workflow.factory']($definition, null, 'test_workflow'));
        $this->assertInstanceOf(StateMachine::class, $app['state_machine.factory']($definition, null, 'test_state_machine'));
    }

    public function testRegisterWithTwigExtension()
    {
        $app = $this->createApplication();
        $app->register(new WorkflowServiceProvider());

        $this->assertInstanceOf(WorkflowExtension::class, $app['twig']->getExtension('workflow'));
    }

    public function testRegisterWithoutTwigExtension()
    {
        $app = new Application();

        $app->register(new WorkflowServiceProvider());
    }

    public function testRegisterSingleStateWorkflow()
    {
        $config = require __DIR__.'/Fixtures/workflow_config.php';

        $app = $this->createApplication();
        $app->register(new WorkflowServiceProvider(), [
            'workflow.config' => $config,
        ]);
        $app->boot();

        $this->assertEquals($config, $app['workflow.config']);
        $this->assertInstanceOf(Workflow::class, $app['workflow.flow']);
        $this->assertInstanceOf(Definition::class, $app['workflow.flow.definition']);
        $this->assertInstanceOf(SingleStateMarkingStore::class, $app['workflow.flow.marking_store']);
        $this->assertInstanceOf(Registry::class, $app['workflow.registry']);
    }

    public function testRegisterMultipleStateStateMachine()
    {
        $config = require __DIR__.'/Fixtures/state_machine_config.php';

        $app = $this->createApplication();
        $app->register(new WorkflowServiceProvider(), [
            'workflow.config' => $config,
        ]);
        $app->boot();

        $this->assertEquals($config, $app['workflow.config']);
        $this->assertInstanceOf(StateMachine::class, $app['state_machine.stm']);
        $this->assertInstanceOf(Definition::class, $app['state_machine.stm.definition']);
        $this->assertInstanceOf(MultipleStateMarkingStore::class, $app['state_machine.stm.marking_store']);
        $this->assertInstanceOf(Registry::class, $app['workflow.registry']);
    }

    private function createApplication()
    {
        $app = new Application();

        $app['twig'] = function () {
            return new \Twig_Environment();
        };

        return $app;
    }
}
