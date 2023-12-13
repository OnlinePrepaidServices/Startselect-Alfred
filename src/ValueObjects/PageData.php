<?php

namespace Startselect\Alfred\ValueObjects;

class PageData extends AbstractValueObject
{
    public function getDocument(): Document
    {
        static $document;

        if (!$document) {
            $document = new Document($this->get('document'));
        }

        return $document;
    }

    /**
     * @return array<FocusableField>
     */
    public function getFocusableFields(): array
    {
        static $focusableFields;

        if ($focusableFields === null) {
            $focusableFields = [];

            foreach ($this->get('focusableFields', []) as $focusableField) {
                $focusableFields[] = new FocusableField($focusableField);
            }
        }

        return $focusableFields;
    }

    public function getUrl(): Url
    {
        static $url;

        if (!$url) {
            $url = new Url($this->get('url'));
        }

        return $url;
    }
}
