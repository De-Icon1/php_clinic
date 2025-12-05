<?php
    include('assets/inc/config.php');
if(!empty($_POST["name"])) 
{
     $id=$_POST['name'];
     if($id=='Card'){
        $query=mysqli_query($mysqli,"SELECT * FROM card order by name ASC");
        ?>
        <?php
         while($row=mysqli_fetch_array($query))
         {
          ?>
          <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
         <?php
         }
     }else if($id=='Laboratory'){
        $query=mysqli_query($mysqli,"SELECT * FROM lab order by id ASC");
        ?>
        <?php
         while($row=mysqli_fetch_array($query))
         {
          ?>
          <option value="<?php echo $row['name']; ?>"><?php echo $row['name']; ?></option>
         <?php
         }
     }else if($id=='Scan'){
        $query=mysqli_query($mysqli,"SELECT * FROM scan order by id ASC");
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