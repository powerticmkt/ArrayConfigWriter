# array-config-writer
Write and update config item. This library was inspired by a CodeIgniter project when there was a need to permanently update the configuration values instead of setting configuration in every request.

for instance, some of the codeigniter config file has $config array with different key and value-pairs as options.

lets say we want to update the default language of the config 

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | This determines which set of language files should be used. Make sure
    | there is an available translation if you intend to use something other
    | than english.
    |
    */
    $config['language']	= 'english';
    
    $config['compress_output']	= false ;

Lets say the path to config file is  [PATH_TO_SITE_FILES]/config/config.php

//include the Writer

    require_once PATH_TO_SITE_FILES . 'libraries/array_config_writer/class-array-config-writer.php';

    $congig_writer = new Array_Config_Writer( '[PATH_TO_SITE_FILES]/config/config.php' , 'config' );

    $congig_writer->write( 'language' , 'french' ) ;

And thats all. The value of $config['language']	in the [PATH_TO_SITE_FILES]/config/config.php will be updated to 'french'

now  [PATH_TO_SITE_FILES]/config/config.php is 
  /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | This determines which set of language files should be used. Make sure
    | there is an available translation if you intend to use something other
    | than english.
    |
    */
    $config['language']	= 'english';
    
    
   
    

WHat if the $config array in '[PATH_TO_SITE_FILES]/config/config.php file does not have 'language' index?
 -Well the the writer will create a new index of 'language'

if you set the third parameter of write() method to true and the 'language' index already exists the writer will skip updating the index value. This can be useful on application upgrades

Awesome right?


Features 

=> Method chaining is allowed 

    $congig_writer->write( 'language' , 'french' )->write('compress_output' , true ) ;


    
=> if your config varible is multi dimentional array like 
    $db['default']['hostname'] = 'localhost' 
    
You can update the 'hostname' index like this 
 1 . You either instatiate new class 
 
     $congig_writer = new Array_Config_Writer( '[PATH_TO_SITE_FILES]/config/database.php' , 'db' );
  
     $congig_writer->write( array( default' , 'hostname' ) , 'remotehost' );
   
or 

2. Update the config variable name for the config writer if  '[PATH_TO_SITE_FILES]/config/config.php' has  $db['default']['hostname'] index 
    
    $congig_writer->setVariableName('db');
    $congig_writer->write( array( default' , 'hostname' ) , 'remotehost' );

Now every write() call will search for $db variable 





   

