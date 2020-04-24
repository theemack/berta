<?php

namespace App\Sites\Sections;

use App\Shared\Helpers;

class SectionBackgroundGalleryRenderService
{
    private $USED_IN_TEMPLATES = ['messy'];

    private function getWrapperAttributes($currentSection, $isEditMode)
    {
        $attributes['id'] = 'xBackground';
        $classes = [
            'xBgDataAutoplay-' . (!empty($currentSection['mediaCacheData']['@attributes']['autoplay']) ? $currentSection['mediaCacheData']['@attributes']['autoplay'] : ''),
            'xBgDataImageSize-' . (!empty($currentSection['mediaCacheData']['@attributes']['image_size']) ? $currentSection['mediaCacheData']['@attributes']['image_size'] : ''),
            'xBgDataFading-' . (!$isEditMode && !empty($currentSection['mediaCacheData']['@attributes']['fade_content']) ? $currentSection['mediaCacheData']['@attributes']['fade_content'] : ''),
            'xBgDataAnimation-' . (!empty($currentSection['mediaCacheData']['@attributes']['animation']) ? $currentSection['mediaCacheData']['@attributes']['animation'] : ''),
        ];

        $attributes['class'] = implode(' ', $classes);

        if (!empty($currentSection['sectionBgColor'])) {
            $attributes['style'] = "background-color: {$currentSection['sectionBgColor']}";
        }

        return Helpers::arrayToHtmlAttributes($attributes);
    }

    private function getGalleryItems($currentSection, $storageService, $selectedGridImage)
    {
        $items = [];
        $files = $currentSection['mediaCacheData']['file'];
        $currentItemIndex = 0;
        $selectedGridImageFound = false;

        if (!$selectedGridImage) {
            $itemIndex = array_search($selectedGridImage, array_column(array_column($files, '@attributes'), 'src'));
            if ($itemIndex !== false) {
                $currentItemIndex = $itemIndex;
                $selectedGridImageFound = true;
            }
        }

        foreach ($currentSection['mediaCacheData']['file'] as $index => $file) {
            $captionClass = [];
            $imageClass = [];
            if ($index === $currentItemIndex) {
                if (!empty($file['@value']) && !$selectedGridImageFound) {
                    $captionClass['class'] = 'sel';
                } else {
                    $imageClass['class'] = 'sel';
                }
            }

            $captionStyles = [];
            if (!empty($currentSection['mediaCacheData']['@attributes']['caption_bg_color'])) {
                $captionStyles[] = "background-color: rgba({$currentSection['mediaCacheData']['@attributes']['caption_bg_color']}, 0.5)";
            }
            if (!empty($currentSection['mediaCacheData']['@attributes']['caption_color'])) {
                $captionStyles[] = "color: {$currentSection['mediaCacheData']['@attributes']['caption_color']}";
            }
            $captionStyle['style'] = implode(';', $captionStyles);

            $items[] = [
                'caption' => !$selectedGridImageFound ? $file['@value'] : '',
                'captionClass' => Helpers::arrayToHtmlAttributes($captionClass),
                'imageClass' => Helpers::arrayToHtmlAttributes($imageClass),
                'captionStyles' => Helpers::arrayToHtmlAttributes($captionStyle),
                'image' => $file['@attributes']['src'],
                'src' => $storageService->MEDIA_URL . '/' . $currentSection['mediafolder'] . '/_bg_' . $file['@attributes']['src'],
                'width' => $file['@attributes']['width'],
                'height' => $file['@attributes']['height'],
            ];
        }

        $current = $items[$currentItemIndex];

        return [
            'all' => $items,
            'current' => $current
        ];
    }

    private function getViewData(
        $storageService,
        $siteSettings,
        $sectionSlug,
        $sections,
        $currentSection,
        $selectedGridImage,
        $isEditMode
    ) {
        $wrapperAttributes = $this->getWrapperAttributes($currentSection, $isEditMode);
        $items = $this->getGalleryItems($currentSection, $storageService, $selectedGridImage);

        return [
            'wrapperAttributes' => $wrapperAttributes,
            'items' => $items
        ];
    }

    public function render(
        $storageService,
        $siteSettings,
        $sectionSlug,
        $sections,
        $request,
        $isEditMode
    ) {
        $templateName = explode('-', $siteSettings['template']['template'])[0];

        if (!in_array($templateName, $this->USED_IN_TEMPLATES)) {
            return '';
        }

        $currentSection = null;
        $currentSectionType = null;

        if (!empty($sections)) {
            $currentSectionOrder = array_search($sectionSlug, array_column($sections, 'name'));
            $currentSection = $sections[$currentSectionOrder];
            $currentSectionType = isset($currentSection['@attributes']['type']) ? $currentSection['@attributes']['type'] : null;
        }

        if (!$currentSection || empty($currentSection['mediaCacheData']['file'])) {
            return '';
        }

        $isGridViewEnabled = $currentSectionType == 'grid' && $request->cookie('_berta_grid_view');
        if ($isGridViewEnabled) {
            return '';
        }

        $selectedGridImage = $request->cookie('_berta_grid_img_link');

        $data = $this->getViewData(
            $storageService,
            $siteSettings,
            $sectionSlug,
            $sections,
            $currentSection,
            $selectedGridImage,
            $isEditMode
        );

        return view('Sites/Sections/sectionBackgroundGallery', $data);
    }
}
