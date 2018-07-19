<h1 align="center">
  <br>
  <img src="http://dev.webwork.fr/videa/git_logo2.png" width="400px">
  <br>
  <br>
</h1>
<h4 align="center">Video Sharing platform and more</h4>

**VideaCMS** is **Content Management System** created for "videos makers" the main purpose is to provide a place where you can reunite your community and share content with lot of features.
<br><br>
<img src="http://dev.webwork.fr/videa/home.png">
<br>
<br>
## How to install ?

Before installing you need **PHP 7.2**, **FFMpeg** and **Composer**

### Clone the repository

`git clone https://github.com/Kodmit/VideaCMS.git`

### Run composer
`composer update`

### Configure the database
First, ensure you have a MySQL Server running on your computer.   

Edit `.env` with your database credentials.

After, run   

`php bin/console doctrine:schema:update --force`

### Start the server

Enter the command  

`php bin/console server:run` and you can now access the website via http://localhost:8000
