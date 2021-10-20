<?php
    include('param.php');
    if(isset($_POST['userkey'])) {
        setcookie('userkey',$_POST['userkey'],time()+60*60*24*365);
    }
    
    if(isset($_POST['kindvalue'])) {
        setcookie('kindvalue',$_POST['kindvalue'],time()+60*60*24*365);
    }


$counter_file = 'count.txt';
$counter_lenght = 8;
$fp = fopen($counter_file, 'r+');
if ($fp) {
    if (flock($fp, LOCK_EX)) {
        $counter = fgets($fp, $counter_lenght);
        if($_SERVER["REQUEST_METHOD"] === "POST"){

        }else{

            
            $counter++;

            $date=date('Y年m月d日 H時i分s秒');
            mb_language("Japanese");
            mb_internal_encoding("UTF-8");
            if(mb_send_mail($email_to,"media_operate",$_SERVER["REMOTE_ADDR"])){
            } else {
              echo "mail fail";
            };


        }    
        rewind($fp);
        if (fwrite($fp,  $counter) === FALSE) {
            echo ('<p>'.'ファイル書き込みに失敗しました'.'</p>');
        }
        flock ($fp, LOCK_UN);
    }
}
fclose ($fp);

echo '<p>';
echo ('あなたは <em>'.$counter.'</em> 人目の訪問者です。');
echo '</p>';
?>

<?php
    include('param.php');

    try{
        $pdo = new PDO($dsn, $user, $pass);

        $date=date('Y年m月d日 H時i分s秒')."\n";


        //ファイルアップロードがあったとき


        if( isset($_POST['fileact']) && strcmp($_POST['fileact'],"filenoup")==0){
            echo "<script>alert(\"ファイルはアップロードしません\")</script>";
            if (isset($_FILES['upfile']['error']) && is_int($_FILES['upfile']['error'])){

                $linkid = $date.$_POST["linkid"];
                $userkey = $_POST["userkey"];
                $kindvalue = $_POST["kindvalue"];
                if($userkey==""){
                    echo "no input userkey";
                    exit;
    
                }
                if(strlen($userkey) < 6){
                    echo "length must greater than 6";
                    exit;
    
                }
    
                if(strlen($kindvalue) < 1){
                    echo "kindvalue must greater than 8";
                    exit;
    
                }
    
                //画像・動画をバイナリデータにする．
                $raw_data = 1;
    
                //拡張子を見る
                $extension = "jpeg";
    
                $fname = "dummy";
    
                //画像・動画をDBに格納．
                $sql = "INSERT INTO $dbtable(linkid,userkey, kindvalue,fname, extension, raw_data) VALUES (:linkid, :userkey, :kindvalue, :fname, :extension, :raw_data);";
                $stmt = $pdo->prepare($sql);
                $stmt -> bindValue(":linkid",$linkid, PDO::PARAM_STR);
                $stmt -> bindValue(":userkey",$userkey, PDO::PARAM_STR);
                $stmt -> bindValue(":kindvalue",$kindvalue, PDO::PARAM_STR);
                $stmt -> bindValue(":fname",$fname, PDO::PARAM_STR);
                $stmt -> bindValue(":extension",$extension, PDO::PARAM_STR);
                $stmt -> bindValue(":raw_data",$raw_data, PDO::PARAM_STR);
                $stmt -> execute();
    
            }    


        }else{
    
    
    
    
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

                $linkid = $date.$_POST["linkid"];
                $userkey = $_POST["userkey"];
                $kindvalue = $_POST["kindvalue"];
                if($userkey==""){
                    echo "no input userkey";
                    exit;

                }
                if(strlen($userkey) < 6){
                    echo "length must greater than 6";
                    exit;

                }

                if(strlen($kindvalue) < 1){
                    echo "kindvalue must greater than 8";
                    exit;

                }




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
                $sql = "INSERT INTO $dbtable(linkid,userkey, kindvalue,fname, extension, raw_data) VALUES (:linkid, :userkey, :kindvalue, :fname, :extension, :raw_data);";
                $stmt = $pdo->prepare($sql);
                $stmt -> bindValue(":linkid",$linkid, PDO::PARAM_STR);
                $stmt -> bindValue(":userkey",$userkey, PDO::PARAM_STR);
                $stmt -> bindValue(":kindvalue",$kindvalue, PDO::PARAM_STR);
                $stmt -> bindValue(":fname",$fname, PDO::PARAM_STR);
                $stmt -> bindValue(":extension",$extension, PDO::PARAM_STR);
                $stmt -> bindValue(":raw_data",$raw_data, PDO::PARAM_STR);
                $stmt -> execute();

            }
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


<?php



                echo "<div>";
                echo "<div style=\"background-color:#CDF7FF;\" calss=\"size_test\">";
                echo "<form action=\"index.php\" enctype=\"multipart/form-data\" method=\"post\">";

                if($_SERVER["REQUEST_METHOD"] === "POST"){
                    //POST有り
                    $userkey = $_POST["userkey"];
                    $kindvalue = $_POST["kindvalue"];

                    if(strlen($userkey) < 6){
                        echo "length must greater than 6";
                        exit;
                    }
                    

                    if($userkey==""){

                    }else{
                        //userkey有り    
                        echo "<label>画像/動画アップロード</label>";
                        echo "<input type=\"file\" name=\"upfile\"><br>";
                        echo "<textarea name=\"linkid\" rows=\"10\" cols=\"80\" id=\"linkid\" style=\"width:100%\" placeholder=\"写真のコメント\" ></textarea>";
                        echo "<br>";
                        echo "<label>操作：ファイル選択-->アップロード-->実行</label><br>";
                        echo "<input type=\"submit\" value=\"アップロード\">";

                        echo "<select name=\"fileact\" id=\"fileact\">";
                        echo "<option value=\"fileup\">記事と画像アップロードする</option>";
                        echo "<option value=\"filenoup\">記事のみアップロードする</option>";
                        echo "</select></p>";
                        echo "<form>";
                        echo "<action=\"index.php\" enctype=\"multipart/form-data\" method=\"post\">";
                        echo "<input type=\"hidden\" size=30 id=\"userkey\" name=\"userkey\" value=$userkey>";
                        echo "<input type=\"hidden\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
                        echo "<input type=\"text\" size=30 id=\"srchvalue\" name=\"srchvalue\">";
                        echo "<input type=\"submit\" value=\"検索\"></p>";
                        echo "</form>";
                                                


                    }        
                    //POST有り　userkey有り、userkey無し    

                    echo "<action=\"index.php\" enctype=\"multipart/form-data\" method=\"post\">";
                    echo "<input type=\"text\" size=30 id=\"userkey\" name=\"userkey\" value=$userkey>";
                    echo "<input type=\"text\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
                    echo "<input type=\"submit\" value=\"実行\">";
                    echo "</form>";
                    echo "</div>";




                    echo "</div>";
    

                }else{
                    //POST無し
                    echo "<action=\"index.php\" enctype=\"multipart/form-data\" method=\"post\">";
                    echo "<label>写真と文書の記録サイトです</label><br>";
                    if(!empty($_COOKIE['userkey'])){
                        $userkey = $_COOKIE['userkey'];
                        echo "<p>以下の設定は、あなたが過去に設定した値です</p><br>";
                        echo "<input type=\"text\" size=30 id=\"userkey\" name=\"userkey\" value=$userkey>";

                    }else{
                        echo "<input type=\"text\" size=30 id=\"userkey\" name=\"userkey\" placeholder=\"userkey\">";

                    }    
                    if(!empty($_COOKIE['kindvalue'])){
                        $kindvalue = $_COOKIE['kindvalue'];

                        echo "<input type=\"text\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
                    }else{
                        echo "<input type=\"text\" size=30 id=\"kindvalue\" name=\"kindvalue\" placeholder=\"kindvalue\">";

                    }

                    echo "<input type=\"submit\" value=\"実行\">";
                    echo "</form>";
                    echo "</div>";
                    echo "<label>uesrkey:6文字以上の任意文字　　　　kindvalue:1文字以上の任意文字</label><br>";
                    echo "<label>例：写真集　　　　　　　　　　　　　例：フリー素材</label><br>";
                    echo "</div>";

                }
?>
<?php
    //DBから取得して表示する．
    if($_SERVER["REQUEST_METHOD"] === "POST"){

        if(isset($_POST["adddel"])){

            $sql = "DELETE FROM $dbtable WHERE id = '${_POST['adddel']}'" ;
            $stmt = $pdo->prepare($sql);
            $stmt -> execute();
            exit;

        }else if(isset($_POST["delseg"])){

            $sql = "DELETE FROM $dbtable WHERE id = '${_POST['delseg']}'" ;
            $stmt = $pdo->prepare($sql);
            $stmt -> execute();
            exit;
        }else if(isset($_POST["update"])){
            $sql = "UPDATE $dbtable SET linkid='${_POST['linkid']}' WHERE id ='${_POST['update']}'";
            $stmt = $pdo->prepare($sql);
            $stmt -> execute();
            exit;


        }else{
            if(strcmp($_POST['kindvalue'],"*")==0){

                $sql = "SELECT * FROM $dbtable WHERE userkey = '${_POST['userkey']}' " ;

            }else{


                if(isset($_POST["srchvalue"])){   

                    $sql = "SELECT * FROM $dbtable WHERE userkey = '${_POST['userkey']}' AND kindvalue ='${_POST['kindvalue']}' AND linkid LIKE '%" . $_POST["srchvalue"] . "%'" ;


                }else{

                    $sql = "SELECT * FROM $dbtable WHERE userkey = '${_POST['userkey']}' AND kindvalue ='${_POST['kindvalue']}' " ;


                }
            }
            //$sql = "SELECT * FROM$dbtableORDER BY id;";
            $stmt = $pdo->prepare($sql);
            $stmt -> execute();
        
        }
        //$sql = "SELECT * FROM$dbtableWHERE userkey = '${_POST['userkey']}'" ;
        //$sql = "SELECT * FROM$dbtableORDER BY id;";
    //$stmt = $pdo->prepare($sql);
    //$stmt -> execute();
    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)){
        echo "<div style=\"background-color:#CDE7FF;\" calss=\"size_test\">";

        //動画と画像で場合分け
        $target = $row["fname"];
        $linkid = $row["linkid"];
        $userkey = $row["userkey"];
        $id = $row["id"];
        $kindvalue = $row["kindvalue"];


        if($row["extension"] == "mp4"){
            echo "<table border =\"3\">";
            echo "<td>";
            echo ("<video src=\"import_media.php?target=$target\" width=\"426\" height=\"240\" controls></video>");
                    echo ($row["id"]."<br/>");
            echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
            echo "<p><input type=\"hidden\" size=5 id=\"userkey\" name=\"userkey\" value=\"$userkey\">";
            echo "<input type=\"hidden\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
            echo "<p><input type=\"hidden\" size=5 id=\"update\" name=\"update\" value=\"$id\">";
            echo "<textarea name=\"linkid\" rows=\"10\" cols=\"80\"  style=\"width:100%\" id=\"linkid\" >$linkid</textarea>";
            echo "<input type=\"submit\" value=\"更新\" /></p>";
            echo "</form>";
    
            echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
            echo "<p><input type=\"hidden\" size=5 id=\"userkey\" name=\"userkey\" value=\"$userkey\">";
            echo "<input type=\"hidden\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
            echo "<p><input type=\"hidden\" size=5 id=\"delseg\" name=\"delseg\" value=\"$id\">";
            echo "<p>__________________________________________________________";
            echo "<input type=\"submit\" value=\"削除\" /></p>";
            echo "</form>";
        

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
                    if(isset($_POST["srchvalue"])){
                        $srchvalue = $_POST["srchvalue"];

                    }else{
                        $srchvalue = "検索無し";

                    }

                    echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                    echo "<p><input type=\"hidden\" size=5 id=\"update\" name=\"update\" value=\"$id\">";
                    echo "<p><input type=\"hidden\" size=5 id=\"userkey\" name=\"userkey\" value=\"$userkey\">";
                    echo "<input type=\"text\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=\"$kindvalue\" style=\"background-color:#bde9ba\">";
                    echo "<input type=\"text\" size=30 id=\"srchvalue\" name=\"srchvalue\" value=\"$srchvalue\" style=\"background-color:#bde9ba\"></p>";
                    echo "<p><textarea name=\"linkid\" rows=\"10\" cols=\"80\"  style=\"width:100%\" id=\"linkid\" style=\"background-color:#bde9ba\">$linkid</textarea>";
                        echo "<input type=\"submit\" value=\"更新\" /></p>";
                    echo "</form>";
            
                    echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                    echo "<p><input type=\"hidden\" size=5 id=\"delseg\" name=\"delseg\" value=\"$id\">";
                    echo "<input type=\"hidden\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
                    echo "<p><input type=\"hidden\" size=5 id=\"userkey\" name=\"userkey\" value=\"$userkey\">";
                    echo "<p>__________________________________________________________";
                    echo "<input type=\"submit\" value=\"削除\" /></p>";
                    echo "</form>";
                    echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                    echo "<p><input type=\"hidden\" size=5 id=\"selseg\" name=\"selseg\" value=\"$id\">";
                    echo "<p><input type=\"hidden\" size=5 id=\"userkey\" name=\"userkey\" value=\"$userkey\">";
                    echo "<input type=\"hidden\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
                    echo "<input type=\"submit\" value=\"画像拡大\" /></p>";
                    echo "</form>";
            
                                echo ("<img src='import_media.php?target=$target'   width=\"400\" height=\"300\">");


                                echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
 
                                echo "<input type=\"file\" name=\"upfile\"><br>";
                                echo "<p><input type=\"hidden\" size=5 id=\"adddel\" name=\"adddel\" value=\"$id\">";
                                echo "<p><input type=\"hidden\" size=5 id=\"userkey\" name=\"userkey\" value=\"$userkey\">";
                                echo "<input type=\"hidden\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue style=\"background-color:#bde9ba\">";
                                echo "<p><input type=\"hidden\"  id=\"linkid\" name=\"linkid\" value=\"$linkid\">";
                                echo "<br>";
                                echo "<input type=\"submit\" value=\"画像更新\">";
                                echo "</form>";
                

                                echo "</td>";







                }        
            }else{
                echo "<table border =\"3\">";
                echo "<td>";
                        echo ($row["id"]."<br/>");
                echo ("<img src='import_media.php?target=$target'   width=\"400\" height=\"300\">");
                echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                echo "<p><input type=\"hidden\" size=5 id=\"userkey\" name=\"userkey\" value=\"$userkey\">";
                echo "<p><input type=\"hidden\" size=5 id=\"update\" name=\"update\" value=\"$id\">";
                echo "<input type=\"hidden\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
                echo "<textarea name=\"linkid\" rows=\"10\" cols=\"80\"  style=\"width:100%\" id=\"linkid\" >$linkid</textarea>";
                echo "<input type=\"submit\" value=\"更新\" /></p>";
                echo "</form>";
        
                echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                echo "<input type=\"hidden\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
                echo "<p><input type=\"hidden\" size=5 id=\"userkey\" name=\"userkey\" value=\"$userkey\">";
                echo "<p><input type=\"hidden\" size=5 id=\"delseg\" name=\"delseg\" value=\"$id\">";
                echo "<p>__________________________________________________________";
                echo "<input type=\"submit\" value=\"削除\" /></p>";
                echo "</form>";
            
                echo "<form action=\"\" method=\"post\" enctype=\"multipart/form-data\">";
                echo "<p><input type=\"hidden\" size=5 id=\"selseg\" name=\"selseg\" value=\"$id\">";
                echo "<input type=\"hidden\" size=30 id=\"kindvalue\" name=\"kindvalue\" value=$kindvalue>";
                echo "<p><input type=\"hidden\" size=5 id=\"userkey\" name=\"userkey\" value=\"$userkey\">";
                echo "<textarea name=\"userkey\" rows=\"1\" cols=\"20\" id=\"userkey\" placeholder=\"userkey\" ></textarea>";
                echo "<input type=\"submit\" value=\"画像拡大\" /></p>";
                echo "</form>";
        
            }    
            echo "</td>";
            echo "</div>";

        }
    }
}    

?>

</body>
</html>