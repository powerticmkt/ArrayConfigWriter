# array-config-writer
Write and update config item. This is a library is inspired by a Code Igniter project where there is a need to permanently update the configuration instead of setting configuration in every request.

for instance, some of the code igniter config file has $config array with different key and value pair as options.
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

This can be found under the [PATHTOSITEFILES]/application/config/config.php
