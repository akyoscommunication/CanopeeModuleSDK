<?php

namespace Akyos\CanopeeModuleSDK\Class;

abstract class AbstractQuery implements QueryInterface
{
    protected ?string $resource = null;

    protected mixed $data = null;

    protected ?string $method = null;

    protected ?int $items = null;
    protected ?int $page = 1;

    protected array $queryParams = [];
    protected array $pathParams = [];

    protected array $body = [];
    protected array $headers = [];
    protected mixed $results = null;

    public function processData($data): void
    {
        $this->setResults($data);
        if(property_exists($data, 'hydra:member')){
            $this->data = $data->{'hydra:member'};
            $this->items = $data->{'hydra:totalItems'};
        } else {
            $this->data = $data;
            $this->items = 1;
        }

        $this->onSetData();
    }


    // GETTER SETTER

    public function getResults(): mixed
    {
        return $this->results;
    }

    public function setResults(mixed $results): self
    {
        $this->results = $results;

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(?string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getItems(): ?int
    {
        return $this->items;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): self
    {
        $this->page = $page;

        return $this;
    }

    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function setQueryParams(array $queryParams): self
    {
        $this->queryParams = $queryParams;

        return $this;
    }

    public function getPathParams(): array
    {
        return $this->pathParams;
    }

    public function setPathParams(array $pathParams): self
    {
        $this->pathParams = $pathParams;

        return $this;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function setBody(array $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }
}