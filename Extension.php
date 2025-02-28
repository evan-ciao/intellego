<?php

require_once 'Parsedown.php';

class Extension extends Parsedown
{
    function __construct()
    {
        // blocks
        $this->BlockTypes['{'][] = 'MetaData';
        $this->BlockTypes['{'][] = 'References';
        
        // inline
        $this->InlineTypes['$'][]= 'Math';
        $this->inlineMarkerList .= '$';
    }

    protected function inlineMath($excerpt)
    {
        if (preg_match('/(?!.*\$\$)\$ (.+) \$/', $excerpt['text'], $matches))
        {
            return array(
                // How many characters to advance the Parsedown's
                // cursor after being done processing this tag.
                'extent' => strlen($matches[0]), 
                'element' => array(
                    'name' => 'span',
                    'text' => '\(' . $matches[1] . '\)',
                    'attributes' => array(
                        'class' => 'katex',
                    ),
                ),

            );
        }
    }

    protected function blockReferences($line, $block)
    {
        if (preg_match('/^{references}/', $line['text']) || preg_match('/^{related}/', $line['text']))
        {
            return array(
                'element' => array(
                    'name' => 'span',
                    'text' => '',
                    'attributes' => array(
                        'class' => 'page-references',
                    ),
                    'elements' => array(
                        'h4' => array(
                            'name' => 'h4',
                            'text' => 'related',
                        ),
                        'references' => array(
                            'name' => 'ul',
                            'elements' => array(),
                        ),
                    ),
                ),
            );
        }
    }

    protected function blockReferencesContinue($line, $block)
    {
        if (isset($block['complete']))
            return;

        // end found
        if (preg_match('/^{end}/', $line['text']))
        {
            $block['complete'] = true;
            return $block;
        }

        // link found
        if (preg_match('/^\[(.+)\]\((.+)\)$/', $line['text'], $matches))
        {
            $block['element']['elements']['references']['elements'][] = array(
                'name' => 'a',
                'text' => $matches[1],
                'attributes' => array(
                    'href' => $matches[2],
                ),
            );
        }

        return $block;
    }

    protected function blockReferencesComplete($block)
    {
        return $block;
    }

    protected function blockMetaData($line, $block)
    {
        if (preg_match('/^{metadata}/', $line['text']))
        {
            return array(
                'element' => array(
                    'name' => 'span',
                    'text' => '',
                    'attributes' => array(
                        'class' => 'page-metadata',
                    ),
                    'elements' => array(
                        'div' => array(
                            'name' =>'div',
                            'elements' => array(
                                'title' => array(
                                    'name' => 'p',
                                    'text' => '',
                                ),
                                'date' => array(
                                    'name' => 'p',
                                    'text' => '-----',
                                ),
                            ),
                            'attributes' => array(
                                'style' => 'height: 20px;',
                            ),
                        ),
                        'tags' => array(
                            'name' => 'ul',
                            'elements' => array(),
                        ),
                        'ruler' => array(
                            'name' => 'hr',
                        ),
                    )
                ),
            );
        }
    }

    protected function blockMetaDataContinue($line, $block)
    {
        if (isset($block['complete']))
            return;
        
        // end found
        if (preg_match('/^{end}/', $line['text']))
        {
            $block['complete'] = true;
            return $block;
        }
        
        // title
        if (preg_match('/^title:\s*(.+)$/', $line['text'], $matches))
        {
            $block['element']['elements']['div']['elements']['title']['text'] = $matches[1];
            $block['element']['elements']['div']['elements']['title']['attributes'] = array('class' => 'page-title');
        }
        // date
        if (preg_match('/^date:\s*(.+)$/', $line['text'], $matches))
        {
            $block['element']['elements']['div']['elements']['date']['text'] = $matches[1];
            $block['element']['elements']['div']['elements']['date']['attributes'] = array('class' => 'page-date');
        }
        // tags
        if (preg_match('/^tags:\s*\[(.+)\]$/', $line['text'], $matches))
        {
            $tags = explode(', ', $matches[1]);

            foreach ($tags as $tag) {
                // assign custom css to tag type
                $tagcss = "";
                switch ($tag) {
                    // growth
                    case 'seed':
                        $tagcss = "tag-seed";
                        break;
                    case 'fern':
                        $tagcss = "tag-fern";
                        break;
                    case 'evergreen':
                        $tagcss = "tag-evergreen";
                        break;
                    // special
                    case 'index':
                        $tagcss = "tag-index";
                        break;
                    case 'glossary':
                        $tagcss = "tag-glossary";
                        break;
                }

                $block['element']['elements']['tags']['elements'][] = array(
                    'name' => 'li',
                    'text' => $tag,
                    'attributes' => array(
                        'class' => $tagcss,
                    ),
                );
                
            }
            $block['element']['elements']['tags']['attributes'] = array('class' => 'page-tags');
        }

        return $block;
    }

    protected function blockMetaDataComplete($block)
    {
        return $block;
    }
}