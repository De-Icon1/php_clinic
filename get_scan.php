<?php
    include('assets/inc/config.php');
     //  $doc_number = $_SESSION['doc_number'];
   if(!empty($_POST["name"])) 
   {
        $id=$_POST['name'];
        echo $id;

           /*$query=mysqli_query($mysqli,"SELECT * FROM scan where name='$id'");
            $row=mysqli_fetch_array($query);
            $nm=mysqli_num_rows($query);
            if($nm > 0){
               $amnt=$row['amount'];
               $sql="insert into pcart values(0,'$date','$id','$qnt','$amnt')";
               $sq=mysqli_query($mysqli,$sql); 
            }*/


   }
?>