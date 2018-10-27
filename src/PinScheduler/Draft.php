<?php

namespace PinScheduler;

class Draft
{
    protected $imageUrl;

    protected $siteUrl;

    protected $description;

    protected $imageWidth;

    protected $imageHeight;

    public function __construct($imageUrl, $siteUrl, $description, $imageWidth, $imageHeight, $boardId = null)
    {
        $this->imageUrl = $imageUrl;
        $this->siteUrl = $siteUrl;
        $this->imageWidth = $imageWidth;
        $this->imageHeight = $imageHeight;
        $this->description = $description;

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
            'image-width' => $this->imageWidth,
            'image-height' => $this->imageHeight,
        ];
    }
}