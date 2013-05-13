<?php

namespace Desk\Model;

use Guzzle\Service\Command\OperationCommand;

interface FactoryInterface
{

    /**
     * Create a response model object from a completed Guzzle command
     *
     * The class to construct should be specified in $className, and
     * should be an instance of Desk\Model\AbstractModel.
     *
     * @param string                                  $className
     * @param Guzzle\Service\Command\OperationCommand $command
     *
     * @return Desk\Model\AbstractModel
     *
     * @throws Desk\Exception\InvalidArgumentException If $className
     * does not represent a subclass of Desk\Model\AbstractModel
     */
    public function fromCommand($className, OperationCommand $command);

    /**
     * Create a response model from raw model data
     *
     * @param string $className Class name of the model to create
     * @param array  $data      The model data (including meta-data)
     *
     * @return Desk\Model\AbstractModel
     *
     * @throws Desk\Exception\InvalidArgumentException If $data isn't
     * in the correct format
     */
    public function fromData($className, array $data);
}
