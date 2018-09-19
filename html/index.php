<?php
/*
 * This script:
 * 1. looks for the reportX.md files in the parent directory,
 * 2. finds the highest numbered file
 * 3. parses a subset of markdown and outputs it in a report template
 * 4. saves to html/reportX.html for posterity
 * 5. and renders to the browser for easy copy/pasting
 */
$tac = 'Admiral Pickled Yoda';

// 1. look for files
$mdDir = dirname(dirname(__FILE__)) . '/';
$htmlDir = $mdDir . 'html/';

// 2. find the most recent
$max = 0;
$maxPath = '';
foreach (scandir($mdDir) as $file) {
    if (substr($file, -3, 3) !== '.md') {
        continue;
    }
    $path = $mdDir . $file;
    $num = str_replace(['report', '.md'], '', $file);
    $max = max($num, $max);
    if ($max == $num) {
        $maxPath = $path;
    }
}

// output template and functions
$header = <<<HEAD
<body style='width: 100%; background: black;'>
<table cellspacing="0" cellpadding="10" style="max-width:700px; width: 100%; margin: auto; border: 1px solid #FEC701;">
HEAD;
$html = $header;

// headings start with #
function headingRow($content)
{
    $row = <<<ROW
    <tr style="background: #5F4B8B;">
        <td style="font-family: 'Verdana'; font-size: 18px; color:#ffffff; text-align: center; font-weight: bold;">$content</td>
    </tr>
ROW;
    return $row;
}

function contentRow($content)
{
    $row = <<<ROW
    <tr style="background: #000000;">
        <td style="font-family: 'Verdana'; font-size: 13px; color:#fff;">$content</td>
    </tr>
ROW;
    return $row;
}

function contentHTML($content)
{
    //spacer - a line with just .
    if ($content === '.') {
        return "<p>&nbsp;</p>";
    }
    //italics - _around some text_
    $content = preg_replace("/(_(.*)_)/U", "<em>\$2</em>", $content);
    //highlights - `like so`
    $highlightSpan = "<span style='color: #FEC701;'>";
    $content = preg_replace("/(`(.*)`)/U", "$highlightSpan\$2</span>", $content);
    //links - [link](url)
    $link = "<a style='color: #BFD58E;' href=";
    $content = preg_replace("/\[(.*)\]\((.*)\)/U", "$link'\$2'>\$1</a>", $content);

    return "<p>$content</p>";
}

$reportNum = 'TAC Report #' . $max;
$html .= headingRow($reportNum);
$date = date("j M Y");
$byLine = "<strong>REPORTING OFFICER</strong>: $tac, Tactical Officer<br /><strong>DATE SUBMITTED</strong>: $date";
$html .= contentRow($byLine);

// 3. parse the markdown
$md = file_get_contents($maxPath);
$lines = explode("\n", $md);
$c = count($lines);
for ($i = 0; $i < $c; $i++) {
    $line = trim($lines[$i]);
    if (substr($line, 0, 2) === '# ') {
        $html .= headingRow(trim(substr($line, 2)));
    } elseif (!$line) {
        // blank line do nothing
    } else {
        $block = contentHTML(trim($line));
        $next = $lines[$i + 1];
        while (substr($next, 0, 2) !== '# ' && ($i + 1) < $c) {
            $n = $i + 1;
            if ($next) {
                $block .= contentHTML($next);
            }
            $i++;
            if ($i + 1 < $c) {
                $next = trim($lines[$i + 1]);
            }
        }
        $i--;
        $html .= contentRow($block);
    }
}

$footer = <<<FOOT
</table>
</body>
FOOT;
$html .= $footer;

// 4. save to file
file_put_contents("{$htmlDir}report{$max}.html", $html);
// 5. output to browser
echo $html;
