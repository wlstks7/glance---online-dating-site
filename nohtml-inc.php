<?php
/**
 * Escape all HTML, JavaScript, and CSS
 * 
 * @param string $input The input string
 * @param string $encoding Which character encoding are we using?
 * @return string
 */

function noHTML($input, $encoding = 'UTF-8'){
	
    return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, $encoding);
}

// echo '<h2 title="', noHTML($title), '">', $articleTitle, '</h2>', "\n";
// echo noHTML($some_data), "\n";

?>