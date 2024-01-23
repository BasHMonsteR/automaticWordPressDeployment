# automaticWordPressDeployment
automated deployment process for a WordPress website using Nginx as the web server, LEMP (Linux, Nginx, MySQL, PHP) stack, and GitHub Actions with security best practices and ensure optimal performance of the website.
this guide helps you to setup automated deployment process for a WordPress website with major **cloud** providers / **locally**  

Server Provisioning:


    (I am using Google Cloud as the Cloud Provider)

    Provision an EC2 instance on GCP with a secure Linux distribution (e.g., Ubuntu 22.04).

    Configure firewall to allow necessary incoming traffic and restrict unnecessary access.

    Generate and configure SSH key pairs for secure remote access...
    you can continue below with your local machine/server. 


**Local -->** :
create a local directory for wordpress changes in our local system : 

        mkdir wordpress

open gitbash here if your OS is windows / open terminal here if your OS is Linux :

        git init  # to initilize directory 

it creates .git directory with all required configurations.
        add your email and user it to conf file inside .git directory.
        now add all your wordpress related files which you want to send to remore server in this directory and everytime do the changes in this directory or add changes in this directory.
        everytime you add a new change commit to git to perform deployment to remote server :
        create a new branch :

        git branch new-branch
        git checkout new-branch
        git add modified files (git add .# to add all modifies files) 
        git commit ( add commit message)
        git push

**VPS and locally -->** :

Nginx, MySQL/MariaDB, and PHP Setup on **VPS and locally**:
 
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

        ##Your website name goes here.
        server_name example.com;
        ##Your only path reference.
        root /var/www/wordpress;
        ##This should be in your http block and if it is, it's not needed here.


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

**VPS -->** :
**Create sftpuser** in server machine to send files securily to servser :

go to ubuntu machine **VPS** run below commands :

        sudo useradd -aG sftpuser  #create user with sftp only access
        id sftpuser  # verify user created or not 
        sudo vim /etc/ssh/sshd_config  # open sshd config files 
        search for Subsystem keyword in the file 
        after finding add below line under that Subsystem line

        Match  User sftpuser
        ForceCommand internal-sftp
        ChrootDirectory /var/www/
        AllowTcpForwarding no
        X11Forwarding   no

       save and close the file and restart the sshd service :

         sudo systemctl restart sshd

Generate id_rsa key files 

           ssh-keygen -t rsa -b 4096 -C "sftpuser"
above commad generates pub and prive key pair. store public key to machine and save private key to github secrets.      

**Github -->** :
Lets create a **github workflow** to create automatic deployment for our wordpress site on succeful commit to out main git branch. 
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
                
        - name: Install Composer dependencies
          uses: php-actions/composer@v6

        - name: Install Node.js LTS
            uses: actions/setup-node@v4.0.1
            with:
            node-version: 'lts/*'
            cache: 'yarn'

        - name: Install Node.js dependencies
            run: yarn install

        - name: Build theme
            run: yarn run build
            
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

        - name: Download artifact
         uses: actions/download-artifact@v4
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

Now, integrate automatic code standards testing to php files with php **code sniffer** : 
clikc on actions button on our git repo, click on  **set up a workflow yourself** button.
create a phpcs.yaml file

                name: "PHP code test "

                on:
                pull_request:
                    paths:
                    - "**.php"
                    - "phpcs.xml"
                    - ".github/workflows/phpcs.yml"

                jobs:
                phpcs:
                    runs-on: ubuntu-latest
                    steps:
                    - uses: actions/checkout@v2
                        with:
                        fetch-depth: 0 # important!

                    # we may use whatever way to install phpcs, just specify the path on the next step
                    # however, curl seems to be the fastest
                    - name: Install PHP_CodeSniffer
                        run: |
                        curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar
                        php phpcs.phar --version

                    - uses: thenabeel/action-phpcs@v8
                        with:
                        files: "**.php" # you may customize glob as needed
                        phpcs_path: php phpcs.phar
                        standard: phpcs.xml


save and commit chages.

**VPS -->** : 

LEMP (Linux, Nginx, MySQL, PHP) stack deployment completed with default and basic configurations.
Lets make optimize nginx performance :

lets turn off server_token in nginx config file to turn off server info in error page and stop click jacking attacks add this lines to nginx conf file.


        sudo vim /etc/nginx/nginx.conf

        server_tokens off;
        proxy_hide_header X-Powered-By;
        add_header X-Frame-Options SAMEORIGIN;                             # injection attacks
        add_header Content-Security-Policy "default-src 'self';" always;   #prevent css and injection attacks
        add_header X-Content-Type-Options "nosniff" always;                #prevent CSS
        proxy_hide_header X-Runtime;                                       #prevent timing bruteforce attack

add the following line in to 

    sudo vi /etc/nginx/sites-available/default

add below line in to server block :

        more_clear_headers Server;

if its hosted in cloud use the cloud provider's WAF (web application firewall), and for more protection and local users use the popular WAF like **modsecurity**.
we can implement Modsecurity opensource WAF for more protection like local file inclution, remote code execution 
To prevent form **owasp top 10** list highly recommanded to use WAF :

        Get the official link here : https://github.com/SpiderLabs/ModSecurity

build **modsecurity** and configure it to use nginx to protect from **owasp** top 10  

follow this link for reference : https://www.linode.com/docs/guides/securing-nginx-with-modsecurity/
run commands :
        sudo apt-get install bison build-essential ca-certificates curl dh-autoreconf doxygen \
        flex gawk git iputils-ping libcurl4-gnutls-dev libexpat1-dev libgeoip-dev liblmdb-dev \
        libpcre3-dev libpcre++-dev libssl-dev libtool libxml2 libxml2-dev libyajl-dev locales \
        lua5.3-dev pkg-config wget zlib1g-dev zlibc libxslt libgd-dev git

        cd /opt && sudo git clone https://github.com/SpiderLabs/ModSecurity

        cd ModSecurity

        sudo git submodule init

        sudo git submodule update

        sudo ./build.sh

        sudo ./configure

        sudo make

        sudo make install

**Downloading ModSecurity-Nginx Connector**  :

        cd /opt && sudo git clone --depth 1 https://github.com/SpiderLabs/ModSecurity-nginx.git

**Building the ModSecurity Module For Nginx**  :

        nginx -v
        nginx version: nginx/1.18.0 (Ubuntu)
        cd /opt && sudo wget http://nginx.org/download/nginx-1.18.0.tar.gz
        sudo tar -xvzmf nginx-1.18.0.tar.gz
        cd nginx-1.18.0
        nginx -V
        sample output :

            nginx version: nginx/1.18.0 (Ubuntu)
            built with OpenSSL 3.0.2 15 Mar 2022
            TLS SNI support enabled
            configure arguments: --with-cc-opt='-g -O2 -ffile-prefix-map=/build/nginx-zctdR4/nginx-1.18.0=. -flto=auto -ffat-lto-objects -flto=auto -ffat-lto-objects -fstack-protector-strong -Wformat -Werror=format-security -fPIC -Wdate-time -D_FORTIFY_SOURCE=2' --with-ld-opt='-Wl,-Bsymbolic-functions -flto=auto -ffat-lto-objects -flto=auto -Wl,-z,relro -Wl,-z,now -fPIC' --prefix=/usr/share/nginx --conf-path=/etc/nginx/nginx.conf --http-log-path=/var/log/nginx/access.log --error-log-path=/var/log/nginx/error.log --lock-path=/var/lock/nginx.lock --pid-path=/run/nginx.pid --modules-path=/usr/lib/nginx/modules --http-client-body-temp-path=/var/lib/nginx/body --http-fastcgi-temp-path=/var/lib/nginx/fastcgi --http-proxy-temp-path=/var/lib/nginx/proxy --http-scgi-temp-path=/var/lib/nginx/scgi --http-uwsgi-temp-path=/var/lib/nginx/uwsgi --with-compat --with-debug --with-pcre-jit --with-http_ssl_module --with-http_stub_status_module --with-http_realip_module --with-http_auth_request_module --with-http_v2_module --with-http_dav_module --with-http_slice_module --with-threads --add-dynamic-module=/build/nginx-zctdR4/nginx-1.18.0/debian/modules/http-geoip2 --with-http_addition_module --with-http_gunzip_module --with-http_gzip_static_module --with-http_sub_module

            To compile the Modsecurity module, copy all of the arguments following configure arguments: from your output of the above command and paste them in place of <Configure Arguments> in the following command:

            sudo ./configure --add-dynamic-module=../ModSecurity-nginx <Configure Arguments>

            sudo make modules
            sudo mkdir /etc/nginx/modules
            sudo cp objs/ngx_http_modsecurity_module.so /etc/nginx/modules

**load_module** /etc/nginx/modules/ngx_http_modsecurity_module.so;

            load_module /etc/nginx/modules/ngx_http_modsecurity_module.so;

            Here is an example portion of an Nginx configuration file that includes the above line:

            user www-data;
            worker_processes auto;
            pid /run/nginx.pid;
            include /etc/nginx/modules-enabled/*.conf;
            load_module /etc/nginx/modules/ngx_http_modsecurity_module.so;

To set up the **OWASP-CRS**, follow the procedures outlined below.

First, delete the current rule set that comes prepackaged with ModSecurity by running the following command:

                sudo rm -rf /usr/share/modsecurity-crs

Clone the OWASP-CRS GitHub repository into the /usr/share/modsecurity-crs directory:

                sudo git clone https://github.com/coreruleset/coreruleset /usr/local/modsecurity-crs

Rename the crs-setup.conf.example to crs-setup.conf:

                sudo mv /usr/local/modsecurity-crs/crs-setup.conf.example /usr/local/modsecurity-crs/crs-setup.conf

Rename the default request exclusion rule file:

                sudo mv /usr/local/modsecurity-crs/rules/REQUEST-900-EXCLUSION-RULES-BEFORE-CRS.conf.example /usr/local/modsecurity-crs/rules/REQUEST-900-EXCLUSION-RULES-BEFORE-CRS.conf

You should now have the OWASP-CRS set up and ready to be used in your Nginx configuration.
        
**Configuring Modsecurity**     
ModSecurity is a firewall and therefore requires rules to function. This section shows you how to implement the OWASP Core Rule Set. First, you must prepare the ModSecurity configuration file.

Start by creating a ModSecurity directory in the /etc/nginx/ directory:

        sudo mkdir -p /etc/nginx/modsec

Copy over the unicode mapping file and the ModSecurity configuration file from your cloned ModSecurity GitHub repository:

        sudo cp /opt/ModSecurity/unicode.mapping /etc/nginx/modsec
        sudo cp /opt/ModSecurity/modsecurity.conf-recommended /etc/nginx/modsec/modsecurity.conf

Remove the .recommended extension from the ModSecurity configuration filename with the following command:

     sudo cp /etc/modsecurity/modsecurity.conf-recommended /etc/modsecurity/modsecurity.conf

With a text editor such as vim, open /etc/modsecurity/modsecurity.conf and change the value for SecRuleEngine to On:

File: /etc/modsecurity/modsecurity.conf

            # -- Rule engine initialization ----------------------------------------------

            # Enable ModSecurity, attaching it to every transaction. Use detection
            # only to start with, because that minimises the chances of post-installation
            # disruption.
            #
            SecRuleEngine On
            ...

Create a new configuration file called main.conf under the /etc/nginx/modsec directory:

            sudo touch /etc/nginx/modsec/main.conf

Open /etc/nginx/modsec/main.conf with a text editor such as vim and specify the rules and the Modsecurity configuration file for Nginx by inserting following lines:

            File: /etc/modsecurity/modsecurity.conf

            Include /etc/nginx/modsec/modsecurity.conf
            Include /usr/local/modsecurity-crs/crs-setup.conf
            Include /usr/local/modsecurity-crs/rules/*.conf

**Configuring Nginx**      

            Open the /etc/nginx/sites-available/default with a text editor such as vim and insert the following lines in your server block:

                modsecurity on;
                modsecurity_rules_file /etc/nginx/modsec/main.conf;

Restart the nginx service to apply the configuration:

             sudo systemctl restart nginx

**Testing ModSecurity**       

Test ModSecurity by performing a simple local file inclusion attack by running the following command:

            curl http://<SERVER-IP/DOMAIN>/index.html?exec=/bin/bash

If ModSecurity has been configured correctly and is actively blocking attacks, the following error is returned:

                <html>
                <head><title>403 Forbidden</title></head>
                <body bgcolor="white">
                <center><h1>403 Forbidden</h1></center>
                <hr><center>nginx/1.14.0 (Ubuntu)</center>
                </body>
                </html>



#Change the default wp-admin login url to prevent attack from automated software attacks :
        

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

  wordpress plugin used for security : 

        change login url :     WPS Hide Login Plugin 
        wordpress firewall:    WordFence for WordPress
        WP-Optimize plugin 
        Query Monitor plugin
        
Modifing gzip compression values in nginx.conf files for better compression add below configs


        gzip on;
        gzip_disable "msie6";

        gzip_vary on;
        gzip_proxied any;
        gzip_comp_level 6;
        gzip_buffers 16 8k;
        gzip_http_version 1.1;
        gzip_min_length 256;
        gzip_types
            application/atom+xml
            application/geo+json
            application/javascript
            application/x-javascript
            application/json
            application/ld+json
            application/manifest+json
            application/rdf+xml
            application/rss+xml
            application/xhtml+xml
            application/xml
            font/eot
            font/otf
            font/ttf
            image/svg+xml
            text/css
            text/javascript
            text/plain
            text/xml;

save and close conf file, restart nginx to take effect using below command :

        sudo systemctl restart nginx

to verify new configs 

    curl -H "Accept-Encoding: gzip" -I http://localhost/test.css

    sample output : 
        HTTP/1.1 200 OK
        Server: nginx/1.18.0 (Ubuntu)
        Date: Tue, 09 Feb 2021 19:21:54 GMT
        Content-Type: text/css
        Last-Modified: Tue, 09 Feb 2021 19:03:45 GMT
        Connection: keep-alive
        Vary: Accept-Encoding
        ETag: W/"6022dc91-400"
        Content-Encoding: gzip
By modifying the number of worker connections, you can simultaneously manage the maximum number of links your server can handle

        worker_connections 1024;

setup Nginx FastCGI Page Cache With WordPress

        sudo vim /etc/nginx/sites-available/example.com.conf

At the top of the file, before the server block, add the following three directives. Some attributes can be configured to best meet the requirements of the site.

The fastcgi_cache_path directive specifies the location of the cache and the cache parameters.

The keys_zone attribute defines the wpcache cache and the size of the shared memory zone. 200m is enough space for over a million keys. In many cases, this can be set to a smaller size.

The max_size field indicates the maximum size of the actual cache on disk. This guide sets max_size to 10g. Feel free to choose a larger or smaller amount.

The inactive attribute tells NGINX when to purge data from the cache. This example uses a two-hour limit, indicated by inactive=2h. Cache contents that have not been accessed during this period are deleted.

The fastcgi_cache_key directive defines the key format.

fastcgi_ignore_headers disables the processing of certain response header fields that could adversely affect caching.        


        fastcgi_cache_path /etc/nginx/cache levels=1:2 keys_zone=wpcache:200m max_size=10g inactive=2h use_temp_path=off;
        fastcgi_cache_key "$scheme$request_method$host$request_uri";
        fastcgi_ignore_headers Cache-Control Expires Set-Cookie;

Include exceptions for any pages that must not be cached. Some examples of pages to bypass are the WordPress administration panel, cookies, session data, queries, and POST requests. When any of the following conditions are met, the temporary variable skip_cache is set to 1. Later on, this variable is used to inform NGINX not to search the cache or cache the new contents. Add the following lines inside the server block, immediately after the line beginning with index.

        set $skip_cache 0;

        if ($request_method = POST) {
            set $skip_cache 1;
        }
        if ($query_string != "") {
            set $skip_cache 1;
        }

        if ($request_uri ~* "/wp-admin/|/xmlrpc.php|wp-.*.php|^/feed/*|/tag/.*/feed/*|index.php|/.*sitemap.*\.(xml|xsl)") {
            set $skip_cache 1;
        }

        if ($http_cookie ~* "comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_no_cache|wordpress_logged_in") {
            set $skip_cache 1;
        }

Add the next set of directives to the block beginning with location ~ \.php$ beneath any pre-existing instructions. This configuration includes the following directive:

    The fastcgi_cache tells NGINX to enable caching. The name of the cache must match the name of the cache defined in the fastcgi_cache_path directive.

    fastcgi_cache_valid defines the cache expiry time for specific HTTP status codes.

    A handy NGINX attribute is the ability to deliver cached content when PHP-FPM or the database are unavailable. 

    fastcgi_cache_use_stale error defines the conditions where NGINX should serve stale content. In many cases, this is preferable to returning an error page to clients.

    fastcgi_cache_min_uses indicates how many times a page must be requested before it is cached. Setting this attribute to a larger value avoids caching rarely-used pages and can help manage the cache size.

    fastcgi_cache_lock tells NGINX how to handle concurrent requests.

    The fastcgi_cache_bypass and fastcgi_no_cache are assigned based on the value of skip_cache from the previous section. This tells NGINX not to search the cache and not to store any new content.

    The add_header instruction is used to add a header field indicating whether the resource is taken from the cache or not. This field is handy for debug purposes, but is not strictly required in production code.

        fastcgi_cache wpcache;
        fastcgi_cache_valid 200 301 302 2h;
        fastcgi_cache_use_stale error timeout updating invalid_header http_500 http_503;
        fastcgi_cache_min_uses 1;
        fastcgi_cache_lock on;
        fastcgi_cache_bypass $skip_cache;
        fastcgi_no_cache $skip_cache;
        add_header X-FastCGI-Cache $upstream_cache_status;

save and close the file

        sudo nginx -t
        sudo systemctl restart nginx        

to test cache :

    curl -I https://example.com/

    sample output:

        HTTP/1.1 200 OK
        Date: Mon, 22 Jan 2024 06:30:39 GMT
        Content-Type: text/html; charset=UTF-8
        Connection: keep-alive
        Vary: Accept-Encoding
        Link: <https://example.com/wp-json/>; rel="https://api.w.org/"
        X-FastCGI-Cache: **MISS**        

hit the same url again with curl : 

        curl -I https://example.com/
    sample output :

        HTTP/1.1 200 OK
        Date: Mon, 22 Jan 2024 06:30:43 GMT
        Content-Type: text/html; charset=UTF-8
        Connection: keep-alive
        Vary: Accept-Encoding
        Link: <https://example.com/wp-json/>; rel="https://api.w.org/"
        X-FastCGI-Cache: **HIT**

check cache bypassing for post requests : 

        curl -I https://example.com/post.php

        sample output:
        HTTP/1.1 200 OK
        Date: Mon, 22 Jan 2024 06:39:46 GMT
        Content-Type: text/html; charset=UTF-8
        Connection: keep-alive
        Vary: Accept-Encoding
        X-FastCGI-Cache: BYPASS

To support cache purging, install the following NGINX module :

        sudo apt install libnginx-mod-http-cache-purge  

In the search box on the upper right corner of the WordPress administration panel, type NGINX Helper and hit Enter. The Nginx Helper plugin is one of the top results on the first line of the plugins. Click the Install Now button beside it to install.          
The Nginx Helper plugin requires some further configuration. From the side navigation panel, click the Settings label, then select Nginx Helper.
On the Nginx Helper Settings page, select Enable Purge. After this option is enabled, the WordPress administration panel displays more options. Ensure the Caching Method is set to nginx Fastcgi cache. Select the Purging Conditions according to your preferences. In most cases, the default settings are appropriate. Select the Save All Changes button to confirm and save the selections.

        On the Nginx Helper Settings page, select Enable Purge. After this option is enabled, the WordPress administration panel displays more options. Ensure the Caching Method is set to nginx Fastcgi cache. Select the Purging Conditions according to your preferences. In most cases, the default settings are appropriate. Select the Save All Changes button to confirm and save the selections.
        Note :
                Debug Options are available near the bottom of the configuration page to enable logging and timestamps for easier troubleshooting.

Inside /etc/nginx/sites-available/example.com.conf, add the following lines to the server context. Add this block immediately after the location ~ \.php$ block.

        location ~ /purge(/.*) {
            fastcgi_cache_purge wpcache "$scheme$request_method$host$1";
        }   

validate conf file and restart nginx to load new changes : 

        sudo nginx -t
        sudo systemctl restart nginx

NGINX can run multiple worker processes, each capable of processing a large number of simultaneous connections. You can control the number of worker processes and how they handle connections with the following directives:

    worker_processes – The number of NGINX worker processes (the default is 1). In most cases, running one worker process per CPU core works well, and we recommend setting this directive to auto to achieve that. There are times when you may want to increase this number, such as when the worker processes have to do a lot of disk I/O.
    worker_connections – The maximum number of connections that each worker process can handle simultaneously. The default is 512, but most systems have enough resources to support a larger number. The appropriate setting depends on the size of the server and the nature of the traffic, and can be discovered through testing.

**Keepalive Connections**

**Keepalive connectionscan** have a major impact on performance by reducing the CPU and network overhead needed to open and close connections. NGINX terminates all client connections and creates separate and independent connections to the upstream servers. NGINX supports keepalives for both clients and upstream servers. The following directives relate to client keepalives:

**keepalive_requests** : – The number of requests a client can make over a single keepalive connection. The default is 100, but a much higher value can be especially useful for testing with a load‑generation tool, which generally sends a large number of requests from a single client.
    keepalive_timeout – How long an idle keepalive connection remains open.

The following directive relates to upstream keepalives:

**keepalive** – The number of idle keepalive connections to an upstream server that remain open for each worker process. There is no default value.

To enable **keepalive** connections to upstream servers you must also include the following directives in the configuration:

        proxy_http_version 1.1;
        proxy_set_header Connection "";        

**limit_conn and limit_conn_zone** – Limit the number of client connections NGINX accepts, for example from a single IP address. Setting them can help prevent individual clients from opening too many connections and consuming more than their share of resources.
**limit_rate** – Limits the rate at which responses are transmitted to a client, per connection (so clients that open multiple connections can consume this amount of bandwidth for each connection). Setting a limit can prevent the system from being overloaded by certain clients, ensuring more even quality of service for all clients.
limit_req and limit_req_zone – Limit the rate of requests being processed by NGINX, which has the same benefits as setting **limit_rate**. They can also improve security, especially for login pages, by limiting the request rate to a value reasonable for human users but too slow for programs trying to overwhelm your application with requests (such as bots in a **DDoS** attack).
**max_conns** parameter to the server directive in an upstream configuration block – Sets the maximum number of simultaneous connections accepted by a server in an upstream group. Imposing a limit can help prevent the upstream servers from being overloaded. Setting the value to 0 (zero, the default) means there is no limit.

**Local -->** : 

Add all created/modified changes into git repo to deploy to remote servers from local git directory. 
All wordpress files themes plugins etc can commit to git deploy to remote server.
every commit changes to wordpress site deploys automatically with github actions.