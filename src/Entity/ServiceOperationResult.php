<?php

namespace App\Entity;

class ServiceOperationResult
{
    private bool $Success;
    private string $Message;
    private ?object $Result;

    public function __construct(bool $Success, string $Message, object $Result=null)
    {
        $this->Success = $Success;
        $this->Message = $Message;
        $this->Result = $Result;
    }

    public function getMessage(): string
    {
        return $this->Message;
    }

    public function getResult(): ?object
    {
        return $this->Result;
    }

    public function isSuccess(): bool
    {
        return $this->Success;
    }

}