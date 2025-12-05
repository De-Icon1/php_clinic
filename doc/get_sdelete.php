<?php
    include('assets/inc/config.php');
     //$doc_id=$_SESSION['doc_id'];

         $date=date('Y-m-d');
          $sql2=mysqli_query($mysqli,"delete FROM doc_scanlab");
           $sql2=mysqli_query($mysqli,"delete FROM doc_lab");
          echo "";
        
        

?>