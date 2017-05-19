## TODO Puko Framework

### Features
* Adding support for another Database Connection (Oracle, SQL Server).
* Adding PDO database tweaks for commit and rollback database transaction.
* Adding support for Exception in Template Engine.
```PHP
{!!PukoException}
  {!Messages}
{/PukoException}
```

### Puko Doc Command (PDC)
* Adding #User Authentication true
* Adding #View Template template/directory/master.html
* Adding #View Template false
* Adding #View Template null
* Adding #Date before dd-mm-yyyy
* Adding #Date after dd-mm-yyyy
* Adding #Date bettwen dd-mm-yyyy dd-mm-yyyy
* Adding #Value PageTitle judulnya boleh pake spasi
* Adding #Value Key Value
* Adding #Throws Exception true
* Adding #Validation name required,number,min[30],max[40] ??
* Adding #....

### Security
* Adding CSRF protection.
* Sanitize input and output and eliminates html character.
* Sanitize URL routing params.
* Adding feature to handle sql injections.

### Modules
* Adding feature import export Excel, Word, PDF.
* Adding Mail utility.

### Command Line
* CLI based database setup guide [puko setup db]
* CLI based encryption setup guide [puko setup secure]
* CLI based model setup [puko model modelname]
* CLI based modules setup