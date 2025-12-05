<?php
    include('assets/inc/config.php');
     //$doc_id=$_SESSION['doc_id'];

         $date=date('Y-m-d');
          $sql=mysqli_query($mysqli,"delete FROM doc_diagnosis where date='$date'");
          $sql1=mysqli_query($mysqli,"delete FROM doc_procedure where date='$date'");
          echo "";
        
        

?>