<?php
    include('assets/inc/config.php');
     //$doc_id=$_SESSION['doc_id'];
if(!empty($_POST["name"])) 
{
         $id=$_POST['name'];
         $date=date('Y-m-d');
          $sql=mysqli_query($mysqli,"SELECT * FROM doc_diagnosis where date='$date'");
          $row=mysqli_fetch_array($sql);
         $nm=mysqli_num_rows($sql);
        
         if($nm > 0){
            $diag=$row['diagnosis'];
            $diagnosis=$diag.",".$id;
            $query=mysqli_query($mysqli,"update doc_diagnosis set diagnosis='$diagnosis' where date='$date'");
            echo $diagnosis;
         }else{
            $query=mysqli_query($mysqli,"insert into doc_diagnosis values(0,'$date','','$id')");
            echo $id;
         }
        
    
}
?>