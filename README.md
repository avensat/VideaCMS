<img src="http://dev.webwork.fr/videa/git_logo2.png">
  
## What is VideaCMS

VideaCMS is content management system created for "videos makers" the main purpose is to provide a place where the "videos makers" can reunite their community and share content.
## How to install ?

Before installing you need PHP 7.2 and Composer

### Clone the repository

`git clone https://github.com/Nsbx/videa_cms.git`

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
