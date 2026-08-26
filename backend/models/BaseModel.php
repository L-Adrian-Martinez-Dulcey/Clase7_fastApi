<?php

class BaseModel
{
    protected array $attributes = [];

    public function __construct(array $data = [])
    {
        $this->attributes = $data;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
