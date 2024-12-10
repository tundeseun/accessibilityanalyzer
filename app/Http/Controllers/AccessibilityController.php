<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;

class AccessibilityController extends Controller
{
    /**
     * Analyze uploaded HTML file for detailed accessibility issues.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:html,htm|max:2048',
        ]);

        $htmlContent = file_get_contents($request->file('file')->getRealPath());
        $crawler = new Crawler($htmlContent);

        $issues = $this->checkAccessibilityIssues($crawler);

        $complianceScore = max(0, 100 - count($issues) * 2); // Deduct 2 points per issue.

        return response()->json([
            'compliance_score' => $complianceScore,
            'issues' => $issues,
        ]);
    }

    /**
     * Check for various accessibility issues.
     *
     * @param  Crawler  $crawler
     * @return array
     */
    private function checkAccessibilityIssues(Crawler $crawler)
    {
        $issues = [];

        $issues = array_merge($issues, $this->checkMissingAltAttributes($crawler));
        $issues = array_merge($issues, $this->checkSkippedHeadings($crawler));
        $issues = array_merge($issues, $this->checkMissingLangAttribute($crawler));
        $issues = array_merge($issues, $this->checkEmptyLinksOrButtons($crawler));
        $issues = array_merge($issues, $this->checkDuplicateIds($crawler));
        $issues = array_merge($issues, $this->checkFormAccessibility($crawler));

        return $issues;
    }

    /**
     * Check for images missing alt attributes.
     */
    private function checkMissingAltAttributes(Crawler $crawler)
    {
        $issues = [];
        $crawler->filter('img:not([alt])')->each(function (Crawler $node) use (&$issues) {
            $element = $node->getNode(0);
            $issues[] = [
                'type' => 'Missing Alt Attribute',
                'element' => $element->ownerDocument->saveHTML($element),
                'suggestion' => 'Add a descriptive alt attribute to the image.',
            ];
        });
        return $issues;
    }

    /**
     * Check for skipped heading levels.
     */
    private function checkSkippedHeadings(Crawler $crawler)
    {
        $issues = [];
        $headings = $crawler->filter('h1, h2, h3, h4, h5, h6');
        $lastLevel = 0;

        foreach ($headings as $heading) {
            $currentLevel = intval(substr($heading->nodeName, 1));
            if ($currentLevel > $lastLevel + 1) {
                $issues[] = [
                    'type' => 'Skipped Heading Level',
                    'element' => $heading->ownerDocument->saveHTML($heading),
                    'suggestion' => "Ensure heading levels follow a logical order.",
                ];
            }
            $lastLevel = $currentLevel;
        }
        return $issues;
    }

    /**
     * Check for missing lang attribute on the HTML tag.
     */
    private function checkMissingLangAttribute(Crawler $crawler)
    {
        $issues = [];
        $html = $crawler->filter('html');

        if ($html->count() && !$html->attr('lang')) {
            $issues[] = [
                'type' => 'Missing Lang Attribute',
                'element' => '<html>',
                'suggestion' => "Add a lang attribute to the <html> tag for language identification.",
            ];
        }
        return $issues;
    }

    /**
     * Check for empty links or buttons.
     */
    private function checkEmptyLinksOrButtons(Crawler $crawler)
    {
        $issues = [];
        $crawler->filter('a, button')->reduce(function (Crawler $node) {
            return trim($node->text()) === '' && !$node->attr('aria-label');
        })->each(function (Crawler $node) use (&$issues) {
            $element = $node->getNode(0);
            $issues[] = [
                'type' => 'Empty Link or Button',
                'element' => $element->ownerDocument->saveHTML($element),
                'suggestion' => 'Provide content or aria-label for meaningful interaction.',
            ];
        });
        return $issues;
    }

    /**
     * Check for duplicate IDs.
     */
    private function checkDuplicateIds(Crawler $crawler)
    {
        $issues = [];
        $ids = [];

        $crawler->filter('[id]')->each(function (Crawler $node) use (&$issues, &$ids) {
            $element = $node->getNode(0);
            $id = $element->getAttribute('id');
            if (isset($ids[$id])) {
                $issues[] = [
                    'type' => 'Duplicate ID',
                    'element' => $element->ownerDocument->saveHTML($element),
                    'suggestion' => "Ensure IDs are unique within the document.",
                ];
            }
            $ids[$id] = true;
        });

        return $issues;
    }

    /**
     * Check for form accessibility issues (missing labels, mismatched `for` attributes).
     */
    private function checkFormAccessibility(Crawler $crawler)
    {
        $issues = [];
        $crawler->filter('input:not([type="hidden"]), textarea, select')->each(function (Crawler $node) use (&$issues) {
            $element = $node->getNode(0);
            $id = $element->getAttribute('id');
            $labels = $node->parents()->filter("label[for='{$id}']");

            if (!$id || $labels->count() === 0) {
                $issues[] = [
                    'type' => 'Missing Form Label',
                    'element' => $element->ownerDocument->saveHTML($element),
                    'suggestion' => "Add a <label> element with a matching for attribute.",
                ];
            }
        });
        return $issues;
    }
}
