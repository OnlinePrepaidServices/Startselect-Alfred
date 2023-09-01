<?php

namespace Startselect\Alfred\ValueObjects;

class Url extends AbstractValueObject
{
    public function getPath(): string
    {
        return $this->get('path');
    }

    public function getHash(): string
    {
        return $this->get('hash') ?? '';
    }

    public function getQuery(): array
    {
        if (!$this->get('query')) {
            return [];
        }

        parse_str(str_replace('?', '', $this->get('query')), $queryParts);

        return $queryParts ?: [];
    }

    public function getFullUrl(bool $includeHash = false): string
    {
        $pageUrl = $this->getPath();

        // Add query parts?
        if ($query = $this->getQuery()) {
            $pageUrl .= '?' . http_build_query($query);
        }

        // Add hash?
        if ($includeHash && $hash = $this->getHash()) {
            $pageUrl .= $hash;
        }

        return $pageUrl;
    }
}
