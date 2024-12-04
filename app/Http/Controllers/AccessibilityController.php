<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class AccessibilityController extends Controller
{
    public function analyze(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:html,htm|max:2048',
        ]);

        $file = $request->file('file');
        $htmlContent = file_get_contents($file->getRealPath());

        $dom = new \DOMDocument();
        // Suppress warnings caused by invalid HTML structures
        libxml_use_internal_errors(true);
        $dom->loadHTML($htmlContent);
        libxml_clear_errors();

        $crawler = new Crawler($dom);
        $issues = [];

        // Check for missing alt attributes
        $images = $crawler->filter('img:not([alt])');
        foreach ($images as $img) {
            $issues[] = [
                'type' => 'Missing Alt Attribute',
                'element' => $dom->saveHTML($img),
                'suggestion' => 'Add a descriptive alt attribute to the image.',
                'highlight' => $this->getElementXPath($img),
            ];
        }

        // Check for skipped heading levels
        $headings = $crawler->filter('h1, h2, h3, h4, h5, h6');
        $prevLevel = 0;
        foreach ($headings as $heading) {
            $currentLevel = intval(substr($heading->nodeName, 1));
            if ($prevLevel && $currentLevel > $prevLevel + 1) {
                $issues[] = [
                    'type' => 'Skipped Heading Levels',
                    'element' => $dom->saveHTML($heading),
                    'details' => "Skipped from h{$prevLevel} to h{$currentLevel}",
                    'suggestion' => 'Use proper hierarchical structure for headings.',
                    'highlight' => $this->getElementXPath($heading),
                ];
            }
            $prevLevel = $currentLevel;
        }

        // Check for missing lang attribute
        $htmlTag = $crawler->filter('html');
        if ($htmlTag->count() && !$htmlTag->attr('lang')) {
            $issues[] = [
                'type' => 'Missing Language Attribute',
                'element' => '<html>',
                'suggestion' => 'Add a lang attribute to specify the document’s language.',
                'highlight' => $this->getElementXPath($htmlTag),
            ];
        }

        // Check for missing ARIA roles or landmarks
        $landmarks = $crawler->filter('header, footer, main, nav, aside');
        foreach ($landmarks as $landmark) {
            if (!$landmark->hasAttribute('role')) {
                $issues[] = [
                    'type' => 'Missing ARIA Role',
                    'element' => $dom->saveHTML($landmark),
                    'suggestion' => 'Add an appropriate ARIA role to this landmark element.',
                    'highlight' => $this->getElementXPath($landmark),
                ];
            }
        }

        // Check for missing ARIA attributes (aria-labelledby, aria-describedby)
        $elementsWithAria = $crawler->filter('[aria-labelledby]:not([aria-describedby]), [aria-describedby]:not([aria-labelledby])');
        foreach ($elementsWithAria as $element) {
            $issues[] = [
                'type' => 'Missing ARIA Attributes',
                'element' => $dom->saveHTML($element),
                'suggestion' => 'Ensure appropriate use of aria-labelledby and aria-describedby attributes.',
                'highlight' => $this->getElementXPath($element),
            ];
        }

        // Check for low contrast (text and background color)
        $texts = $crawler->filter('p, h1, h2, h3, h4, h5, h6, a, span');
        foreach ($texts as $text) {
            $style = $text->getAttribute('style');
            if (preg_match('/color:\s*([^;]+)/', $style, $matches)) {
                $color = $matches[1];
                // Check the background color if present
                $background = $this->getBackgroundColor($text);
                if ($background && $this->isLowContrast($color, $background)) {
                    $issues[] = [
                        'type' => 'Low Contrast',
                        'element' => $dom->saveHTML($text),
                        'suggestion' => 'Ensure there is sufficient contrast between text and background.',
                        'highlight' => $this->getElementXPath($text),
                    ];
                }
            }
        }

        // Check for missing form labels
        $forms = $crawler->filter('input, select, textarea');
        foreach ($forms as $form) {
            $id = $form->getAttribute('id');
            $label = $crawler->filter('label[for="' . $id . '"]');
            if ($label->count() === 0) {
                $issues[] = [
                    'type' => 'Missing Label',
                    'element' => $dom->saveHTML($form),
                    'suggestion' => 'Add a label with a matching for attribute to the form element.',
                    'highlight' => $this->getElementXPath($form),
                ];
            }
        }

        // Check for empty links and buttons
        $links = $crawler->filter('a');
        foreach ($links as $link) {
            if (empty(trim($link->textContent))) {
                $issues[] = [
                    'type' => 'Empty Link',
                    'element' => $dom->saveHTML($link),
                    'suggestion' => 'Ensure that links have meaningful text content.',
                    'highlight' => $this->getElementXPath($link),
                ];
            }
        }

        $buttons = $crawler->filter('button');
        foreach ($buttons as $button) {
            if (empty(trim($button->textContent))) {
                $issues[] = [
                    'type' => 'Empty Button',
                    'element' => $dom->saveHTML($button),
                    'suggestion' => 'Ensure that buttons have meaningful text content.',
                    'highlight' => $this->getElementXPath($button),
                ];
            }
        }

        // Calculate compliance score
        $complianceScore = max(0, 100 - count($issues) * 5);

        return response()->json([
            'compliance_score' => $complianceScore,
            'issues' => $issues,
        ]);
    }

    /**
     * Get the XPath for a DOMNode.
     */
    private function getElementXPath($element)
    {
        $xpath = '';
        while ($element) {
            $siblingIndex = 1;
            $sibling = $element;
            while ($sibling = $sibling->previousSibling) {
                if ($sibling->nodeName == $element->nodeName) {
                    $siblingIndex++;
                }
            }
            $xpath = '/' . $element->nodeName . '[' . $siblingIndex . ']' . $xpath;
            $element = $element->parentNode;
        }
        return $xpath;
    }

    /**
     * Calculate contrast ratio between two colors.
     */
    private function calculateContrastRatio($color1, $color2)
    {
        $luminance1 = $this->getLuminance($color1);
        $luminance2 = $this->getLuminance($color2);

        $contrastRatio = ($luminance1 + 0.05) / ($luminance2 + 0.05);
        if ($contrastRatio < 1) {
            $contrastRatio = 1 / $contrastRatio;
        }

        return $contrastRatio;
    }

    /**
     * Get the luminance of a color.
     */
    private function getLuminance($color)
    {
        // Assuming color is in hex format
        if (preg_match('/#([a-fA-F0-9]{6})/', $color, $matches)) {
            $rgb = hexdec($matches[1]);
            $r = (($rgb >> 16) & 0xFF) / 255;
            $g = (($rgb >> 8) & 0xFF) / 255;
            $b = ($rgb & 0xFF) / 255;

            $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
            $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
            $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

            return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        }
        return 0;
    }

    /**
     * Check if the contrast between text and background is low.
     */
    private function isLowContrast($textColor, $bgColor)
    {
        $ratio = $this->calculateContrastRatio($textColor, $bgColor);
        return $ratio < 4.5; // WCAG 2.0 standard for normal text
    }

    /**
     * Get the background color of an element.
     */
    private function getBackgroundColor($element)
    {
        // Here we would attempt to get the background color from inline styles or computed styles
        // For simplicity, we will check the inline styles for background-color
        $style = $element->getAttribute('style');
        if (preg_match('/background-color:\s*([^;]+)/', $style, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
