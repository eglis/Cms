# Cms Module
A simple Cms module for handle the content of the pages for ShineISP 2.0

## Prerequisites
You will need:
* [Git](http://git-scm.com/)
* [Composer](https://getcomposer.org/)
* [ShineISP 2](http://www.shineisp.com)

## Apache Configuration

Now we have to tell to our Apache webserver that we have a new site called shineisp.it (the tld could be changed properly as you like)
Open the /etc/hosts file and type:

    127.0.0.1 shineisp.it www.shineisp.it

Create a new file by your preferite text editor I am using the "nano":

    sudo nano /etc/apache2/sites-available/shineisp.conf

and paste this text:

	<VirtualHost *:80>
		ServerName shineisp.it
		ServerAlias www.shineisp.it
		DocumentRoot /var/www/PROJECT-FOLDER/public
		SetEnv APPLICATION_ENV "development"
		<Directory /var/www/PROJECT-FOLDER/public>
		    DirectoryIndex index.php
		    AllowOverride All
		    Order allow,deny
		    Allow from all
		</Directory>
	</VirtualHost>

and then enable the website configuration by this shell command:

    sudo a2ensite shineisp

and reload the Apache configuration by this shell command:

    service apache2 reload

## Getting Started
To get the application running, perform the following steps:

1. Create a new application by Zend Framework 2 cloning the [Skeleton](http://framework.zend.com/manual/current/en/user-guide/skeleton-application.html).
2. After creation, paste the following JSON into the "composer.json" text file within the repositories section:

```json
    [
        {
            "type": "vcs",
            "url": "https://github.com/shineisp/Cms"
        }
    ],
```
3. Run the following commands from the console:

  ```bash
  cd /var/www/YOUR-INSTALLATION-PATH
  composer update
  ```

4. Navigate to [www.your-shineisp-domain.com].

5. Hooray! Now open your preferite browser and type: http://www.shineisp.it/ you will see the standard Zend Framework page! Now you can see the module in action! How simple was that??
