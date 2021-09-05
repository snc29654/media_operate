<?php
    include('param.php');

    try{
        $pdo = new PDO($dsn, $user, $pass);

        //ファイルアップロードがあったとき
        if (isset($_FILES['upfile']['error']) && is_int($_FILES['upfile']['error']) && $_FILES["upfile"]["name"] !== ""){
            //エラーチェック
            switch ($_FILES['upfile']['error']) {
                case UPLOAD_ERR_OK: // OK
                    break;
                case UPLOAD_ERR_NO_FILE:   // 未選択
                    throw new RuntimeException('ファイルが選択されていません', 400);
                case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
                    throw new RuntimeException('ファイルサイズが大きすぎます', 400);
                default:
                    throw new RuntimeException('その他のエラーが発生しました', 500);
            }

            $linkid = $_POST["linkid"];
            //画像・動画をバイナリデータにする．
            $raw_data = file_get_contents($_FILES['upfile']['tmp_name']);

            //拡張子を見る
            $tmp = pathinfo($_FILES["upfile"]["name"]);
            $extension = $tmp["extension"];
            if($extension === "jpg" || $extension === "jpeg" || $extension === "JPG" || $extension === "JPEG"){
                $extension = "jpeg";
            }
            elseif($extension === "png" || $extension === "PNG"){
                $extension = "png";
            }
            elseif($extension === "gif" || $extension === "GIF"){
                $extension = "gif";
            }
            elseif($extension === "mp4" || $extension === "MP4"){
                $extension = "mp4";
            }
            else{
                echo "非対応ファイルです．<br/>";
                echo ("<a href=\"index.php\">戻る</a><br/>");
                exit(1);
            }

            //DBに格納するファイルネーム設定
            //サーバー側の一時的なファイルネームと取得時刻を結合した文字列にsha256をかける．
            $date = getdate();
            $fname = $_FILES["upfile"]["tmp_name"].$date["year"].$date["mon"].$date["mday"].$date["hours"].$date["minutes"].$date["seconds"];
            $fname = hash("sha256", $fname);

            //画像・動画をDBに格納．
            $sql = "INSERT INTO media(linkid, fname, extension, raw_data) VALUES (:linkid, :fname, :extension, :raw_data);";
            $stmt = $pdo->prepare($sql);
            $stmt -> bindValue(":linkid",$linkid, PDO::PARAM_STR);
            $stmt -> bindValue(":fname",$fname, PDO::PARAM_STR);
            $stmt -> bindValue(":extension",$extension, PDO::PARAM_STR);
            $stmt -> bindValue(":raw_data",$raw_data, PDO::PARAM_STR);
            $stmt -> execute();

        }

    }
    catch(PDOException $e){
        echo("<p>500 Inertnal Server Error</p>");
        exit($e->getMessage());
    }
?>

<!DOCTYPE HTML>

<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>media</title>
    <link rel="stylesheet" href="sample.css" type="text/css">

</head>

<body>

    <form action="" method="post" enctype="multipart/form-data">
    <p><input type="text" size="10" id="username" name="username" value="guest">
    <input type="submit" value="ユーザー名" /></p>
    </form>

    <?php
    if($_SERVER["REQUEST_METHOD"] === "POST"){
        if(isset($_POST["username"])){
            $username = $_POST["username"];
            if(strcmp($username,"admin")==0){    



    echo "<div>";
    echo "<div calss=\"size_test\">";
    echo "<form action=\"index.php\" enctype=\"multipart/form-data\" method=\"post\">";
    echo "<label>画像/動画アップロード</label>";
    echo "<input type=\"file\" name=\"upfile\"><br>";
    echo "<textarea name=\"linkid\" rows=\"10\" cols=\"80\" id=\"linkid\" placeholder=\"写真のコメント\" ></textarea>";
    echo "<br>";
    echo "<label>操作：ファイル選択-->アップロード-->読み出し</label><br>";
    echo "<input type=\"submit\" value=\"アップロード\">";
    echo "<action=\"index.php\" enctype=\"multipart/form-data\" method=\"post\">";
    echo "<input type=\"submit\" value=\"読み出し\">";
    echo "</form>";
    echo "</div>";
    echo "</div>";
    }
    }

    }
    ?>
    <?php
    //DBから取得して表示する．
    if($_SERVER["REQUEST_METHOD"] === "POST"){
        if(isset($_POST["delseg"])){

            $sql = "DELETE FROM media WHERE id = '${_POST['delseg']}'" ;
            $stmt = $pdo->prepare($sql);
            $stmt -> execute();
            exit;
        }else if(isset($_POST["update"])){
            $sql = "UPDATE media SET linkid='${_POST['linkid']}' WHERE id ='${_POST['update']}' ";
            $stmt = $pdo->prepare($sql);
            $stmt -> execute();
            exit;

        }else{
            $sql = "SELECT * FROM media" ;
            //$sql = "SELECT * FROM media ORDER BY id;";
            $stmt = $pdo->prepare($sql);
            $stmt -> execute();
        
        }
                $sql = "SELECT * FROM media" ;
    //$sql = "SELECT * FROM media ORDER BY id;";
    $stmt = $pdo->prepare($sql);
    $stmt -> execute();
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)){

        //動画と画像で場合分け
        $target = $row["fname"];
        $linkid = $row["linkid"];
        $id = $row["id"];
        if($row["extension"] == "mp4"){
            echo ("<video src=\"import_media.php?target=$target\" width=\"426\" height=\"240\" controls></video>");
        }
        elseif($row["extension"] == "jpeg" || $row["extension"] == "png" || $row["extension"] == "gif"){

            if($_SERVER["REQUEST_METHOD"] === "POST"){
                if(isset($_POST['selseg'])){
                    if($_POST['selseg'] ==$id){    
                        echo "<table border =\"3\">";
                        echo "<td>";
                                        echo ($row["id"]."<br/>");
                        echo ("<img src='import_media.php?target=$target'>");
                        exit;
                    }else{
//                        echo ("<img src='import_media.php?target=$target'   width=\"150\" height=\"135\">");
                    }
                }else{
                    echo "<table border =\"3\">";
                    echo "<td>";
                                echo ($row["id"]."<br/>");



                    echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                    echo "<p><input type=\"hidden\" size=5 id=\"update\" name=\"update\" value=\"$id\">";
                    echo "<textarea name=\"linkid\" rows=\"10\" cols=\"80\" id=\"linkid\" >$linkid</textarea>";
                        echo "<input type=\"submit\" value=\"更新\" /></p>";
                    echo "</form>";
            
                    echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                    echo "<p><input type=\"hidden\" size=5 id=\"delseg\" name=\"delseg\" value=\"$id\">";
                        echo "<input type=\"submit\" value=\"削除\" /></p>";
                    echo "</form>";
                    echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                    echo "<p><input type=\"hidden\" size=5 id=\"selseg\" name=\"selseg\" value=\"$id\">";
                    echo "<input type=\"submit\" value=\"画像拡大\" /></p>";
                    echo "</form>";
            
                    echo "</td>";
                                echo ("<img src='import_media.php?target=$target'   width=\"150\" height=\"135\">");
                }        
            }else{
                echo "<table border =\"3\">";
                echo "<td>";
                        echo ($row["id"]."<br/>");
                echo ("<img src='import_media.php?target=$target'   width=\"150\" height=\"135\">");
                echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                echo "<p><input type=\"hidden\" size=5 id=\"update\" name=\"update\" value=\"$id\">";
                echo "<textarea name=\"linkid\" rows=\"10\" cols=\"80\" id=\"linkid\" >$linkid</textarea>";
                echo "<input type=\"submit\" value=\"更新\" /></p>";
                echo "</form>";
        
                echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                echo "<p><input type=\"hidden\" size=5 id=\"delseg\" name=\"delseg\" value=\"$id\">";
                echo "<input type=\"submit\" value=\"削除\" /></p>";
                echo "</form>";
            
                echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                echo "<p><input type=\"hidden\" size=5 id=\"selseg\" name=\"selseg\" value=\"$id\">";
                echo "<input type=\"submit\" value=\"画像拡大\" /></p>";
                echo "</form>";
        
                    }    
                    echo "</td>";

        }
    }
}    

    ?>

</body>
</html>