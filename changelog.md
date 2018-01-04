<img align="left" src="https://github.com/Velliz/pukodocs/blob/gh-pages/icon/material/puko-material-50.png">

## Puko Framework Changelog

**The 0.x.x version is not released because it's not production ready**

### 0.1.0-beta (14 October 2015)
* Creating Puko Template Engine (PTE).

### 0.2.0-beta (26 February 2016)
* Creating static PDO Database Connection as DBI class.

### 0.3.0-beta (1 March 2016)
* Creating URL REST Routing style.
* Host code on GitHub repository.

### 0.4.0-beta (17 March 2016)
* Creating Micro Model-View style code and Class Autoloader PSR-0.

### 0.5.0-beta (15 April 2016)
* Creating support for Combining URL REST and Puko Template Engine (PTE).

### 0.6.0-beta (22 April 2016)
* Creating more human-readable error message.

### 0.7.0-beta (29 April 2016)
* Updating PDO Database Delete Function.
* Fix Controller constructor id variable value empty.

### 0.8.0-beta (3 May 2016)
* Creating css and js template renderer feature.
* Repositioning view directory structure.

### 0.8.1-beta (5 May 2016)
* Update PDO to handling BLOB data objects and Date objects.

### 0.8.2-beta (9 May 2016)
* Create different output type in Controller.
```PHP
class Example extends View {
```
```PHP
class Example extends Service {
```
* Create 404 Not Found Pages handling.

### 0.9.0-beta (12 May 2016)
* Adding Puko Session.
* Creating support for master .html template.

### 0.9.1-beta (15 May 2016)
* Updating framework from Micro Model-View to (MVC) pattern.
* Creating variable dump and development mode options.
* Optimizing code in Abstract Parser and Puko Template Engine (PTE).
* Creating RedirectTo function in Controller.

### 0.9.2-beta (28 May 2016)
* Remove Session and Creating Encrypted Cookies Support.
* Adding dynamic url in Puko Template Engine (PTE) with /ref/
* Multiple Language Support en & id

### 0.9.3-beta (11 June 2016)
* Cleanup Code and Change directory name to lowercase.
* Creating Puko Doc Command (PDC).
* Adding Value, Date, User Puko Doc Command (PDC).
* Set minimum requirement to PHP 5.6 for security.

**First release begin**

### 0.9.4-RC (7 July 2016)
* Customize PHP error handler.
* Optimize PDC loop tag.
* Bugfix.

**Stable release begin** 

### 1.0.0 (21 October 2016)
* Initial Release, Add license and author information.
* Bugfix.

### 1.0.1 (30 October 2016)
* Auth feature for PDC.
* ClearOutput feature for PDC.
* Session Expire Time.
* Bugfix (Puko Framework Error on PHP 7.0.11).

### 1.0.2 (12 December 2016)
* Controller & view file group in directory.
* System Exception & Error now can rendered to system html.
* DisplayException feature for PDC.
* General bugfix and Puko fatal Error codes.
* ValueException Handler
* Error & Global Exception Handler

### 1.0.3 (12 May 2017)
* Adding Controller on initialize.
* Separate Master feature for PDC.
* Bugfix in Framework and PDC.
* Add Framework official icon.

### 1.1.0 (12 July 2017)
* Introduce new Router system.
* Introduce new Exception Handler system.
* Introduce new Puko Command Line Interface (PCLI).
* Adding Scaffolding for models.

### 1.1.1 (2 December 2017)
* Introduce Permissions.
* Introduce Router Generator.

### 1.1.2 (23 December 2017)
* Introduce new template engine.
* Introduce view elements.

### 1.1.3 (TBA 2018)
* Renamed Permission into Role.
* Add permission into model.
* Enhance model.