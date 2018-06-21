VideaCMS
=======

## What is VideaCMS

VideaCMS is content management system created for "videos maker" the main purpose is to provide a place where the "video maker" can reunite her community and share content.
## How to install ?

Before installing you need PHP 7.1 and Composer

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
