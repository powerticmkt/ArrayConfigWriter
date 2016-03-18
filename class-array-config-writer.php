<?php

/*
Copyright 2016 Wakeel Ogunsanya
Licensed under GPLv2 or above

Version 1.0.0
*/

class Array_Config_Writer {
    
    
    /**
     *
     * @var strng 
     */
    protected $_file ;
    
    /**
     *
     * @var string 
     */
    protected $_fileContent;

    
    /**
     *
     * @var string 
     */
    protected $_variable ;

    /**
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
     * 
     * @param string $config_file Asolute path to config file
     * @param string $variable_name the name of the config varible to update
     */
    public function __construct($config_file , $variable_name = '\$config' ) 
    {
        $this->_file = $config_file ;
        $this->setVariableName($variable_name );
        
        if ( ! file_exists($this->_file))
        {
            //throw new Exception('Config Write Error: Config file doesnt exists ' . $this->_file);
            $this->_lastError = 'Config Write Error: Config file doesnt exists ' . $this->_file ;
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
     * @param null|array $comments Comment to add to the top of item, each element
     *  will be placed oon a new line. *  is added before each line , meaning
     *  you dont have to put /** or *  unleass you want it as a comment
     *  
     * 
     * @return boolean
     * 
     */
    public function write( $index  = null, $replacement = null , $skip_if_exists = FALSE , $comments = null)
    {
        
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
           // add the comment 
            $comment_str = '/**' ;
            $comment_str .= "\n" ;
            if( $comments )
            {
                foreach ($comments as $line)
                {
                    $comment_str .= ' * ' . $line . "\n" ;
                }
            }
            // close the comment
            $comment_str .= '*/'  . "\n";
            
             // lets try remove traling slash from the variable name since 
            // we are writing php here 
            if ( substr($mark, 0 , 1) == '\\' )
            {
                $mark = substr($mark, 1 );
            }
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
        return $this;
    }

    /**
     * We can now update the file content
     * 
     * This allow calling Array_Config_Writer::write() as many times as required
     */
    public function __destruct() {
        file_put_contents( $this->_file , $this->_fileContent ) ;
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
