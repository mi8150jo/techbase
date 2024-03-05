<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title></title>
</head>
<body>
    
    <?php
    
    // DB接続設定
    $dsn = '*';
    $user = '*';
    $password = '*';
    
    // PHP Data Objects(PDO)を使用して接続
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // deleteフォームの処理
        try{
            if (!empty($_POST['delete']) && !empty($_POST['delete-password'])) {
                // 変数の宣言
                $delete = $_POST['delete'];
                $password = $_POST['delete-password'];
                
                $sql = 'SELECT password FROM board WHERE col=:col';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':col', $delete, PDO::PARAM_INT);
                $stmt->execute();
                $results = $stmt->fetch();
         
                if($results['password'] == $password) {
                    // 削除する処理
                    $sql = 'DELETE FROM board WHERE col=:col';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':col', $delete, PDO::PARAM_INT);
                    $stmt->execute();
                    echo $delete."行目を削除しました"."<br>";
                    // colの連続性を維持する処理
                    $sql = 'UPDATE board SET col = col - 1 WHERE col > :col';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':col', $delete, PDO::PARAM_INT);
                    $stmt->execute();
                } else {
                    throw new Exception("パスワードが一致しません");
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "<br>";
        }
        
        // Editフォームの処理
        try {
            if (!empty($_POST['edit']) && !empty($_POST['edit-password'])) {
                $edit = $_POST['edit'];
                $password = $_POST['edit-password'];
                
                $sql = 'SELECT col, name, comment, password FROM board WHERE col=:col';
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':col', $edit, PDO::PARAM_INT);
                $stmt->execute();
                $results = $stmt->fetch();
                
                if ($results){
                    if ($results['password'] == $password){
                        $hide_edit_data = $results['col'];
                        $name_data = $results['name'];
                        $comment_data = $results['comment'];
                        $password_data = $results['password'];
                    } else {
                        throw new Exception("パスワードが一致しません");
                    }
                } else {
                    throw new Exception("指定された行が見つかりません");
                }
        
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "<br>";
        }
        
        // 編集フォームの処理 editlineが含まれているとき
        if(!empty($_POST['editline']) && !empty($_POST['name']) && !empty($_POST['comment']) && !empty($_POST['comment-password'])){
            // 変数の宣言
            $editline = $_POST['editline'];
            $name = $_POST['name'];
            $comment = $_POST['comment'];
            $password = $_POST['comment-password'];
            
            $sql = 'UPDATE board SET name=:name, comment=:comment, password=:password WHERE col=:col';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->bindParam(':col', $editline, PDO::PARAM_INT);
            $stmt->execute();
            
            echo "編集しました"."<br>";
            
        // 投稿フォームの処理
        } elseif(!empty($_POST['comment']) && !empty($_POST['name']) && !empty($_POST['comment-password'])) {
            // 変数の宣言
            $comment = $_POST['comment'];
            $name = $_POST['name'];
            $password = $_POST['comment-password'];
            // 日付の取得
            $date = date("Y/m/d H:i:s");
            
            // テーブルのデータ件数を取得
            $sql = "SELECT COUNT(*) FROM board";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            
            // 表示する行を計算
            if($result == 0){
                $count = 1;
            } else {
                $count = $result+1;
            }
            
            // テーブルにデータを追加
            $sql = "INSERT INTO board (col, name, comment, post_date, password) VALUES (:col, :name, :comment, :post_date, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':col', $count, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':post_date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();
    
            // コメントが完成のとき
            if($comment == "完成"){
                echo "おめでとう"."<br>";
            } else {
                echo $comment . "(送信内容)を受け付けました"."<br>";
            }
        } 
    }
    
    // 出力
    $sql = 'SELECT * FROM board';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['col'].',';
        echo $row['name'].',';
        echo $row['comment'].",";
        // echo $row['password'].",";
        echo $row['post_date'].'<br>';
    echo "<hr>";
    }
    
    ?>

    <!--editフォーム-->
    <form action="" method="post">
        <input type="text" name="edit" placeholder="編集番号">
        <input type="text" name="edit-password" placeholder="パスワード">
        <input type="submit" name="submit">
    </form>
    
    <!--commentフォーム-->
    <form action="" method="post">
        <input type="text" name="editline" value="<?php echo isset($hide_edit_data) ? $hide_edit_data : ''; ?>" hidden=True>
        <input type="text" name="name" placeholder="名前" value="<?php echo isset($name_data) ? $name_data : ''; ?>">
        <input type="text" name="comment" placeholder="コメント" value="<?php echo isset($comment_data) ? $comment_data : ""; ?>">
        <input type="text" name="comment-password" placeholder="パスワード" value="<?php echo isset($password_data) ? $password_data : ""; ?>">
        <input type="submit" name="submit">
    </form>
    
    <!--deleteフォーム-->
    <form action="" method="post">
        <input type="text" name="delete" placeholder="削除番号">
        <input type="text" name="delete-password" placeholder="パスワード">
        <input type="submit" name="submit">
    </form>
    
</body>
</html>