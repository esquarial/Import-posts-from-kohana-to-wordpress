<?php

public function importDB()
{
    ini_set('max_execution_time', 300);

    $pdoKohana = new PDO('mysql:host=localhost;dbname=kohana;encoding=utf8', 'root', '');
    $pdoKohana->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoKohana->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdoKohana->exec("set names utf8");

    $newsKoh = $pdoKohana->query('SELECT * FROM news');
    $newsKoh->execute();

    // Tablica z newsami
    $newsKoh = $newsKoh->fetchAll();

    $posts = [];
    $doc = new DOMDocument();

    foreach ($newsKoh as $key => $article) {
        // get post date
        $time = date_create($article['date']);
        $time = $time->format('Y/m');

        // filter contents
        $doc->loadHTML($article['short_post']);

        $xpath = new DOMXPath($doc);
        $src = $xpath->evaluate("string(//img/@src)");

        $posts[$key] = $post = [
            'post_author' => 1,
            'post_date' => $article['date'],
            'post_date_gmt' => $article['date'],
            'post_content' => $article['short_post'],
            'post_title' => $article['title'],
            'post_excerpt' => '',
            'post_status' => 'publish',
            'comment_status' => 'open',
            'ping_status' => 'open',
            'post_password' => '',
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $article['date'],
            'post_modified_gmt' => $article['date'],
            'post_content_filtered' => '',
            'post_parent' => 0,
            'menu_order' => 0,
            'post_type' => 'post',
            'post_mime_type' => '',
            'comment_count' => 0,
            'post_category' => [],
        ];

        require_once(ABSPATH . 'wp-admin/includes/post.php');
        if (post_exists($article['title'], $article['short_post'])) {
            continue;
        }

        if ($src) {
            $contentFile = file_get_contents($src);
            $targetPath = wp_upload_dir($time)['path'] . '/' . pathinfo($src)['basename'];
            file_put_contents($targetPath, $contentFile);

            $post['post_content'] = str_replace(pathinfo($src)['dirname'], wp_upload_dir($time)['url'], $article['short_post']);
        }

        $parent = wp_insert_post($post);

        if (isset($article['img1'])) {
            try {
                $fileName = $article['img1'];
                $filetype = wp_check_filetype($fileName, null);

                $filePath = wp_upload_dir()['basedir'] . '/blog/' . $fileName;
                $file = file_get_contents($filePath);

                $targetUrl = wp_upload_dir($time)['url'] . '/' . $fileName;
                $targetPath = wp_upload_dir($time)['path'] . '/' . $fileName;

                // upload image
                file_put_contents($targetPath, $file);

                $attachment = array(
                    'guid' => $targetUrl,
                    'post_mime_type' => $filetype['type'],
                    'post_title' => $fileName,
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $attach_id = wp_insert_attachment($attachment, $targetUrl, $parent);

                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $targetPath);
                wp_update_attachment_metadata($attach_id, $attach_data);

                set_post_thumbnail($parent, $attach_id);

            } catch (Exception $e) {
                var_dump($e);
            }
        };
    }
}

?>