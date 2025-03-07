<?php

namespace Startselect\Alfred\Http\Requests;

use Illuminate\Http\Request;
use Startselect\Alfred\ValueObjects\AlfredData;
use Startselect\Alfred\ValueObjects\PageData;

class AlfredRequest extends Request
{
    public function getAlfredData(): AlfredData
    {
        return new AlfredData($this->get('alfred', []));
    }

    public function getPageData(): PageData
    {
        return new PageData($this->get('page', []));
    }
}