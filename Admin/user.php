<?php
session_start();
include 'connect.php';
include 'function.php';
if(isset($_POST['btnadduser']))
{
    adduser();//function call
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <style>
        
.form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    background-color: white;
    padding: 2.5em;
    border-radius: 25px;
    transition: .4s ease-in-out;
    
    align-items: center;
    justify-content: center;
  }
  

  
  .heading {
    color: #0cccee;
    padding-bottom: 2em;
    text-align: center;
    font-weight: bold;
  }
  
  .input {
    border-radius: 5px;
    border: 1px solid whitesmoke;
    background-color: whitesmoke;
    outline: none;
    padding: 0.7em;
    transition: .4s ease-in-out;
    
  }
  
  .input:hover {
    box-shadow: 6px 6px 0px #969696,
               -3px -3px 10px #ffffff;
  }
  
  .input:focus {
    background: #ffffff;
    box-shadow: inset 2px 5px 10px rgba(0,0,0,0.3);
  }
  
  .form .btn {
    margin-top: 2em;
    align-self: center;
    padding: 0.7em;
    padding-left: 1em;
    padding-right: 1em;
    border-radius: 10px;
    border: none;
    background-color: #0cccee;
    color: black;
    transition: .4s ease-in-out;
    box-shadow: rgba(0, 0, 0, 0.4) 1px 1px 1px;
  }
  
  .form .btn:hover {
    box-shadow: 6px 6px 0px #969696,
               -3px -3px 10px #ffffff;
    transform: translateX(-0.5em) translateY(-0.5em);
  }
  
  .form .btn:active {
    transition: .2s;
    transform: translateX(0em) translateY(0em);
    box-shadow: none;
  }
    </style>
</head>
<body>

<?php include 'header.php' ?>
<br>
<div class="container">
<div class="row">
<div class="col-md-12">
<h3>Welcome Admin,
<span class="text-danger">
<?php
                    if(isset($_SESSION['admin']))
                    {
                        echo $_SESSION['admin'];
                    }
                    else
                    {
                        $_SESSION['admin']='';
                    }
                    ?>     
</span>
</h3>
</div>
</div>
<section>
<form class="form" method="post">
    <h3 class="heading">Add User</h3>
    <input type="text" name="username" placeholder="Username" id="" class="input">
    <input type="email" name="email" placeholder="Enter your email" id="" required class="input">
    <input type="password" name="password" class="input" placeholder="Password"> 
    <input type="password" name="cpassword" class="input" placeholder="Confirm password">
    <div> 
    <label class="input">User Role :&nbsp;&nbsp;  &nbsp;     
   
      <select name="role" id="" class="input" style="color: #0cccee;">               
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select></label>
    </div> 

    <button class="btn" name="btnadduser">Adduser</button>
</form>
</section>

    </body>
</html>
  
