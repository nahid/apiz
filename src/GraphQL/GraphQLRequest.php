<?php

declare(strict_types=1);

namespace Apiz\GraphQL;

class GraphQLRequest extends AbstractRequest
{
    protected string $query;
    protected array $variables;

    public function __construct(string $query, array $variables = [])
    {
        $this->query = $query;
        $this->variables = $variables;
    }

    public function query(): string
    {
        return $this->query;
    }

    public function variables(): array
    {
        return $this->variables;
    }
}
