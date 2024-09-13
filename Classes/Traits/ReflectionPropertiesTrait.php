<?php
namespace OliverKroener\Helpers\Traits;

trait ReflectionPropertiesTrait
{
    /**
     * Magic method to dynamically handle getters and setters using reflection.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, array $arguments)
    {
        if (preg_match('/^get(.+)$/', $name, $matches)) {
            $property = lcfirst($matches[1]);
            return $this->getProperty($property);
        } elseif (preg_match('/^set(.+)$/', $name, $matches)) {
            $property = lcfirst($matches[1]);
            return $this->setProperty($property, $arguments[0]);
        }

        throw new \Exception("Method $name not found");
    }

    /**
     * Get the value of a property using reflection.
     *
     * @param string $property
     * @return mixed
     * @throws \Exception
     */
    private function getProperty(string $property)
    {
        $reflector = new \ReflectionClass($this);
        if (!$reflector->hasProperty($property)) {
            throw new \Exception("Property $property does not exist");
        }

        $propertyReflector = $reflector->getProperty($property);
        $propertyReflector->setAccessible(true);

        return $propertyReflector->getValue($this);
    }

    /**
     * Set the value of a property using reflection.
     *
     * @param string $property
     * @param mixed $value
     * @return self
     * @throws \Exception
     */
    private function setProperty(string $property, $value)
    {
        $reflector = new \ReflectionClass($this);
        if (!$reflector->hasProperty($property)) {
            throw new \Exception("Property $property does not exist");
        }

        $propertyReflector = $reflector->getProperty($property);
        $propertyReflector->setAccessible(true);
        $propertyReflector->setValue($this, $value);

        return $this;
    }
}
