# CraptionReporter-Server
Tutorial create CraptionReporter's server side classes and databases

# Attention
on shared hosts, auto retrace not work because API can't call exec() in php and retrace.jar not run
but stacktrace will save in table and you can retraced it manually!

# Version
compatible with android library version >= 1.6.6

# Step 1
```
create database (for example: 'craptionreporter_db')
craete table like 'reports.sql'
```

# Step 2
```
put 'craptionreporter' folder in your public_html
```

# Step 3
```
open 'craptionreporter' in your public_html and edit 'DBConfig.php'.
set your db name, user and pass
```

# Step 4
```
put your app's release mapping files in 'mappings' directory
rename your mapping files into this: 'mapping-[versionCode].txt'
(for example: 'mapping-1.txt')
```
