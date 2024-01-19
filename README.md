# automaticWordPressDeployment
automated deployment process for a WordPress website using Nginx as the web server, LEMP (Linux, Nginx, MySQL, PHP) stack, and GitHub Actions with security best practices and ensure optimal performance of the website.
this guide helps you to setup automated deployment process for a WordPress website with major **cloud** providers /** locally**  

Server Provisioning:


    (I am using Google Cloud as the Cloud Provider)

    Provision an EC2 instance on GCP with a secure Linux distribution (e.g., Ubuntu 22.04).

    Configure firewall to allow necessary incoming traffic and restrict unnecessary access.

    Generate and configure SSH key pairs for secure remote access...
    you can continue below with your local machine/server. 

Nginx, MySQL/MariaDB, and PHP Setup:
 
Brefore going to setup update the system:

    sudo apt-get update && sudo apt-get upgrade
    
Installing NGINX and starting NGINX service at startup:

    sudo apt install nginx
    sudo systemctl enable nginx
    sudo systemctl start nginx
    sudo systemctl status nginx
    
If firewall is active/going to enable we need to allow NGINX with ufw :

    sudo ufw app list
    sudo ufw allow 'Nginx HTTP'
    sudo ufw allow 'Nginx HTTPS'
    sudo ufw allow ssh
    sudo ufw status
    sudo ufw enable
    
restart nginx :    

    sudo systemctl restart nginx
    
Visit public ip to see nginx welcome page :

    http://your_server_ip    

Install and configure mysql server :

    sudo apt install mysql-server 
    sudo mysql_secure_installation
    sudo systemctl restart mysql
    sudo systemctl enable mysql
    
If you enabled ufw allow traffic to mysql on its default port 3306 : 
    
        sudo ufw allow from any to any port 3306 proto tcp
        
change root password and create user for wordpress :   
    
        sudo mysql
        ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password by 'Testpassword@123';
        CREATE DATABASE wp;
        CREATE USER 'wp_user'@localhost IDENTIFIED BY 'Testpassword@123';
        GRANT ALL PRIVILEGES ON wp.* TO 'wp_user'@localhost;
        FLUSH PRIVILEGES;
        exit;
        
Install and setup PHP :

    sudo apt install php-fpm php-mysql php php-curl php-gd php-intl php-zip 
    sudo systemctl start php8.1-fpm   //8.1 version 
    sudo systemctl enable php8.1-fpm
    
Download and install WordPress :

    wget -O /tmp/wordpress.tar.gz https://wordpress.org/latest.tar.gz 
    sudo tar -xzvf /tmp/wordpress.tar.gz -C /var/www
    sudo chown -R www-data.www-data /var/www/wordpress
    
Configure Nginx to access the wordpress :

    sudo vim /etc/nginx/sites-available/wordpress    
put below code block in to config file 
    
    server {
                listen 80;
                listen [::]:80;

                root /var/www/wordpress;

                index index.php;

                server_name _;

                location / {
                        try_files $uri $uri/ =404;
                }

                location ~ \.php$ {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
                }
            }   
         
   ** save and exit the file**
   
    sudo rm /etc/nginx/sites-enabled/default 
    sudo ln -s /etc/nginx/sites-available/wordpress /etc/nginx/sites-enabled/wordpress
    sudo systemctl restart nginx
    
Visit public ip to see wordpress setup page :

    http://your_server_ip       

