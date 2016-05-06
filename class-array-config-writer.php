<?php

/*
Copyright 2016 Wakeel Ogunsanya
Licensed under GPLv2 or above

Version 1.1.0
*/

class Array_Config_Writer {
    
    
    /**
     * The config file to write
     * 
     * set during with class construct
     * @var strng 
     */
    protected $_file ;
    
    /**
     * Content read from file
     * @var string 
     */
    protected $_fileContent;

    
    /**
     * The variable to search for in the file
     * 
     * eg if we can to update 'option_name' value in a file with
     * $config['option_name'] array, the variable is 'config'
     * 
     * @note we assume each option index is on seperate line like 
     *  $config['option_name_one']  = 'one' ;
     *  $config['option_name_two']  = 'two' ;
     * 
     * if you have array like $config['options'] = array(
     *                                      'option_name_one' => 'one',
     *                                      'option_name_two' => 'two'
     *                                 );
     * You have to first get $config['options'] in variable then manually update 
     * the elements, after which you will can use this config writer to update the
     * $config['options'] element
     * 
     * @var string 
     */
    protected $_variable ;

    /**
     * The target index of the variable
     * 
     * @var string 
     */
    protected $_index ;
    
    /**
     *
     * @var string 
     */
    protected $_replacement;
    
    /**
     *
     * @var string 
     */
    protected $_lastError;
    
    
    /**
     * This option determines whether the file should be updated automatically
     * 
     * if set to false, you will manually call save() method after you 
     * have done your writing with write() method
     * 
     * @since 1.1.0
     * @var boolean 
     */
    protected $_autoSave = true ;


    /**
     * 
     * @param string $config_file Asolute path to config file
     * @param string $variable_name the name of the config varible to update
     */
    public function __construct($config_file , $variable_name = '\$config' , $auto_save = true ) 
    {
        $this->_file = $config_file ;
        $this->_autoSave = $auto_save ;
        $this->setVariableName($variable_name );
        
        if ( ! file_exists($this->_file))
        {
            //throw new Exception('Config Write Error: Config file doesnt exists ' . $this->_file);
            $this->_lastError = 'Config Write Error: Config file doesnt exists ' . $this->_file ;
        
            return ;
        }
        if ( ! $variable_name)
        {
            $this->_lastError = 'You must set the set parameter of the library construct has varible to update' ;
            return ;
        }
        
        $this->_fileContent =  file_get_contents( $this->_file ) ;
        
    }
    
    
    /**
     * Wite or update an item of the config array
     * 
     * @param string|array $index To update 'language' index  $config['language']
     *  This will be string 'language' 
     *  To update hostname' of $db['default']['hostname'] , then index
     *  will be array( 'default' , 'hostname' )
     *
     * @param mixed $replacement
     * @param boolean $skip_if_exists Skip updating item if already exists
     * @param null|array $comments Comment to add to the top of item (new item), each element
     *  will be placed oon a new line. *  is added before each line , meaning
     *  you dont have to put /** or *  unless you want it show 
     * 
     * @note you can not update existing item comment
     *  
     * 
     * @return boolean
     * 
     */
    public function write( $index  = null, $replacement = null , $skip_if_exists = FALSE , $comments = null)
    {
        // error exists in the constructor?
        if($this->_lastError)
        {
            return $this;
        }
        
        if(!$index)
        {
            $this->_lastError = 'You must set index you want to update' ;
            return $this;
        }
        $prefix = $this->_variable  ;
        
        $regex = '#(' . $prefix  . ')(';
        // add a mark in case config item doesnt exists
        $mark = "{$prefix}" ;
        // we can update multi dementional
        $indece = is_array($index)? $index : array( $index ) ;
       
        foreach ( $indece as $index => $i)
        {
            $regex .= '\[\s*(\'|\")(' . $i . ')*(\'|\")\s*\]' ;
            $mark .= "['{$i}']" ;
        }
        // closing
        $regex .= ')\s*=[^\;]*#' ; 
        $mark .= " = ";
        
        if(preg_match($regex, $this->_fileContent)){
            
            // well config aleady exists 
            // may be is application upgrade :) we wouldnt wana overide user settings 
            if( $skip_if_exists  )
            {
                return $this;
            }
            // update th content
            $this->_fileContent = preg_replace(   $regex ,  '$1$2 = ' .  var_export( $replacement , true ) , $this->_fileContent   ) ;
        }
        else{
            // new item here 
            $mark .= var_export( $replacement , true ) . ' ;' ;
            $mark .= "\n" ;
           
            if(is_array($comments) && count($comments) > 0 )
            {
                // add the comment 
                $comment_str = '/**' ;
                $comment_str .= "\n" ;
                foreach ($comments as $line)
                {
                    $comment_str .= ' * ' . $line . "\n" ;
                }
                // close the comment
                $comment_str .= '*/'  . "\n";
            }
            
             // lets try remove traling slash from the variable name since 
            // we are writing php here 
            if ( substr($mark, 0 , 1) == '\\' )
            {
                $mark = substr($mark, 1 );
            }
            // we have updated the index, the file will save automatically in the class shutdwon
            // we did this allow update multiple index by call write() method as manay times 
            // as required
            $this->_fileContent = $this->_fileContent . "\n" . $comment_str . "\n"  . $mark . "\n" ;
        }
        return $this;
    }
    
    /**
     * Update the variable name of the array config
     * 
     * @param string $name
     */
    public function setFilename($name = null)
    {
        $this->_file = $name;
    }
    
    
    /**
     *  Set the config varible name
     * // actually we expect someting like '\$config' but user migt just provide 'config'
     * 
     * @param string $name
     */
    public function setVariableName($name = null)
    {
       
       
        if(substr($name, 1, 1 ) != '$')
        {
            $name = '$' . $name ;
        }
         if(substr($name, 0 , 1 ) != '\\')
        {
            $name = '\\' . $name ;
        }
        
        $this->_variable = $name;
    }

    /**
     * We can now update the file content
     * 
     * if the _autosave property is true, we auto updte file
     * 
     */
    public function __destruct() 
    {
        if($this->_autoSave)
        {
            $this->save();
        }
    }
    
    /**
     * Save the new content to file 
     * 
     * You can call Array_Config_Writer::write() as many times as required
     * before calling this method
     * 
     * @return \Array_Config_Writer
     */
    public function save()
    {
        if(!$this->_lastError)
        {
            file_put_contents( $this->_file , $this->_fileContent ) ;
        }
        return $this;
    }

    
    /**
     * Get last error that occured
     * 
     * @return string
     */
    public function getLastError()
    {
        return $this->_lastError;
    }
}
