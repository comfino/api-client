<?php

declare(strict_types=1);

namespace Comfino;

trait ReflectionTrait
{
    public function getConstantFromObject(object $object, string $name): mixed
    {
        return (new \ReflectionObject($object))->getConstant($name);
    }

    /**
     * @throws \ReflectionException
     */
    public function getConstantFromClass(string $class, string $name): mixed
    {
        return (new \ReflectionClass($class))->getConstant($name);
    }

    /**
     * @throws \ReflectionException
     */
    public function getPropertyValue(object $object, string $propertyName): mixed
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @throws \ReflectionException
     */
    public function getMethodResult(object $object, string $methodName, array $arguments = []): mixed
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arguments);
    }
}
