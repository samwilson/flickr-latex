\documentclass{book}
\usepackage[a4paper]{geometry}
\usepackage{grffile}
\usepackage[T1]{fontenc}
\usepackage{float}

\usepackage{hyperref}
\hypersetup{
  colorlinks = true,  % Colours links instead of ugly boxes
  urlcolor   = black,  % Colour for external hyperlinks
  linkcolor  = black, % Colour of internal links
  citecolor  = black  % Colour of citations
}

\usepackage{chngcntr}
\counterwithout{figure}{chapter}

\usepackage{makeidx}
\makeindex

\usepackage{figsize}
\usepackage[margin=10pt,font=small,labelfont=bf,labelsep=period]{caption}
\renewcommand{\figurename}{}
\SetFigLayout[3]{2}{1}
\renewcommand{\listfigurename}{Contents}
\title{<?= $title ?>}
\date{}
\begin{document}
\maketitle
\frontmatter
\listoffigures
\mainmatter
\chapter{Photographs}
<?php
$img_count = 0;
foreach ($photoData as $photo)
{
    //$date = date('g:iA l, F j Y', strtotime($photo['date_taken']));
    $date = flickrDate($photo['date_taken'], $photo['granularity']);
    $title = texEsc($photo['title']);
    echo '\begin{figure}'."\n"
        .'  \begin{center}'."\n"
        .'  \includegraphics{'.$dataDir.'/photos/'.$photo['id'].'/medium.jpg}'."\n"
        .'  \caption['.$date.': '.$title.']{'.$date.': \\textbf{'.$title.'}'."\n";
    echo '    '.texEsc($photo['description']);
    if (count($tags = $photo['tags']) > 0)
    {
        $tag_links = array();
        foreach($tags as $tag)
        {
            $t = texEsc($tag['raw']);
            $tag_links[] = '\index{'.$t.'} \textsc{'.$t.'}';
        }
        echo '    {\small '.join(', ', $tag_links)."}\n";
    }
    echo '    \hfill {\tiny \href{https://www.flickr.com/photos/'.$photo['user_id'].'/'.$photo['id'].'}{'.$photo['id'].'}}'."\n";
    echo "  } % End caption\n"
        ."  \\end{center}\n"
        ."\\end{figure}\n\n";
    if ($img_count>0 && $img_count%12==0) echo '\clearpage';
    $img_count++;
}
?>

\printindex

\end{document}
