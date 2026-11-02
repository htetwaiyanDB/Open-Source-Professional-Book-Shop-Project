<?php
require_once dirname(__DIR__) . '/Admin/connect.php';
?>
<style>
footer{
    width: 100%;
    background: #eaeaea;
    height: 100%;
}

footer .footer_main{
    width: min(100% - 32px, 1240px);
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-around;
    gap: 24px;
}

footer .footer_main .tag{
    margin: 10px 0;
    min-width: 0;
}

footer .footer_main .tag img{
    width: min(300px, 100%);
    margin-bottom: 10px;
    margin-left: 0;
}

footer .footer_main .tag h2,h4 {
    font-family :  'Poppins', sans-serif;
}

footer .footer_main .tag p{
    width: min(250px, 100%);
    line-height: 22px;
    text-align: justify;
}

footer .footer_main .tag h1{
    font-size: 25px;
    margin: 25px 0;
    color: #000;
}

footer .footer_main .tag a{
    display: block;
    color: black;
    text-decoration: none;
    margin: 10px 0;
}

footer .footer_main .tag i{
    margin-right: 10px;
}

footer .footer_main .tag .social_link i{
    margin: 0 5px;
}

footer .footer_main .tag .search_bar{
    width: 230px;
    height: 30px;
    background: rgba(202,202,202);
    border-radius: 25px;
}

footer .footer_main .tag .search_bar input{
    width: 200px;
    padding: 2px 0;
    position: relative;
    top: 17%;
    left: 6%;
    border: none;
    outline: none;
    font-size: 13px;
    background: none;
}

footer .footer_main .tag .search_bar button{
    padding: 7px 15px;
    background: #089da1;
    border: none;
    margin-top: 15px;
    border-radius: 25px;
    color: #fff;
    cursor: pointer;
}

footer .social_link {
    display: flex;
    gap: 8px;
}

footer .social_link a {
    display: inline-flex !important;
    width: 44px;
    height: 44px;
    align-items: center;
    justify-content: center;
    margin: 0 !important;
    border-radius: 50%;
}

@media (max-width: 767.98px) {
    footer .footer_main {
        width: calc(100% - 32px);
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        justify-content: stretch;
    }

    footer .footer_main .tag:first-child {
        grid-column: 1 / -1;
        text-align: center;
    }
}

@media (max-width: 480px) {
    footer .footer_main {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    footer .footer_main .tag:first-child {
        grid-column: auto;
    }

    footer .footer_main .tag h1 {
        margin-block: 16px 10px;
    }
}
</style>
<footer>
    <div class="footer_main">
  
        <div class="tag">
            <img src="../Photos/logo4.png" alt="Book Palace">
            
        </div>
  
        <div class="tag">
            <h1>Quick Link</h1>
            <a href="home.php">Home</a>
            <a href="userindex.php">All Books</a>
            <a href="bestseller.php">Bestseller</a>
            <a href="newrelease.php">Newrelease</a>
            <a href="contact.php">Contact us</a>
            <a href="login.php">Account</a>
            
        </div>
  
        <div class="tag">
            <h1>Contact Info</h1>
            <a href="#"><i class="fa-solid fa-phone"></i>+95 01 123 456 789</a>
            <a href="#"><i class="fa-solid fa-phone"></i>+95 09 123 456 789</a>
            <a href="#"><i class="fa-solid fa-envelope"></i>bookpalace@gmail.com</a>
            
        </div>
  
        <div class="tag">
            <h1>Follow Us</h1>
            <div class="social_link">
                <a href="../Bookpalace/facebook login.html"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="../Bookpalace/instagram.html"><i class="fa-brands fa-instagram"></i></a>
                <a href="../Bookpalace/twitter.html"><i class="fa-brands fa-twitter"></i></a>
            </div>
            
        </div>        
        
    </div>

  </footer>
