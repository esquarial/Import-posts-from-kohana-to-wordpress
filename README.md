### Import-posts-from-kohana-to-wordpress

This is a simple script which i wrote in order to import posts from kohana framework into wordpress

If your kohana app is posts based and you wanna switch to wordpress which (in my opinion ) is way more user friendly than kohana :)
all you need to do is to change db column names for your kohana db table column names :

####In $article array
    'post_date' => $article['date'],
    'post_date_gmt' => $article['date'],
    'post_content' => $article['short_post'],
    'post_title' => $article['title'],
    
It will also upload pics into proper wordpress folder like so

    if ($src) {
        $contentFile = file_get_contents($src);
        $targetPath = wp_upload_dir($time)['path'] . '/' . pathinfo($src)['basename'];
        file_put_contents($targetPath, $contentFile);
    
        $post['post_content'] = str_replace(pathinfo($src)['dirname'], wp_upload_dir($time)['url'], $article['short_post']);
    }

And when you're done with all customizing work then you need to run this script in wordpress functions.php file 
and that's it! Enjoy :)