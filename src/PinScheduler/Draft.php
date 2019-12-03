<?php

namespace PinScheduler;

class Draft
{
    protected $imageUrl;

    protected $siteUrl;

    protected $description;

    protected $imageWidth;

    protected $imageHeight;

    protected $title;

    public function __construct($imageUrl, $siteUrl, $description, $imageWidth, $imageHeight, $boardId = null, $title = null)
    {
        $this->imageUrl = $imageUrl;
        $this->siteUrl = $siteUrl;
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;
        $this->description = $description;
        $this->title = $title ?? $description;

        if ($boardId) {
            $this->description .= " {".$boardId."}";
        }
    }

    public function toApiObject()
    {
        return (object) [
            'image-url' => $this->imageUrl,
            'site-url' => $this->siteUrl,
            'description' => $this->description,
            'title' => $this->title,
            'image-width' => $this->imageWidth,
            'image-height' => $this->imageHeight,
        ];
    }
}