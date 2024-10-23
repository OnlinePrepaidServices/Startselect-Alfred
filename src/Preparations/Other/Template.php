<?php

namespace Startselect\Alfred\Preparations\Other;

use Illuminate\Support\Facades\View;
use Startselect\Alfred\Preparations\AbstractPreparation;

class Template extends AbstractPreparation
{
    protected string $html = '';

    protected array $returnableProperties = [
        'html',
    ];

    /**
     * Which blade file will be rendered.
     */
    public function render(string $path, array $data = []): self
    {
        try {
            $this->html = View::make($path, $data)->render();
        } catch (\Throwable) {
            $this->html = 'Could not render template: ' . $path;
        }

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'html' => $this->html,
        ]);
    }
}
