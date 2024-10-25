<?php

namespace Mailplug\Health\Client;

class Components
{
    public string $status;

    public array $components;

    public function __construct(int $status, array $components)
    {
        $this->status = $this->convertStatus($status);
        $this->components = $components;
    }

    private function convertStatus(int $status): string
    {
        return $status === 200 ? 'UP' : 'DOWN';
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getComponents(): array
    {
        return $this->components;
    }
}
