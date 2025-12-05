<?php
    include('assets/inc/config.php');
    include('assets/inc/functions.php'); 
if(!empty($_POST["name"])) 
{
     $id=$_POST['name'];
     if($id=='Tab'){
        $query=mysqli_query($mysqli,"SELECT * FROM drug where category='$id' order by name ASC");
        ?>
        <?php

         while($row=mysqli_fetch_array($query))
         {
          ?>
          <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
         <?php
         }
     }else if($id=='Syrup'){
        $query=mysqli_query($mysqli,"SELECT * FROM drug where category='$id' order by name ASC");
        ?>
        <?php
         while($row=mysqli_fetch_array($query))
         {
          ?>
          <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
         <?php
         }
     }else if($id=='Cream'){
        $query=mysqli_query($mysqli,"SELECT * FROM drug where category='$id' order by name ASC");
        ?>
        <?php
         while($row=mysqli_fetch_array($query))
         {
          ?>
          <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
         <?php
         }
     }else if($id=='Injection'){
        $query=mysqli_query($mysqli,"SELECT * FROM drug where category='$id' order by name ASC");
        ?>
        <?php
         while($row=mysqli_fetch_array($query))
         {
          ?>
          <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
         <?php
         }
     }
}
?>