<?php
    include('assets/inc/config.php');
     //  $doc_number = $_SESSION['doc_number'];
   if(!empty($_POST["cart"])) 
   {
        $id=$_POST['cart'];
        $date=date('Y-m-d');

           $query=mysqli_query($mysqli,"SELECT * FROM card where name='$id'");
            $row=mysqli_fetch_array($query);
            $nm=mysqli_num_rows($query);
            if($nm>0){
               $amnt=$row['amount'];
               $sql="insert into cart values(0,'$date','$id','$amnt','')";
              $sq=mysqli_query($mysqli,$sql); 
            }

            $query2=mysqli_query($mysqli,"SELECT * FROM lab where name='$id'");
            $row2=mysqli_fetch_array($query2);
             $nm2=mysqli_num_rows($query2);
            if($nm2>0){
               $amnt2=$row2['amount'];
               $sql2="insert into cart values(0,'$date','$id','$amnt2','')";
              $sq2=mysqli_query($mysqli,$sql2); 
            }


             $query3=mysqli_query($mysqli,"SELECT * FROM scan where name='$id'");
            $row3=mysqli_fetch_array($query3);
             $nm3=mysqli_num_rows($query3);
            if($nm3>0){
               $amnts=$row3['amount'];
               $sql3="insert into cart values(0,'$date','$id','$amnts','')";
              $sq3=mysqli_query($mysqli,$sql3); 
            }
        
   }
?>