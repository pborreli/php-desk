<?php

namespace Desk\Test\Unit\Model;

use Desk\Model\Factory;
use Desk\Test\Helper\UnitTestCase;

class FactoryTest extends UnitTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getMockedClass()
    {
        return 'Desk\\Model\\Factory';
    }

    /**
     * @covers Desk\Model\Factory::fromCommand
     */
    public function testFromCommand()
    {
        $client = \Mockery::mock('Desk\\Client');

        $model = \Mockery::mock('Desk\\Model\\AbstractModel')
            ->shouldReceive('setClient')
                ->with($client)
            ->getMock();

        $factory = $this->mock('fromData')
            ->shouldReceive('fromData')
                ->with('myClass', array('raw' => 'data'))
                ->andReturn($model)
            ->getMock();

        $command = \Mockery::mock('Guzzle\\Service\\Command\\OperationCommand');
        $command
            ->shouldReceive('getClient')
                ->andReturn($client)
            ->shouldReceive('getResponse->json')
                ->andReturn(array('raw' => 'data'));

        $result = $factory->fromCommand('myClass', $command);
        $this->assertSame($model, $result);
    }

    /**
     * @covers Desk\Model\Factory::fromData
     */
    public function testFromData()
    {
        $model = \Mockery::mock('Desk\\Model\\HasRelationships');

        $mockedMethods = array(
            'getModelData',
            'createClass',
            'addRelationships'
        );

        $data = array('raw' => 'data');
        $modelData = array('model' => 'data');

        $factory = $this->mock($mockedMethods)
            ->shouldReceive('getModelData')
                ->with($data)
                ->andReturn($modelData)
            ->shouldReceive('createClass')
                ->with('myClass', array($modelData))
                ->andReturn($model)
            ->shouldReceive('addRelationships')
                ->with($model, $data)
            ->getMock();

        $result = $factory->fromData('myClass', $data);
        $this->assertSame($model, $result);
    }

    /**
     * @covers Desk\Model\Factory::createClass
     */
    public function testCreateClass()
    {
        $factory = new Factory();
        $result = $factory->createClass('SplObjectStorage');
        $this->assertInstanceOf('SplObjectStorage', $result);
    }

    /**
     * @covers Desk\Model\Factory::getModelData
     */
    public function testGetModelData()
    {
        $factory = new Factory();

        $input = array(
            '_foo' => 'bar',
            'baz' => 'qux',
            'grault' => 'bazola',
        );

        $expected = array(
            'baz' => 'qux',
            'grault' => 'bazola',
        );

        $actual = $factory->getModelData($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Desk\Model\Factory::addRelationships
     */
    public function testAddRelationships()
    {
        $data = array(
            'foo' => 'bar',
            '_links' => array('my' => 'Links'),
            '_embedded' => array('my' => 'Embeds'),
        );

        $model = \Mockery::mock('Desk\\Model\\HasRelationships')
            ->shouldReceive('setLinks')
                ->with(array('my' => 'Links'))
            ->shouldReceive('setEmbeds')
                ->with(array('my' => 'Embeds'))
            ->getMock();

        $factory = new Factory();

        $modelClone = clone $model;
        $factory->addRelationships($model, $data);
        $this->assertEquals($modelClone, $model);
    }
}
