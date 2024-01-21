# automaticWordPressDeployment
automated deployment process for a WordPress website using Nginx as the web server, LEMP (Linux, Nginx, MySQL, PHP) stack, and GitHub Actions with security best practices and ensure optimal performance of the website.
this guide helps you to setup automated deployment process for a WordPress website with major **cloud** providers / **locally**  

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
    
Download and configure WordPress :

    wget -O /tmp/wordpress.tar.gz https://wordpress.org/latest.tar.gz 
    sudo tar -xzvf /tmp/wordpress.tar.gz -C /var/www
    sudo chown -R www-data.www-data /var/www/wordpress
    sudo cp /var/www/wordpress/wp-config-sample.php /var/www/wordpress/wp-config.php
    curl -s https://api.wordpress.org/secret-key/1.1/salt/


You will get back unique values that look something like this:

    define('AUTH_KEY',         '1jl/vqfs<XhdXoAPz9 DO NOT COPY THESE VALUES c_j{iwqD^<+c9.k<J@4H');
    define('SECURE_AUTH_KEY',  'E2N-h2]Dcvp+aS/p7X DO NOT COPY THESE VALUES {Ka(f;rv?Pxf})CgLi-3');
    define('LOGGED_IN_KEY',    'W(50,{W^,OPB%PB<JF DO NOT COPY THESE VALUES 2;y&,2m%3]R6DUth[;88');
    define('NONCE_KEY',        'll,4UC)7ua+8<!4VM+ DO NOT COPY THESE VALUES #`DXF+[$atzM7 o^-C7g');
    define('AUTH_SALT',        'koMrurzOA+|L_lG}kf DO NOT COPY THESE VALUES  07VC*Lj*lD&?3w!BT#-');
    define('SECURE_AUTH_SALT', 'p32*p,]z%LZ+pAu:VY DO NOT COPY THESE VALUES C-?y+K0DK_+F|0h{!_xY');
    define('LOGGED_IN_SALT',   'i^/G2W7!-1H2OQ+t$3 DO NOT COPY THESE VALUES t6**bRVFSD[Hi])-qS`|');
    define('NONCE_SALT',       'Q6]U:K?j4L%Z]}h^q7 DO NOT COPY THESE VALUES 1% ^qUswWgn+6&xqHN&%');

These are configuration lines that you can paste directly in your configuration file to set secure keys. Copy the output you received now.

        sudo vim /var/www/wordpress/wp-config.php 

Next, let’s modify some of the database connection settings at the beginning of the file. You’ll have to adjust the database name, the database user, and the associated password that was configured within MySQL.

                
        define( 'DB_NAME', 'wordpress' );

        /** MySQL database username */
        define( 'DB_USER', 'wordpressuser' );

        /** MySQL database password */
        define( 'DB_PASSWORD', 'password' );



        define( 'FS_METHOD', 'direct' );       

    
Configure Nginx to access the wordpress :

    sudo vim /etc/nginx/sites-available/wordpress  

put below code block in to config file 
    
# Upstream to abstract backend connection(s) for php

upstream php {
 server unix:/var/run/php/php8.1-fpm.sock;
 server 127.0.0.1:9000;

}

server {

 ## Your website name goes here.
 server_name example.com;
 ## Your only path reference.
 root /var/www/wordpress;
 ## This should be in your http block and if it is, it's not needed here.


 index index.php;
 location = /favicon.ico {
 log_not_found off;
 access_log off;

 }
 location = /robots.txt {
 allow all;
 log_not_found off;
 access_log off;

 }
 location / {
 # This is cool because no php is touched for static content.


 # include the "?$args" part so non-default permalinks doesn't break when using query string
 try_files $uri $uri/ /index.php?$args;
 }
 location ~ .php$ {
 #NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini
 include fastcgi_params;
 fastcgi_intercept_errors on;
 fastcgi_pass php;
 #The following parameter can be also included in fastcgi_params file
 fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
 }
 location ~* .(js|css|png|jpg|jpeg|gif|ico)$ {
 expires max;
 log_not_found off;
 }
         
   **save and exit the file**
   
    sudo rm /etc/nginx/sites-enabled/default 
    sudo ln -s /etc/nginx/sites-available/wordpress /etc/nginx/sites-enabled/wordpress
    sudo systemctl restart nginx
    
Visit public ip to see wordpress setup page :

    http://your_server_ip       

Secure Nginx with Let's Encrypt. lets limit **MITM** attacks:
Regester or get domain name of your choice. This guide will use **example.com** throughout
i am using https://www.noip.com/ for DNS which is free within 30 days 
created **A** record with **example.com** pointing to your server’s **public IP** address.

will use certbot for this activity : 

    sudo apt install certbot python3-certbot-nginx

verify nginx conf file with below command (it should complete without any errors) :

    sudo nginx -t         

reload nginx : 

    sudo systemctl reload nginx

update the firewall settings ufw :

    sudo ufw allow 'Nginx Full'
    sudo ufw delete allow 'Nginx HTTP'  
    sudo ufw status

generate certs with certbot :
    
    sudo certbot --nginx -d example.com -d www.example.com

Now your wordpress website should be run on https with let's encrypt encryption.

Lets create a github workflow to create automatic deployment for our wordpress site on succeful commit to out main git branch. 
clikc on actions button on our git repo, click on  **set up a workflow yourself** button.
create a deployment.yaml file

this deployment file i am trying to build themes by installing dependacies and building then after that i am syncing the directory with rsync utility to sych with my wordpress site location files and send securly.


        name: build-deploy
    on:
    push:
        branches:
        - main
        paths-ignore:
        - 'bin/**'
        - 'README.md'

    jobs:
    build:
        runs-on: ubuntu-latest
        steps:
        - name: Checkout
            uses: actions/checkout@v4.1.1
                
        #- name: Install Composer dependencies
        # uses: php-actions/composer@v6

        - name: Install Node.js LTS
            uses: actions/setup-node@v4.0.1
            with:
            node-version: 'lts/*'
            cache: 'yarn'

        - name: Install Node.js dependencies
            run: yarn install

        #- name: Build theme
            #run: yarn run build
            
        - name: Upload artifact
            uses: actions/upload-artifact@v4.2.0
            with:
            name: my-theme-build
            path: |
                dist/
                vendor/
            retention-days: 1

    deploy:
        runs-on: ubuntu-latest
        needs: build
        steps:
        - name: Checkout
            uses: actions/checkout@v4.1.1
            with:
            fetch-depth: 0

        #- name: Download artifact
        # uses: actions/download-artifact@v4
            #with:
            # name: my-theme-build
            #path: .

        - name: Sync
            env:
            dest: '${{secrets.SERVER_USER}}@${{secrets.SERVER}}:${{secrets.SERVER_PATH}}'
            run: |
            echo "${{secrets.DEPLOY_KEY}}" > deploy_key
            chmod 600 ./deploy_key
            rsync -chav --delete \
                -e 'ssh -i ./deploy_key -o StrictHostKeyChecking=no' \
                --exclude /deploy_key \
                --exclude /.git/ \
                --exclude /.github/ \
                ./ ${{env.dest}}

On succesful commit to main branch deploment will happen to server, dont forget to creat github secret variable with scp user name and id_rsa kay and host name. Becareful with location as rsync mirrors the dest and src so it might delete files.

LEMP (Linux, Nginx, MySQL, PHP) stack deployment completed with default and basic configurations.
Lets make optimize nginx performance :

lets turn off server_token in nginx config file to turn off server info in error page and stop click jacking attacks add this lines to nginx conf file.


        sudo vim /etc/nginx/nginx.conf

        server_tokens off;
        proxy_hide_header X-Powered-By;
        add_header X-Frame-Options SAMEORIGIN;          # injection attacks
        add_header Content-Security-Policy "default-src 'self';" always;   #prevent css and injection attacks
        add_header X-Content-Type-Options "nosniff" always;  #prevent CSS
        proxy_hide_header X-Runtime; #prevent timing bruteforce attack

add the following line in to 

    sudo vi /etc/nginx/sites-available/default

add below line in to server block :

        more_clear_headers Server;

if its hosted in cloud use the cloud provider's WAF (web application firewall), and for more protection and local users use the popular WAF like modsecurity.
we can implement Modsecurity opensource WAF for more protection like local file inclution, remote code execution 
To prevent form owasp top 10 list highly recommanded to use WAF :

        Get the official link here : https://github.com/SpiderLabs/ModSecurity
Change the default wp-admin login url to prevent attack from automated software attacks
        

        Add constant to wp-config.php :

        define('WP_ADMIN_DIR', 'secret-folder');  
        define( 'ADMIN_COOKIE_PATH', SITECOOKIEPATH . WP_ADMIN_DIR);  

        Add below filter to functions.php :

        add_filter('site_url',  'wpadmin_filter', 10, 3);  

        function wpadmin_filter( $url, $path, $orig_scheme ) {  
            $old  = array( "/(wp-admin)/");  
            $admin_dir = WP_ADMIN_DIR;  
            $new  = array($admin_dir);  
            return preg_replace( $old, $new, $url, 1);  
        }

        Add below line to .htaccess file :

        RewriteRule ^secret-folder/(.*) wp-admin/$1?%{QUERY_STRING} [L]

to makeit simple wordpress plugin also available : 

        change login url :     WPS Hide Login Plugin 
        wordpress firewall:    WordFence for WordPress





