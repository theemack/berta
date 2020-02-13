<?php

namespace App\Sites\Sections;

use App\Shared\Helpers;

class SectionsMenuRenderService
{
    private $site;
    private $sections;
    private $sectionSlug;
    private $siteSettings;
    private $siteTemplateSettings;
    private $sectionTags;
    private $tagSlug;
    private $isEditMode;
    private $isResponsive;
    private $templateName;

    public function __construct(
        $site,
        array $sections,
        $sectionSlug,
        array $siteSettings,
        array $siteTemplateSettings,
        array $sectionTags,
        $tagSlug,
        $isEditMode
    ) {
        $this->site = $site;
        $this->sections = $sections;
        $this->sectionSlug = $sectionSlug;
        $this->siteSettings = $siteSettings;
        $this->siteTemplateSettings = $siteTemplateSettings;
        $this->sectionTags = $sectionTags;
        $this->tagSlug = $tagSlug;
        $this->isEditMode = $isEditMode;
        $this->templateName = explode('-', $this->siteSettings['template']['template'])[0];
    }

    private function getTags()
    {
        $tags = array_filter($this->sectionTags['section'], function ($section) {
            return !empty($section['tag']);
        });

        $tags = array_reduce($tags, function ($sections, $section) {
            // @TODO Add url for each tag
            $sections[$section['@attributes']['name']] = array_map(function ($tag) {
                return [
                    'title' => $tag['@value'],
                    'url' => $tag['@attributes']['name']
                ];
            }, $section['tag']);
            return $sections;
        }, []);

        return $tags;
    }

    private function getViewData()
    {
        $sections = $this->sections;
        $tags = $this->getTags();
        $submenu = [];

        // Filter sections
        $sections = array_filter($sections, function ($section) {
            $isEmptyTitle = empty($section['title']);
            $isCartSection = isset($section['@attributes']['type']) && $section['@attributes']['type'] == 'shopping_cart';
            return !$isEmptyTitle && !$isCartSection;
        });

        if (!$this->isEditMode) {
            // Remove unpublished sections from public page
            $sections = array_filter($sections, function ($section) {
                return $section['@attributes']['published'] == '1';
            });

            // Show menu in first section?
            if ($this->siteSettings['navigation']['landingSectionMenuVisible'] == 'no' && !empty($sections) && current($sections)['name'] == $this->sectionSlug) {
                $sections = [];
            }

            // Is first section visible in menu?
            // Hide except if there is tags
            if ($this->siteSettings['navigation']['landingSectionVisible'] == 'no' && !empty($sections)) {
                $firstSectionSlug = current($sections)['name'];

                if (empty($tags[$firstSectionSlug])) {
                    array_shift($sections);
                }
            }
        }

        $currentSectionOrder = array_search($this->sectionSlug, array_column($sections, 'name'));
        $currentSection = $sections[$currentSectionOrder];
        $currentSectionType = isset($currentSection['@attributes']['type']) ? $currentSection['@attributes']['type'] : null;
        $isResponsiveTemplate = isset($this->siteTemplateSettings['pageLayout']['responsive']) && $this->siteTemplateSettings['pageLayout']['responsive'] == 'yes';
        $this->isResponsive = $currentSectionType == 'portfolio' || $isResponsiveTemplate;

        $sections = array_map(function ($section) use ($tags, $isResponsiveTemplate) {
            // @todo Add url to section

            $section['attributes'] = Helpers::arrayToHtmlAttributes([
                'class' => $this->getSectionClassList($section),
                'data-path' => $this->isEditMode && !$this->isResponsive ? $this->site . '/section/' . $section['order'] . '/positionXY' : ''
            ]);

            $section['tags'] = !empty($tags[$section['name']]) ? $tags[$section['name']] : [];

            switch ($this->templateName) {
                case 'messy':
                    $section['tags'] = array_filter($section['tags'], function ($tag) use ($section) {
                        if ($this->siteTemplateSettings['tagsMenu']['hidden'] == 'yes') {
                            return false;
                        }

                        if (!$this->isResponsive && $this->siteTemplateSettings['tagsMenu']['alwaysOpen'] != 'yes' && $this->sectionSlug != $section['name']) {
                            return false;
                        }

                        return true;
                    });
                    break;

                case 'white':
                    $section['tags'] = array_filter($section['tags'], function ($tag) use ($section) {
                        if ($this->sectionSlug != $section['name']) {
                            return false;
                        }

                        return true;
                    });
                    break;
                case 'default':
                    $section['tags'] = array_filter($section['tags'], function ($tag) use ($isResponsiveTemplate) {
                        return $isResponsiveTemplate;
                    });
                    break;
            }

            return $section;
        }, $sections);

        // @todo
        // settings.navigation.alwaysSelectTag - check this when building tag link

        // Separate submenu for `default` template
        if ($this->templateName == 'default' && isset($tags[$this->sectionSlug])) {
            $submenu = $tags[$this->sectionSlug];
        }

        return [
            'sections' => $sections,
            'submenu' => $submenu
        ];
    }

    private function getSectionClassList($section)
    {
        $classList = [];

        if ($section['name'] == $this->sectionSlug) {
            $classList[] = 'selected';
        }

        if ($this->templateName == 'messy') {
            $classList[] = 'xSection-' . $section['name'];

            if ($this->siteTemplateSettings['menu']['position'] == 'fixed') {
                $classList[] = 'xFixed';
            }

            if (!$this->isResponsive) {
                $classList = array_merge($classList, ['mess', 'xEditableDragXY', 'xProperty-positionXY']);
            }
        }

        return implode(' ', $classList);
    }

    public function render()
    {
        $data = $this->getViewData();
        if (empty($data['sections'])) {
            return '';
        }

        return view('Sites/Sections/sectionsMenu', $data);
    }
}
