<?php
    include('assets/inc/config.php');
     //$doc_id=$_SESSION['doc_id'];
if(!empty($_POST["name"])) 
{
         $id=$_POST['name'];
         $date=date('Y-m-d');
          $sql=mysqli_query($mysqli,"SELECT * FROM doc_procedure where date='$date'");
          $row=mysqli_fetch_array($sql);
         $nm=mysqli_num_rows($sql);
         $amnt=gettotPro($mysqli,$id);
        
         if($nm > 0){
            $diag=$row['procedures'];
            $procedure=$diag.",".$id;
            $ptot=gettotPros($mysqli,$date);
            $tot=$ptot + $amnt;
            $query=mysqli_query($mysqli,"update doc_procedure set procedures='$procedure',Total='$tot' where date='$date'");
            echo $procedure;
         }else{
            $query=mysqli_query($mysqli,"insert into doc_procedure values(0,'$date','','$id','$amnt')");
            echo $id;
         }
        
    
}
function gettotPro($mysqli,$name){
            $bal=0;
            $result=mysqli_query($mysqli,"select * from procedures where name='$name'");
            while($reply = mysqli_fetch_array($result)){
                $amnt=$reply['amount'];
                $bal=$amnt;
            }
            return $bal;
        }

        function gettotPros($mysqli,$date){
            $bal=0;
            $result=mysqli_query($mysqli,"select * from doc_procedure where date='$date' ");
            while($reply = mysqli_fetch_array($result)){
                $amnt=$reply['Total'];
                $bal=$amnt;
            }
            return $bal;
        }
?>