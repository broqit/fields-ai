<?php

namespace Broqit\FieldsAi\Traits;

use DOMXPath;
use Masterminds\HTML5;

trait CanChunkedHtml
{
    protected function chunkLongText($content): array
    {
        // Розбиваємо по елементах і джоінимо на окремі запити
        $contentElements = $this->gatherParentElements($content);

        $elementsToRewrite = [];
        foreach ($contentElements as $elementKey => $element) {
            $elementsToRewrite[$elementKey] = $element;
        }

        // Розбиваємо елементи на відповідну кількість частин
        $chunkSize = 5;

        // Визначаємо кількість запитів на основі кількості елементів, по 5 елементів на сторінку
        $numberOfQueries = ceil(count($contentElements) / $chunkSize);

        // Ініціалізуємо масив запитів
        $queries = array_fill(0, $numberOfQueries, '');

        for ($i = 0; $i < $numberOfQueries; $i++) {
            $start = $i * $chunkSize;
            $queries[$i] = implode(array_slice($elementsToRewrite, $start, $chunkSize));
        }

        return $queries;
    }

    protected function gatherParentElements($html): array
    {
        $html5 = new HTML5();
        $dom = $html5->loadHTML($html);

        $xpath = new DOMXPath($dom);
        // Вибираємо всі елементи, що знаходяться безпосередньо всередині на рівні 0
        $elements = $xpath->query('/*/*');

        $result = [];

        foreach ($elements as $element) {
            $innerHTML = '';
            foreach ($element->childNodes as $child) {
                $innerHTML .= $dom->saveHTML($child);
            }
            $elementHTML = '<' . $element->tagName;

            // Додаємо атрибути
            foreach ($element->attributes as $attr) {
                if (!in_array($attr->name, ['class', 'id']) && !str_contains($attr->name, 'data-')) {
                    $elementHTML .= ' ' . $attr->name . '="' . $attr->value . '"';
                }
            }

            $elementHTML .= '>' . $innerHTML . '</' . $element->tagName . '>';
            $result[] = $elementHTML;
        }

        return $result;
    }

    public function formated(string $content): string
    {
        $result = trim($content);

        return str_replace(['```html', '```'], '', $result);
    }
}