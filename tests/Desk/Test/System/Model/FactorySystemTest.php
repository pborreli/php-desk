<?php

namespace Desk\Test\System\Model;

use Desk\Client;
use Desk\Test\Helper\SystemTestCase;
use Guzzle\Service\Description\ServiceDescription;

/**
 * @coversNothing
 * @group system
 */
class FactorySystemTest extends SystemTestCase
{

    /**
     * Gets a Desk client model, configured for use in this test case
     *
     * @return Desk\Client
     */
    private function client()
    {
        $model = \Mockery::mock('Desk\\Model\\AbstractModel[]');

        $client = new Client();
        $client->setDescription(
            new ServiceDescription(
                array(
                    'baseUrl' => 'http://mock.localhost/',
                    'operations' => array(
                        'myOperation' => array(
                            'class' => 'Desk\\Command\\AbstractCommand',
                            'responseType' => 'class',
                            'responseClass' => get_class($model),
                        ),
                        'myLinkOperation' => array(
                            'class' => 'Desk\\Command\\AbstractCommand',
                            'responseType' => 'class',
                            'responseClass' => get_class($model),
                            'data' => array(
                                'class' => 'myLinkClass',
                            ),
                        ),
                    ),
                )
            )
        );

        return $client;
    }

    /**
     * Creates a model from a mock response for use in this test case
     *
     * @param string $responseName The name of the mock response to use
     *
     * @return Desk\Command\AbstractCommand
     */
    private function modelFromMockResponse($responseName)
    {
        $client = $this->client();
        $command = $client->getCommand('myOperation');

        $this->setMockResponse($client, $responseName);
        $model = $command->execute();

        $this->assertInstanceOf('Desk\\Model\\AbstractModel', $model);
        return $model;
    }

    /**
     * Tests a model is created as the result of a command
     */
    public function testFromCommand()
    {
        $model = $this->modelFromMockResponse('testFromCommand');
        $this->assertSame('bar', $model->get('foo'));
        $this->assertSame('qux', $model->get('baz'));
    }

    /**
     * Tests models with relationship links
     */
    public function testLinks()
    {
        $model = $this->modelFromMockResponse('testLinks');

        $link = $model->getLink('myLink');
        $this->assertInstanceOf('Desk\\Command\\AbstractCommand', $link);
        $this->assertSame('myLinkOperation', $link->getName());

        $request = $link->prepare();

        $url = (string) $request->getUrl();
        $this->assertSame('http://mock.localhost/path/to/myLink', $url);
    }

    /**
     * Tests models with relationship links
     */
    public function testEmbed()
    {
        $model = $this->modelFromMockResponse('testEmbed');

        $embed = $model->getEmbed('myLink');
        $this->assertSame('ztesch', $embed->get('bazola'));
        $this->assertSame('grunt', $embed->get('thud'));
    }
}
