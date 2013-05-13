<?php

namespace Desk\Model;

use Desk\Client;
use Desk\Exception\InvalidArgumentException;
use Desk\Exception\UnexpectedValueException;
use Desk\Model\Factory;
use Desk\Model\HasRelationships;
use Guzzle\Common\Collection;
use Guzzle\Service\Command\OperationCommand;
use Guzzle\Service\Command\ResponseClassInterface;

class AbstractModel extends Collection implements ResponseClassInterface, HasRelationships
{

    /**
     * The model factory that creates instances of this class
     *
     * @var Desk\Model\FactoryInterface
     */
    private static $factory;


    /**
     * Client that created this model
     *
     * @var Desk\Client
     */
    private $client;

    /**
     * Related models that have been linked to from the API response
     *
     * @var array
     */
    private $links = array();

    /**
     * Related models that have been embedded in API response
     *
     * @var array
     */
    private $embeds = array();


    /**
     * Gets the model factory that creates instances of this class
     *
     * @return Desk\Model\FactoryInterface
     */
    public static function getFactory()
    {
        if (!self::$factory) {
            self::$factory = new Factory();
        }

        return self::$factory;
    }

    /**
     * Sets the model factory that creates instances of this class
     *
     * If called with no argument, this will reset the model factory
     * to an instance of the default Desk\Model\Factory.
     *
     * @param Desk\Model\FactoryInterface $factory
     */
    public static function setFactory($factory = null)
    {
        self::$factory = $factory;
    }

    /**
     * {@inheritdoc}
     *
     * @return Desk\Model\AbstractModel
     */
    public static function fromCommand(OperationCommand $command)
    {
        $className = get_called_class();
        return self::getFactory()->fromCommand($className, $command);
    }


    /**
     * {@inheritdoc}
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
    }

    /**
     * Gets the data about related models to this model
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmbeds(array $embeds)
    {
        $this->embeds = $embeds;
    }

    /**
     * Gets the data about models embedded in this model
     *
     * @return array
     */
    public function getEmbeds()
    {
        return $this->embeds;
    }

    /**
     * {@inheritdoc}
     */
    public function getLink($linkName)
    {
        $links = $this->getLinks();
        if (!isset($links[$linkName])) {
            $modelName = get_called_class();
            throw new InvalidArgumentException(
                "Invalid link '$linkName' for model '$modelName'"
            );
        }

        $link = $links[$linkName];

        $command = $this->getClient()->getCommandForDeskClass($link['class']);
        $command->getOperation()->setUri($link['href']);

        return $command;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmbed($embedName)
    {
        $embeds = $this->getEmbeds();
        if (!isset($embeds[$embedName])) {
            $modelName = get_called_class();
            throw new InvalidArgumentException(
                "Invalid embedded link '$embedName' for model '$modelName'"
            );
        }

        $data = $embeds[$embedName];

        $deskClassAvailable =
            isset($data['_links']) &&
            isset($data['_links']['self']) &&
            isset($data['_links']['self']['class']) &&
            is_string($data['_links']['self']['class']);

        if (!$deskClassAvailable) {
            throw new UnexpectedValueException(
                "Embedded model doesn't contain a class, can't " .
                "determine what class to instantiate"
            );
        }

        $deskClass = $data['_links']['self']['class'];
        $className = $this->getClient()
            ->getOperationForDeskClass($deskClass)
            ->getResponseClass();

        $model = self::getFactory()->fromData($className, $data);
        $model->setClient($this->getClient());

        return $model;
    }
}
