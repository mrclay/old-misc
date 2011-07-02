<?php

/**
 * Allows exporting a single page/post as a JSON.
 * 
 * Setup class autoloading in functions.php, then include this code at the top of
 * page/post/single.php in your theme folder:
 * <code>
 * if (isset($_GET['asJson'])) {
 *     Coewp_JsonApi::sendOne();
 * }
 * </code>
 */
class Coewp_JsonApi {
    public static function sendOne()
    {
        header('Content-Type: text/javascript;charset=utf-8');
        if (! have_posts()) {
            echo "{noContent:1}";
            exit();
        }
        the_post();

        $delim1 = "hc78ocr4hbyu487gffrvuGBgBF%UBobDvc";
        $delim2 = "nd903knfbvgdstalw;";
        ob_start();
        the_title(); echo $delim1;
        the_content(); echo $delim1;
        the_time('U'); echo $delim1;
        the_category($delim2); echo $delim1;
        the_tags('', $delim2, '');
        list(
            $data['title'],
            $data['content'],
            $data['time'],
            $data['categoryLinks'],
            $data['tagLinks']
            ) = explode($delim1, ob_get_clean());
        $data['categoryLinks'] = ($data['categoryLinks'] === '')
            ? array()
            : explode($delim2, $data['categoryLinks']);
        $data['tagLinks'] = ($data['tagLinks'] === '')
            ? array()
            : explode($delim2, $data['tagLinks']);
        echo json_encode($data);
        exit();
    }
}