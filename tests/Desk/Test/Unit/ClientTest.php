<?php

namespace Desk\Test\Unit;

use Desk\Client;
use Desk\Client\Factory as ClientFactory;
use Desk\Test\Helper\UnitTestCase;
use Guzzle\Common\Collection;

class ClientTest extends UnitTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getMockedClass()
    {
        return 'Desk\\Client';
    }

    /**
     * @covers Desk\Client::getFactory
     */
    public function testGetFactory()
    {
        $this->assertInstanceOf('Desk\\Client\\Factory', Client::getFactory());
    }

    /**
     * @covers Desk\Client::setFactory
     */
    public function testSetFactory()
    {
        $factory = \Mockery::mock('Desk\\Client\\FactoryInterface');
        Client::setFactory($factory);

        $this->assertSame($factory, Client::getFactory());
    }

    /**
     * @covers Desk\Client::setFactory
     */
    public function testSetFactoryWithNoArgumentsResetsToDefault()
    {
        $factory = \Mockery::mock('Desk\\Client\\FactoryInterface');
        Client::setFactory($factory);

        $this->assertSame($factory, Client::getFactory());

        Client::setFactory();
        $this->assertNotSame($factory, Client::getFactory());
    }

    /**
     * @covers Desk\Client::factory
     */
    public function testFactory()
    {
        $factory = \Mockery::mock('Desk\\Client\\Factory')
            ->shouldReceive('factory')
                ->with(array('foo' => 'bar'))
                ->andReturn('return value')
            ->getMock();

        Client::setFactory($factory);

        $result = Client::factory(array('foo' => 'bar'));
        $this->assertSame('return value', $result);
    }

    /**
     * @covers Desk\Client::setAuth
     */
    public function testSetAuth()
    {
        $client = $this->mock('addDefaultHeader')
            ->shouldReceive('addDefaultHeader')
            ->with('Authorization', 'Basic Zm9vOmJhcg==')
            ->andReturn(\Mockery::self())
            ->getMock();

        $this->assertSame($client, $client->setAuth('foo', 'bar'));
    }

    /**
     * @covers Desk\Client::addDefaultHeader
     */
    public function testAddDefaultHeader()
    {
        $client = $this->mock(array('getDefaultHeaders', 'setDefaultHeaders'))
            ->shouldReceive('getDefaultHeaders')
                ->andReturn(new Collection(array('foo' => 'bar')))
            ->shouldReceive('setDefaultHeaders')
                ->with(
                    \Mockery::on(
                        function ($headers) {
                            return
                                $headers->hasKey('baz') &&
                                $headers->get('baz') === 'qux'
                            ;
                        }
                    )
                )
            ->getMock();

        $this->assertSame($client, $client->addDefaultHeader('baz', 'qux'));
    }

    /**
     * @covers Desk\Client::getCommandForDeskClass
     */
    public function testGetCommandForDeskClass()
    {
        $operation = \Mockery::mock('Guzzle\\Service\\Description\\Operation')
            ->shouldReceive('getName')
                ->andReturn('operationName')
            ->getMock();

        $client = $this->mock(array('getOperationForDeskClass', 'getCommand'))
            ->shouldReceive('getOperationForDeskClass')
                ->with('myClass')
                ->andReturn($operation)
            ->shouldReceive('getCommand')
                ->with('operationName')
                ->andReturn('returned command')
            ->getMock();

        $result = $client->getCommandForDeskClass('myClass');
        $this->assertSame('returned command', $result);
    }

    /**
     * @covers Desk\Client::getOperationForDeskClass
     */
    public function testGetOperationForDeskClass()
    {
        $client = $this->mock('getDescription');

        $operation1 = \Mockery::mock('Guzzle\\Service\\Description\\Operation')
            ->shouldReceive('getData')
                ->with('class')
                ->andReturn(null)
            ->getMock();

        $operation2 = \Mockery::mock('Guzzle\\Service\\Description\\Operation')
            ->shouldReceive('getData')
                ->with('class')
                ->andReturn('foo')
            ->getMock();

        $client->shouldReceive('getDescription->getOperations')
            ->andReturn(array($operation1, $operation2));

        $result = $client->getOperationForDeskClass('foo');
        $this->assertSame($operation2, $result);
    }

    /**
     * @covers Desk\Client::getOperationForDeskClass
     * @expectedException Desk\Exception\InvalidArgumentException
     */
    public function testGetOperationForDeskClassInvalid()
    {
        $client = $this->mock(array('getDescription', 'getCommand'));

        $client->shouldReceive('getDescription->getOperations')
            ->andReturn(array());

        $client->getOperationForDeskClass('bar');
    }
}
