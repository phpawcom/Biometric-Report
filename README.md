# Biometric-Report
This is very basic class to read from "IN01 A Biometric Access Control Terminal" access database. Class will generate a report for a specific period of time contains total presence hours of specific employee. This class is tested only with the mentioned device and might need some modifications on other devices that use "Microsoft Access" databases.

##### How to use the class:
- Configure database connect. In this case to add file path, username and password (if applicable)
- Specify the selector field (preferably primary or unique key) that to use to fetch employee records
- Using generated array to show the report!

##### Example:
```
error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE); ## To make sure to see errors if occur
$biometric = new biometric('C:/Inetpub/vhosts/testdomain.com/mdb/dbTimeManager.demo.mdb', 'username', 'password');
echo '<pre>'.print_r($biometric->readUserData('Badgenumber', 131)->getDaysList(), true).'</pre>'; ## Get records per day, all records since started
echo '<pre>'.print_r($biometric->readUserData('Badgenumber', 131, array('2016-06-20', '2016-06-23'))->getDaysList(), true).'</pre>'; ## Get records per day, records for specific period
echo '<h3>Total: '.$biometric->readUserData('Badgenumber', 131)->getTotalHours(0).' </h3>'; ## Get Total working hours, to see decimal edit getTotalHours(0) to getTotalHours(2) or any number
```

##### Notes
- This is  a beta version and not supported
- Class uses PDO ODCB extenison
- Date range format must be 'yyyy-mm-dd' to make sure to get correct results
- In case you have another device using SQL Server, would be happy to modifiy the class to extend its range
