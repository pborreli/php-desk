<?php

namespace Desk\Test\Helper;

use Desk\Test\Helper\TestCase;

abstract class UnitTestCase extends TestCase
{

    /**
     * Gets the class name that will be mocked by self::mock()
     *
     * @return string
     */
    abstract protected function getMockedClass();

    /**
     * Creates a mocked instance of self::getMockedClass()
     *
     * All methods will be passed through to the underlying
     * implementation, except for method names passed in to $methods
     * (these can be stubbed using shouldReceive()... etc).
     *
     * @param array $methods Any methods that will be overridden
     *
     * @return Desk\Client\Factory
     */
    protected function mock($methods = array())
    {
        $class = $this->getMockedClass();
        $methods = implode(',', (array)$methods);
        return \Mockery::mock("{$class}[{$methods}]");
    }
}
