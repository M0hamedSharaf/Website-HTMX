<?php

$db_name = 'mysql:host=localhost;dbname=htmx_db';
$user_name = 'root';
$user_password = '';

$db = new PDO($db_name, $user_name, $user_password);

function render_html($id, $title,$content,$image)
{
    echo "<div class='col m-2' id=post-{$id}>
            <div class='card ' style='width: 18rem;'>
                <div class='card-body'>
                    <h5 class='card-title'>{$title}</h5>
                    <p class='card-text'>{$content}</p>
                    <img src='{$image}'>
                    <a href='#' class='btn btn-danger' hx-delete='./api.php?action=delete_post&id={$id}'>
                      Delete Post
                    <a>
                    <a href='#' class='btn btn-success' hx-get='./edit.php?id={$id}&title={$title}&content={$content}&image={$image}' hx-target='#post-{$id}'>Edit Post</a>
                </div>
            </div>
          </div>";
}

function upload_image (){

    $uploadDir = 'uploads/';
    $imageSrc =  $uploadDir . basename($_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'],$imageSrc);

    return $imageSrc;
}
switch ($_GET['action']) {
        case 'add_post':

            $imageSrc = upload_image();

            $sql = $db->prepare("INSERT INTO posts (title, content, image)VALUES (:title, :content, :image)");
            $sql->execute([
                ':title' => $_POST['title'],
                ':content' => $_POST['content'],
                ':image' =>  $imageSrc,
            ]);
            render_html($db->lastInsertId(), $_POST['title'],
            $_POST['content'],$imageSrc);
        break;

        case 'get_posts':
            $sql = $db->query(
                "SELECT * FROM posts"
            );
            $posts = $sql->fetchAll(PDO::FETCH_ASSOC);

            foreach($posts as $post)
            {
                render_html($post["id"], $post['title'],$post['content'],$post['image']);
            }
        break;

        case 'update_post':
            header("HX-Trigger: post_update");
            $id = $_POST["id"];
            $title = $_POST["title"];
            $content = $_POST["content"];
            $imageSrc = upload_image();

            $sql = $db->prepare("UPDATE posts
            SET title = :title, content= :content ,image= :image
            WHERE id = :id LIMIT 1");
             $sql->execute([
                ':id' =>  $id,
                ':title' => $title,
                ':content' => $content,
                ':image' => $imageSrc,
            ]);
        break;


        case 'delete_post':
            header("HX-Trigger: post_deleted");
            $id = $_GET["id"];

            $sql = $db->prepare("DELETE FROM posts WHERE id = :id");
             $sql->execute([
                ':id' =>  $id,
            ]);
        break;

    default:
       echo"Inva action";
        break;
}