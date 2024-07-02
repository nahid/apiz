<?php

namespace Apiz\GraphQL;

use Apiz\Constants\GraphQLRequestType;

abstract class AbstractRequest
{
    protected array $variables = [];

    abstract public function query(): string;

    public function getVariables(): array
    {
        return $this->variables;
    }
    public function getType(): string
    {
        $query = trim($this->query());
        $words = explode(' ', $query, 2);
        if (isset($words[0]) && isset($words[1])) {
            $type = strtolower($words[0]);

            if ($type === GraphQLRequestType::QUERY || $type === GraphQLRequestType::MUTATION) {
                return $type;
            }
        }

        throw new \InvalidArgumentException('Invalid query type');
    }

    public function getQuery(): array
    {
        $query = [
            'query' => $this->query(),
        ];

        if (!empty($this->getVariables())) {
            $query['variables'] = $this->getVariables();
        }

        return $query;
    }

    public function setVariables(array $variables): self
    {
        $this->variables = $variables;

        return $this;
    }

    public function __toString()
    {
        return json_encode($this->getQuery());
    }

}
