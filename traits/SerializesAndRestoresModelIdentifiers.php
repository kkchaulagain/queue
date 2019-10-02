<?php


trait SerializesAndRestoresModelIdentifiers
{
    /**
     * Get the property value prepared for serialization.
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getSerializedPropertyValue($value)
    {
        return $value;
    }
}