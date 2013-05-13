<?php

namespace Desk\Model;

use Desk\Exception\InvalidArgumentException;
use Desk\Model\AbstractModel;
use Desk\Model\FactoryInterface;
use Desk\Model\HasRelationships;
use Guzzle\Service\Command\OperationCommand;
use ReflectionClass;

class Factory implements FactoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function fromCommand($className, OperationCommand $command)
    {
        $data = $command->getResponse()->json();
        $model = $this->fromData($className, $data);
        $model->setClient($command->getClient());
        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function fromData($className, array $data)
    {
        $modelData = $this->getModelData($data);
        $model = $this->createClass($className, array($modelData));

        if ($model instanceof HasRelationships) {
            $this->addRelationships($model, $data);
        }

        return $model;
    }

    /**
     * Creates an instance of a class
     *
     * Optionally, constructor arguments may be passed in as an array
     * using the second parameter to this function. The first element
     * in the array will be the first constructor argument, the second
     * element will be the second argument, etc.
     *
     * @param string $className The class to construct
     * @param array  $arguments Optional constructor arguments
     *
     * @return mixed
     */
    public function createClass($className, $arguments = array())
    {
        $class = new ReflectionClass($className);
        return $class->newInstanceArgs($arguments);
    }

    /**
     * Gets model data without metadata (e.g. relationships)
     *
     * This method assumes that all meta-data exists in any top-level
     * keys which begin with an underscore ("_"). Effectively, it
     * filters out any top-level keys beginning with an underscore.
     *
     * @param array $data The raw data from the API response
     *
     * @return array
     */
    public function getModelData(array $data)
    {
        $result = array();

        foreach ($data as $key => $value) {
            if (strpos($key, '_') !== 0) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Adds relationships to a model from API response data
     *
     * The model will be modified directly (not returned from this
     * function).
     *
     * @param Desk\Model\HasRelationships $model The model
     * @param array                       $data  Response data from API
     */
    public function addRelationships(HasRelationships &$model, array $data)
    {
        if (isset($data['_links'])) {
            $model->setLinks($data['_links']);
        }

        if (isset($data['_embedded'])) {
            $model->setEmbeds($data['_embedded']);
        }
    }
}
