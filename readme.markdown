Interlude
=========

This project is being shelved permanently. When I first began thinking about it, PHP Frameworks were uncommon. However, now that there are plenty -- many which duplicate a lot of the aims of this project -- I do not see a reason to proceed at this time. Perhaps in the future, there will be a great enough need to dust this project off and continue it. For now, it is here for the community.

Most of the work has been conceptual, and those concepts have changed as internet technologies have evolved, but this project was never updated frequently enough. In some ways, it is still a relic of PHP back when OO programming and MVC structure were relatively uncommon in the popular PHP scripts. But that has not changed the conceptual aims of this project, only its implementation.

System Requirements
-------------------
 * PHP 5.1.2 or greater
 * MySQL 5 (currently)

Installation
------------
 1. Copy the files to a web-accessible directory (e.g. your public_html directory)
 2. The app folder can be moved outside of the web root. If so,
    * Edit index.php to change the path to autoload.php
 3. Set the following directories to be writeable by apache (generally unnecessary)
    * app/cache/
    * media/images/
    * media/uploads/ (optional: this is only if you allow uploads)
 4. Open the site in a web browser (http://yoursite.com/) and follow the installation
