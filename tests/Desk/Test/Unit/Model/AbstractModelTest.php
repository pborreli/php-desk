<?php

namespace Desk\Test\Unit\Model;

use Desk\Model\AbstractModel;
use Desk\Test\Helper\UnitTestCase;
use ReflectionObject;

class AbstractModelTest extends UnitTestCase
{

    protected function getMockedClass()
    {
        return 'Desk\\Model\\AbstractModel';
    }

    /**
     * @covers Desk\Model\AbstractModel::getFactory
     */
    public function testGetFactory()
    {
        $factory = AbstractModel::getFactory();
        $this->assertInstanceOf('Desk\\Model\\Factory', $factory);
    }

    /**
     * @covers Desk\Model\AbstractModel::setFactory
     */
    public function testSetFactory()
    {
        $factory = \Mockery::mock('Desk\\Model\\FactoryInterface');
        AbstractModel::setFactory($factory);

        $this->assertSame($factory, AbstractModel::getFactory());
    }

    /**
     * @covers Desk\Model\AbstractModel::setFactory
     */
    public function testSetFactoryWithNoArgumentsResetsToDefault()
    {
        $factory = \Mockery::mock('Desk\\Model\\FactoryInterface');
        AbstractModel::setFactory($factory);

        $this->assertSame($factory, AbstractModel::getFactory());

        AbstractModel::setFactory();
        $this->assertNotSame($factory, AbstractModel::getFactory());
    }

    /**
     * @covers Desk\Model\AbstractModel::fromCommand
     */
    public function testFromCommand()
    {
        $modelName = get_class($this->mock());

        $command = \Mockery::mock('Guzzle\\Service\\Command\\OperationCommand');

        // $model = \Mockery::mock('Desk\\Model\\AbstractModel');

        AbstractModel::setFactory(
            \Mockery::mock('Desk\\Model\\FactoryInterface')
                ->shouldReceive('fromCommand')
                    ->with($modelName, $command)
                    ->andReturn('return_value')
                ->getMock()
        );

        $result = $modelName::fromCommand($command);
        $this->assertSame('return_value', $result);
    }

    /**
     * @covers Desk\Model\AbstractModel::getClient
     * @covers Desk\Model\AbstractModel::setClient
     */
    public function testSetClient()
    {
        $client = \Mockery::mock('Desk\\Client');
        $model = $this->mock();

        $model->setClient($client);
        $this->assertSame($client, $model->getClient());
    }

    /**
     * @covers Desk\Model\AbstractModel::getLinks
     * @covers Desk\Model\AbstractModel::setLinks
     */
    public function testSetLinks()
    {
        $links = array(
            'self' => array(
                'href' => '/path/to/self',
                'class' => 'myclass',
            ),
        );

        $model = $this->mock();
        $model->setLinks($links);
        $this->assertSame($links, $model->getLinks());
    }

    /**
     * @covers Desk\Model\AbstractModel::getEmbeds
     * @covers Desk\Model\AbstractModel::setEmbeds
     */
    public function testSetEmbeds()
    {
        $embeds = array(
            'foo' => array(
                'bar' => 'baz',
                'qux' => 'quux',
                '_links' => array(
                    'href' => '/path/to/foo',
                    'class' => 'fooClass',
                ),
            ),
        );

        $model = $this->mock();
        $model->setEmbeds($embeds);
        $this->assertSame($embeds, $model->getEmbeds());
    }

    /**
     * @covers Desk\Model\AbstractModel::getLink
     */
    public function testGetLink()
    {
        $command = \Mockery::mock('Desk\\Command\\AbstractCommand')
            ->shouldReceive('getOperation')
                ->andReturn(
                    \Mockery::mock('Guzzle\\Service\\Description\\Operation')
                        ->shouldReceive('setUri')
                            ->with('/path/to/foo')
                            ->once()
                        ->getMock()
                )
            ->getMock();

        $client = \Mockery::mock('Desk\\Client')
            ->shouldReceive('getCommandForDeskClass')
                ->with('fooClass')
                ->andReturn($command)
            ->getMock();

        $model = $this->mock(array('getLinks', 'getClient'))
            ->shouldReceive('getLinks')
                ->andReturn(
                    array(
                        'fooLink' => array(
                            'href' => '/path/to/foo',
                            'class' => 'fooClass',
                        ),
                    )
                )
            ->shouldReceive('getClient')
                ->andReturn($client)
            ->getMock();

        $result = $model->getLink('fooLink');
        $this->assertSame($command, $result);
    }

    /**
     * @covers Desk\Model\AbstractModel::getLink
     * @expectedException Desk\Exception\InvalidArgumentException
     */
    public function testGetLinkInvalid()
    {
        $model = $this->mock('getLinks')
            ->shouldReceive('getLinks')
                ->andReturn(
                    array(
                        'fooLink' => array(
                            'href' => '/path/to/foo',
                            'class' => 'fooClass',
                        ),
                    )
                )
            ->getMock();

        $model->getLink('barLink');
    }

    /**
     * @covers Desk\Model\AbstractModel::getEmbed
     */
    public function testGetEmbed()
    {
        $embed = array(
            'foo' => 'bar',
            'baz' => 'qux',
            '_links' => array(
                'self' => array(
                    'href' => '/path/to/self',
                    'class' => 'myClass',
                ),
            ),
        );

        $operation = \Mockery::mock('Guzzle\\Service\\Description\\Operation')
            ->shouldReceive('getResponseClass')
                ->andReturn('modelClass')
            ->getMock();

        $client = \Mockery::mock('Desk\\Client')
            ->shouldReceive('getOperationForDeskClass')
                ->with('myClass')
                ->andReturn($operation)
            ->getMock();

        $model = $this->mock(array('getEmbeds', 'getClient'))
            ->shouldReceive('getEmbeds')
                ->andReturn(array('barEmbed' => $embed))
            ->shouldReceive('getClient')
                ->andReturn($client)
            ->mock();

        $embedModel = $this->mock('setClient')
            ->shouldReceive('setClient')
                ->with($client)
            ->getMock();

        AbstractModel::setFactory(
            \Mockery::mock('Desk\\Model\\FactoryInterface')
                ->shouldReceive('fromData')
                    ->with('modelClass', $embed)
                    ->andReturn($embedModel)
                ->getMock()
        );

        $result = $model->getEmbed('barEmbed');
        $this->assertSame($embedModel, $result);
    }

    /**
     * @covers Desk\Model\AbstractModel::getEmbed
     * @expectedException Desk\Exception\InvalidArgumentException
     */
    public function testGetEmbeddedWithInvalidNameThrowsException()
    {
        $model = $this->mock('getEmbeds')
            ->shouldReceive('getEmbeds')
                ->andReturn(array())
            ->getMock();

        $model->getEmbed('nonExistantEmbeddedModel');
    }

    /**
     * @covers Desk\Model\AbstractModel::getEmbed
     * @expectedException Desk\Exception\UnexpectedValueException
     */
    public function testGetEmbeddedWithInvalidFormatThrowsException()
    {
        $embed = array(
            'foo' => 'bar',
            'baz' => 'qux',
            '_links' => array(
                'self' => array(
                    'href' => '/path/to/self',
                    'invalidKey' => 'myClass',
                ),
            ),
        );

        $model = $this->mock('getEmbeds')
            ->shouldReceive('getEmbeds')
                ->andReturn(array('fooEmbed' => $embed))
            ->getMock();

        $model->getEmbed('fooEmbed');
    }
}
