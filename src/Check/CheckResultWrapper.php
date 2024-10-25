<?php

namespace Mailplug\Health\Check;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Warning;

class CheckResultWrapper
{
    /**
     * success, failure or warning
     */
    private string $status = 'success';

    private ResultInterface $result;

    public function __construct(ResultInterface $result)
    {
        if ($result instanceof Failure) {
            $this->status = 'failure';
        }

        if ($result instanceof Warning) {
            $this->status = 'warning';
        }

        $this->result = $result;
    }

    /**
     * Convert the ResultInterface object to an array
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'message' => $this->result->getMessage(),
            'data' => $this->result->getData(),
        ];
    }
}
