<?php
namespace DWenzel\T3events\Tests\Controller;

/**
 * This file is part of the TYPO3 CMS project.
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 * The TYPO3 project - inspiring people to share!
 */

use DWenzel\T3events\Controller\Routing\Route;
use DWenzel\T3events\Controller\Routing\RouterInterface;
use DWenzel\T3events\Controller\RoutingTrait;
use DWenzel\T3events\Controller\SignalInterface;
use DWenzel\T3events\Controller\SignalTrait;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Mvc\Web\Request;

class MockSignalController implements SignalInterface
{
    use RoutingTrait, SignalTrait;
}
/**
 * Class RouteTraitTest
 *
 * @package DWenzel\T3events\Tests\Controller
 */
class RoutingTraitTest extends UnitTestCase
{
    /**
     * @var RoutingTrait|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * set up subject
     */
    public function setUp()
    {
        $this->subject = $this->getMockForTrait(
            RoutingTrait::class
        );
    }

    /**
     * @test
     */
    public function routerCanBeInjected()
    {
        $router = $this->getMockForAbstractClass(
            RouterInterface::class
        );
        $this->subject->injectRouter($router);

        $this->assertAttributeEquals(
            $router, 'router', $this->subject
        );
    }

    /**
     * @test
     */
    public function dispatchGetsIdentifierFromRequest()
    {
        $identifier = 'foo';

        $mockRequest = $this->getMock(
            Request::class, ['getControllerActionName', 'getControllerObjectName']
        );
        $this->inject($this->subject, 'request', $mockRequest);
        $mockRequest->expects($this->once())
            ->method('getControllerActionName');
        $mockRequest->expects($this->once())
            ->method('getControllerObjectName');
        $mockRoute = $this->getMock(Route::class, ['getMethod'], [$identifier]);

        $mockRouter = $this->getMockForAbstractClass(
            RouterInterface::class, ['getRoute']
        );
        $this->subject->injectRouter($mockRouter);
        $mockRouter->expects($this->once())
            ->method('getRoute')
            ->will($this->returnValue($mockRoute));

        $this->subject->dispatch();
    }

    /**
     * @test
     */
    public function dispatchGetsRouteForIdentifier()
    {
        $identifier = 'foo';
        $mockRoute = $this->getMock(Route::class, null, [$identifier]);

        $mockRouter = $this->getMockForAbstractClass(
            RouterInterface::class, ['getRoute']
        );

        $this->subject->injectRouter($mockRouter);
        $mockRouter->expects($this->once())
            ->method('getRoute')
            ->with($identifier)
            ->will($this->returnValue($mockRoute));

        $this->subject->dispatch(null, $identifier);
    }

    /**
     * @test
     */
    public function dispatchCallsMethodFromRoute()
    {
        $identifier = 'foo';
        $method = 'bam';
        $mockRoute = $this->getMock(Route::class, ['getMethod'], [$identifier]);

        $this->subject = $this->getMockForTrait(
            RoutingTrait::class, [], '', true, true, true, [$method]
        );
        $mockRouter = $this->getMockForAbstractClass(
            RouterInterface::class, ['getRoute']
        );

        $this->subject->injectRouter($mockRouter);
        $mockRouter->expects($this->once())
            ->method('getRoute')
            ->will($this->returnValue($mockRoute));
        $mockRoute->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue($method));

        $this->subject->expects($this->once())
            ->method($method);
        $this->subject->dispatch(null, $identifier);
    }

    /**
     * @test
     */
    public function dispatchUsesArgumentsFromOrigin()
    {
        $arguments = ['foo' => 'bar'];
        $optionsFromRoute = [
            'actionName' => null,
            'controllerName' => null,
            'extensionName' => null,
            'arguments' => null,
            'pageUid' => null,
            'delay' => 0,
            'statusCode' => 303,
        ];

        $expectedOptions = [
            null,
            null,
            null,
            $arguments,
            null,
            0,
            303
        ];
        $identifier = 'foo';
        $method = 'bam';
        $mockRoute = $this->getMock(Route::class, ['getOptions', 'getMethod'], [$identifier]);

        $this->subject = $this->getMockForTrait(
            RoutingTrait::class, [], '', true, true, true, [$method]
        );

        $mockRouter = $this->getMockForAbstractClass(
            RouterInterface::class, ['getRoute']
        );

        $this->subject->injectRouter($mockRouter);
        $mockRouter->expects($this->once())
            ->method('getRoute')
            ->will($this->returnValue($mockRoute));

        $mockRoute->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue($method));
        $mockRoute->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($optionsFromRoute));

        $this->subject->expects($this->once())
            ->method($method)
            ->with(
                $expectedOptions[0],
                $expectedOptions[1],
                $expectedOptions[2],
                $expectedOptions[3],
                $expectedOptions[4],
                $expectedOptions[5],
                $expectedOptions[6]
            );

        $this->subject->dispatch($arguments, $identifier);
    }

    /**
     * @test
     */
    public function dispatchUsesDefaultArguments()
    {
        $defaultArguments =[
            'baz' => 'boom',
            'foo' => null
        ];
        $argumentsFromOrigin = ['foo' => 'bar'];
        $optionsFromRoute = [
            'actionName' => null,
            'controllerName' => null,
            'extensionName' => null,
            'arguments' => $defaultArguments,
            'pageUid' => null,
            'delay' => 0,
            'statusCode' => 303,
        ];
        $expectedArguments = array_merge($defaultArguments, $argumentsFromOrigin);
        $expectedOptions = [
            null,
            null,
            null,
            $expectedArguments,
            null,
            0,
            303
        ];
        $identifier = 'foo';
        $method = 'bam';
        $mockRoute = $this->getMock(Route::class, ['getOptions', 'getOption', 'getMethod'], [$identifier]);
        $this->subject = $this->getMockForTrait(
            RoutingTrait::class, [], '', true, true, true, [$method]
        );

        $mockRouter = $this->getMockForAbstractClass(
            RouterInterface::class, ['getRoute']
        );

        $this->subject->injectRouter($mockRouter);
        $mockRouter->expects($this->once())
            ->method('getRoute')
            ->will($this->returnValue($mockRoute));

        $mockRoute->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue($method));
        $mockRoute->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($optionsFromRoute));
        $mockRoute->expects($this->atLeastOnce())
            ->method('getOption')
            ->with('arguments')
            ->will($this->returnValue($defaultArguments));

        $this->subject->expects($this->once())
            ->method($method)
            ->with(
                $expectedOptions[0],
                $expectedOptions[1],
                $expectedOptions[2],
                $expectedOptions[3],
                $expectedOptions[4],
                $expectedOptions[5],
                $expectedOptions[6]
            );

        $this->subject->dispatch($argumentsFromOrigin, $identifier);
    }

    /**
     * @test
     */
    public function dispatchEmitsSignalDispatchBegin()
    {
        $this->subject = $this->getMock(
            MockSignalController::class, ['emitSignal']
        );
        $arguments = ['foo'];
        $identifier = 'bar';
        $mockRoute = $this->getMock(Route::class, [], [$identifier]);
        $mockRouter = $this->getMockForAbstractClass(
            RouterInterface::class, ['getRoute']
        );
        $mockRouter->expects($this->once())
            ->method('getRoute')
            ->will($this->returnValue($mockRoute));

        $this->subject->injectRouter($mockRouter);

        $this->subject->expects($this->once())
            ->method('emitSignal')
            ->with(
                $this->equalTo(MockSignalController::class),
                $this->equalTo('dispatchBegin')
            );
        $this->subject->dispatch($arguments, $identifier);
    }
}
