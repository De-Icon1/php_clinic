<?php
    include('assets/inc/config.php');
     //$doc_id=$_SESSION['doc_id'];

         $date=date('Y-m-d');

            $result=mysqli_query($mysqli,"select * from doc_procedure where date='$date'");
            $reply = mysqli_fetch_array($result);
                $amnt=$reply['Total'];
                $bal=$amnt;
    
           echo $bal;
      
?>