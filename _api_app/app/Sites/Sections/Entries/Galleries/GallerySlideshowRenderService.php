<?php

namespace App\Sites\Sections\Entries\Galleries;

use App\Shared\Storage;
use App\Shared\Helpers;

class GallerySlideshowRenderService extends EntryGalleryRenderService
{
    public $entry;
    public $siteSettings;
    public $siteTemplateSettings;
    public $storageService;
    public $isEditMode;
    public $isLoopAvailable;
    public $asRowGallery;

    public $galleryItemsData;
    public $galleryItems;

    public function __construct(
        array $entry,
        array $siteSettings,
        array $siteTemplateSettings,
        Storage $storageService,
        $isEditMode,
        $isLoopAvailable = true,
        $asRowGallery = false
    ) {
        $this->entry = $entry;
        $this->siteSettings = $siteSettings;
        $this->siteTemplateSettings = $siteTemplateSettings;
        $this->storageService = $storageService;
        $this->isEditMode = $isEditMode;
        $this->isLoopAvailable = $isLoopAvailable;
        $this->asRowGallery = $asRowGallery;

        parent::__construct();

        $this->galleryItemsData = $this->getGalleryItemsData($this->entry);
        $this->galleryItems = $this->generateGalleryItems($this->galleryItemsData);
    }

    public function getViewData()
    {
        $data = parent::getViewData();
        $data['galleryClassList'] = $this->getGalleryClassList();
        $data['attributes'] = [
            'gallery' => Helpers::arrayToHtmlAttributes([
                'data-fullscreen' => $data['isFullscreen'] ? 1 : null,
                'data-as-row-gallery' => $this->asRowGallery,
                'data-autoplay' => ($this->isLoopAvailable && !empty($this->entry['mediaCacheData']['@attributes']['autoplay'])) ? $this->entry['mediaCacheData']['@attributes']['autoplay'] : '0',
                'data-loop' =>  $this->isLoopAvailable && isset($this->siteSettings['entryLayout']['gallerySlideshowAutoRewind']) && $this->siteSettings['entryLayout']['gallerySlideshowAutoRewind'] == 'yes'
            ])
        ];
        $data['galleryStyles'] = $this->getGalleryStyles();

        $data['items'] = $this->galleryItems;
        $data['showNavigation'] = count($this->galleryItemsData) > 1;

        return $data;
    }

    public function getGalleryClassList()
    {
        $classes = parent::getGalleryClassList();

        if (!empty($this->galleryItemsData)) {
            $gallerySlideNumbersVisible = !empty($this->entry['mediaCacheData']['@attributes']['slide_numbers_visible']) ? $this->entry['mediaCacheData']['@attributes']['slide_numbers_visible'] : $this->siteSettings['entryLayout']['gallerySlideNumberVisibilityDefault'];

            $classes[] = 'xSlideNumbersVisible-' . $gallerySlideNumbersVisible;
        }

        return implode(' ', $classes);
    }

    public function getGalleryStyles()
    {
        $styles = [];

        $galleryWidth = $this->getGalleryWidth();
        if ($galleryWidth) {
            $styles[] = "width: {$galleryWidth}px";
        }

        return implode(';', $styles);
    }

    public function getGalleryWidth()
    {
        if (!$this->galleryItems) {
            return false;
        }

        $template = $this->siteSettings['template']['template'];
        $templateName = explode('-', $template)[0];
        $isMessyTemplate = $templateName == 'messy';
        $galleryWidthByWidestSlide = !empty($this->entry['mediaCacheData']['@attributes']['gallery_width_by_widest_slide']) ? $this->entry['mediaCacheData']['@attributes']['gallery_width_by_widest_slide'] : 'no';

        // Set slideshow gallery width by widest slide
        // except if current template is messy and gallery setting `galleryWidthByWidestSlide` is OFF
        if (!$isMessyTemplate || $isMessyTemplate && $galleryWidthByWidestSlide === 'yes') {
            return max(array_column($this->galleryItems, 'width'));
        }

        return $this->galleryItems[0]['width'];
    }

    public function render()
    {
        if ($this->isEditMode && empty($this->galleryItemsData)) {
            return view('Sites/Sections/Entries/Galleries/editEmptyGallery');
        }

        $data = $this->getViewData();

        return view('Sites/Sections/Entries/Galleries/gallerySlideshow', $data);
    }
}
