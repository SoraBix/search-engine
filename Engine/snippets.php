<?php
// include "simple_html_dom.php";

function generateSnippet($url, $query)
{
    $firstHit = INF;
    $currentHit = 0;
    $max = 0;
    $position = -1;

    $new_url = "/home/student/Documents/HTML_files/" . substr($url, 37);

    $terms = explode(" ", $query);
    $sentences = explode('.', file_get_html($new_url)->plaintext);

    foreach($sentences as $index => $sentence)
    {
        if(preg_match("/\b$query\b/i", $sentence))
        {
            return snippet($sentence, $query, 1, stripos($sentence, $query));
        }

        $hit = 0;

        foreach($terms as $term)
        {
            if(preg_match("/\b$term\b/i", $sentence))
            {
                $currentHit = stripos($sentence, $term);

                if($firstHit > $currentHit)
                {
                    $firstHit = $currentHit;
                }

                $hit++;
            }
        }

        if($hit == count($terms))
        {
            return snippet($sentence, $query, 2, $firstHit);
        }
        else if($hit > $max)
        {
            $max = $hit;
            $position = $index;
        }
    }
    return ($position == -1) ? "N/A" : snippet($sentences[$position], $query, 3, $firstHit);
}

function snippet($sentence, $query, $case, $position)
{
    $snippet = ($position > 130) ? "..." . substr($sentence, strpos($sentence ," ", $position - 130)) : $sentence;

    if($case == 1)
    {
        $snippet = preg_replace("/\b$query\b/i", "<b>$query</b>", $snippet);
    }
    else
    {
        $terms = explode(" ", $query);
        
        foreach($terms as $term)
        {
            $snippet = preg_replace("/\b$term\b/i", "<b>$term</b>", $snippet);
        }
    }
    $snippetLength = 160;
    return (strlen($snippet) > $snippetLength) ? substr($snippet, 0, $snippetLength - 3) . "..." : $snippet;
}
?>