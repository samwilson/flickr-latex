<?php use Samwilson\FlickrLatex\Latex; ?>
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
\title{<?php echo $title ?>}
\date{}
\begin{document}
\maketitle
\frontmatter
\listoffigures
\mainmatter
\chapter{Photographs}
<?php
$img_count = 0;
foreach ($photoData as $photo) {
    $title = Latex::texEsc($photo['title']);
    echo '\begin{figure}'."\n"
         .'  \begin{center}'."\n"
         .'  \includegraphics{'.$dataDir.'/photos/'.$photo['id'].'/medium.jpg}'."\n"
         .'  \caption['.$photo['date_taken'].': '.$title.']'
         .'{'.$photo['date_taken'].': \\textbf{'.$title.'}'."\n";
    echo '    ' . Latex::texEsc($photo['description']);
    if (count($tags = $photo['tags']) > 0) {
        $tag_links = array();
        foreach ($tags as $tag) {
            $t = Latex::texEsc($tag);
            $tag_links[] = '\index{'.$t.'} \textsc{'.$t.'}';
        }
        echo '    {\small '.join(', ', $tag_links)."}\n";
    }
    $url = 'https://www.flickr.com/photos/'.$photo['user_id'].'/'.$photo['id'];
    echo '    \hfill {\tiny \href{'.$url.'}{'.$photo['id'].'}}'."\n";
    echo "  } % End caption\n"
        ."  \\end{center}\n"
        ."\\end{figure}\n\n";
    if ($img_count>0 && $img_count%12==0) {
        echo '\clearpage';
    }
    $img_count++;
}
?>

\printindex

\end{document}
