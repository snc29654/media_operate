<?php
include('param.php');


try{
    

    $pdo = new PDO(
        $dsn, 
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("create table if not exists $dbtable(
        id int not null auto_increment,
        linkid TEXT,
        fname TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , 
        extension TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , 
        raw_data LONGBLOB NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
        )");
  

    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->query("SET NAMES UTF8;");


}catch(PDOException $Exception){
    die('接続できません：' .$Exception->getMessage());
}
?>