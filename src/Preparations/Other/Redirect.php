<?php

namespace Startselect\Alfred\Preparations\Other;

use Startselect\Alfred\Preparations\AbstractPreparation;

class Redirect extends AbstractPreparation
{
    public const TYPE_AJAX = 'ajax';
    public const TYPE_REGULAR = 'regular';

    public const WINDOW_NEW = 'new';
    public const WINDOW_SAME = 'same';

    protected ?string $url = null;
    protected string $type = self::TYPE_REGULAR;
    protected string $window = self::WINDOW_SAME;

    protected array $returnableProperties = [
        'url',
        'type',
        'window',
    ];

    private array $allowedTypes = [
        self::TYPE_AJAX,
        self::TYPE_REGULAR,
    ];

    private array $allowedWindows = [
        self::WINDOW_NEW,
        self::WINDOW_SAME
    ];

    /**
     * URL to redirect to.
     */
    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * The type of the redirect.
     */
    public function type(string $type): static
    {
        if (in_array($type, $this->allowedTypes)) {
            $this->type = $type;
        }

        return $this;
    }

    /**
     * The window where the redirect will take place.
     */
    public function window(string $window): static
    {
        if (in_array($window, $this->allowedWindows)) {
            $this->window = $window;
        }

        return $this;
    }
}
