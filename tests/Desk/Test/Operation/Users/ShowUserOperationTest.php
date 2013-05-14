<?php

namespace Desk\Test\Operation\Users;

use Desk\Test\Helper\OperationTestCase;

/**
 * @coversNothing
 * @group system
 */
class ShowUserOperationTest extends OperationTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getOperationName()
    {
        return 'ShowUser';
    }

    /**
     * {@inheritdoc}
     */
    public function dataParameterValid()
    {
        return array(
            array(array('id' => 3), array('url' => '#/users/3$#')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function dataParameterInvalid()
    {
        return array(
            array(array()),
            array(array('id' => 4, 'embed' => 'foo')),
            array(array('id' => 4, 'embed' => 'self')),
            array(array('id' => true)),
            array(array('id' => false)),
            array(array('id' => null)),
            array(array('id' => 0)),
            array(array('id' => -12)),
            array(array('id' => 12.3)),
            array(array('id' => -12.3)),
            array(array('id' => '3')),
            array(array('id' => new \stdClass())),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function testSystem()
    {
        $client = $this->client();
        $command = $client->getCommand(
            $this->getOperationName(),
            array('id' => 5)
        );

        $this->setMockResponse($client, 'success');

        $user = $command->execute();

        $this->assertInstanceOf('Desk\\Model\\UserModel', $user);

        $this->assertSame('John Doe', $user->get('name'));
        $this->assertSame('John Doe', $user->get('public_name'));
        $this->assertSame('john@acme.com', $user->get('email'));
        $this->assertSame('agent', $user->get('level'));
    }
}
